<?php

namespace App\Helpers;

use Carbon\Carbon;

class BnHelper
{
    private static array $digits = [
        '0' => '০', '1' => '১', '2' => '২', '3' => '৩', '4' => '৪',
        '5' => '৫', '6' => '৬', '7' => '৭', '8' => '৮', '9' => '৯',
    ];

    private static array $months = [
        1  => 'জানুয়ারি',  2  => 'ফেব্রুয়ারি', 3  => 'মার্চ',
        4  => 'এপ্রিল',    5  => 'মে',           6  => 'জুন',
        7  => 'জুলাই',     8  => 'আগস্ট',        9  => 'সেপ্টেম্বর',
        10 => 'অক্টোবর',  11 => 'নভেম্বর',      12 => 'ডিসেম্বর',
    ];

    /**
     * Convert English digits in a string to Bangla digits.
     */
    public static function num(string|int|float $value): string
    {
        return strtr((string) $value, self::$digits);
    }

    /**
     * Format a number with commas and convert to Bangla digits.
     * e.g. 1234.50 → ১,২৩৪.৫০
     */
    public static function money(float|string $amount, int $decimals = 2): string
    {
        return self::num(number_format((float) $amount, $decimals));
    }

    /**
     * Format a date in Bangla: ১৮ এপ্রিল ২০২৬
     */
    public static function date(Carbon|string|null $date): string
    {
        if (! $date) {
            return '—';
        }
        $carbon = $date instanceof Carbon ? $date : Carbon::parse($date);
        $day    = self::num($carbon->day);
        $month  = self::$months[$carbon->month];
        $year   = self::num($carbon->year);
        return "{$day} {$month} {$year}";
    }

    /**
     * Format a datetime in Bangla: ১৮ এপ্রিল ২০২৬, ১৪:৩০
     */
    public static function datetime(Carbon|string|null $dt): string
    {
        if (! $dt) {
            return '—';
        }
        $carbon = $dt instanceof Carbon ? $dt : Carbon::parse($dt);
        return self::date($carbon) . ', ' . self::num($carbon->format('H:i'));
    }

    /**
     * Format a billing month/year: এপ্রিল ২০২৬
     */
    public static function monthYear(int $month, int $year): string
    {
        return (self::$months[$month] ?? '') . ' ' . self::num($year);
    }

    /**
     * Return Bangla or English value based on current locale.
     * Falls back to the English value if Bangla is empty.
     */
    public static function localized(?string $bn, ?string $en): string
    {
        if (app()->getLocale() === 'bn' && ! empty($bn)) {
            return $bn;
        }
        return $en ?? '';
    }

    /**
     * Conditionally convert number to Bangla based on locale.
     */
    public static function n(string|int|float $value): string
    {
        if (app()->getLocale() === 'bn') {
            return self::num($value);
        }
        return (string) $value;
    }

    /**
     * Conditionally format money based on locale.
     */
    public static function m(float|string $amount, int $decimals = 2): string
    {
        $formatted = number_format((float) $amount, $decimals);
        if (app()->getLocale() === 'bn') {
            return self::num($formatted);
        }
        return $formatted;
    }

    /**
     * Conditionally format date based on locale.
     */
    public static function d(Carbon|string|null $date): string
    {
        if (! $date) {
            return '—';
        }
        if (app()->getLocale() === 'bn') {
            return self::date($date);
        }
        $carbon = $date instanceof Carbon ? $date : Carbon::parse($date);
        return $carbon->format('d M Y');
    }

    /**
     * Conditionally format datetime based on locale.
     */
    public static function dt(Carbon|string|null $dt): string
    {
        if (! $dt) {
            return '—';
        }
        if (app()->getLocale() === 'bn') {
            return self::datetime($dt);
        }
        $carbon = $dt instanceof Carbon ? $dt : Carbon::parse($dt);
        return $carbon->format('d M Y H:i');
    }

    /**
     * Conditionally format billing month/year based on locale.
     */
    public static function my(int $month, int $year): string
    {
        if (app()->getLocale() === 'bn') {
            return self::monthYear($month, $year);
        }
        return Carbon::create($year, $month)->format('F Y');
    }
}
