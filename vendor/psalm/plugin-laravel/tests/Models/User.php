<?php declare(strict_types=1);

namespace Tests\Psalm\LaravelPlugin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

final class User extends Model {
    protected $table = 'users';

    /**
     * @psalm-return HasOne<Phone>
     */
    public function phone(): HasOne
    {
        return $this->hasOne(Phone::class);
    }

    /**
     * @psalm-return BelongsToMany<Role>
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * @psalm-return HasManyThrough<Mechanic>
     */
    public function carsAtMechanic(): HasManyThrough
    {
        return $this->hasManyThrough(Mechanic::class, Car::class);
    }

    /**
     * Get the user's image.
     */
    public function image()
    {
        return $this->morphOne(Image::Class, 'imageable');
    }
}
