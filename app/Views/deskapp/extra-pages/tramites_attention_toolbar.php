<?php
$summary = is_array($summary ?? null) ? $summary : [];
$mode = $mode ?? 'normal';
$normalUrl = $normal_url ?? '#';
$attentionUrl = $attention_url ?? '#';
$riesgoTotal = (int) ($summary['riesgo_total'] ?? 0);
$vencidoTotal = (int) ($summary['vencido_total'] ?? 0);
$atencionTotal = (int) ($summary['atencion_total'] ?? 0);
?>
<div style="margin-bottom: 20px; padding: 18px 20px; border-radius: 14px; background: linear-gradient(135deg, #f8fafc, #eef2ff); border: 1px solid #dbe4ff; box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06); display: flex; flex-wrap: wrap; gap: 16px; align-items: center; justify-content: space-between;">
	<div>
		<div style="font-size: 12px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: #475569; margin-bottom: 6px;">Seguimiento de pasos 1 a 3</div>
		<div style="font-size: 14px; color: #334155; max-width: 700px;">
			<?= $mode === 'attention'
				? 'Esta bandeja concentra los trámites en riesgo o vencidos para que no se pierdan en la operación diaria.'
				: 'Esta vista excluye por defecto los trámites en riesgo o vencidos y deja la operación normal limpia.' ?>
		</div>
	</div>
	<div style="display: flex; flex-wrap: wrap; gap: 10px; align-items: center;">
		<a href="<?= esc($normalUrl) ?>" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 14px; border-radius: 999px; text-decoration: none; font-weight: 700; background: <?= $mode === 'normal' ? '#0f172a' : '#ffffff' ?>; color: <?= $mode === 'normal' ? '#ffffff' : '#0f172a' ?>; border: 1px solid <?= $mode === 'normal' ? '#0f172a' : '#cbd5e1' ?>; box-shadow: <?= $mode === 'normal' ? '0 10px 20px rgba(15,23,42,.16)' : 'none' ?>;">
			<span>Flujo normal</span>
		</a>
		<a href="<?= esc($attentionUrl) ?>" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 14px; border-radius: 999px; text-decoration: none; font-weight: 700; background: <?= $mode === 'attention' ? '#991b1b' : '#ffffff' ?>; color: <?= $mode === 'attention' ? '#ffffff' : '#991b1b' ?>; border: 1px solid <?= $mode === 'attention' ? '#991b1b' : '#fecaca' ?>; box-shadow: <?= $mode === 'attention' ? '0 10px 20px rgba(153,27,27,.18)' : 'none' ?>;">
			<span>Atención</span>
			<span style="display:inline-flex;align-items:center;justify-content:center;min-width:22px;height:22px;padding:0 7px;border-radius:999px;background:<?= $mode === 'attention' ? 'rgba(255,255,255,.2)' : '#fee2e2' ?>;font-size:12px;"><?= esc((string) $atencionTotal) ?></span>
		</a>
	</div>
	<div style="display:flex; gap:10px; flex-wrap: wrap; width: 100%;">
		<div style="display:inline-flex; align-items:center; gap:8px; padding:8px 12px; border-radius:999px; background:#fef3c7; color:#92400e; font-weight:700; font-size:13px;">
			<span>En riesgo</span>
			<span><?= esc((string) $riesgoTotal) ?></span>
		</div>
		<div style="display:inline-flex; align-items:center; gap:8px; padding:8px 12px; border-radius:999px; background:#fee2e2; color:#991b1b; font-weight:700; font-size:13px;">
			<span>Vencidos</span>
			<span><?= esc((string) $vencidoTotal) ?></span>
		</div>
	</div>
</div>