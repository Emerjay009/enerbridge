<?php

namespace App\Support;

class PhoneNumber
{
    /**
     * Turns any common Philippine mobile format into +639XXXXXXXXX.
     * Accepts: 0917 123 4567, 09171234567, +63 917 123 4567, 639171234567, 9171234567
     * If it is not recognised, the cleaned text is returned and validation will reject it.
     */
    public static function normalize(?string $input): ?string
    {
        if ($input === null || trim($input) === '') {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $input);

        if (preg_match('/^09\d{9}$/', $digits)) {
            return '+63'.substr($digits, 1);
        }

        if (preg_match('/^639\d{9}$/', $digits)) {
            return '+'.$digits;
        }

        if (preg_match('/^9\d{9}$/', $digits)) {
            return '+63'.$digits;
        }

        return $digits === '' ? null : $input;
    }

    /** +639171234567 -> 0917 123 4567 */
    public static function display(?string $e164): string
    {
        if (! $e164 || ! preg_match('/^\+639\d{9}$/', $e164)) {
            return (string) $e164;
        }

        $local = '0'.substr($e164, 3);

        return substr($local, 0, 4).' '.substr($local, 4, 3).' '.substr($local, 7, 4);
    }
}
