<?php

namespace Tests\Support\Controllers;

use App\Controllers\Deskapp\Tramitesn;

class TestableTramitesn extends Tramitesn
{
    public static $tramiteRow = [];
    public static $denyTenantAccess = false;
    public static $useDatabaseLookups = false;
    public static $skipLegacyFinalSaveSideEffects = false;
    public static $skipLegacyUpdateSaveSideEffects = false;
    public static $fakeUploadMoves = false;

    public function __construct()
    {
    }

    protected function requireJsonTenantAccess(int $tramiteId, int $userId, array $roles)
    {
        if (!static::$denyTenantAccess) {
            return null;
        }

        helper('acl_guard');

        return acl_deny('Acceso denegado.', 403, null, true);
    }

    protected function getTramiteRowWithStatuses(int $tramiteId): array
    {
        if (static::$useDatabaseLookups) {
            return parent::getTramiteRowWithStatuses($tramiteId);
        }

        return static::$tramiteRow;
    }

    protected function getTramiteRowWithFolioAndStatuses(int $tramiteId): array
    {
        if (static::$useDatabaseLookups) {
            return parent::getTramiteRowWithFolioAndStatuses($tramiteId);
        }

        return static::$tramiteRow;
    }

    protected function recordFinalSaveBitacora(array $changes, int $tramiteId, int $userId): void
    {
        if (static::$skipLegacyFinalSaveSideEffects) {
            return;
        }

        parent::recordFinalSaveBitacora($changes, $tramiteId, $userId);
    }

    protected function recordFinalSaveUserLog(int $tramiteId, int $userId): void
    {
        if (static::$skipLegacyFinalSaveSideEffects) {
            return;
        }

        parent::recordFinalSaveUserLog($tramiteId, $userId);
    }

    protected function recordUpdateSaveSideEffects(int $tramiteId, ?string $folio, int $userId, int $statusUpdatedTo, array $changes): void
    {
        if (static::$skipLegacyUpdateSaveSideEffects) {
            return;
        }

        parent::recordUpdateSaveSideEffects($tramiteId, $folio, $userId, $statusUpdatedTo, $changes);
    }

    protected function moveCobroClienteUploadedFile(string $tempFile, string $targetFile): bool
    {
        if (!static::$fakeUploadMoves) {
            return parent::moveCobroClienteUploadedFile($tempFile, $targetFile);
        }

        if (!is_file($tempFile)) {
            return false;
        }

        return copy($tempFile, $targetFile);
    }

    public function hasPendingPagoConciliationForTest(int $tramiteId): bool
    {
        return $this->hasPendingPagoConciliation($tramiteId);
    }

    public function classifyAttentionBucketForTest(int $statusId, ?int $municipioId, ?string $startedAt, ?string $createdAt = null, ?array $trackedStatusIds = null): array
    {
        return $this->classifyAttentionBucket($statusId, $municipioId, $startedAt, $createdAt, $trackedStatusIds);
    }

    public function resolveAttentionPresentationForTest(array $daysData, int $statusId): array
    {
        return $this->resolveAttentionPresentation($daysData, $statusId);
    }

    public function getAttentionTrackedStatusIdsForTest(): array
    {
        return $this->getAttentionTrackedStatusIds();
    }

    public function resolveAttentionListBucketForTest(?string $bucket): string
    {
        return $this->resolveAttentionListBucket($bucket);
    }

    public function resolveAttentionListMetaForTest(string $bucket): array
    {
        return $this->resolveAttentionListMeta($bucket);
    }

    public function buildAttentionBucketSqlForTest(string $bucket = 'attention', string $tableAlias = 'tramite'): string
    {
        return $this->buildAttentionBucketSql($bucket, $tableAlias);
    }
}