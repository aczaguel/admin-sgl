<?php

namespace App\Controllers\Deskapp;

use App\Controllers\BaseController;

/**
 * FilePreview — proxies stored files with Content-Disposition: inline
 * so the browser renders PDFs and images instead of downloading.
 *
 * For S3: fetches the object via the SDK and streams it through PHP,
 * setting the correct headers ourselves (no browser redirect to S3).
 * For local: reads the file and streams it directly.
 *
 * GET /deskapp/file/preview?file=<storedValue>&category=<cat>[&id=<int>]
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

        if (strpos($storedValue, '..') !== false || strpos($storedValue, "\0") !== false) {
            return $this->response->setStatusCode(400)->setBody('Invalid file');
        }

        try {
            $key = keyFromStored($storedValue, $category, $id > 0 ? $id : null);
            if ($key === '') {
                return $this->response->setStatusCode(400)->setBody('Unresolvable file');
            }

            $ext = strtolower((string) pathinfo($key, PATHINFO_EXTENSION));
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
            $name = addslashes(basename($key));

            $storage = service('fileStorage');

            // S3 driver: stream through PHP to control Content-Disposition header
            if (method_exists($storage, 'getObject')) {
                $body = $storage->getObject($key);
                if ($body === null || $body === false || $body === '') {
                    log_message('error', 'FilePreview: getObject returned empty for key=[' . $key . '] bucket=[' . (method_exists($storage, 'getBucket') ? $storage->getBucket() : 'unknown') . ']');
                    // Check if regular url works for debugging
                    $testUrl = method_exists($storage, 'url') ? substr($storage->url($key, 60), 0, 120) : 'no url method';
                    return $this->response->setStatusCode(404)->setBody('File not found: key=[' . esc($key) . '] testUrl=[' . esc($testUrl) . ']');
                }
                return $this->response
                    ->setHeader('Content-Type', $mime)
                    ->setHeader('Content-Disposition', 'inline; filename="' . $name . '"')
                    ->setHeader('Cache-Control', 'private, max-age=300')
                    ->setHeader('X-Content-Type-Options', 'nosniff')
                    ->setBody((string) $body);
            }

            // Fallback: if driver supports inlineUrl, redirect (best effort)
            if (method_exists($storage, 'inlineUrl')) {
                $url = $storage->inlineUrl($key, 3600, $mime);
                if ($url !== '') {
                    return redirect()->to($url);
                }
                // Try regular url
                $url = $storage->url($key, 3600);
                if ($url !== '') {
                    return redirect()->to($url);
                }
            }

            // Local driver: stream file directly
            $localPath = FCPATH . 'assets/uploads/' . ltrim($key, '/');
            if (!is_file($localPath)) {
                return $this->response->setStatusCode(404)->setBody('File not found');
            }

            return $this->response
                ->setHeader('Content-Type', $mime)
                ->setHeader('Content-Disposition', 'inline; filename="' . $name . '"')
                ->setHeader('Cache-Control', 'private, max-age=300')
                ->setHeader('X-Content-Type-Options', 'nosniff')
                ->setBody(file_get_contents($localPath));

        } catch (\Throwable $e) {
            log_message('error', 'FilePreview::inline error for [' . $storedValue . ']: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setBody('Error: ' . $e->getMessage());
        }
    }
}
