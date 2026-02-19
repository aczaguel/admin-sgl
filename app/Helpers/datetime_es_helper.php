<?php

if (!function_exists('format_datetime_es')) {
    /**
     * Formatea fechas de BD a un formato legible en español.
     *
     * Ejemplos:
     * - 19 feb 2026, 14:05
     * - 19 feb 2026
     */
    function format_datetime_es($value, bool $withTime = true, string $fallback = 'N/A'): string
    {
        if (empty($value)) {
            return $fallback;
        }

        $ts = strtotime((string) $value);
        if (!$ts) {
            return (string) $value;
        }

        $months = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
        $mIndex = (int) date('n', $ts);
        $mon = $months[max(1, min(12, $mIndex)) - 1];

        $datePart = date('j', $ts) . ' ' . $mon . ' ' . date('Y', $ts);
        if (!$withTime) {
            return $datePart;
        }

        return $datePart . ', ' . date('H:i', $ts);
    }
}

if (!function_exists('format_date_es')) {
    function format_date_es($value, string $fallback = 'N/A'): string
    {
        return format_datetime_es($value, false, $fallback);
    }
}
