<?php

declare(strict_types=1);

namespace SevenSpan\Matomo\Helpers;

use Illuminate\Support\Facades\Log;
use SevenSpan\WhatsApp\Exceptions\CustomException;

class MatomoHelper
{
    public static function generateApiParamStr(array $params): string
    {
        $params['module'] = 'API';
        $params['format'] = 'json';
        return '?' . http_build_query($params);
    }

    public static function parseMatomoResponse(string $response): bool
    {
        $matomoResAry = json_decode($response, true);
        if (!empty($matomoResAry) && isset($matomoResAry['result']) && $matomoResAry['result'] === 'error') {
            if (empty($accessToken)) {
                throw new CustomException($matomoResAry['message']);
            }
        }
        return true;
    }

    public static function callApi($url)
    {
        try {
            return file_get_contents($url);
        } catch (\Exception $e) {
            Log::error($e);
        }
    }

    /**
     *    'Monday, June 1, 2020' to '2020-06-01'
     */
    public static function parseDateString($date): string
    {
        $date = date_create_from_format('l, F j, Y', $date); // Your original format
        return  date_format($date, 'Y-m-d');
    }

    /**
     * week August 15 – 21, 2022 to "start_date" => "2022-08-15" and "end_date" => "2022-08-22"
     * 
     * @param string $date
     * 
     * @return array $data
     */
    public static function parseDateStringByWeek($date)
    {
        $date =  str_replace('week ', '', $date);
        $month = preg_replace("/[^a-zA-Z]+/", "", $date);
        $year = substr($date, -4);
        $dateRange = str_replace([$month . ' ', ', ' . $year,], '', $date);
        $weekStart = str_replace(' ', '', substr($dateRange, 0, 2));
        $weekEnd = str_replace(' ', '', substr($date, -2));
        $data['start_date'] = strtotime(date_format(date_create_from_format("j-M-Y", $weekStart . "-" . $month . "-" . $year), 'Y-m-d'));
        $data['end_date'] = strtotime(date_format(date_create_from_format("j-M-Y", $weekEnd . "-" . $month . "-" . $year), 'Y-m-d'));
        return $data;
    }

    public static function calculateTotalAndPercentage($data)
    {
        $total = 0;
        $result = [];
        foreach ($data as $key => $value) {
            $total += $value['count'];
        }

        $result['total'] = $total;
        foreach ($data as $key => $value) {
            $value['percentage'] = self::calculatePercentage($total, $value['count']);
            $result['analytics'][] = $value;
        }
        return $result;
    }

    public static function calculatePercentage($total, $value)
    {
        return (float) number_format(($value / $total) * 100, 2);
    }

    function calculatePercentageviews($value, $total)
    {
        if ($total == '0') {
            return 0;
        } else {
            return (float) number_format(($value / $total) * 100, 2);
        }
    }
}
