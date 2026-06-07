<?php
$summary = is_array($summary ?? null) ? $summary : [];
$mode = in_array(($mode ?? 'normal'), ['normal', 'vencido'], true) ? $mode : 'normal';
$normalUrl = $normal_url ?? '#';
$vencidoUrl = $vencido_url ?? '#';
$vencidoTotal = (int) ($summary['vencido_total'] ?? 0);
$modeCopy = [
	'normal' => 'Esta vista muestra el flujo operativo normal y deja aparte sólo los trámites vencidos.',
    'vencido' => 'Aquí ves los trámites ya muy tardados, es decir, los que ya superaron el umbral operativo.',
];
?>
<div style="margin-bottom: 20px; padding: 18px 20px; border-radius: 14px; background: linear-gradient(135deg, #f8fafc, #eef2ff); border: 1px solid #dbe4ff; box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06); display: flex; flex-wrap: wrap; gap: 16px; align-items: center; justify-content: space-between;">
	<div>
		<div style="font-size: 12px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: #475569; margin-bottom: 6px;">Seguimiento de pasos 1 a 3</div>
		<div style="font-size: 14px; color: #334155; max-width: 700px;">
			<?= esc($modeCopy[$mode] ?? $modeCopy['normal']) ?>
		</div>
	</div>
	<div style="display: flex; flex-wrap: wrap; gap: 10px; align-items: center;">
		<a href="<?= esc($normalUrl) ?>" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 14px; border-radius: 999px; text-decoration: none; font-weight: 700; background: <?= $mode === 'normal' ? '#0f172a' : '#ffffff' ?>; color: <?= $mode === 'normal' ? '#ffffff' : '#0f172a' ?>; border: 1px solid <?= $mode === 'normal' ? '#0f172a' : '#cbd5e1' ?>; box-shadow: <?= $mode === 'normal' ? '0 10px 20px rgba(15,23,42,.16)' : 'none' ?>;">
			<span>Flujo normal</span>
		</a>
		<a href="<?= esc($vencidoUrl) ?>" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 14px; border-radius: 999px; text-decoration: none; font-weight: 700; background: <?= $mode === 'vencido' ? '#312e81' : '#ffffff' ?>; color: <?= $mode === 'vencido' ? '#ffffff' : '#312e81' ?>; border: 1px solid <?= $mode === 'vencido' ? '#312e81' : '#c7d2fe' ?>; box-shadow: <?= $mode === 'vencido' ? '0 10px 20px rgba(49,46,129,.18)' : 'none' ?>;">
			<span>Vencidos</span>
			<span style="display:inline-flex;align-items:center;justify-content:center;min-width:22px;height:22px;padding:0 7px;border-radius:999px;background:<?= $mode === 'vencido' ? 'rgba(255,255,255,.2)' : '#e0e7ff' ?>;font-size:12px;"><?= esc((string) $vencidoTotal) ?></span>
		</a>
	</div>
	<div style="display:flex; gap:10px; flex-wrap: wrap; width: 100%;">
		<a href="<?= esc($vencidoUrl) ?>" style="display:inline-flex; align-items:center; gap:8px; padding:8px 12px; border-radius:999px; background:#e0e7ff; color:#312e81; font-weight:700; font-size:13px; text-decoration:none;">
			<span>Vencidos</span>
			<span><?= esc((string) $vencidoTotal) ?></span>
		</a>
	</div>
</div>