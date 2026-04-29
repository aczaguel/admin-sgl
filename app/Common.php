<?php

/**
 * The goal of this file is to allow developers a location
 * where they can overwrite core procedural functions and
 * replace them with their own. This file is loaded during
 * the bootstrap process and is called during the frameworks
 * execution.
 *
 * This can be looked at as a `master helper` file that is
 * loaded early on, and may also contain additional functions
 * that you'd like to use throughout your entire application
 *
 * @link: https://codeigniter4.github.io/CodeIgniter4/
 */

if (! function_exists('sgl_format_local_date')) {
	function sgl_format_local_date(?int $timestamp = null, string $locale = 'es_MX', string $timezone = 'America/Mexico_City', int $dateType = \IntlDateFormatter::FULL, int $timeType = \IntlDateFormatter::NONE, ?string $pattern = null): string
	{
		$timestamp = $timestamp ?? time();

		if (class_exists('\IntlDateFormatter')) {
			$formatter = new \IntlDateFormatter($locale, $dateType, $timeType, $timezone, null, $pattern);
			$formatted = $formatter->format($timestamp);
			if (is_string($formatted) && $formatted !== '') {
				return mb_strtoupper(mb_substr($formatted, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($formatted, 1, null, 'UTF-8');
			}
		}

		return date('Y-m-d', $timestamp);
	}
}
