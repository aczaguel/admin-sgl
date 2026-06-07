<?php

namespace Tests\App\Controllers\Deskapp;

use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\Controllers\TestableTramitesn;

class TramitesnAttentionBucketTest extends CIUnitTestCase
{
    public function testTrackedLocalTramiteUnderThresholdStaysNormal(): void
    {
        $controller = new TestableTramitesn();

        $result = $controller->classifyAttentionBucketForTest(
            7,
            270,
            date('Y-m-d H:i:s', strtotime('-4 days')),
            null,
            [7, 8, 9]
        );

        $this->assertSame('normal', $result['bucket']);
        $this->assertTrue($result['tracked']);
        $this->assertSame('local', $result['scope']);
    }

    public function testTrackedLocalTramiteAtWarningThresholdStillStaysNormal(): void
    {
        $controller = new TestableTramitesn();

        $result = $controller->classifyAttentionBucketForTest(
            7,
            270,
            date('Y-m-d H:i:s', strtotime('-5 days')),
            null,
            [7, 8, 9]
        );

        $this->assertSame('normal', $result['bucket']);
        $this->assertSame(5, $result['days']);
    }

    public function testTrackedForaneoTramiteAtExpiredThresholdBecomesVencido(): void
    {
        $controller = new TestableTramitesn();

        $result = $controller->classifyAttentionBucketForTest(
            9,
            100,
            date('Y-m-d H:i:s', strtotime('-16 days')),
            null,
            [7, 8, 9]
        );

        $this->assertSame('vencido', $result['bucket']);
        $this->assertSame('foraneo', $result['scope']);
    }

    public function testUntrackedStatusNeverEntersAttentionBuckets(): void
    {
        $controller = new TestableTramitesn();

        $result = $controller->classifyAttentionBucketForTest(
            21,
            270,
            date('Y-m-d H:i:s', strtotime('-40 days')),
            null,
            [7, 8, 9]
        );

        $this->assertSame('normal', $result['bucket']);
        $this->assertFalse($result['tracked']);
    }

    public function testResolveAttentionPresentationReturnsNormalBadgeForTrackedNormal(): void
    {
        $controller = new TestableTramitesn();

        $presentation = $controller->resolveAttentionPresentationForTest([
            'bucket' => 'normal',
            'tracked' => true,
            'days' => 6,
            'scope' => 'local',
        ], 7);

        $this->assertSame('Normal', $presentation['label']);
        $this->assertSame('background-verde', $presentation['class']);
    }

    public function testResolveAttentionPresentationReturnsConcluidoForLockedStatus(): void
    {
        $controller = new TestableTramitesn();

        $presentation = $controller->resolveAttentionPresentationForTest([
            'bucket' => 'normal',
            'tracked' => false,
            'days' => 0,
            'scope' => 'foraneo',
        ], SGL_TRA_STATUS_CONCLUIDO);

        $this->assertSame('Concluido', $presentation['label']);
        $this->assertSame('background-azul', $presentation['class']);
    }

    public function testResolveAttentionPresentationReturnsRedBadgeForTrackedVencido(): void
    {
        $controller = new TestableTramitesn();

        $presentation = $controller->resolveAttentionPresentationForTest([
            'bucket' => 'vencido',
            'tracked' => true,
            'days' => 12,
            'scope' => 'local',
        ], 7);

        $this->assertSame('Vencido', $presentation['label']);
        $this->assertSame('background-rojo', $presentation['class']);
    }

    public function testTrackedStatusIdsExcludeLockedStatusesEvenIfDatabaseStepIsInRange(): void
    {
        $controller = new TestableTramitesn();

        $trackedIds = $controller->getAttentionTrackedStatusIdsForTest();

        $this->assertNotContains(SGL_TRA_STATUS_CONCLUIDO, $trackedIds);
        $this->assertNotContains(SGL_TRA_STATUS_CANCELADO, $trackedIds);
    }

    public function testResolveAttentionListBucketFallsBackToNormalForUnknownValues(): void
    {
        $controller = new TestableTramitesn();

        $this->assertSame('normal', $controller->resolveAttentionListBucketForTest('desconocido'));
        $this->assertSame('vencido', $controller->resolveAttentionListBucketForTest('vencido'));
        $this->assertSame('normal', $controller->resolveAttentionListBucketForTest('attention'));
        $this->assertSame('normal', $controller->resolveAttentionListBucketForTest('riesgo'));
    }

    public function testResolveAttentionListMetaReturnsExpectedTitleForVencidoBucket(): void
    {
        $controller = new TestableTramitesn();

        $meta = $controller->resolveAttentionListMetaForTest('vencido');

        $this->assertSame('Trámites Vencidos', $meta['title']);
        $this->assertSame('Muy tardados', $meta['badge_label']);
        $this->assertSame('vencido', $meta['badge_tone']);
    }

    public function testBuildAttentionBucketSqlTreatsNullMunicipioAsForaneo(): void
    {
        $controller = new TestableTramitesn();

        $sql = $controller->buildAttentionBucketSqlForTest('vencido');

        $this->assertStringContainsString('COALESCE(tramite.ent_municipio_id, 0)', $sql);
    }
}