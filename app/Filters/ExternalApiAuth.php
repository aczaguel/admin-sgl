<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class ExternalApiAuth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $config = config('ExternalApi');
        $response = service('response');

        if (!$config->enabled) {
            return $response->setStatusCode(503)->setJSON([
                'success' => false,
                'status' => 'error',
                'message' => 'La API externa está deshabilitada.',
            ]);
        }

        if ($config->keys === []) {
            return $response->setStatusCode(503)->setJSON([
                'success' => false,
                'status' => 'error',
                'message' => 'La API externa no tiene llaves configuradas.',
            ]);
        }

        $providedKey = trim((string) $request->getHeaderLine($config->apiKeyHeader));
        if ($providedKey === '') {
            $authorization = trim((string) $request->getHeaderLine('Authorization'));
            $prefix = $config->authorizationScheme . ' ';
            if (stripos($authorization, $prefix) === 0) {
                $providedKey = trim(substr($authorization, strlen($prefix)));
            }
        }

        if ($providedKey === '') {
            return $response->setStatusCode(401)->setJSON([
                'success' => false,
                'status' => 'error',
                'message' => 'Falta credencial de API.',
            ]);
        }

        foreach ($config->keys as $allowedKey) {
            if (hash_equals($allowedKey, $providedKey)) {
                return null;
            }
        }

        return $response->setStatusCode(401)->setJSON([
            'success' => false,
            'status' => 'error',
            'message' => 'Credencial de API inválida.',
        ]);
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}