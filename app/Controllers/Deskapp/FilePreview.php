<?php

namespace App\Controllers\Deskapp;

use App\Controllers\BaseController;

/**
 * FilePreview — serves stored files with Content-Disposition: inline
 * so the browser renders them instead of downloading.
 *
 * GET /deskapp/file/preview?file=<storedValue>&category=<cat>[&id=<int>]
 *
 * Accepts the raw stored value (bare filename) + category + optional id,
 * resolves the canonical key via keyFromStored(), then:
 * - S3:    redirects to a presigned inlineUrl (PHP doesn't stream the file)
 * - Local: streams the file with Content-Disposition: inline + correct MIME
 */
class FilePreview extends BaseController
{
    public function inline()
    {
        helper(['acl_guard', 'filestorage']);

        if ($resp = acl_require_login('/deskapp/auth/login', 'Sesión expirada.', false)) {
            return $resp;
        }

        $storedValue = trim((string) ($this->request->getGet('file') ?? ''));
        $category    = trim((string) ($this->request->getGet('category') ?? ''));
        $id          = (int) ($this->request->getGet('id') ?? 0);

        if ($storedValue === '') {
            return $this->response->setStatusCode(400)->setBody('Missing file');
        }

        // Security: reject traversal in the raw value
        if (strpos($storedValue, '..') !== false || strpos($storedValue, "\0") !== false) {
            return $this->response->setStatusCode(400)->setBody('Invalid file');
        }

        try {
            // Resolve canonical key the same way as file_inline_url/file_url
            $key = keyFromStored($storedValue, $category, $id > 0 ? $id : null);
            if ($key === '') {
                return $this->response->setStatusCode(400)->setBody('Unresolvable file: storedValue=[' . $storedValue . '] category=[' . $category . '] id=[' . $id . ']');
            }

            $storage = service('fileStorage');
            $driverClass = get_class($storage);
            $ext     = strtolower((string) pathinfo($key, PATHINFO_EXTENSION));
            $mimeMap = [
                'pdf'  => 'application/pdf',
                'jpg'  => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png'  => 'image/png',
                'gif'  => 'image/gif',
                'webp' => 'image/webp',
                'svg'  => 'image/svg+xml',
                'tiff' => 'image/tiff',
                'tif'  => 'image/tiff',
            ];
            $mime = $mimeMap[$ext] ?? 'application/octet-stream';

            // S3 driver: redirect to presigned URL with inline disposition
            if (method_exists($storage, 'inlineUrl')) {
                // Try inline first, fall back to regular url() which we know works
                $url = '';
                try {
                    $url = $storage->inlineUrl($key, 3600, $mime);
                } catch (\Throwable $inlineErr) {
                    log_message('warning', 'FilePreview: inlineUrl threw [' . $inlineErr->getMessage() . '] for key=[' . $key . '], trying url()');
                }
                if ($url === '') {
                    log_message('info', 'FilePreview: falling back to url() for key=[' . $key . ']');
                    $url = $storage->url($key, 3600);
                }
                if ($url === '') {
                    log_message('error', 'FilePreview: both inlineUrl and url() returned empty for key=[' . $key . '] driver=[' . $driverClass . ']');
                    return $this->response->setStatusCode(404)->setBody('File not found: key=[' . esc($key) . '] driver=[' . esc($driverClass) . ']');
                }
                log_message('info', 'FilePreview: redirecting key=[' . $key . '] to url=[' . substr($url, 0, 80) . '...]');
                return redirect()->to($url);
            }

            // Local driver: stream file with inline header
            $localPath = FCPATH . 'assets/uploads/' . ltrim($key, '/');
            if (!is_file($localPath)) {
                log_message('error', 'FilePreview: local file not found at [' . $localPath . ']');
                return $this->response->setStatusCode(404)->setBody('Local file not found: key=[' . esc($key) . '] path=[' . esc($localPath) . '] driver=[' . esc($driverClass) . ']');
            }

            $name = basename($key);
            return $this->response
                ->setHeader('Content-Type', $mime)
                ->setHeader('Content-Disposition', 'inline; filename="' . addslashes($name) . '"')
                ->setHeader('Cache-Control', 'private, max-age=300')
                ->setHeader('X-Content-Type-Options', 'nosniff')
                ->setBody(file_get_contents($localPath));
        } catch (\Throwable $e) {
            log_message('error', 'FilePreview::inline error for [' . $storedValue . ']: ' . $e->getMessage() . ' trace: ' . $e->getTraceAsString());
            return $this->response->setStatusCode(500)->setBody('Error: ' . $e->getMessage());
        }
    }
}
