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

    public function testTrackedLocalTramiteAtWarningThresholdBecomesRiesgo(): void
    {
        $controller = new TestableTramitesn();

        $result = $controller->classifyAttentionBucketForTest(
            7,
            270,
            date('Y-m-d H:i:s', strtotime('-5 days')),
            null,
            [7, 8, 9]
        );

        $this->assertSame('riesgo', $result['bucket']);
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

    public function testResolveAttentionPresentationReturnsRiskBadgeForTrackedRiesgo(): void
    {
        $controller = new TestableTramitesn();

        $presentation = $controller->resolveAttentionPresentationForTest([
            'bucket' => 'riesgo',
            'tracked' => true,
            'days' => 6,
            'scope' => 'local',
        ], 7);

        $this->assertSame('En riesgo', $presentation['label']);
        $this->assertSame('background-amarillo', $presentation['class']);
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
}