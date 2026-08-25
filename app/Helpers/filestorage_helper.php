<?php

/**
 * File Storage helper.
 *
 * Pure key-derivation / normalization helpers for the S3 File Storage
 * abstraction. These functions never touch the filesystem or the AWS SDK;
 * they only build and normalize traversal-safe relative keys.
 *
 * @see .kiro/specs/s3-file-storage/design.md ("Build a storage key from an upload")
 */

if (!defined('FILESTORAGE_PER_ID_CATEGORIES')) {
    /**
     * Categories whose keys place the numeric tramite id as the second
     * path segment (category/<id>/filename). These require a valid
     * positive-integer id.
     */
    define('FILESTORAGE_PER_ID_CATEGORIES', ['pago_gestor', 'pago_derechos', 'cobro_cliente']);
}

if (!function_exists('buildKey')) {
    /**
     * Derive a traversal-safe relative storage key from an upload.
     *
     * The produced key matches `^[A-Za-z0-9._-]+(/[A-Za-z0-9._-]+)*$`, is
     * 1-1024 characters long, contains no `..` segment, no leading `/`, and
     * no backslash. Uniqueness is guaranteed by a random suffix (>= 8
     * alphanumeric chars), so keys derived for the same category/id within
     * the same second are distinct.
     *
     * For per-id categories (pago_gestor, pago_derechos, cobro_cliente) the
     * numeric id is placed as the second segment and a valid positive-integer
     * id is required.
     *
     * @param string   $category     Known non-empty category (e.g. "documentostatus", "pago_gestor").
     * @param int|null $id           Tramite id for per-id categories; optional otherwise.
     * @param string   $originalName Client-supplied original filename.
     *
     * @return string Canonical relative key, e.g. "pago_gestor/12472/comprobante_12472_ab12cd34ef56.jpg".
     *
     * @throws \InvalidArgumentException When the category is empty/invalid, or a
     *                                   per-id category is missing a valid positive-integer id.
     * @throws \RuntimeException         When the produced key fails the format/length assertion.
     */
    function buildKey(string $category, ?int $id, string $originalName): string
    {
        // --- Validate + sanitize the category ---------------------------------
        $category = trim($category);
        if ($category === '') {
            throw new \InvalidArgumentException('buildKey: category must be a non-empty string.');
        }

        $safeCategory = preg_replace('/[^A-Za-z0-9._-]+/', '_', $category);
        $safeCategory = trim($safeCategory, '_');
        if ($safeCategory === '') {
            throw new \InvalidArgumentException('buildKey: category contains no valid characters.');
        }

        // The character whitelist above intentionally preserves '.' so that
        // legitimate dotted categories keep working. However, a category that
        // collapses to a pure path-traversal / current-dir segment ('.' or
        // '..') would become the first key segment and defeat the traversal
        // guarantee (Req 3.2 / 3.4). Reject such categories outright; they are
        // never valid storage categories. Note this only rejects segments that
        // are EXACTLY '.' or '..' — dotted names like 'a.b' or '...' are kept.
        if ($safeCategory === '.' || $safeCategory === '..') {
            throw new \InvalidArgumentException(
                "buildKey: category '{$category}' is not a valid storage category (resolves to a path-traversal segment)."
            );
        }

        // --- Enforce per-id category id requirement ---------------------------
        $isPerId = in_array($category, FILESTORAGE_PER_ID_CATEGORIES, true);
        if ($isPerId && ($id === null || $id <= 0)) {
            throw new \InvalidArgumentException(
                "buildKey: category '{$category}' requires a valid positive-integer id."
            );
        }

        // --- Extension (lowercased, sanitized) --------------------------------
        $ext = strtolower((string) pathinfo($originalName, PATHINFO_EXTENSION));
        $ext = preg_replace('/[^a-z0-9]+/', '', $ext);

        // --- Base name without extension (sanitized) --------------------------
        $baseName = (string) pathinfo($originalName, PATHINFO_FILENAME);
        $safeBase = preg_replace('/[^A-Za-z0-9_-]+/', '_', $baseName);
        $safeBase = trim($safeBase, '_');
        if ($safeBase === '') {
            $safeBase = 'documento';
        }

        // --- Random suffix (>= 8 alphanumeric chars) --------------------------
        try {
            $random = bin2hex(random_bytes(8)); // 16 hex chars
        } catch (\Throwable $e) {
            // Fallback if a CSPRNG is unavailable.
            $random = preg_replace('/[^A-Za-z0-9]+/', '', uniqid('', true));
        }
        if (strlen($random) < 8) {
            $random = str_pad($random, 8, '0');
        }

        // --- Compose the filename ---------------------------------------------
        $fileName = $safeBase;
        if ($id !== null) {
            $fileName .= '_' . (string) $id;
        }
        $fileName .= '_' . $random;
        if ($ext !== '') {
            $fileName .= '.' . $ext;
        }

        // --- Compose the key --------------------------------------------------
        if ($id !== null) {
            $key = $safeCategory . '/' . (string) $id . '/' . $fileName;
        } else {
            $key = $safeCategory . '/' . $fileName;
        }

        // --- Assert format + length -------------------------------------------
        if (!preg_match('#^[A-Za-z0-9._-]+(/[A-Za-z0-9._-]+)*$#', $key)) {
            throw new \RuntimeException('buildKey: produced key does not match the required pattern: ' . $key);
        }
        $length = strlen($key);
        if ($length < 1 || $length > 1024) {
            throw new \RuntimeException('buildKey: produced key length out of bounds (1-1024): ' . $length);
        }

        return $key;
    }
}

if (!function_exists('keyFromStored')) {
    /**
     * Normalize a legacy stored value into a canonical relative storage key.
     *
     * Handles the four historical shapes of a stored file reference:
     *  - Bare filename (the common legacy case): rebuilt as
     *    `category[+ '/' + id] + '/' + filename`.
     *  - Relative key (contains '/', no scheme): used as-is after stripping any
     *    leading '/'.
     *  - Absolute URL containing the `/assets/uploads/` prefix: origin and
     *    prefix are stripped, leaving `category/.../file`.
     *  - Absolute URL without that prefix: only the origin is stripped and any
     *    leading '/' removed.
     *
     * A stored value is classified as a bare filename when, after trimming, it
     * contains no '/' and no `http://` or `https://` scheme. The function is a
     * fixed point over its own output: applying it to a canonical relative key
     * returns that key unchanged.
     *
     * @param string   $storedValue Raw DB value (bare filename, relative key, or absolute URL).
     * @param string   $category    Category used to rebuild bare-filename keys, e.g. "pago_gestor".
     * @param int|null $id          Optional tramite id for per-id categories.
     *
     * @return string Canonical relative key, or '' when the input is empty/whitespace-only.
     */
    function keyFromStored(string $storedValue, string $category = '', ?int $id = null): string
    {
        $value = trim($storedValue);
        if ($value === '') {
            return '';
        }

        // Absolute URL: strip the origin, then the /assets/uploads/ prefix if present.
        if (preg_match('#^https?://#i', $value) === 1) {
            $path = parse_url($value, PHP_URL_PATH);
            if (!is_string($path) || $path === '') {
                return '';
            }
            $path = rawurldecode($path);

            $marker = '/assets/uploads/';
            $pos    = strpos($path, $marker);
            if ($pos !== false) {
                // Strip origin + everything up to and including the prefix.
                return ltrim(substr($path, $pos + strlen($marker)), '/');
            }

            // No known prefix: strip only the origin (leading '/').
            return ltrim($path, '/');
        }

        // Relative key already (contains a '/', no scheme): use as-is.
        if (strpos($value, '/') !== false) {
            return ltrim($value, '/');
        }

        // Bare filename: rebuild category [+ '/' + id] + '/' + filename.
        $cat = trim($category, '/');
        if ($cat === '') {
            // No category to rebuild against; return the filename unchanged.
            return $value;
        }

        if ($id !== null) {
            return $cat . '/' . $id . '/' . $value;
        }

        return $cat . '/' . $value;
    }
}

if (!function_exists('file_url')) {
    /**
     * Resolve a browser URL for a stored file value.
     *
     * Single call site for views/galleries/JSON responses that previously
     * built `base_url('/assets/uploads/...')`. Empty, null-ish, or
     * whitespace-only stored values short-circuit to '' WITHOUT invoking the
     * legacy normalizer or the storage service. Otherwise the value is
     * normalized to a canonical relative key via `keyFromStored()` and the
     * `fileStorage` service resolves the browser URL (a presigned URL when the
     * active driver is s3, or a `base_url()` path when local).
     *
     * A legacy value that cannot be normalized to a relative key resolves to
     * '' without raising an unhandled error (the failure is logged); the
     * underlying DB row is never modified.
     *
     * @param string   $storedValue Raw DB value: bare filename (legacy), relative key, or absolute URL.
     * @param string   $category    e.g. "documentostatus", "pago_gestor" (used to rebuild legacy keys).
     * @param int|null $id          Tramite id for per-id categories (pago_gestor/pago_derechos/cobro_cliente).
     * @param int      $ttl         Presigned TTL (seconds) when the active driver is s3.
     *
     * @return string Browser URL for the stored file, or '' when the value is empty/unresolvable.
     */
    function file_url(string $storedValue, string $category = '', ?int $id = null, int $ttl = 3600): string
    {
        // Empty / whitespace-only: short-circuit WITHOUT touching normalizer or service.
        if (trim($storedValue) === '') {
            return '';
        }

        try {
            $key = keyFromStored($storedValue, $category, $id);
            if ($key === '') {
                return '';
            }

            return (string) service('fileStorage')->url($key, $ttl);
        } catch (\Throwable $e) {
            // A legacy value that cannot be normalized/resolved must not raise
            // an unhandled error; log and degrade gracefully to ''.
            log_message(
                'error',
                'file_url: could not resolve URL for stored value [{value}] (category={category}, id={id}): {message}',
                [
                    'value'    => $storedValue,
                    'category' => $category,
                    'id'       => $id ?? 'null',
                    'message'  => $e->getMessage(),
                ]
            );

            return '';
        }
    }
}

if (!function_exists('file_download_url')) {
    /**
     * Resolve a browser URL that forces a download for a stored file value.
     *
     * Mirrors file_url() but returns a URL with Content-Disposition: attachment
     * (s3) so files that browsers would otherwise render inline (e.g. XML) are
     * downloaded instead. Empty/unresolvable values degrade to '' exactly like
     * file_url().
     *
     * @param string   $storedValue Raw DB value (bare filename, relative key, or absolute URL).
     * @param string   $category    e.g. "documentostatus", "pago_gestor".
     * @param int|null $id          Tramite id for per-id categories.
     * @param int      $ttl         Presigned TTL (seconds) when the active driver is s3.
     *
     * @return string Download URL for the stored file, or '' when unresolvable.
     */
    function file_download_url(string $storedValue, string $category = '', ?int $id = null, int $ttl = 3600): string
    {
        if (trim($storedValue) === '') {
            return '';
        }

        try {
            $key = keyFromStored($storedValue, $category, $id);
            if ($key === '') {
                return '';
            }

            return (string) service('fileStorage')->downloadUrl($key, $ttl, basename($key));
        } catch (\Throwable $e) {
            log_message(
                'error',
                'file_download_url: could not resolve download URL for [{value}] (category={category}, id={id}): {message}',
                [
                    'value'    => $storedValue,
                    'category' => $category,
                    'id'       => $id ?? 'null',
                    'message'  => $e->getMessage(),
                ]
            );

            return '';
        }
    }
}

if (!function_exists('file_inline_url')) {
    /**
     * Resolve a browser URL that forces inline rendering (Content-Disposition: inline).
     * Used for PDFs so the browser displays them inline instead of downloading.
     * Falls back to file_url() for local driver (same-origin inline rendering works by default).
     *
     * @param string   $storedValue Raw DB value (bare filename, relative key, or absolute URL).
     * @param string   $category    e.g. "documentostatus", "pago_gestor".
     * @param int|null $id          Tramite id for per-id categories.
     * @param int      $ttl         Presigned TTL (seconds) when the active driver is s3.
     *
     * @return string Inline URL for the stored file, or '' when unresolvable.
     */
    function file_inline_url(string $storedValue, string $category = '', ?int $id = null, int $ttl = 3600): string
    {
        if (trim($storedValue) === '') {
            return '';
        }

        try {
            $key = keyFromStored($storedValue, $category, $id);
            if ($key === '') {
                return '';
            }

            $storage = service('fileStorage');
            // Use inlineUrl() if the driver supports it (S3), otherwise fall back to url()
            if (method_exists($storage, 'inlineUrl')) {
                return (string) $storage->inlineUrl($key, $ttl);
            }

            return (string) $storage->url($key, $ttl);
        } catch (\Throwable $e) {
            log_message(
                'error',
                'file_inline_url: could not resolve inline URL for [{value}] (category={category}, id={id}): {message}',
                [
                    'value'    => $storedValue,
                    'category' => $category,
                    'id'       => $id ?? 'null',
                    'message'  => $e->getMessage(),
                ]
            );

            return '';
        }
    }
}

if (!function_exists('avatar_url')) {
    /**
     * Resolve a browser URL for a user avatar value, preserving legacy behavior.
     *
     * Avatars historically were stored as a public-relative path
     * (`uploads/avatars/<file>`) and rendered as `/public/<value>`, while the
     * default avatar is the sentinel `uploads/avatars/default.png`. Going
     * forward, uploads flow through the Storage_Service under the `avatars`
     * category and are stored as a canonical bare filename.
     *
     * This resolver keeps both shapes working:
     *  - Empty value                -> the default sentinel.
     *  - Value containing a `/`      -> legacy public-relative path (or the
     *    default sentinel); rendered via `base_url('public/' . value)` exactly
     *    as before, so existing avatars and the default keep resolving.
     *  - Bare filename (no `/`)      -> resolved through `file_url(value, 'avatars')`
     *    (a `base_url('/assets/uploads/avatars/...')` path when the driver is
     *    local, or a presigned URL when the driver is s3).
     *
     * @param string|null $storedValue Raw DB/session avatar value.
     * @param string      $default     Default sentinel used when the value is empty.
     *
     * @return string Browser URL for the avatar image.
     */
    function avatar_url(?string $storedValue, string $default = 'uploads/avatars/default.png'): string
    {
        $value = trim((string) $storedValue);
        if ($value === '') {
            $value = $default;
        }

        // Legacy public-relative path (e.g. "uploads/avatars/x.png") or the
        // default sentinel: keep rendering from /public as the current system does.
        if (strpos($value, '/') !== false) {
            return base_url('public/' . ltrim($value, '/'));
        }

        // Canonical bare filename: resolve through the storage service.
        $url = file_url($value, 'avatars');

        return $url !== '' ? $url : base_url('public/' . ltrim($default, '/'));
    }
}

if (!function_exists('is_image_filename')) {
    /**
     * Report whether a filename names a browser-renderable image.
     *
     * The extension is defined as the substring following the final `.`
     * character in the (trimmed) filename. The predicate returns true only
     * when that extension is one of `png`, `jpg`, `jpeg`, `gif`, `webp`,
     * `bmp`, or `svg`, compared case-insensitively.
     *
     * It returns false for a filename that is empty or whitespace-only,
     * contains no `.` character, or whose only `.` is its first character
     * (a leading-dot-only name such as ".png").
     *
     * @param string $name Filename to inspect (e.g. "comprobante_12472.JPG").
     *
     * @return bool True when the extension is a renderable image type, false otherwise.
     */
    function is_image_filename(string $name): bool
    {
        $name = trim($name);
        if ($name === '') {
            return false;
        }

        // Require a '.' that is not the first character, so leading-dot-only
        // names (".png") and names without any '.' return false.
        $pos = strrpos($name, '.');
        if ($pos === false || $pos === 0) {
            return false;
        }

        $ext = substr($name, $pos + 1);

        return preg_match('/^(png|jpe?g|gif|webp|bmp|svg)$/i', $ext) === 1;
    }
}

if (!function_exists('file_url_map')) {
    /**
     * Resolve a list of stored file values into a { storedValue => url } map.
     *
     * Thin convenience wrapper over `file_url()`: it adds no new resolution
     * logic, so every produced URL still flows through the single resolution
     * point and inherits its graceful degradation (empty/unresolvable => '').
     *
     * Each value is trimmed; empty/whitespace-only values are skipped, and
     * duplicate keys collapse to a single entry (exactly one per distinct
     * non-empty stored value). Keys are the ORIGINAL trimmed stored values (as
     * they appear in the doc rows / DB), so a view can look up the URL by the
     * same filename it already renders. A value that `file_url()` resolves to
     * '' is RETAINED as a key mapped to '' rather than excluded.
     *
     * @param string[] $storedValues Raw DB values (bare filename, relative key, or absolute URL).
     * @param string   $category     e.g. "cobro_cliente", "pago_gestor", "documentostatus".
     * @param int|null $id           Tramite id for per-id categories.
     * @param int      $ttl          Presigned TTL (seconds) when the active driver is s3.
     *
     * @return array<string,string> Map of storedValue => browser URL ('' when unresolvable).
     */
    function file_url_map(array $storedValues, string $category = '', ?int $id = null, int $ttl = 3600): array
    {
        $map = [];
        foreach ($storedValues as $value) {
            $name = trim((string) $value);
            if ($name === '' || array_key_exists($name, $map)) {
                continue;
            }
            $map[$name] = file_url($name, $category, $id, $ttl); // '' if unresolvable
        }

        return $map;
    }
}

if (!function_exists('file_url_list')) {
    /**
     * Resolve a list of stored file values into an ordered list of
     * [name, url] entries.
     *
     * Thin convenience wrapper over `file_url()` for galleries that render an
     * ordered list rather than a keyed lookup. Each value is trimmed;
     * empty/whitespace-only values are skipped. Input order is preserved and,
     * unlike `file_url_map()`, duplicate values are retained — one entry per
     * occurrence.
     *
     * @param string[] $storedValues Raw DB values (bare filename, relative key, or absolute URL).
     * @param string   $category     e.g. "cobro_cliente", "pago_gestor", "documentostatus".
     * @param int|null $id           Tramite id for per-id categories.
     * @param int      $ttl          Presigned TTL (seconds) when the active driver is s3.
     *
     * @return array<int,array{name:string,url:string}> Ordered list of resolved entries.
     */
    function file_url_list(array $storedValues, string $category = '', ?int $id = null, int $ttl = 3600): array
    {
        $list = [];
        foreach ($storedValues as $value) {
            $name = trim((string) $value);
            if ($name === '') {
                continue;
            }
            $list[] = ['name' => $name, 'url' => file_url($name, $category, $id, $ttl)];
        }

        return $list;
    }
}
