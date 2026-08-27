<?php

namespace App\Controllers\Deskapp;

use App\Controllers\BaseController;

/**
 * FilePreview — serves stored files with Content-Disposition: inline
 * so the browser renders them instead of downloading.
 *
 * GET /deskapp/file/preview?key=<encoded_key>&category=<cat>[&id=<int>]
 *
 * For S3: redirects to a presigned inlineUrl (avoids streaming through PHP).
 * For local: streams the file directly with correct Content-Type + inline header.
 */
class FilePreview extends BaseController
{
    public function inline()
    {
        helper(['acl_guard', 'filestorage']);

        if ($resp = acl_require_login('/deskapp/auth/login', 'Sesión expirada.', false)) {
            return $resp;
        }

        $key      = trim((string) ($this->request->getGet('key') ?? ''));
        $category = trim((string) ($this->request->getGet('category') ?? ''));
        $id       = (int) ($this->request->getGet('id') ?? 0);

        if ($key === '') {
            return $this->response->setStatusCode(400)->setBody('Missing key');
        }

        // Security: reject traversal
        if (strpos($key, '..') !== false || strpos($key, "\0") !== false) {
            return $this->response->setStatusCode(400)->setBody('Invalid key');
        }

        try {
            $storage = service('fileStorage');
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

            // S3 driver: redirect to presigned inline URL (no PHP streaming)
            if (method_exists($storage, 'inlineUrl')) {
                $url = $storage->inlineUrl($key, 3600, $mime);
                if ($url === '') {
                    return $this->response->setStatusCode(404)->setBody('File not found');
                }
                return redirect()->to($url);
            }

            // Local driver: stream file with inline header
            $localPath = FCPATH . 'assets/uploads/' . ltrim($key, '/');
            if (!is_file($localPath)) {
                return $this->response->setStatusCode(404)->setBody('File not found');
            }

            $name = basename($key);
            return $this->response
                ->setHeader('Content-Type', $mime)
                ->setHeader('Content-Disposition', 'inline; filename="' . addslashes($name) . '"')
                ->setHeader('Cache-Control', 'private, max-age=300')
                ->setHeader('X-Content-Type-Options', 'nosniff')
                ->setBody(file_get_contents($localPath));
        } catch (\Throwable $e) {
            log_message('error', 'FilePreview::inline error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setBody('Error');
        }
    }
}
