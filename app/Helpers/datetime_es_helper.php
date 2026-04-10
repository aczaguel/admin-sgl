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

        try {
            $timezoneName = function_exists('app_timezone') ? app_timezone() : date_default_timezone_get();
            $timezone = new \DateTimeZone($timezoneName ?: 'America/Mexico_City');

            if ($value instanceof \DateTimeInterface) {
                $dateTime = \DateTimeImmutable::createFromInterface($value)->setTimezone($timezone);
            } else {
                $rawValue = trim((string) $value);
                if ($rawValue === '') {
                    return $fallback;
                }

                $hasExplicitTimezone = (bool) preg_match('/(?:Z|[+-]\d{2}:?\d{2})$/i', $rawValue);
                $dateTime = $hasExplicitTimezone
                    ? new \DateTimeImmutable($rawValue)
                    : new \DateTimeImmutable($rawValue, $timezone);
                $dateTime = $dateTime->setTimezone($timezone);
            }
        } catch (\Throwable $e) {
            return (string) $value;
        }

        $months = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
        $mIndex = (int) $dateTime->format('n');
        $mon = $months[max(1, min(12, $mIndex)) - 1];

        $datePart = $dateTime->format('j') . ' ' . $mon . ' ' . $dateTime->format('Y');
        if (!$withTime) {
            return $datePart;
        }

        return $datePart . ', ' . $dateTime->format('H:i');
    }
}

if (!function_exists('format_date_es')) {
    function format_date_es($value, string $fallback = 'N/A'): string
    {
        return format_datetime_es($value, false, $fallback);
    }
}
