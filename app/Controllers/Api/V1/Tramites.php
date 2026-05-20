<?php

namespace App\Controllers\Api\V1;

use App\Controllers\BaseController;
use App\Services\ExternalTramiteService;

class Tramites extends BaseController
{
    protected function makeExternalTramiteService(): ExternalTramiteService
    {
        return new ExternalTramiteService();
    }

    public function create()
    {
        $payload = $this->request->getJSON(true);
        if (!is_array($payload)) {
            $payload = $this->request->getPost();
        }

        $service = $this->makeExternalTramiteService();
        $result = $service->createFromExternalPayload(
            is_array($payload) ? $payload : [],
            $this->request->getFiles(),
            [
                'idempotencyKey' => trim((string) $this->request->getHeaderLine('Idempotency-Key')),
                'sourceSystem' => trim((string) $this->request->getHeaderLine('X-Source-System')),
            ]
        );
        $statusCode = (int) ($result['statusCode'] ?? 200);
        unset($result['statusCode']);

        return $this->response->setStatusCode($statusCode)->setJSON($result);
    }

    public function show($tramiteId)
    {
        $service = $this->makeExternalTramiteService();
        $result = $service->getStatusSnapshot((int) $tramiteId);
        $statusCode = (int) ($result['statusCode'] ?? 200);
        unset($result['statusCode']);

        return $this->response->setStatusCode($statusCode)->setJSON($result);
    }

    public function showByReference($externalReference)
    {
        $service = $this->makeExternalTramiteService();
        $result = $service->getStatusSnapshotByExternalReference(
            urldecode((string) $externalReference),
            trim((string) ($this->request->getHeaderLine('X-Source-System') ?: $this->request->getGet('source_system')))
        );
        $statusCode = (int) ($result['statusCode'] ?? 200);
        unset($result['statusCode']);

        return $this->response->setStatusCode($statusCode)->setJSON($result);
    }
}