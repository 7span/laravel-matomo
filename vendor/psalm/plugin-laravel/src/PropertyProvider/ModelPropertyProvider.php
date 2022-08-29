<?php

namespace Psalm\LaravelPlugin\PropertyProvider;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use PhpParser;
use Psalm\Context;
use Psalm\CodeLocation;
use Psalm\Type;
use Psalm\StatementsSource;
use function in_array;
use function str_replace;

class ModelPropertyProvider implements
    \Psalm\Plugin\Hook\PropertyExistenceProviderInterface,
    \Psalm\Plugin\Hook\PropertyVisibilityProviderInterface,
    \Psalm\Plugin\Hook\PropertyTypeProviderInterface
{
    /** @return array<string, string> */
    public static function getClassLikeNames() : array
    {
        return \Psalm\LaravelPlugin\Plugin::$model_classes;
    }

    /**
     * @return ?bool
     */
    public static function doesPropertyExist(
        string $fq_classlike_name,
        string $property_name,
        bool $read_mode,
        StatementsSource $source = null,
        Context $context = null,
        CodeLocation $code_location = null
    ) {
        if (!$source || !$read_mode) {
            return null;
        }

        $codebase = $source->getCodebase();

        $class_like_storage = $codebase->classlike_storage_provider->get($fq_classlike_name);

        if (self::relationExists($codebase, $fq_classlike_name, $property_name)) {
            return true;
        }

        if (self::accessorExists($codebase, $fq_classlike_name, $property_name)) {
            return true;
        }

        if (isset($class_like_storage->pseudo_property_get_types['$' . $property_name])) {
            return null;
        }

        return null;
    }

    /**
     * @return ?bool
     */
    public static function isPropertyVisible(
        StatementsSource $source,
        string $fq_classlike_name,
        string $property_name,
        bool $read_mode,
        Context $context,
        CodeLocation $code_location = null
    ) {
        if (!$read_mode) {
            return null;
        }

        $codebase = $source->getCodebase();
        $class_like_storage = $codebase->classlike_storage_provider->get($fq_classlike_name);

        if (self::relationExists($codebase, $fq_classlike_name, $property_name)) {
            return true;
        }

        if (self::accessorExists($codebase, $fq_classlike_name, $property_name)) {
            return true;
        }

        if (isset($class_like_storage->pseudo_property_get_types['$' . $property_name])) {
            return null;
        }

        return null;
    }

    /**
     * @param  array<PhpParser\Node\Arg>    $call_args
     *
     * @return ?Type\Union
     */
    public static function getPropertyType(
        string $fq_classlike_name,
        string $property_name,
        bool $read_mode,
        StatementsSource $source = null,
        Context $context = null
    ) {
        if (!$source || !$read_mode) {
            return null;
        }

        $codebase = $source->getCodebase();

        if (self::relationExists($codebase, $fq_classlike_name, $property_name)) {
            $methodReturnType = $codebase->getMethodReturnType($fq_classlike_name . '::' . $property_name, $fq_classlike_name);
            if (!$methodReturnType) {
                return Type::getMixed();
            }

            /** @var \Psalm\Type\Union|null $modelType */
            $modelType = null;
            /** @var \Psalm\Type\Atomic\TGenericObject|null $relationType */
            $relationType = null;

            // In order to get the property value, we need to decipher the generic relation object
            foreach ($methodReturnType->getAtomicTypes() as $atomicType) {
                if (!$atomicType instanceof Type\Atomic\TGenericObject) {
                    continue;
                }

                $relationType = $atomicType;

                foreach ($atomicType->getChildNodes() as $childNode) {
                    if (!$childNode instanceof Type\Union) {
                        continue;
                    }
                    foreach ($childNode->getAtomicTypes() as $atomicType) {
                        if (!$atomicType instanceof Type\Atomic\TNamedObject) {
                            continue;
                        }

                        $modelType = $childNode;
                        break 3;
                    }
                }
            }

            $returnType = $modelType;

            $relationsThatReturnACollection = [
                BelongsToMany::class,
                HasMany::class,
                HasManyThrough::class,
                MorphMany::class,
                MorphToMany::class,
            ];

            if ($modelType && $relationType && in_array($relationType->value, $relationsThatReturnACollection)) {
                $returnType = new Type\Union([
                    new Type\Atomic\TGenericObject(Collection::class, [
                        $modelType
                    ]),
                ]);
            }

            return $returnType ?: Type::getMixed();
        }

        if (self::accessorExists($codebase, $fq_classlike_name, $property_name)) {
            return $codebase->getMethodReturnType($fq_classlike_name . '::get' . str_replace('_', '', $property_name) . 'Attribute', $fq_classlike_name)
                ?: Type::getMixed();
        }
    }

    /**
     * @param \Psalm\Codebase $codebase
     * @param string $fq_classlike_name
     * @param string $property_name
     *
     * @return bool
     */
    private static function relationExists(\Psalm\Codebase $codebase, string $fq_classlike_name, string $property_name): bool
    {
        // @todo: ensure this is a relation method
        return $codebase->methodExists($fq_classlike_name . '::' . $property_name);
    }

    /**
     * @param \Psalm\Codebase $codebase
     * @param string $fq_classlike_name
     * @param string $property_name
     *
     * @return bool
     */
    private static function accessorExists(\Psalm\Codebase $codebase, string $fq_classlike_name, string $property_name): bool
    {
        return $codebase->methodExists($fq_classlike_name . '::get' . str_replace('_', '', $property_name) . 'Attribute');
    }
}
