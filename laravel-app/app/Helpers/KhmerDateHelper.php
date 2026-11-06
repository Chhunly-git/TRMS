<?php

namespace App\Helpers;

use Carbon\Carbon;

class KhmerDateHelper
{
    private const KHMER_DIGITS = ['០', '១', '២', '៣', '៤', '៥', '៦', '៧', '៨', '៩'];

    private const KHMER_MONTHS = [
        1 => 'មករា',
        2 => 'កុម្ភៈ',
        3 => 'មីនា',
        4 => 'មេសា',
        5 => 'ឧសភា',
        6 => 'មិថុនា',
        7 => 'កក្កដា',
        8 => 'សីហា',
        9 => 'កញ្ញា',
        10 => 'តុលា',
        11 => 'វិច្ឆិកា',
        12 => 'ធ្នូ',
    ];

    private const KHMER_DAYS_OF_WEEK = [
        0 => 'អាទិត្យ',
        1 => 'ច័ន្ទ',
        2 => 'អង្គារ',
        3 => 'ពុធ',
        4 => 'ព្រហស្បតិ៍',
        5 => 'សុក្រ',
        6 => 'សៅរ៍',
    ];

    /**
     * Convert digits in a string or integer to Khmer digits.
     */
    public static function toKhmerDigits(int|string|null $number): string
    {
        if ($number === null || $number === '') {
            return '';
        }

        $str = (string) $number;
        $result = '';
        $len = mb_strlen($str, 'UTF-8');

        for ($i = 0; $i < $len; $i++) {
            $char = mb_substr($str, $i, 1, 'UTF-8');
            $result .= is_numeric($char) ? self::KHMER_DIGITS[(int) $char] : $char;
        }

        return $result;
    }

    /**
     * Format solar date in official Cambodian administrative style:
     * e.g. "រាជធានីភ្នំពេញ ថ្ងៃទី០៦ ខែវិច្ឆិកា ឆ្នាំ២០២៦"
     */
    public static function toKhmerSolarDate($date = null, string $city = 'រាជធានីភ្នំពេញ'): string
    {
        $c = $date ? Carbon::parse($date)->setTimezone('Asia/Phnom_Penh') : Carbon::now('Asia/Phnom_Penh');
        $day = str_pad($c->day, 2, '0', STR_PAD_LEFT);
        $dayKh = self::toKhmerDigits($day);
        $monthKh = self::KHMER_MONTHS[$c->month] ?? '';
        $yearKh = self::toKhmerDigits($c->year);

        return "{$city} ថ្ងៃទី{$dayKh} ខែ{$monthKh} ឆ្នាំ{$yearKh}";
    }

    /**
     * Format short date for leader opinion boxes:
     * e.g. "០៦/១១/២៦"
     */
    public static function toKhmerShortDate($date = null): string
    {
        $c = $date ? Carbon::parse($date)->setTimezone('Asia/Phnom_Penh') : Carbon::now('Asia/Phnom_Penh');
        $day = str_pad($c->day, 2, '0', STR_PAD_LEFT);
        $month = str_pad($c->month, 2, '0', STR_PAD_LEFT);
        $year = substr((string) $c->year, -2);

        $shortStr = "{$day}/{$month}/{$year}";
        return self::toKhmerDigits($shortStr);
    }

    /**
     * Compute Buddhist Era year (BE = CE + 543/544 depending on lunar month, standard default +544)
     */
    public static function getBuddhistEraYear($date = null): string
    {
        $c = $date ? Carbon::parse($date)->setTimezone('Asia/Phnom_Penh') : Carbon::now('Asia/Phnom_Penh');
        $beYear = $c->year + 543; // Standard conversion
        return self::toKhmerDigits($beYear);
    }

    /**
     * Standard lunar date line or placeholder
     */
    public static function getDefaultLunarDateString($date = null): string
    {
        $beYear = self::getBuddhistEraYear($date);
        return "ថ្ងៃ................... ខែ............. ឆ្នាំ........ ព.ស.{$beYear}";
    }
}
