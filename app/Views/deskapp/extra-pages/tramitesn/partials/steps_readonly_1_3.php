<div class="sgl-step-center">
	<?php if ($showSection('generales')): ?>
	<div class="sgl-step-form-ribbon <?= !empty($step1_complete) ? 'is-complete' : 'is-incomplete' ?>" data-ribbon-step="1">
		<div class="sgl-icon"><i class="<?= !empty($step1_complete) ? 'fas fa-check' : 'fas fa-exclamation' ?>"></i></div>
		<div class="sgl-text">Paso 1: Datos del tramite</div>
		<button class="btn btn-sm btn-outline-secondary sgl-btn-icon" type="button" data-toggle="collapse" data-target="#collapsePaso1" aria-expanded="false" aria-controls="collapsePaso1">
			<i class="fas fa-chevron-down"></i>
		</button>
	</div>

	<div id="collapsePaso1" class="collapse">
	<div class="sgl-soft-panel">
		<p class="sgl-soft-panel-title">Tipos de tramite ligados</p>
		<div class="d-flex flex-wrap" style="gap:8px;">
			<?php if (!empty($servicios_asociados) && is_array($servicios_asociados)): ?>
				<?php foreach ($servicios_asociados as $srv): ?>
					<span class="badge badge-success badge-pill sgl-pill">✓ <?= esc($srv['label'] ?? '') ?></span>
				<?php endforeach; ?>
			<?php else: ?>
				<span class="badge badge-secondary badge-pill sgl-pill">Sin tipos ligados</span>
			<?php endif; ?>
		</div>
	</div>

	<div class="sgl-soft-panel mt-3">
		<p class="sgl-soft-panel-title">Datos del tramite</p>
		<div class="sgl-info-grid">
			<?php if (!empty($readonly_step1) && is_array($readonly_step1)): ?>
				<?php foreach ($readonly_step1 as $item): ?>
					<?php
						$label = $item['label'] ?? '';
						$val = $item['value'] ?? '';
						$display = ($val === null || $val === '') ? '--' : $val;
					?>
					<div class="sgl-info-item">
						<div class="sgl-info-label"><?= esc($label) ?></div>
						<div class="sgl-info-value"><?= esc($display) ?></div>
					</div>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
	</div>
	</div>
	<?php endif; ?>

	<?php if ($showSection('asigna_gestor')): ?>
	<div class="sgl-step-form-ribbon mt-3 <?= !empty($step2_complete) ? 'is-complete' : 'is-incomplete' ?>" data-ribbon-step="2">
		<div class="sgl-icon"><i class="<?= !empty($step2_complete) ? 'fas fa-check' : 'fas fa-exclamation' ?>"></i></div>
		<div class="sgl-text">Paso 2: Gestor y Empresa</div>
		<button class="btn btn-sm btn-outline-secondary sgl-btn-icon" type="button" data-toggle="collapse" data-target="#collapsePaso2" aria-expanded="false" aria-controls="collapsePaso2">
			<i class="fas fa-chevron-down"></i>
		</button>
	</div>

	<div id="collapsePaso2" class="collapse">
	<div class="sgl-soft-panel mt-3">
		<p class="sgl-soft-panel-title">Gestor y Empresa</p>
		<div class="sgl-info-grid">
			<?php if (!empty($readonly_step2) && is_array($readonly_step2)): ?>
				<?php foreach ($readonly_step2 as $item): ?>
					<?php
						$label = $item['label'] ?? '';
						$val = $item['value'] ?? '';
						$display = ($val === null || $val === '') ? '--' : $val;
					?>
					<div class="sgl-info-item">
						<div class="sgl-info-label"><?= esc($label) ?></div>
						<div class="sgl-info-value"><?= esc($display) ?></div>
					</div>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
	</div>
	</div>
	<?php endif; ?>

	<?php if ($showSection('pago_derechos')): ?>
	<div class="sgl-step-form-ribbon mt-3 <?= !empty($step3_complete) ? 'is-complete' : 'is-incomplete' ?>" data-ribbon-step="3">
		<div class="sgl-icon"><i class="<?= !empty($step3_complete) ? 'fas fa-check' : 'fas fa-exclamation' ?>"></i></div>
		<div class="sgl-text">Paso 3: Pagos de Derechos</div>
		<button class="btn btn-sm btn-outline-secondary sgl-btn-icon" type="button" data-toggle="collapse" data-target="#collapsePaso3" aria-expanded="false" aria-controls="collapsePaso3">
			<i class="fas fa-chevron-down"></i>
		</button>
	</div>

	<div id="collapsePaso3" class="collapse">
	<div class="sgl-soft-panel mt-3">
		<p class="sgl-soft-panel-title">Datos de pagos de derechos</p>
		<div class="sgl-info-grid">
			<?php if (!empty($readonly_step3) && is_array($readonly_step3)): ?>
				<?php foreach ($readonly_step3 as $item): ?>
					<?php
						$label = $item['label'] ?? '';
						$val = $item['value'] ?? '';
						$display = ($val === null || $val === '') ? '--' : $val;
					?>
					<div class="sgl-info-item">
						<div class="sgl-info-label"><?= esc($label) ?></div>
						<div class="sgl-info-value"><?= esc($display) ?></div>
					</div>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
	</div>

	<div class="sgl-soft-panel mt-3">
		<p class="sgl-soft-panel-title">Documentos de derechos</p>
		<div class="gallery-preview" id="documentos-container-readonly">
			<?php if (!empty($pago_derechos_db) && is_array($pago_derechos_db)): ?>
				<?php foreach ($pago_derechos_db as $doc): ?>
					<?php
						$fileName = (string) ($doc['file'] ?? '');
						$fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
						$isImage = in_array($fileExt, ['jpg','jpeg','png','gif','webp'], true);
						$fileUrl = base_url('/assets/uploads/pago_derechos/' . $id . '/' . $fileName);
					?>
					<div class="file-preview" data-file="<?= esc($fileName) ?>" style="position:relative;border:1px solid #ddd;border-radius:5px;padding:5px;background-color:#f9f9f9;display:inline-block;margin:4px;text-align:center;">
						<a href="<?= esc($fileUrl) ?>" target="_blank">
							<?php if ($isImage): ?>
								<img src="<?= esc($fileUrl) ?>" alt="<?= esc($fileName) ?>" class="img-thumbnail" style="width:60px;height:60px;object-fit:cover;">
							<?php else: ?>
								<i class="far fa-file" style="font-size:32px;color:#6b7280;"></i>
							<?php endif; ?>
						</a>
						<p style="font-size:10px;margin-top:5px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
							<?= esc($fileName) ?>
						</p>
					</div>
				<?php endforeach; ?>
			<?php else: ?>
				<div class="text-muted">Sin documentos registrados.</div>
			<?php endif; ?>
		</div>
	</div>
	</div>
	<?php endif; ?>
</div>