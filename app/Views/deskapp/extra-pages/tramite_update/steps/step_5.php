<?php if (isset($tra_status_id) && in_array($tra_status_id, [23, 27, 28, 20, 21])) : ?>
<div class="modal fade" id="modal-cobro-cliente" tabindex="-1" role="dialog" aria-labelledby="modalCobroClienteLabel" aria-hidden="true">
	<div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
		<div class="modal-content">
			<div class="modal-header" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white;">
				<h4 class="modal-title" id="modalCobroClienteLabel">
					<i class="fas fa-money-check-alt"></i> Cobros al Cliente
				</h4>
				<button type="button" class="close text-white" data-dismiss="modal" aria-hidden="true">x</button>
			</div>
			<div class="modal-body">
				<div class="pd-20">
					<?php
						if (!empty($output_cobro_cliente)) {
							echo $output_cobro_cliente;
						} else {
							echo '<div class="alert alert-info"><i class="fas fa-info-circle"></i> No hay registros de cobros al cliente</div>';
						}
					?>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
			</div>
		</div>
	</div>
</div>
<?php endif; ?>

<?php if (isset($tra_status_id) && in_array($tra_status_id, [23, 27, 28, 20, 21])) : ?>
<div class="modal fade" id="modal-evidencias-finales" tabindex="-1" role="dialog" aria-labelledby="modalEvidenciasFinalesLabel" aria-hidden="true">
	<div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
		<div class="modal-content">
			<div class="modal-header" style="background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); color: #333;">
				<h4 class="modal-title" id="modalEvidenciasFinalesLabel">
					<i class="fas fa-check-double"></i> Evidencias Finales
				</h4>
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
			</div>
			<div class="modal-body">
				<div class="pd-20">
					<?php
						if (!empty($outputevidencias_finales)) {
							echo $outputevidencias_finales;
						} else {
							echo '<div class="alert alert-info"><i class="fas fa-info-circle"></i> No hay evidencias finales disponibles</div>';
						}
					?>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
			</div>
		</div>
	</div>
</div>
<?php endif; ?>
