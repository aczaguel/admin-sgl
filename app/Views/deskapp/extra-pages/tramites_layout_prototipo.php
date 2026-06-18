<?php
$isEmbeddedPrototypeBody = !empty($isEmbeddedPrototypeBody);
$activeStep = isset($activeStep) ? (int) $activeStep : 2;
$prototypeTramiteId = isset($prototypeTramiteId) ? (int) $prototypeTramiteId : 12454;
$prototypeCanApproveStep2 = !empty($prototypeCanApproveStep2);
$prototypeStep1Form = !empty($prototypeStep1Form) && is_array($prototypeStep1Form) ? $prototypeStep1Form : [];
$prototypeStep1CanEdit = !empty($prototypeStep1Form['canEdit']);
$prototypeStep1BlockedReason = trim((string) ($prototypeStep1Form['blockedReason'] ?? ''));
$prototypeStep1ServicesForm = !empty($prototypeStep1ServicesForm) && is_array($prototypeStep1ServicesForm) ? $prototypeStep1ServicesForm : [];
$prototypeStep1ServicesBlockedReason = trim((string) ($prototypeStep1ServicesForm['blockedReason'] ?? ''));
$prototypeStep1DocsForm = !empty($prototypeStep1DocsForm) && is_array($prototypeStep1DocsForm) ? $prototypeStep1DocsForm : [];
$prototypeStep1DocsCanView = !empty($prototypeStep1DocsForm['canView']);
$prototypeStep1DocsCanUpload = !empty($prototypeStep1DocsForm['canUpload']);
$prototypeStep1DocsCanDelete = !empty($prototypeStep1DocsForm['canDelete']);
$prototypeStep1DocsBlockedReason = trim((string) ($prototypeStep1DocsForm['blockedReason'] ?? ''));
$prototypeStep1DocsDeleteBlockedReason = trim((string) ($prototypeStep1DocsForm['deleteBlockedReason'] ?? ''));
$prototypeStep2Form = !empty($prototypeStep2Form) && is_array($prototypeStep2Form) ? $prototypeStep2Form : [];
$prototypeStep2CanEdit = !empty($prototypeStep2Form['canEdit']);
$prototypeStep2BlockedReason = trim((string) ($prototypeStep2Form['blockedReason'] ?? ''));
$prototypeStep2CanUploadDocs = !empty($prototypeStep2Form['canUploadDocs']);
$prototypeStep2CanDeleteDocs = !empty($prototypeStep2Form['canDeleteDocs']);
$prototypeStep2DocsBlockedReason = trim((string) ($prototypeStep2Form['docsBlockedReason'] ?? ''));
$prototypeStep2DeleteBlockedReason = trim((string) ($prototypeStep2Form['deleteBlockedReason'] ?? ''));
$prototypeStep3Form = !empty($prototypeStep3Form) && is_array($prototypeStep3Form) ? $prototypeStep3Form : [];
$prototypeStep3CanUpload = !empty($prototypeStep3Form['canUpload']);
$prototypeStep3BlockedReason = trim((string) ($prototypeStep3Form['blockedReason'] ?? ''));
$prototypeStep3CanDelete = !empty($prototypeStep3Form['canDelete']);
$prototypeStep3DeleteBlockedReason = trim((string) ($prototypeStep3Form['deleteBlockedReason'] ?? ''));
$prototypeStep4Form = !empty($prototypeStep4Form) && is_array($prototypeStep4Form) ? $prototypeStep4Form : [];
$prototypeStep4CanView = !empty($prototypeStep4Form['canView']);
$prototypeStep4CanEdit = !empty($prototypeStep4Form['canEdit']);
$prototypeStep4CanUploadDocs = !empty($prototypeStep4Form['canUploadDocs']);
$prototypeStep4CanDeleteDocs = !empty($prototypeStep4Form['canDeleteDocs']);
$prototypeStep4BlockedReason = trim((string) ($prototypeStep4Form['blockedReason'] ?? ''));
$prototypeStep4UploadBlockedReason = trim((string) ($prototypeStep4Form['uploadBlockedReason'] ?? ''));
$prototypeStep4DeleteBlockedReason = trim((string) ($prototypeStep4Form['deleteBlockedReason'] ?? ''));
$prototypeStep5Form = !empty($prototypeStep5Form) && is_array($prototypeStep5Form) ? $prototypeStep5Form : [];
$prototypeStep5CanView = !empty($prototypeStep5Form['canView']);
$prototypeStep5CanEdit = !empty($prototypeStep5Form['canEdit']);
$prototypeStep5CanUploadDocs = !empty($prototypeStep5Form['canUploadDocs']);
$prototypeStep5CanDeleteDocs = !empty($prototypeStep5Form['canDeleteDocs']);
$prototypeStep5BlockedReason = trim((string) ($prototypeStep5Form['blockedReason'] ?? ''));
$prototypeStep5UploadBlockedReason = trim((string) ($prototypeStep5Form['uploadBlockedReason'] ?? ''));
$prototypeStep5DeleteBlockedReason = trim((string) ($prototypeStep5Form['deleteBlockedReason'] ?? ''));
$prototypeStep4NotesForm = !empty($prototypeStep4NotesForm) && is_array($prototypeStep4NotesForm) ? $prototypeStep4NotesForm : [];
$prototypeStep4NotesCanView = !empty($prototypeStep4NotesForm['canView']);
$prototypeStep4NotesCanAdd = !empty($prototypeStep4NotesForm['canAdd']);
$prototypeStep4NotesBlockedReason = trim((string) ($prototypeStep4NotesForm['blockedReason'] ?? ''));
$prototypeStep4NotesItems = !empty($prototypeStep4NotesForm['items']) && is_array($prototypeStep4NotesForm['items'])
  ? array_values($prototypeStep4NotesForm['items'])
  : [];
$prototypeStep5NotesForm = !empty($prototypeStep5NotesForm) && is_array($prototypeStep5NotesForm) ? $prototypeStep5NotesForm : [];
$prototypeStep5NotesCanView = !empty($prototypeStep5NotesForm['canView']);
$prototypeStep5NotesCanAdd = !empty($prototypeStep5NotesForm['canAdd']);
$prototypeStep5NotesBlockedReason = trim((string) ($prototypeStep5NotesForm['blockedReason'] ?? ''));
$prototypeStep5NotesItems = !empty($prototypeStep5NotesForm['items']) && is_array($prototypeStep5NotesForm['items'])
  ? array_values($prototypeStep5NotesForm['items'])
  : [];
$prototypeEvidenceForm = !empty($prototypeEvidenceForm) && is_array($prototypeEvidenceForm) ? $prototypeEvidenceForm : [];
$prototypeEvidenceCanView = !empty($prototypeEvidenceForm['canView']);
$prototypeEvidenceCanAdd = !empty($prototypeEvidenceForm['canAdd']);
$prototypeEvidenceBlockedReason = trim((string) ($prototypeEvidenceForm['blockedReason'] ?? ''));
$prototypeEvidenceItems = !empty($prototypeEvidenceForm['items']) && is_array($prototypeEvidenceForm['items'])
  ? array_values($prototypeEvidenceForm['items'])
  : [];
$prototypeReadOnlyTramite = !empty($prototypeReadOnlyTramite) && is_array($prototypeReadOnlyTramite)
  ? $prototypeReadOnlyTramite
  : null;
$prototypeStep1Form += [
  'csrfName' => 'csrf_test_name',
  'csrfHash' => '',
  'urls' => [
    'updateSave' => '#',
    'getEjecutivosByClienteIdBase' => '#',
  ],
  'options' => [
    'cliente' => [],
    'ejecutivo' => [],
    'entidad' => [],
  ],
  'values' => [
    'folio' => '',
    'cli_directo_id' => 0,
    'cli_directo_ejecutivo_id' => 0,
    'contrato' => '',
    'unidad' => '',
    'serie' => '',
    'placas' => '',
    'entidad_id' => 0,
    'observaciones' => '',
    'current_step' => 1,
  ],
];
$prototypeStep1ServicesForm += [
  'canManageBase' => false,
  'canEditPrincipal' => false,
  'canEditAsociado' => false,
  'canDeleteAsociado' => false,
  'csrfName' => 'csrf_test_name',
  'csrfHash' => '',
  'urls' => [
    'principalUpdate' => '#',
    'add' => '#',
    'update' => '#',
    'delete' => '#',
  ],
  'tramiteId' => $prototypeTramiteId,
  'principalTipoId' => 0,
  'options' => [
    'traTipos' => [],
  ],
  'services' => [],
];
$prototypeStep1DocsForm += [
  'canView' => false,
  'canUpload' => false,
  'canDelete' => false,
  'blockedReason' => '',
  'deleteBlockedReason' => '',
  'csrfName' => 'csrf_test_name',
  'csrfHash' => '',
  'tramiteId' => $prototypeTramiteId,
  'fileBaseUrl' => '',
  'urls' => [
    'upload' => '#',
    'delete' => '#',
  ],
  'options' => [
    'documentTypes' => [],
    'documentTypeMeta' => [],
  ],
  'documents' => [],
  'summary' => [
    'requiredTotal' => 0,
    'uploadedRequired' => 0,
    'uploadedTotal' => 0,
  ],
];
$prototypeStep2Form += [
  'csrfName' => 'csrf_test_name',
  'csrfHash' => '',
  'currentStatusId' => 0,
  'currentStep' => 0,
  'isApprovedLock' => false,
  'isLockedStatus' => false,
  'urls' => [
    'updateGestorSave' => '#',
    'updateDerechosSave' => '#',
    'getGestoresByEmpresaIdBase' => '#',
  ],
  'options' => [
    'empresaGestora' => [],
    'gestor' => [],
    'derechosPagoSitio' => [],
    'derechosRevolCliente' => [],
  ],
  'values' => [
    'empresa_gestora_id' => 0,
    'gestor_id' => 0,
    'derechos_tramite' => '',
    'derechos_pago_sitio' => '',
    'derechos_vigencia' => '',
    'derechos_revol_cliente' => '',
    'derechos_refer_banc' => '',
  ],
];
$prototypeStep2CurrentStep = (int) ($prototypeStep2Form['currentStep'] ?? 0);
$prototypeStep2IsApprovedLock = !empty($prototypeStep2Form['isApprovedLock']);
$prototypeStep2IsLockedStatus = !empty($prototypeStep2Form['isLockedStatus']);
$prototypeStep2PostApprovalStage = $prototypeStep2CurrentStep > 3 || $prototypeStep2IsApprovedLock;
$prototypeStep4Form += [
  'canUploadDocs' => false,
  'canDeleteDocs' => false,
  'csrfName' => 'csrf_test_name',
  'csrfHash' => '',
  'tramiteId' => $prototypeTramiteId,
  'fileBaseUrl' => '',
  'url' => '#',
  'urls' => [
    'upload' => '#',
    'delete' => '#',
    'getServiceCosts' => '#',
    'updateServiceCost' => '#',
  ],
  'options' => [
    'pagoGestorStatus' => [],
    'statusDoctosGestor' => [],
    'reembolsoStatus' => [],
    'comprobanteFinal' => [],
  ],
  'docs' => [],
  'deleteBlockedReason' => '',
  'values' => [
    'costo_tramite' => '',
    'deposito_gestor' => '',
    'col_a_favor' => '',
    'num_factura_gestor' => '',
    'impuesto_gestoria' => '',
    'gestoria_comision' => '',
    'costo_paqueteria' => '',
    'gestor_total_pago' => '',
    'pago_gestor_st_id' => 0,
    'status_doctos_gestor' => 'en proceso',
    'reembolso_status_id' => 0,
  ],
];
$prototypeStep5Form += [
  'canView' => false,
  'canEdit' => false,
  'canUploadDocs' => false,
  'canDeleteDocs' => false,
  'blockedReason' => '',
  'uploadBlockedReason' => '',
  'deleteBlockedReason' => '',
  'csrfName' => 'csrf_test_name',
  'csrfHash' => '',
  'tramiteId' => $prototypeTramiteId,
  'fileBaseUrl' => '',
  'url' => '#',
  'urls' => [
    'getFiles' => '#',
    'upload' => '#',
    'delete' => '#',
  ],
  'options' => [
    'cobroStatus' => [],
    'cobroCorrecto' => [],
  ],
  'docs' => [],
  'values' => [
    'id_give_cliente' => '',
    'numero_factura' => '',
    'numero_refactura' => '',
    'cobro_status_id' => 0,
    'evidencia_cobro_txt' => '',
    'costo_gestoria' => '0.00',
    'costo_gestoria_hidden' => '0.00',
    'costo_pago_cliente' => '0',
    'comision_derechos' => '0',
    'iva' => '0.00',
    'costo_total' => '0.00',
  ],
];
$prototypeEvidenceForm += [
  'canView' => false,
  'canAdd' => false,
  'blockedReason' => '',
  'csrfName' => 'csrf_test_name',
  'csrfHash' => '',
  'tramiteId' => $prototypeTramiteId,
  'urls' => [
    'create' => '#',
  ],
  'items' => [],
];
$prototypeStep4NotesForm += [
  'canView' => false,
  'canAdd' => false,
  'blockedReason' => '',
  'csrfName' => 'csrf_test_name',
  'csrfHash' => '',
  'tramiteId' => $prototypeTramiteId,
  'urls' => [
    'create' => '#',
  ],
  'items' => [],
];
$prototypeStep5NotesForm += [
  'canView' => false,
  'canAdd' => false,
  'blockedReason' => '',
  'csrfName' => 'csrf_test_name',
  'csrfHash' => '',
  'tramiteId' => $prototypeTramiteId,
  'urls' => [
    'create' => '#',
  ],
  'items' => [],
];
$prototypeStep3Form += [
  'canUpload' => false,
  'blockedReason' => '',
  'csrfName' => 'csrf_test_name',
  'csrfHash' => '',
  'urls' => [
    'upload' => '#',
    'openPagoGestor' => '#',
  ],
  'tramiteId' => $prototypeTramiteId,
  'fileBaseUrl' => '',
  'options' => [
    'comprobanteFinal' => [],
  ],
  'docs' => [],
  'hasTramiteRecibido' => false,
  'hasAcuseRecibo' => false,
];
$prototypeStep2VigenciaInputValue = '';
$prototypeStep2VigenciaRaw = trim((string) ($prototypeStep2Form['values']['derechos_vigencia'] ?? ''));
$prototypeStep2VigenciaWarningText = '';
$prototypeStep2VigenciaIsUrgent = false;
if ($prototypeStep2VigenciaRaw !== '') {
  $prototypeStep2VigenciaInputValue = str_replace(' ', 'T', substr($prototypeStep2VigenciaRaw, 0, 16));
  try {
    $prototypeStep2Now = new DateTimeImmutable('now');
    $prototypeStep2VigenciaDate = new DateTimeImmutable(str_replace('T', ' ', $prototypeStep2VigenciaInputValue));
    $prototypeStep2SecondsRemaining = $prototypeStep2VigenciaDate->getTimestamp() - $prototypeStep2Now->getTimestamp();
    $prototypeStep2DaysRemaining = (int) floor($prototypeStep2SecondsRemaining / 86400);
    if ($prototypeStep2SecondsRemaining < 0) {
      $prototypeStep2VigenciaIsUrgent = true;
      $prototypeStep2VigenciaWarningText = 'La referencia ya vencio. Se requiere atencion inmediata.';
    } elseif ($prototypeStep2DaysRemaining <= 15) {
      $prototypeStep2VigenciaIsUrgent = true;
      $prototypeStep2VigenciaWarningText = 'La referencia vence en ' . $prototypeStep2DaysRemaining . ' dia(s). Se requiere premura antes de que venza.';
    }
  } catch (Throwable $e) {
    $prototypeStep2VigenciaWarningText = '';
    $prototypeStep2VigenciaIsUrgent = false;
  }
}
if ($activeStep < 1 || $activeStep > 5) {
  $activeStep = 2;
}

$stepLabels = [
  1 => 'Informacion general',
  2 => 'Gestion y documentos',
  3 => 'Evidencias finales',
  4 => 'Pago a gestor',
  5 => 'Cobro a cliente',
];

$stepCopies = [
  1 => 'Base del expediente y contexto inicial del tramite.',
  2 => 'Etapa operativa con su propia zona documental y validaciones.',
  3 => 'Cierre documental previo al arranque de la parte financiera.',
  4 => 'Salida financiera independiente y posterior al cierre documental.',
  5 => 'Recuperacion hacia cliente, separada del pago al gestor.',
];

$stepNextActions = [
  1 => 'Completar datos base',
  2 => 'Validar evidencia',
  3 => 'Liberar cierre documental',
  4 => 'Registrar comprobantes del gestor',
  5 => 'Confirmar recuperacion del cliente',
];

$stepProgress = [1 => 20, 2 => 42, 3 => 63, 4 => 82, 5 => 94];
$stepRisk = [1 => 'Bajo', 2 => 'Bajo', 3 => 'Medio', 4 => 'Controlado', 5 => 'Seguimiento'];
$stepDocCounts = [1 => 'sin uploader', 2 => '3 cargados', 3 => '2 finales', 4 => '2 pago gestor', 5 => '2 cobro cliente'];
$stepDocTitles = [
  1 => 'Referencias del expediente en paso 1',
  2 => 'Documentos relacionados del paso',
  3 => 'Evidencias finales del tramite',
  4 => 'Comprobantes de pago a gestor',
  5 => 'Documentos de cobro a cliente',
];
$stepDocCopies = [
  1 => 'En el flujo actual, el paso 1 no opera un dropzone propio. Esta zona sirve para comunicar referencias y soportes base sin inventar un uploader que no existe.',
  2 => 'La zona documental se representa como parte del paso actual y no como un bloque global unico.',
  3 => 'Esta etapa concentra el cierre documental previo a cualquier movimiento financiero posterior.',
  4 => 'Pago a gestor conserva su propia zona documental y no debe mezclarse con evidencias finales ni con cobro a cliente.',
  5 => 'Cobro a cliente tiene su propia logica documental y de recuperacion, separada del pago al gestor.',
];
$stepDocFiles = [
  1 => ['solicitud_apertura_demo.pdf', 'identificacion_cliente_demo.jpg'],
  2 => ['solicitud_firmada_demo.pdf', 'acuse_gestion_demo.jpg', 'referencia_interna_demo.png'],
  3 => ['tramite_recibido_demo.pdf', 'acuse_recibo_cliente_demo.jpg'],
  4 => ['factura_gestor_demo.pdf', 'comprobante_pago_demo.jpg'],
  5 => ['factura_cliente_demo.pdf', 'acuse_cobro_demo.png'],
];
$stepSaveContracts = [
  1 => [
    ['label' => 'Guardado principal', 'endpoint' => 'Datos base del expediente', 'note' => 'Persistencia de identidad del expediente y datos base del tramite.'],
    ['label' => 'Tipos asociados', 'endpoint' => 'Altas, cambios y bajas de ligados', 'note' => 'Subflujo propio del paso 1 para ligar, cambiar o quitar tipos asociados.'],
    ['label' => 'Tipo principal', 'endpoint' => 'Cambio del servicio principal', 'note' => 'Cambio del tipo principal con sincronizacion sobre tra_tramite_asociado.'],
  ],
  2 => [
    ['label' => 'Gestor y empresa', 'endpoint' => 'Asignacion operativa', 'note' => 'Guardar asignacion de gestor y empresa gestora.'],
    ['label' => 'Pago de derechos', 'endpoint' => 'Captura de derechos', 'note' => 'Guardar derechos y evidencia del bloque actual.'],
  ],
  3 => [
    ['label' => 'Cierre documental', 'endpoint' => 'Registro de evidencias finales', 'note' => 'Persistencia de evidencias finales antes del pago a gestor.'],
  ],
  4 => [
    ['label' => 'Pago a gestor', 'endpoint' => 'Captura financiera', 'note' => 'Guardar datos financieros del pago al gestor.'],
    ['label' => 'Documentos del paso', 'endpoint' => 'Carga y retiro de comprobantes', 'note' => 'Surface documental propia del paso 4.'],
  ],
  5 => [
    ['label' => 'Archivos de cobro', 'endpoint' => 'Consulta documental', 'note' => 'Listado documental del paso 5.'],
    ['label' => 'Carga documental', 'endpoint' => 'Carga y retiro de documentos', 'note' => 'Surface propia de cobro a cliente; no sigue un save unico clasico.'],
  ],
];

$currentStepLabel = $stepLabels[$activeStep];
$currentStepCopy = $stepCopies[$activeStep];
$currentStepNextAction = $stepNextActions[$activeStep];
$currentStepProgress = $stepProgress[$activeStep];
$currentStepRisk = $stepRisk[$activeStep];
$currentStepDocCount = $stepDocCounts[$activeStep];
$currentStepDocTitle = $stepDocTitles[$activeStep];
$currentStepDocCopy = $stepDocCopies[$activeStep];
$currentStepDocFiles = $stepDocFiles[$activeStep];
$currentStepSaveContracts = $stepSaveContracts[$activeStep];
$isOperationalBasePhase = true; // pantalla unificada: siempre modo operativo completo
$useThreeRailLayout = true;
$hasRealStepContext = !empty($prototypeReadOnlyTramite);
$hasRealOperationalBaseContext = $hasRealStepContext;
$hasRealStep1Context = $hasRealStepContext;
$hasRealStep4Context = $hasRealStepContext;

$prototypeCurrentSurfaceMode = 'Solo lectura';
if ($activeStep === 1 && $prototypeStep1CanEdit) {
  $prototypeCurrentSurfaceMode = 'Editable';
} elseif ($activeStep === 2 && $prototypeStep2CanEdit) {
  $prototypeCurrentSurfaceMode = 'Editable';
} elseif ($activeStep === 3 && $prototypeStep3CanUpload) {
  $prototypeCurrentSurfaceMode = 'Editable';
} elseif ($activeStep === 4 && $prototypeStep4CanEdit) {
  $prototypeCurrentSurfaceMode = 'Editable';
}

$prototypeHeroTitle = 'SOF-DEMO-260604-08 · Constancia No Infraccion';
$prototypeHeroDetailChips = [
  ['label' => 'ID', 'value' => '12454'],
  ['label' => 'Estatus', 'value' => 'En captura'],
  ['label' => 'Contrato', 'value' => 'CONTRATO-DEMO-008'],
  ['label' => 'Responsable', 'value' => 'Luisa Flores'],
];
$prototypeRealContextCopy = 'Este prototipo toma datos del tramite ' . (int) $prototypeTramiteId . ' y reutiliza contratos reales del flujo actual. El modo visible de esta superficie es ' . strtolower($prototypeCurrentSurfaceMode) . '.';
if ($hasRealStepContext) {
  $prototypeHeroTitle = trim((string) ($prototypeReadOnlyTramite['folio'] ?? ('Tramite #' . $prototypeTramiteId)) . ' · ' . (string) ($prototypeReadOnlyTramite['tipo_principal_label'] ?? 'Sin tipo principal'));
  $prototypeHeroDetailChips = [
    ['label' => 'ID', 'value' => (string) (int) ($prototypeReadOnlyTramite['id'] ?? $prototypeTramiteId)],
    ['label' => 'Estatus', 'value' => (string) ($prototypeReadOnlyTramite['tra_status_label'] ?? 'Sin estatus')],
    ['label' => 'Contrato', 'value' => (string) ($prototypeReadOnlyTramite['contrato'] ?? '--')],
    ['label' => 'Responsable', 'value' => (string) ($prototypeReadOnlyTramite['ejecutivo_name'] ?? 'Sin ejecutivo')],
  ];
}

$step1IdentityFields = [
  ['label' => 'Contrato', 'value' => 'CONTRATO-DEMO-008', 'type' => 'input'],
  ['label' => 'Unidad', 'value' => 'UNIDAD-DEMO-8', 'type' => 'input'],
  ['label' => 'Serie', 'value' => 'SERIE-DEMO-8', 'type' => 'input'],
  ['label' => 'Placas', 'value' => 'DEM0008', 'type' => 'input'],
  ['label' => 'Entidad', 'value' => 'Aguascalientes', 'type' => 'select'],
  ['label' => 'Observaciones', 'value' => 'Cliente solicita confirmar vigencia antes de pasar a autorizacion. La captura debe sentirse ordenada y guiada, no como una masa de campos.', 'type' => 'textarea', 'wide' => true],
];

$step1LinkedFields = [
  ['label' => 'Cliente', 'value' => 'Cliente Directo Sofia', 'type' => 'select'],
  ['label' => 'Ejecutivo', 'value' => 'Ejecutivo Sofia', 'type' => 'select'],
];

$step1MetaSignals = [
  ['label' => 'Folio', 'value' => 'SOF-DEMO-260604-08', 'note' => 'Se conserva oculto en el guardado real.'],
  ['label' => 'Completa paso', 'value' => 'Contrato + Entidad', 'note' => 'Regla actual de step1_complete.'],
  ['label' => 'Persistencia', 'value' => 'Datos base del expediente', 'note' => 'Guarda identidad del expediente en tramite.'],
];

$step1ServiceCards = [
  ['title' => 'Tipo principal', 'value' => 'Constancia No Infraccion', 'note' => 'Vive en tramite.tra_tipos_id y marca la regla de duplicados con la serie.'],
  ['title' => 'Tipos ligados', 'value' => '2 asociados', 'note' => 'Se administran en tra_tramite_asociado y no deben repetir el principal.'],
];

$step1TypeBadges = [
  ['label' => 'Constancia No Infraccion', 'state' => 'principal'],
  ['label' => 'Tenencia', 'state' => 'asociado'],
  ['label' => 'Reporte de robo', 'state' => 'asociado'],
];

$step1AssociatedRows = [
  ['label' => 'Tenencia', 'kind' => 'Asociado editable', 'actions' => 'Cambiar / Eliminar'],
  ['label' => 'Reporte de robo', 'kind' => 'Asociado editable', 'actions' => 'Cambiar / Eliminar'],
];

$step1RuleHighlights = [
  ['label' => 'Validacion minima', 'value' => 'folio + contrato obligatorios; el step se marca completo con contrato + entidad.'],
  ['label' => 'Regla critica', 'value' => 'Bloquea duplicados por tipo principal + serie dentro del ultimo ano.'],
  ['label' => 'Bloqueo de negocio', 'value' => 'Si el tramite ya fue aprobado o concluido, los pasos 1-3 quedan readonly.'],
];

$step1PermissionStates = [
  ['label' => 'Base del paso', 'value' => 'write_tramite_datos_tramite', 'note' => 'Habilita el guardado de identidad y el subflujo del paso 1.'],
  ['label' => 'Editar principal', 'value' => 'editar_tramite_principal', 'note' => 'Gobierna el cambio del tipo principal.'],
  ['label' => 'Editar asociados', 'value' => 'editar_tramite_asociado / delete_tramite_asociado', 'note' => 'Controla cambiar y eliminar tipos ligados.'],
];

$baseSectionTitle = $isOperationalBasePhase
  ? 'Identidad del expediente'
  : ($activeStep === 5 ? 'Resumen de recuperacion' : 'Datos base del tramite');
$baseSectionCopy = $isOperationalBasePhase
  ? 'Primer frente real del paso: identidad del expediente y datos que terminan en la tabla tramite.'
  : ($activeStep === 5
      ? 'Bloque principal con la lectura minima de cobranza para confirmar recuperacion, soporte y seguimiento sin mezclarlo con Pago a gestor.'
      : 'Bloque principal con los campos de contexto que el usuario necesita confirmar o ajustar antes de bajar al detalle operativo del paso.');

$stepMiniSummary = [
  1 => [
    ['label' => 'Estado del paso', 'value' => 'Separado en identidad del expediente y composicion del servicio.'],
    ['label' => 'Campo critico', 'value' => 'Serie + tipo principal disparan la regla de duplicados.'],
  ],
  2 => [
    ['label' => 'Estado del paso', 'value' => 'Asignacion de gestor y gestion documental del tramo operativo.'],
    ['label' => 'Campo critico', 'value' => 'No mezclar la asignacion con evidencias de cierre ni con finanzas.'],
  ],
  3 => [
    ['label' => 'Estado del paso', 'value' => 'Cierre documental previo al arranque financiero.'],
    ['label' => 'Campo critico', 'value' => 'Las evidencias finales deben quedar completas antes de pago a gestor.'],
  ],
  4 => [
    ['label' => 'Estado del paso', 'value' => 'Lectura financiera del pago a gestor y su superficie documental propia.'],
    ['label' => 'Campo critico', 'value' => 'Conciliar factura, comprobante y estatus de reembolso.'],
  ],
  5 => [
    ['label' => 'Estado del paso', 'value' => 'Recuperacion hacia cliente con zona documental separada.'],
    ['label' => 'Campo critico', 'value' => 'No mezclar cobro a cliente con pago a gestor ni con cierre documental.'],
  ],
];

$stepChecklist = [
  1 => [
    ['label' => '1. Confirmar identidad', 'value' => 'Contrato, cliente y entidad definidos.'],
    ['label' => '2. Revisar composicion', 'value' => 'Tipo principal y asociados sin duplicidades.'],
    ['label' => '3. Validar edicion', 'value' => 'Base y composicion deben quedar habilitadas donde corresponda.'],
  ],
  2 => [
    ['label' => '1. Asignar gestor', 'value' => 'Listo'],
    ['label' => '2. Subir docs del paso', 'value' => 'Pendiente'],
    ['label' => '3. Validar responsables', 'value' => 'Pendiente'],
  ],
  3 => [
    ['label' => '1. Recepcion final', 'value' => 'Listo'],
    ['label' => '2. Revisar documentos', 'value' => 'Listo'],
    ['label' => '3. Liberar cierre', 'value' => 'Pendiente'],
  ],
  4 => [
    ['label' => '1. Registrar pago', 'value' => 'Listo'],
    ['label' => '2. Subir docs del paso', 'value' => 'Listo'],
    ['label' => '3. Revisar conciliacion', 'value' => 'Pendiente'],
  ],
  5 => [
    ['label' => '1. Emitir cobro', 'value' => 'Pendiente'],
    ['label' => '2. Validar evidencia', 'value' => 'Pendiente'],
    ['label' => '3. Confirmar recuperacion', 'value' => 'Pendiente'],
  ],
];

$step2AssignmentFields = [
  ['label' => 'Empresa gestora', 'value' => 'Sin empresa', 'type' => 'select'],
  ['label' => 'Gestor', 'value' => 'Sin asignar', 'type' => 'select'],
];

$step2DerechosFields = [
  ['label' => 'Monto pago de derechos', 'value' => '0.00', 'type' => 'input'],
  ['label' => 'Pago', 'value' => 'En Linea', 'type' => 'select'],
  ['label' => 'Fecha vigencia', 'value' => '', 'type' => 'input'],
  ['label' => 'Forma de pago', 'value' => 'Fondo Revolvente', 'type' => 'select'],
  ['label' => 'Referencia bancaria', 'value' => '', 'type' => 'input'],
];

$step2CompletionSignals = [
  ['label' => 'Asignacion', 'value' => 'Pendiente', 'note' => 'Empresa gestora y gestor son obligatorios para abrir el frente operativo.'],
  ['label' => 'Pago de derechos', 'value' => 'Pendiente', 'note' => 'Monto, forma de pago y referencia bancaria son el minimo real del bloque.'],
];

$step2DocPreviewItems = ['Sin archivos de derechos registrados'];

$step3EvidenceChips = [
  ['label' => 'Tramite Entregado por Gestor', 'isSuccess' => false],
  ['label' => 'Acuse de Recibo del Cliente', 'isSuccess' => false],
];

$step3EvidenceDocItems = ['Sin evidencias finales registradas'];
$step3EvidenceNote = 'Aqui solo viven las evidencias finales del cierre operativo: tramite entregado y acuse del cliente.';

if ($hasRealOperationalBaseContext) {
  $step1LinkedFields = [
    ['label' => 'Cliente', 'value' => (string) ($prototypeReadOnlyTramite['cliente_name'] ?? 'Sin cliente'), 'type' => 'select'],
    ['label' => 'Ejecutivo', 'value' => (string) ($prototypeReadOnlyTramite['ejecutivo_name'] ?? 'Sin ejecutivo'), 'type' => 'select'],
  ];

  $step1IdentityFields = [
    ['label' => 'Contrato', 'value' => (string) ($prototypeReadOnlyTramite['contrato'] ?? ''), 'type' => 'input'],
    ['label' => 'Unidad', 'value' => (string) ($prototypeReadOnlyTramite['fields']['unidad'] ?? ''), 'type' => 'input'],
    ['label' => 'Serie', 'value' => (string) ($prototypeReadOnlyTramite['fields']['serie'] ?? ''), 'type' => 'input'],
    ['label' => 'Placas', 'value' => (string) ($prototypeReadOnlyTramite['fields']['placas'] ?? ''), 'type' => 'input'],
    ['label' => 'Entidad', 'value' => (string) ($prototypeReadOnlyTramite['entidad_name'] ?? 'Sin entidad'), 'type' => 'select'],
    ['label' => 'Observaciones', 'value' => (string) ($prototypeReadOnlyTramite['fields']['observaciones'] ?? ''), 'type' => 'textarea', 'wide' => true],
  ];

  $step1MetaSignals = [
    ['label' => 'Folio', 'value' => (string) ($prototypeReadOnlyTramite['folio'] ?? '--'), 'note' => 'Se conserva oculto en el guardado real.'],
    ['label' => 'Completa paso', 'value' => !empty($prototypeReadOnlyTramite['step1_complete']) ? 'Contrato + Entidad listos' : 'Pendiente de completar', 'note' => 'Regla actual de step1_complete.'],
    ['label' => 'Persistencia', 'value' => 'Datos base del expediente', 'note' => 'Guarda identidad del expediente en tramite.'],
  ];

  $associatedLabels = [];
  foreach (($prototypeReadOnlyTramite['associated_service_rows'] ?? []) as $associatedRow) {
    $label = trim((string) ($associatedRow['label'] ?? ''));
    if ($label !== '') {
      $associatedLabels[] = $label;
    }
  }

  $step1ServiceCards = [
    ['title' => 'Tipo principal', 'value' => (string) ($prototypeReadOnlyTramite['tipo_principal_label'] ?? 'Sin tipo principal'), 'note' => 'Vive en tramite.tra_tipos_id y se administra por separado de los asociados.'],
    ['title' => 'Tipos ligados', 'value' => $associatedLabels !== [] ? count($associatedLabels) . ' asociados' : 'Sin tipos asociados', 'note' => 'Se persisten en tra_tramite_asociado y no deben duplicar el tipo principal.'],
  ];

  $step1TypeBadges = !empty($prototypeReadOnlyTramite['linked_service_badges']) && is_array($prototypeReadOnlyTramite['linked_service_badges'])
    ? $prototypeReadOnlyTramite['linked_service_badges']
    : $step1TypeBadges;

  $step1AssociatedRows = !empty($prototypeReadOnlyTramite['associated_service_rows']) && is_array($prototypeReadOnlyTramite['associated_service_rows'])
    ? $prototypeReadOnlyTramite['associated_service_rows']
    : [['label' => 'Sin asociados registrados', 'kind' => 'Solo tipo principal', 'actions' => '--']];

  $currentStepDocFiles = array_values(array_filter([
    'Paso 1 no tiene dropzone propio en el flujo actual.',
    'Los soportes documentales se vuelven protagonistas en pasos posteriores.',
  ]));
  $currentStepDocCount = 'solo referencia';
  $currentStepNextAction = 'Comparar identidad y composicion del servicio';
  $currentStepRisk = !empty($prototypeReadOnlyTramite['step1_complete']) ? 'Controlado' : 'Atencion';

  $step2AssignmentFields = [
    ['label' => 'Empresa gestora', 'value' => (string) ($prototypeReadOnlyTramite['empresa_gestora_name'] ?? 'Sin empresa'), 'type' => 'select'],
    ['label' => 'Gestor', 'value' => (string) ($prototypeReadOnlyTramite['gestor_name'] ?? 'Sin asignar'), 'type' => 'select'],
  ];

  $step2DerechosFields = [
    ['label' => 'Monto pago de derechos', 'value' => number_format((float) ($prototypeReadOnlyTramite['fields']['derechos_tramite'] ?? 0), 2, '.', ','), 'type' => 'input'],
    ['label' => 'Pago', 'value' => (string) ($prototypeReadOnlyTramite['fields']['derechos_pago_sitio_label'] ?? 'Sin definir'), 'type' => 'select'],
    ['label' => 'Fecha vigencia', 'value' => (string) ($prototypeReadOnlyTramite['fields']['derechos_vigencia'] ?? ''), 'type' => 'input'],
    ['label' => 'Forma de pago', 'value' => (string) ($prototypeReadOnlyTramite['fields']['derechos_revol_cliente_label'] ?? 'Sin definir'), 'type' => 'select'],
    ['label' => 'Referencia bancaria', 'value' => (string) ($prototypeReadOnlyTramite['fields']['derechos_refer_banc'] ?? ''), 'type' => 'input'],
  ];

  $step2CompletionSignals = [
    [
      'label' => 'Asignacion',
      'value' => !empty($prototypeReadOnlyTramite['step2_complete']) ? 'Gestor listo' : 'Pendiente de asignar',
      'note' => 'Depende de empresa gestora + gestor dentro del mismo frente operativo.',
    ],
    [
      'label' => 'Pago de derechos',
      'value' => !empty($prototypeReadOnlyTramite['step3_complete']) ? 'Campos completos' : 'Faltan obligatorios',
      'note' => 'El minimo real del bloque es monto, forma de pago y referencia bancaria.',
    ],
  ];

  $step2DocPreviewItems = !empty($prototypeReadOnlyTramite['pago_derechos_docs']) && is_array($prototypeReadOnlyTramite['pago_derechos_docs'])
    ? $prototypeReadOnlyTramite['pago_derechos_docs']
    : ['Sin archivos de derechos registrados'];

  $step3EvidenceChips = [
    ['label' => 'Tramite Entregado por Gestor', 'isSuccess' => !empty($prototypeReadOnlyTramite['has_tramite_recibido'])],
    ['label' => 'Acuse de Recibo del Cliente', 'isSuccess' => !empty($prototypeReadOnlyTramite['has_acuse_recibo'])],
  ];

  $step3EvidenceDocItems = !empty($prototypeReadOnlyTramite['evidence_docs']) && is_array($prototypeReadOnlyTramite['evidence_docs'])
    ? $prototypeReadOnlyTramite['evidence_docs']
    : ['Sin evidencias finales registradas'];

  $step3EvidenceNote = !empty($prototypeReadOnlyTramite['has_tramite_recibido']) && !empty($prototypeReadOnlyTramite['has_acuse_recibo'])
    ? 'Las dos evidencias finales ya estan presentes y dejan abiertos en paralelo Pago a gestor y Cobro a cliente.'
    : 'Falta al menos una de las dos evidencias finales que desbloquean la lectura financiera posterior.';
}

$step1ContratoValue = '';
$step1EntidadValue = '';
$step1SerieValue = '';
foreach ($step1IdentityFields as $field) {
  $fieldLabel = (string) ($field['label'] ?? '');
  $fieldValue = trim((string) ($field['value'] ?? ''));
  if ($fieldLabel === 'Contrato') {
    $step1ContratoValue = $fieldValue;
  }
  if ($fieldLabel === 'Entidad') {
    $step1EntidadValue = $fieldValue;
  }
  if ($fieldLabel === 'Serie') {
    $step1SerieValue = $fieldValue;
  }
}

$step1ClienteValue = trim((string) ($step1LinkedFields[0]['value'] ?? ''));
$step1EjecutivoValue = trim((string) ($step1LinkedFields[1]['value'] ?? ''));
$step1AssociatedCount = count(array_filter($step1AssociatedRows, static function (array $row): bool {
  return stripos((string) ($row['label'] ?? ''), 'sin asociados') === false;
}));
$step1AssociatedLabels = array_values(array_filter(array_map(static function (array $row): string {
  return trim((string) ($row['label'] ?? ''));
}, $step1AssociatedRows), static function (string $label): bool {
  return $label !== '' && stripos($label, 'sin asociados') === false;
}));
$step1AssociatedSummary = $step1AssociatedLabels !== []
  ? implode(', ', array_slice($step1AssociatedLabels, 0, 3))
  : 'Sin tipos ligados';
$step1DocumentSummary = !empty($prototypeStep1DocsForm['summary']) && is_array($prototypeStep1DocsForm['summary'])
  ? $prototypeStep1DocsForm['summary']
  : ['requiredTotal' => 0, 'uploadedRequired' => 0, 'uploadedTotal' => 0];
$step1RequiredDocTotal = (int) ($step1DocumentSummary['requiredTotal'] ?? 0);
$step1UploadedRequiredDocs = (int) ($step1DocumentSummary['uploadedRequired'] ?? 0);
$step1UploadedTotalDocs = (int) ($step1DocumentSummary['uploadedTotal'] ?? 0);
$step1DocProgressLabel = $step1RequiredDocTotal > 0
  ? ($step1UploadedRequiredDocs . '/' . $step1RequiredDocTotal . ' obligatorios')
  : ($step1UploadedTotalDocs > 0 ? ($step1UploadedTotalDocs . ' cargado(s)') : 'Sin catálogo');

$step1OperationalChecks = [
  [
    'label' => 'Completitud minima',
    'detail' => ($step1ContratoValue !== '' && $step1EntidadValue !== '' && stripos($step1EntidadValue, 'Sin ') !== 0)
      ? 'Contrato y entidad listos para step1_complete.'
      : 'Aun falta la dupla contrato + entidad.',
    'state' => ($step1ContratoValue !== '' && $step1EntidadValue !== '' && stripos($step1EntidadValue, 'Sin ') !== 0) ? 'ok' : 'warn',
  ],
  [
    'label' => 'Relacion cliente-ejecutivo',
    'detail' => ($step1ClienteValue !== '' && stripos($step1ClienteValue, 'Sin ') !== 0 && $step1EjecutivoValue !== '' && stripos($step1EjecutivoValue, 'Sin ') !== 0)
      ? 'Dependencia resuelta y visible en el mismo bloque.'
      : 'Conviene revisar cliente o ejecutivo antes de guardar.',
    'state' => ($step1ClienteValue !== '' && stripos($step1ClienteValue, 'Sin ') !== 0 && $step1EjecutivoValue !== '' && stripos($step1EjecutivoValue, 'Sin ') !== 0) ? 'ok' : 'warn',
  ],
  [
    'label' => 'Riesgo de duplicado',
    'detail' => $step1SerieValue !== ''
      ? 'La serie alimenta la regla de duplicado junto con el tipo principal.'
      : 'Sin serie no se puede validar bien la regla de duplicados.',
    'state' => $step1SerieValue !== '' ? 'ok' : 'warn',
  ],
  [
    'label' => 'Composicion del servicio',
    'detail' => $step1AssociatedCount > 0
      ? 'Hay ' . $step1AssociatedCount . ' tipo(s) ligado(s) para revisar en el subflujo.'
      : 'Solo existe el tipo principal; no hay asociados registrados.',
    'state' => $step1AssociatedCount > 0 ? 'ok' : 'info',
  ],
];

$step1SurfaceItems = [
  [
    'label' => 'Identidad base',
    'endpoint' => 'Datos base del expediente',
    'note' => 'Contrato, serie, placas, entidad y observaciones terminan en tramite.',
  ],
  [
    'label' => 'Tipo principal',
    'endpoint' => 'Cambio del servicio principal',
    'note' => 'Cambia tramite.tra_tipos_id y reacomoda la composicion del servicio.',
  ],
  [
    'label' => 'Tipos ligados',
    'endpoint' => 'Altas, cambios y bajas de ligados',
    'note' => 'Subflujo propio para altas, cambios y bajas de asociados.',
  ],
];

$step1DetailCards = [
  [
    'label' => 'Tipo principal',
    'value' => (string) ($step1ServiceCards[0]['value'] ?? 'Sin tipo principal'),
    'note' => 'Es la base del expediente y marca la regla de duplicados.',
  ],
  [
    'label' => 'Tipos ligados',
    'value' => $step1AssociatedCount > 0 ? $step1AssociatedCount . ' activo(s)' : 'Sin ligados',
    'note' => $step1AssociatedSummary,
  ],
  [
    'label' => 'Subflujo real',
    'value' => 'Tipo principal y tipos ligados',
    'note' => 'Tipo principal y asociados no se guardan igual que la identidad base.',
  ],
];

$step1ControlCards = [
  [
    'label' => 'Guardado base',
    'value' => 'Datos base del expediente',
    'note' => 'Contrato, serie, placas, entidad y observaciones terminan en tramite.',
  ],
  [
    'label' => 'Regla critica',
    'value' => 'Duplicado tipo + serie',
    'note' => 'Se evalua contra el historial reciente del mismo tipo principal.',
  ],
  [
    'label' => 'Edicion',
    'value' => 'Base y composicion por separado',
    'note' => 'La identidad base y la composicion del servicio se habilitan de forma independiente.',
  ],
];

$operationalBaseDocBlocks = [
  [
    'title' => 'Paso 1 · Referencias base',
    'copy' => 'Aqui no existe dropzone propio. La pantalla solo comunica el punto de partida del expediente y la composicion del servicio.',
    'items' => ['Sin uploader propio', 'Datos base del expediente', 'Tipo principal y ligados'],
  ],
  [
    'title' => 'Paso 2 · Derechos y soporte operativo',
    'copy' => 'Este tramo concentra la documentacion de pago de derechos dentro del mismo frente operativo.',
    'items' => $step2DocPreviewItems,
  ],
  [
    'title' => 'Paso 3 · Evidencias finales',
    'copy' => 'Este tramo cierra documentalmente el expediente antes del pago a gestor.',
    'items' => $step3EvidenceDocItems,
  ],
];

$operationalBaseSubsteps = [
  ['step' => 1, 'label' => 'Datos generales', 'note' => 'Cliente, ejecutivo, identidad y composicion del servicio.'],
  ['step' => 2, 'label' => 'Gestor y derechos', 'note' => 'Asignacion operativa y guardado coordinado del pago de derechos.'],
  ['step' => 3, 'label' => 'Evidencias finales', 'note' => 'Cierre documental antes de entrar a finanzas.'],
];

$displayStepLabel = $isOperationalBasePhase ? 'Operacion base (pasos 1-3)' : $currentStepLabel;
$displayStepCopy = $isOperationalBasePhase
  ? 'Datos generales, gestor, pago de derechos y evidencias finales viven en una sola pantalla operativa previa a finanzas.'
  : $currentStepCopy;
$displayStepNextAction = $isOperationalBasePhase
  ? ($activeStep === 1
      ? 'Completar identidad y composicion del servicio'
      : ($activeStep === 2 ? 'Resolver gestor y pago de derechos' : 'Liberar cierre documental'))
  : $currentStepNextAction;
$displayStepDocCount = $isOperationalBasePhase ? '2 zonas documentales + referencias base' : $currentStepDocCount;
$displayStepRisk = $isOperationalBasePhase ? 'Operativo' : $currentStepRisk;
$displayStepDocTitle = $isOperationalBasePhase ? 'Superficies documentales de la fase operativa' : $currentStepDocTitle;
$displayStepDocCopy = $isOperationalBasePhase
  ? 'La pantalla principal integra los tramos 1, 2 y 3, pero cada tramo conserva su propia logica documental y de guardado.'
  : $currentStepDocCopy;
$displayStepSaveContracts = $isOperationalBasePhase
  ? array_merge($stepSaveContracts[1], $stepSaveContracts[2], $stepSaveContracts[3])
  : $currentStepSaveContracts;
$displayMiniSummary = $isOperationalBasePhase
  ? [
      ['label' => 'Estructura', 'value' => 'Una sola pantalla para datos generales, gestor, derechos y cierre documental.'],
      ['label' => 'Separacion sana', 'value' => 'Pago a gestor y cobro a cliente siguen fuera de esta fase principal.'],
    ]
  : ($stepMiniSummary[$activeStep] ?? []);
$displayChecklist = $isOperationalBasePhase
  ? [
      ['label' => '1. Confirmar expediente', 'value' => 'Cliente, ejecutivo, contrato, entidad y servicio principal listos.'],
      ['label' => '2. Resolver operacion', 'value' => 'Gestor asignado y pago de derechos validado en el mismo frente.'],
      ['label' => '3. Cerrar documentalmente', 'value' => 'Evidencias finales completas antes de pasar a finanzas.'],
    ]
  : ($stepChecklist[$activeStep] ?? []);
$displaySummaryTitle = $isOperationalBasePhase ? 'Resumen de fase' : 'Resumen del paso';
$displayChecklistTitle = $isOperationalBasePhase ? 'Checklist de fase' : 'Checklist operativo';
$displaySaveContractsTitle = $isOperationalBasePhase ? 'Guardados actuales de la fase' : 'Guardados actuales del paso';
$useThreeRailLayout = $isOperationalBasePhase || $activeStep === 4 || $activeStep === 5;
$baseSectionDisplayTitle = $isOperationalBasePhase
  ? 'Paso 1 · Datos generales y servicio'
  : ($activeStep === 4 ? 'Contexto previo del expediente' : $baseSectionTitle);
$baseSectionDisplayCopy = $isOperationalBasePhase
  ? 'La entrada de la pantalla principal sigue siendo el expediente: identidad, cliente, ejecutivo, servicio principal y tipos asociados.'
  : ($activeStep === 4
      ? 'Pago a gestor conserva visibles los datos previos del expediente, la asignacion operativa y el cierre documental para no perder contexto al entrar a finanzas.'
      : $baseSectionCopy);
$baseSectionDisplayTag = $isOperationalBasePhase ? 'Paso 1' : ($activeStep === 4 ? 'Contexto acumulado' : ($activeStep === 5 ? 'Recuperacion' : 'Bloque prioritario'));

$formatMoney = static function ($value): string {
  return '$' . number_format((float) $value, 2, '.', ',');
};

$step4VisualData = [
  'folio' => 'SOF-DEMO-260604-08',
  'contrato' => 'CONTRATO-DEMO-008',
  'gestor_name' => 'Nora Alicia Medrano',
  'pago_gestor_status_label' => 'En proceso',
  'reembolso_status_label' => 'Pendiente',
  'cobro_status_label' => 'Sin definir',
  'status_doctos_gestor_label' => 'En Proceso',
  'fields' => [
    'costo_tramite' => 1500,
    'deposito_gestor' => 1850,
    'col_a_favor' => 350,
    'num_factura_gestor' => 'FAC-GES-008',
    'impuesto_gestoria' => 220,
    'gestoria_comision' => 130,
    'costo_paqueteria' => 45,
    'gestor_total_pago' => 395,
  ],
  'payment_docs' => ['factura_gestor_demo.pdf', 'comprobante_pago_demo.jpg'],
  'evidence_docs' => ['tramite_recibido_demo.pdf', 'acuse_recibo_cliente_demo.jpg'],
  'has_factura_gestor' => true,
  'has_comprobante_pago' => true,
];

if ($hasRealStep4Context) {
  $step4VisualData = array_replace_recursive($step4VisualData, $prototypeReadOnlyTramite);
  $currentStepDocFiles = !empty($step4VisualData['payment_docs']) ? $step4VisualData['payment_docs'] : $currentStepDocFiles;
  $currentStepDocCount = count($currentStepDocFiles) . ' reales';
  $currentStepNextAction = (!empty($step4VisualData['has_factura_gestor']) && !empty($step4VisualData['has_comprobante_pago']))
    ? 'Revisar reembolso y conciliacion'
    : 'Completar comprobantes del pago';
  $currentStepRisk = (!empty($step4VisualData['has_factura_gestor']) && !empty($step4VisualData['has_comprobante_pago']))
    ? 'Controlado'
    : 'Atencion';
}

$step4DependencyCards = [
  ['label' => 'Expediente', 'value' => 'Contrato + cliente + servicio principal'],
  ['label' => 'Gestion', 'value' => 'Empresa gestora + gestor asignado'],
  ['label' => 'Derechos', 'value' => 'Monto + forma + referencia bancaria'],
  ['label' => 'Evidencias', 'value' => 'Tramite recibido + acuse del cliente'],
];

$step4DependencyChips = [
  ['label' => 'Paso 1', 'isSuccess' => !empty($prototypeReadOnlyTramite['step1_complete'])],
  ['label' => 'Gestor', 'isSuccess' => !empty($prototypeReadOnlyTramite['step2_complete'])],
  ['label' => 'Derechos', 'isSuccess' => !empty($prototypeReadOnlyTramite['step3_complete'])],
  ['label' => 'Evidencias finales', 'isSuccess' => !empty($prototypeReadOnlyTramite['has_tramite_recibido']) && !empty($prototypeReadOnlyTramite['has_acuse_recibo'])],
];

if ($hasRealStepContext) {
  $linkedCount = count(array_filter(($prototypeReadOnlyTramite['associated_service_rows'] ?? []), static function ($row): bool {
    return !empty($row['label']);
  }));
  $step4DependencyCards = [
    [
      'label' => 'Expediente',
      'value' => trim((string) ($prototypeReadOnlyTramite['contrato'] ?? '--') . ' · ' . (string) ($prototypeReadOnlyTramite['cliente_name'] ?? 'Sin cliente')),
    ],
    [
      'label' => 'Servicios',
      'value' => trim((string) ($prototypeReadOnlyTramite['tipo_principal_label'] ?? 'Sin tipo principal') . ($linkedCount > 0 ? ' + ' . $linkedCount . ' ligados' : '')),
    ],
    [
      'label' => 'Gestion',
      'value' => trim((string) ($prototypeReadOnlyTramite['empresa_gestora_name'] ?? 'Sin empresa') . ' · ' . (string) ($prototypeReadOnlyTramite['gestor_name'] ?? 'Sin gestor')),
    ],
    [
      'label' => 'Derechos',
      'value' => '$' . number_format((float) ($prototypeReadOnlyTramite['fields']['derechos_tramite'] ?? 0), 2, '.', ',') . ' · ' . (string) ($prototypeReadOnlyTramite['fields']['derechos_revol_cliente_label'] ?? 'Sin forma'),
    ],
  ];
}

$step2SupportCountLabel = (count($step2DocPreviewItems) === 1 && stripos((string) ($step2DocPreviewItems[0] ?? ''), 'Sin ') === 0)
  ? 'sin documentos'
  : count($step2DocPreviewItems) . ' soporte(s)';
$step3EvidenceCountLabel = (count($step3EvidenceDocItems) === 1 && stripos((string) ($step3EvidenceDocItems[0] ?? ''), 'Sin ') === 0)
  ? 'sin evidencias'
  : count($step3EvidenceDocItems) . ' evidencia(s)';
$step2ApprovalReady = !empty($step2DerechosFields[0]['value'])
  && (string) ($step2DerechosFields[0]['value'] ?? '') !== '0.00'
  && !empty($step2DerechosFields[3]['value'])
  && stripos((string) ($step2DerechosFields[3]['value'] ?? ''), 'Sin ') !== 0
  && !empty($step2DerechosFields[4]['value'])
  && stripos((string) ($step2DerechosFields[4]['value'] ?? ''), 'Sin ') !== 0;
$step2ApprovalMissing = [];
if (!$step2ApprovalReady) {
  if (empty($step2DerechosFields[0]['value']) || (string) ($step2DerechosFields[0]['value'] ?? '') === '0.00') {
    $step2ApprovalMissing[] = 'Monto pago de derechos';
  }
  if (empty($step2DerechosFields[3]['value']) || stripos((string) ($step2DerechosFields[3]['value'] ?? ''), 'Sin ') === 0) {
    $step2ApprovalMissing[] = 'Forma de pago';
  }
  if (empty($step2DerechosFields[4]['value']) || stripos((string) ($step2DerechosFields[4]['value'] ?? ''), 'Sin ') === 0) {
    $step2ApprovalMissing[] = 'Referencia bancaria';
  }
}
$step3GateReady = !empty($step3EvidenceChips[0]['isSuccess']) && !empty($step3EvidenceChips[1]['isSuccess']);
$step3GateMissing = [];
if (!$step3GateReady) {
  if (empty($step3EvidenceChips[0]['isSuccess'])) {
    $step3GateMissing[] = 'Tramite entregado por gestor';
  }
  if (empty($step3EvidenceChips[1]['isSuccess'])) {
    $step3GateMissing[] = 'Acuse de recibo del cliente';
  }
}

$step4CompactSummaryItems = [
  ['label' => 'Cliente', 'value' => $step1ClienteValue !== '' ? $step1ClienteValue : 'Sin cliente'],
  ['label' => 'Ejecutivo', 'value' => $step1EjecutivoValue !== '' ? $step1EjecutivoValue : 'Sin ejecutivo'],
  ['label' => 'Servicio', 'value' => (string) ($step1ServiceCards[0]['value'] ?? 'Sin tipo principal')],
  ['label' => 'Ligados', 'value' => $step1AssociatedCount > 0 ? $step1AssociatedCount . ' activos' : 'Sin ligados'],
  ['label' => 'Contrato', 'value' => $step1ContratoValue !== '' ? $step1ContratoValue : 'Sin contrato'],
  ['label' => 'Entidad', 'value' => $step1EntidadValue !== '' ? $step1EntidadValue : 'Sin entidad'],
];

$step4PreviousStageCards = [
  [
    'label' => 'Paso 1',
    'endpoint' => $step1ContratoValue !== '' ? $step1ContratoValue : 'Expediente base',
    'note' => ($step1ClienteValue !== '' ? $step1ClienteValue : 'Sin cliente') . ' · ' . (string) ($step1ServiceCards[0]['value'] ?? 'Sin tipo principal'),
  ],
  [
    'label' => 'Paso 2',
    'endpoint' => trim((string) ($step2AssignmentFields[0]['value'] ?? 'Sin empresa') . ' · ' . (string) ($step2AssignmentFields[1]['value'] ?? 'Sin gestor')),
    'note' => 'Derechos ' . (string) ($step2DerechosFields[3]['value'] ?? 'Sin forma') . ' · ' . $step2SupportCountLabel,
  ],
  [
    'label' => 'Paso 3',
    'endpoint' => $step3GateReady ? 'Cierre documental completo' : 'Cierre documental pendiente',
    'note' => $step3GateReady
      ? 'Tramite recibido y acuse completos · ' . $step3EvidenceCountLabel
      : 'Falta al menos una evidencia final · ' . $step3EvidenceCountLabel,
  ],
];

$step4CompactIdentityFields = array_values(array_filter($step1IdentityFields, static function (array $field): bool {
  return (string) ($field['label'] ?? '') !== 'Observaciones';
}));

$step4CompactObservationPreview = '';
foreach ($step1IdentityFields as $field) {
  if ((string) ($field['label'] ?? '') !== 'Observaciones') {
    continue;
  }
  $rawObservationValue = trim((string) ($field['value'] ?? ''));
  if ($rawObservationValue === '') {
    break;
  }
  $step4CompactObservationPreview = strlen($rawObservationValue) > 120
    ? substr($rawObservationValue, 0, 117) . '...'
    : $rawObservationValue;
  break;
}

$step2ApprovalTitleText = 'Falta aprobacion para continuar';
$step2ApprovalCopyText = 'El paso no se destraba por subir comprobantes. Primero debe quedar completo el bloque obligatorio de derechos y despues mostrarse el boton de aprobacion para continuar.';
$step2ApprovalInfoText = 'Aprobacion lista, pendiente de un perfil autorizado.';
if ($step2ApprovalReady) {
  if ($prototypeCanApproveStep2) {
    $step2ApprovalTitleText = 'Listo para aprobacion';
    $step2ApprovalCopyText = 'El tramo ya cumple los campos obligatorios reales. Los comprobantes de linea de captura pueden seguir subiendose aqui como soporte opcional, pero el siguiente gesto esperado es aprobar el tramite.';
    $step2ApprovalInfoText = 'Al aprobar, la siguiente lectura operativa es cerrar evidencias finales en Paso 3.';
  } elseif ($prototypeStep2IsLockedStatus) {
    $step2ApprovalTitleText = 'Tramite cerrado';
    $step2ApprovalCopyText = 'El tramite esta concluido o cancelado. La autorizacion ya no aplica en este frente.';
    $step2ApprovalInfoText = 'Tramite en modo de solo lectura.';
  } elseif ($prototypeStep2PostApprovalStage) {
    $step2ApprovalTitleText = 'Tramite ya autorizado';
    $step2ApprovalCopyText = 'El tramite ya paso a Pago a gestor o a una etapa posterior. La autorizacion ya no aplica en este frente.';
    $step2ApprovalInfoText = 'Autorizacion ya aplicada en una etapa previa.';
  } else {
    $step2ApprovalTitleText = 'Listo pero sin autorizacion disponible';
    $step2ApprovalCopyText = 'El tramo ya esta listo para autorizacion, pero este perfil no puede ejecutar esa accion desde esta pantalla. Los comprobantes de linea de captura siguen siendo opcionales y no cambian ese bloqueo.';
  }
}

$buildChecklistItem = static function (string $label, string $value, string $status = 'pending'): array {
  return [
    'label' => $label,
    'value' => $value,
    'status' => $status,
  ];
};

$step2AssignmentReady = !empty($prototypeStep2Form['values']['empresa_gestora_id'])
  && !empty($prototypeStep2Form['values']['gestor_id']);
$step2SupportReady = $step2SupportCountLabel !== 'sin documentos';
$step2ApprovalStatus = !$step2ApprovalReady
  ? 'pending'
  : ($prototypeCanApproveStep2 ? 'warning' : ($prototypeStep2PostApprovalStage ? 'done' : 'info'));
$step2ApprovalValue = !$step2ApprovalReady
  ? 'Pendiente'
  : ($prototypeCanApproveStep2 ? 'Listo para aprobar' : ($prototypeStep2PostApprovalStage ? 'Ya autorizado' : 'Sin permiso'));
$step3DocsStatus = $step3GateReady
  ? 'done'
  : ($step3EvidenceCountLabel !== 'sin evidencias' ? 'warning' : 'pending');
$step3DocsValue = $step3GateReady
  ? 'Completo'
  : ($step3EvidenceCountLabel !== 'sin evidencias' ? $step3EvidenceCountLabel : 'Pendiente');

$stepChecklist[2] = [
  $buildChecklistItem('1. Asignacion de Gestor', $step2AssignmentReady ? 'Listo' : 'Pendiente', $step2AssignmentReady ? 'done' : 'pending'),
  $buildChecklistItem('2. Datos de pagos de derechos', $step2ApprovalReady ? 'Listo' : 'Pendiente', $step2ApprovalReady ? 'done' : 'pending'),
  $buildChecklistItem('3. Documentos del paso', $step2SupportReady ? $step2SupportCountLabel : 'Pendiente', $step2SupportReady ? 'done' : 'pending'),
  $buildChecklistItem('4. Aprobacion', $step2ApprovalValue, $step2ApprovalStatus),
];

$stepChecklist[3] = [
  $buildChecklistItem('1. Asignacion de Gestor', $step2AssignmentReady ? 'Listo' : 'Pendiente', $step2AssignmentReady ? 'done' : 'pending'),
  $buildChecklistItem('2. Datos de pagos de derechos', $step2ApprovalReady ? 'Listo' : 'Pendiente', $step2ApprovalReady ? 'done' : 'pending'),
  $buildChecklistItem('3. Documentos del paso', $step3DocsValue, $step3DocsStatus),
  $buildChecklistItem('4. Aprobacion', $step2ApprovalValue, $step2ApprovalStatus),
];

$step2AssignmentCards = [
  [
    'key' => 'empresa_gestora',
    'label' => 'Empresa gestora',
    'value' => (string) ($step2AssignmentFields[0]['value'] ?? 'Sin empresa'),
    'note' => 'Define quien absorbe la operacion del tramite.',
  ],
  [
    'key' => 'gestor_asignado',
    'label' => 'Gestor asignado',
    'value' => (string) ($step2AssignmentFields[1]['value'] ?? 'Sin asignar'),
    'note' => 'Responsable directo del tramo operativo.',
  ],
  [
    'key' => 'guardado_frente',
    'label' => 'Guardado del frente',
    'value' => 'Asignacion operativa',
    'note' => 'La asignacion vive separada del guardado financiero de derechos.',
  ],
];

$step2DerechosCards = [
  [
    'key' => 'monto_derechos',
    'label' => 'Monto de derechos',
    'value' => (string) ($step2DerechosFields[0]['value'] ?? '0.00'),
    'note' => 'Monto base del bloque de derechos.',
  ],
  [
    'key' => 'pago_forma',
    'label' => 'Pago y forma',
    'value' => trim((string) ($step2DerechosFields[1]['value'] ?? 'Sin definir') . ' · ' . (string) ($step2DerechosFields[3]['value'] ?? 'Sin definir'), ' ·'),
    'note' => 'Aqui se decide como baja el pago y con que flujo interno se resuelve.',
  ],
  [
    'key' => 'vigencia_referencia',
    'label' => 'Vigencia / referencia',
    'value' => trim((string) ($step2DerechosFields[2]['value'] ?? 'Sin vigencia') . ' · ' . (string) ($step2DerechosFields[4]['value'] ?? 'Sin referencia bancaria'), ' ·'),
    'note' => 'Los dos datos ayudan a validar soporte y conciliacion del tramo.',
  ],
];

$step2ControlCards = [
  [
    'key' => 'asignacion',
    'label' => 'Asignacion',
    'value' => (string) ($step2CompletionSignals[0]['value'] ?? 'Pendiente'),
    'note' => (string) ($step2CompletionSignals[0]['note'] ?? ''),
  ],
  [
    'key' => 'pago_derechos',
    'label' => 'Pago de derechos',
    'value' => (string) ($step2CompletionSignals[1]['value'] ?? 'Pendiente'),
    'note' => (string) ($step2CompletionSignals[1]['note'] ?? ''),
  ],
  [
    'key' => 'soporte_documental',
    'label' => 'Soporte documental',
    'value' => $step2SupportCountLabel,
    'note' => 'Los comprobantes de linea de captura pueden adjuntarse aqui, pero no bloquean la aprobacion del tramite.',
  ],
  [
    'key' => 'guardado_coordinado',
    'label' => 'Guardado coordinado',
    'value' => 'Asignacion + derechos',
    'note' => 'La pantalla puede compactarse, pero persisten dos guardados distintos.',
  ],
];

$heroCopyByStep = [
  1 => 'Paso 1 define la identidad del expediente y la composicion del servicio. Las tarjetas de arriba deben dejar claro que se captura aqui y que puede bloquear el guardado.',
  2 => 'Paso 2 concentra la asignacion operativa y el pago de derechos en un mismo frente. Las tarjetas deben explicar responsables, monto y soporte del tramo.',
  3 => 'Paso 3 ya no es un formulario largo: confirma el cierre documental que habilita la lectura financiera posterior.',
  4 => 'Paso 4 ya es una salida financiera independiente. Las tarjetas de arriba deben orientar sobre pago, comprobantes y conciliacion.',
  5 => 'Paso 5 se mantiene separado para la recuperacion hacia cliente. Las tarjetas solo deben recordar su propia logica documental y de cobro.',
];

$heroCardsByStep = [
  1 => [
    ['label' => 'Cliente / Ejecutivo', 'value' => trim($step1ClienteValue . ' · ' . $step1EjecutivoValue, ' ·'), 'note' => 'Relacion base del expediente.'],
    ['label' => 'Servicio principal', 'value' => (string) ($step1ServiceCards[0]['value'] ?? 'Sin tipo principal'), 'note' => 'Define la captura principal del paso.'],
    ['label' => 'Tipos ligados', 'value' => $step1AssociatedCount > 0 ? $step1AssociatedSummary : 'Sin tipos ligados', 'note' => 'Subflujo propio del paso 1.'],
    ['label' => 'Validacion clave', 'value' => 'Contrato + entidad + serie', 'note' => 'La regla de duplicado depende del tipo principal y la serie.'],
  ],
  2 => [
    ['label' => 'Empresa gestora', 'value' => (string) ($step2AssignmentFields[0]['value'] ?? 'Sin empresa'), 'note' => 'Abre la gestion del tramite.'],
    ['label' => 'Gestor', 'value' => (string) ($step2AssignmentFields[1]['value'] ?? 'Sin asignar'), 'note' => 'Responsable operativo actual.'],
    ['label' => 'Derechos', 'value' => (string) ($step2DerechosFields[0]['value'] ?? '0.00'), 'note' => (string) ($step2DerechosFields[3]['value'] ?? 'Sin forma de pago')],
    ['label' => 'Soporte del tramo', 'value' => $step2SupportCountLabel, 'note' => 'Comprobantes de linea de captura opcionales.'],
  ],
  3 => [
    ['label' => 'Tramite entregado', 'value' => !empty($step3EvidenceChips[0]['isSuccess']) ? 'Listo' : 'Pendiente', 'note' => 'Confirmacion desde gestor.'],
    ['label' => 'Acuse del cliente', 'value' => !empty($step3EvidenceChips[1]['isSuccess']) ? 'Listo' : 'Pendiente', 'note' => 'Recepcion final del expediente.'],
    ['label' => 'Evidencias', 'value' => $step3EvidenceCountLabel, 'note' => 'Archivos del cierre documental.'],
    ['label' => 'Salida siguiente', 'value' => 'Habilita Pago a gestor', 'note' => 'Este paso no mezcla finanzas, solo las desbloquea.'],
  ],
  4 => [
    ['label' => 'Gestor', 'value' => (string) ($step4VisualData['gestor_name'] ?? 'Sin asignar'), 'note' => 'Responsable del pago.'],
    ['label' => 'Estatus pago', 'value' => (string) ($step4VisualData['pago_gestor_status_label'] ?? 'Sin definir'), 'note' => 'Seguimiento financiero actual.'],
    ['label' => 'Comprobantes', 'value' => count($step4VisualData['payment_docs'] ?? []) . ' archivo(s)', 'note' => 'Factura y comprobante del pago.'],
    ['label' => 'Total a gestor', 'value' => $formatMoney($step4VisualData['fields']['gestor_total_pago'] ?? 0), 'note' => 'Lectura de salida financiera.'],
  ],
  5 => [
    ['label' => 'Cobro cliente', 'value' => 'Recuperacion posterior', 'note' => 'Se mantiene separado del pago a gestor.'],
    ['label' => 'Documentos', 'value' => $stepDocCounts[5], 'note' => 'Surface propia del cobro.'],
    ['label' => 'Siguiente accion', 'value' => $stepNextActions[5], 'note' => 'Confirmacion de recuperacion.'],
    ['label' => 'Riesgo', 'value' => $stepRisk[5], 'note' => 'Seguimiento posterior al cierre.'],
  ],
];

$currentHeroCopy = $heroCopyByStep[$activeStep] ?? $stepCopies[$activeStep];
$currentHeroCards = $heroCardsByStep[$activeStep] ?? [];

$stepPrototypeUrl = static function (int $stepNumber) use ($prototypeTramiteId): string {
  $query = $prototypeTramiteId > 0 ? '?tramite_id=' . $prototypeTramiteId : '';
  if ($stepNumber >= 1 && $stepNumber <= 5) {
    return base_url('/deskapp/tramitesn/prototipo-layout/paso-' . $stepNumber) . $query;
  }

  return base_url('/deskapp/tramitesn/prototipo-layout/paso/' . $stepNumber) . $query;
};

$currentPrototypeStepUrl = $stepPrototypeUrl($activeStep);
$prototypeRealUpdateUrl = base_url('/deskapp/tramitesn/update/' . $prototypeTramiteId);
$prototypeListUrl = base_url('/deskapp/tramitesn/tramite');

$phaseTabs = [
  ['phase' => 'base', 'url' => $stepPrototypeUrl(1), 'kicker' => 'Fase 1', 'label' => 'Operacion base', 'active' => $activeStep <= 3],
  ['phase' => 'pay', 'url' => $stepPrototypeUrl(4), 'kicker' => 'Fase 2', 'label' => 'Pago a gestor', 'active' => $activeStep === 4],
  ['phase' => 'collect', 'url' => $stepPrototypeUrl(5), 'kicker' => 'Fase 3', 'label' => 'Cobro a cliente', 'active' => $activeStep === 5],
];

$operationalBaseClientState = null;
if ($isOperationalBasePhase) {
  $step1DocCountLabel = $step1RequiredDocTotal > 0
    ? ($step1UploadedRequiredDocs . '/' . $step1RequiredDocTotal . ' obligatorios')
    : ($step1UploadedTotalDocs > 0 ? $step1UploadedTotalDocs . ' cargado(s)' : 'sin documentos');
  $step2DocCountLabel = (count($step2DocPreviewItems) === 1 && stripos((string) ($step2DocPreviewItems[0] ?? ''), 'Sin ') === 0)
    ? 'sin documentos'
    : count($step2DocPreviewItems) . ' soporte(s)';
  $step3DocCountLabel = (count($step3EvidenceDocItems) === 1 && stripos((string) ($step3EvidenceDocItems[0] ?? ''), 'Sin ') === 0)
    ? 'sin evidencias'
    : count($step3EvidenceDocItems) . ' evidencia(s)';
  $step1RiskLabel = !empty($prototypeReadOnlyTramite['step1_complete']) ? 'Controlado' : ($hasRealOperationalBaseContext ? 'Atencion' : $stepRisk[1]);
  $step2RiskLabel = (!empty($prototypeReadOnlyTramite['step2_complete']) && !empty($prototypeReadOnlyTramite['step3_complete']))
    ? 'Controlado'
    : ($hasRealOperationalBaseContext ? 'Operativo' : $stepRisk[2]);
  $step3RiskLabel = (!empty($prototypeReadOnlyTramite['has_tramite_recibido']) && !empty($prototypeReadOnlyTramite['has_acuse_recibo']))
    ? 'Controlado'
    : ($hasRealOperationalBaseContext ? 'Atencion' : $stepRisk[3]);

  $operationalBaseClientState = [
    'activeStep' => $activeStep,
    'steps' => [
      1 => [
        'url' => $stepPrototypeUrl(1),
        'displayLabel' => 'Paso 1 · ' . $stepLabels[1],
        'heroMeta' => 'Paso 1 de 3 · Operacion base',
        'nextAction' => 'Completar identidad y composicion del servicio',
        'progress' => $stepProgress[1],
        'risk' => $step1RiskLabel,
        'docCount' => $hasRealOperationalBaseContext ? $step1DocCountLabel : $stepDocCounts[1],
        'summaryTitle' => 'Resumen del paso',
        'summaryItems' => $stepMiniSummary[1] ?? [],
        'checklistTitle' => 'Checklist operativo',
        'checklistItems' => $stepChecklist[1] ?? [],
        'saveTitle' => 'Guardados actuales del paso',
        'saveContracts' => $stepSaveContracts[1] ?? [],
      ],
      2 => [
        'url' => $stepPrototypeUrl(2),
        'displayLabel' => 'Paso 2 · ' . $stepLabels[2],
        'heroMeta' => 'Paso 2 de 3 · Operacion base',
        'nextAction' => 'Resolver gestor y pago de derechos',
        'progress' => $stepProgress[2],
        'risk' => $step2RiskLabel,
        'docCount' => $step2DocCountLabel,
        'summaryTitle' => 'Resumen del paso',
        'summaryItems' => $stepMiniSummary[2] ?? [],
        'checklistTitle' => 'Checklist operativo',
        'checklistItems' => $stepChecklist[2] ?? [],
        'saveTitle' => 'Guardados actuales del paso',
        'saveContracts' => $stepSaveContracts[2] ?? [],
      ],
      3 => [
        'url' => $stepPrototypeUrl(3),
        'displayLabel' => 'Paso 3 · ' . $stepLabels[3],
        'heroMeta' => 'Paso 3 de 3 · Operacion base',
        'nextAction' => 'Liberar cierre documental',
        'progress' => $stepProgress[3],
        'risk' => $step3RiskLabel,
        'docCount' => $step3DocCountLabel,
        'summaryTitle' => 'Resumen del paso',
        'summaryItems' => $stepMiniSummary[3] ?? [],
        'checklistTitle' => 'Checklist operativo',
        'checklistItems' => $stepChecklist[3] ?? [],
        'saveTitle' => 'Guardados actuales del paso',
        'saveContracts' => $stepSaveContracts[3] ?? [],
      ],
    ],
  ];

  foreach ([1, 2, 3] as $stepNumber) {
    if (!empty($operationalBaseClientState['steps'][$stepNumber])) {
      $operationalBaseClientState['steps'][$stepNumber]['heroCopy'] = $heroCopyByStep[$stepNumber] ?? '';
      $operationalBaseClientState['steps'][$stepNumber]['heroCards'] = $heroCardsByStep[$stepNumber] ?? [];
    }
  }
}

$phaseFlowCardClass = static function (string $phase) use ($activeStep): string {
  $classes = ['tp-flow-card'];
  if ($phase === 'base') {
    if ($activeStep <= 3) {
      $classes[] = 'is-active';
    } elseif ($activeStep > 3) {
      $classes[] = 'is-done';
    }
  }
  if ($phase === 'pay') {
    $classes[] = 'is-finance';
    if ($activeStep === 4) {
      $classes[] = 'is-active';
    }
    if ($activeStep === 5) {
      $classes[] = 'is-done';
    }
  }
  if ($phase === 'collect') {
    $classes[] = 'is-finance';
    if ($activeStep === 5) {
      $classes[] = 'is-active';
    }
  }

  return implode(' ', $classes);
};

$flowCardClass = static function (int $stepNumber) use ($activeStep): string {
  $classes = ['tp-flow-card'];
  if ($stepNumber < $activeStep) {
    $classes[] = 'is-done';
  }
  if ($stepNumber === $activeStep) {
    $classes[] = 'is-active';
  }
  if ($stepNumber >= 4) {
    $classes[] = 'is-finance';
  }

  return implode(' ', $classes);
};
?>

<?php if (!$isEmbeddedPrototypeBody): ?>
<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<?php endif; ?>

<script>
  (function () {
    if (document.body) {
      document.body.classList.add('tp-no-floating-shell-menu');
    }
  })();
</script>

<style>
  body.tp-no-floating-shell-menu .header {
    position: static !important;
    top: auto !important;
    left: auto !important;
    width: 100% !important;
  }

  body.tp-no-floating-shell-menu .mobile-menu-overlay {
    display: none !important;
    visibility: hidden !important;
    opacity: 0 !important;
    pointer-events: none !important;
  }

  body.tp-no-floating-shell-menu .main-container.tramites-proto-page {
    padding-top: 22px !important;
  }

  .tramites-proto-page {
    --tp-bg: #eef3f8;
    --tp-panel: #ffffff;
    --tp-panel-soft: #f7fafc;
    --tp-text: #10233d;
    --tp-muted: #66758a;
    --tp-line: #d8e2ee;
    --tp-deep: #123b66;
    --tp-deep-2: #1d5f8f;
    --tp-accent: #ea6b2d;
    --tp-accent-soft: #fff1e8;
    --tp-success: #0f766e;
    --tp-warning: #a16207;
    --tp-danger: #b42318;
    --tp-shadow-lg: 0 28px 60px rgba(15, 23, 42, 0.10);
    --tp-shadow-md: 0 14px 30px rgba(15, 23, 42, 0.08);
    --tp-radius-xl: 28px;
    --tp-radius-lg: 22px;
    --tp-radius-md: 16px;
    --tp-radius-sm: 12px;
    color: var(--tp-text);
  }

  .tramites-proto-page .tp-canvas {
    padding-bottom: 24px;
  }

  .tramites-proto-page .tp-intro-card {
    margin-bottom: 18px;
    padding: 20px 22px;
    border-radius: 20px;
    border: 1px solid var(--tp-line);
    background:
      radial-gradient(circle at top left, rgba(29, 95, 143, 0.14), transparent 35%),
      radial-gradient(circle at top right, rgba(234, 107, 45, 0.10), transparent 28%),
      linear-gradient(180deg, #f8fbff 0%, var(--tp-bg) 100%);
    box-shadow: var(--tp-shadow-md);
  }

  .tramites-proto-page .tp-intro {
    display: flex;
    justify-content: space-between;
    align-items: end;
    gap: 18px;
    flex-wrap: wrap;
  }

  .tramites-proto-page .tp-page-title {
    margin: 0;
    font-size: 1.85rem;
    font-weight: 850;
    letter-spacing: -0.03em;
  }

  .tramites-proto-page .tp-page-copy {
    margin: 8px 0 0;
    max-width: 860px;
    color: var(--tp-muted);
    line-height: 1.55;
  }

  .tramites-proto-page .tp-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 14px;
    border-radius: 999px;
    border: 1px solid #c8d7e7;
    background: rgba(255,255,255,0.84);
    color: var(--tp-deep);
    font-size: 0.78rem;
    font-weight: 800;
    white-space: nowrap;
  }

  .tramites-proto-page .tp-hero {
    display: grid;
    grid-template-columns: minmax(0, 1fr);
    gap: 18px;
    margin-bottom: 18px;
  }

  .tramites-proto-page .tp-flow-rail {
    display: grid;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: 12px;
    margin-bottom: 18px;
  }

  .tramites-proto-page .tp-flow-rail.is-grouped {
    grid-template-columns: 1.8fr 1fr 1fr;
  }

  .tramites-proto-page .tp-flow-card {
    position: relative;
    padding: 16px;
    border-radius: 18px;
    border: 1px solid var(--tp-line);
    background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
    box-shadow: var(--tp-shadow-md);
    overflow: hidden;
  }

  .tramites-proto-page .tp-flow-card::after {
    content: '';
    position: absolute;
    inset: auto 0 0 0;
    height: 4px;
    background: #d8e2ee;
  }

  .tramites-proto-page .tp-flow-card.is-done::after {
    background: linear-gradient(90deg, #0f766e 0%, #34d399 100%);
  }

  .tramites-proto-page .tp-flow-card.is-active::after {
    background: linear-gradient(90deg, var(--tp-accent) 0%, #ff9a4d 100%);
  }

  .tramites-proto-page .tp-flow-card.is-finance::after {
    background: linear-gradient(90deg, #123b66 0%, #1d5f8f 100%);
  }

  .tramites-proto-page .tp-flow-step {
    display: block;
    margin-bottom: 6px;
    color: var(--tp-muted);
    font-size: 0.68rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.08em;
  }

  .tramites-proto-page .tp-flow-card strong {
    display: block;
    margin-bottom: 6px;
    font-size: 0.92rem;
    font-weight: 840;
  }

  .tramites-proto-page .tp-flow-card p {
    margin: 0;
    color: var(--tp-muted);
    font-size: 0.8rem;
    line-height: 1.45;
  }

  .tramites-proto-page .tp-snapshot,
  .tramites-proto-page .tp-main-panel,
  .tramites-proto-page .tp-side-card {
    border-radius: var(--tp-radius-xl);
    box-shadow: var(--tp-shadow-lg);
  }

  .tramites-proto-page .tp-hero-panel {
    overflow: hidden;
    background: linear-gradient(135deg, var(--tp-deep) 0%, var(--tp-deep-2) 58%, #2e7ba9 100%);
    color: #fff;
  }

  .tramites-proto-page .tp-hero-inner {
    padding: 22px 22px 18px;
  }

  .tramites-proto-page .tp-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 7px 11px;
    border-radius: 999px;
    background: rgba(255,255,255,0.13);
    border: 1px solid rgba(255,255,255,0.14);
    font-size: 0.72rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.08em;
  }

  .tramites-proto-page .tp-hero-title {
    margin: 10px 0 8px;
    font-size: 1.55rem;
    font-weight: 850;
    letter-spacing: -0.03em;
  }

  .tramites-proto-page .tp-hero-copy {
    margin: 0;
    max-width: 720px;
    color: rgba(255,255,255,0.82);
    line-height: 1.55;
  }

  .tramites-proto-page .tp-chip-row {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
  }

  .tramites-proto-page .tp-chip {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 9px 13px;
    border-radius: 999px;
    background: rgba(255,255,255,0.11);
    border: 1px solid rgba(255,255,255,0.14);
    font-size: 0.78rem;
    font-weight: 750;
  }

  .tramites-proto-page .tp-chip-label {
    color: rgba(255,255,255,0.76);
    font-size: 0.7rem;
    font-weight: 800;
    letter-spacing: 0.05em;
    text-transform: uppercase;
  }

  .tramites-proto-page .tp-chip-value {
    min-width: 0;
    font-weight: 800;
  }

  .tramites-proto-page .tp-chip.status {
    background: rgba(15, 118, 110, 0.24);
  }

  .tramites-proto-page .tp-chip.warning {
    background: rgba(234, 107, 45, 0.22);
  }

  .tramites-proto-page .tp-hero-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 12px;
  }

  .tramites-proto-page .tp-hero-card {
    min-width: 0;
    padding: 14px;
    border-radius: 18px;
    background: rgba(255,255,255,0.11);
    border: 1px solid rgba(255,255,255,0.14);
  }

  .tramites-proto-page .tp-hero-label {
    display: block;
    margin-bottom: 4px;
    font-size: 0.65rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: rgba(255,255,255,0.72);
  }

  .tramites-proto-page .tp-hero-value {
    font-size: 0.92rem;
    font-weight: 780;
    line-height: 1.3;
    word-break: break-word;
  }

  .tramites-proto-page .tp-hero-note {
    display: block;
    margin-top: 6px;
    color: rgba(255,255,255,0.72);
    font-size: 0.74rem;
    line-height: 1.35;
  }

  .tramites-proto-page .tp-snapshot {
    padding: 18px;
    border: 1px solid var(--tp-line);
    background:
      linear-gradient(180deg, rgba(255,255,255,0.92) 0%, rgba(248,250,252,0.96) 100%),
      linear-gradient(120deg, rgba(18,59,102,0.05), rgba(234,107,45,0.05));
  }

  .tramites-proto-page .tp-snapshot-title,
  .tramites-proto-page .tp-side-title,
  .tramites-proto-page .tp-mini-title,
  .tramites-proto-page .tp-section-title,
  .tramites-proto-page .tp-content-title {
    margin: 0;
    font-weight: 840;
  }

  .tramites-proto-page .tp-snapshot-copy,
  .tramites-proto-page .tp-side-copy,
  .tramites-proto-page .tp-mini-copy,
  .tramites-proto-page .tp-section-copy,
  .tramites-proto-page .tp-content-copy {
    margin: 6px 0 0;
    color: var(--tp-muted);
    line-height: 1.45;
  }

  .tramites-proto-page .tp-progress-block,
  .tramites-proto-page .tp-signal-card,
  .tramites-proto-page .tp-metric,
  .tramites-proto-page .tp-mini-item,
  .tramites-proto-page .tp-action-item,
  .tramites-proto-page .tp-note-item,
  .tramites-proto-page .tp-gallery-item {
    border: 1px solid var(--tp-line);
    background: var(--tp-panel-soft);
  }

  .tramites-proto-page .tp-progress-block {
    display: flex;
    flex-direction: column;
    gap: 10px;
    padding: 14px;
    border-radius: var(--tp-radius-md);
    margin: 14px 0;
  }

  .tramites-proto-page .tp-progress-label-row {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    font-size: 0.78rem;
    font-weight: 750;
    color: var(--tp-muted);
  }

  .tramites-proto-page .tp-progress-track {
    height: 10px;
    border-radius: 999px;
    background: #d9e5f1;
    overflow: hidden;
  }

  .tramites-proto-page .tp-progress-fill {
    width: 68%;
    height: 100%;
    background: linear-gradient(90deg, var(--tp-accent) 0%, #ff9a4d 100%);
  }

  .tramites-proto-page .tp-signal-grid,
  .tramites-proto-page .tp-metrics-row {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px;
  }

  .tramites-proto-page .tp-signal-card,
  .tramites-proto-page .tp-metric {
    padding: 12px;
    border-radius: 16px;
  }

  .tramites-proto-page .tp-signal-card span,
  .tramites-proto-page .tp-metric span {
    display: block;
    margin-bottom: 4px;
    font-size: 0.68rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--tp-muted);
  }

  .tramites-proto-page .tp-main-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 340px;
    gap: 18px;
    align-items: start;
  }

  .tramites-proto-page .tp-main-grid.is-operational-base {
    grid-template-columns: minmax(0, 1fr);
  }

  .tramites-proto-page .tp-unified-layout {
    display: flex;
    flex-direction: column;
    gap: 22px;
  }

  .tramites-proto-page .tp-step-row {
    display: flex;
    flex-direction: column;
    gap: 14px;
  }

  .tramites-proto-page .tp-step-row.is-active .tp-step-row-header {
    border-color: rgba(18, 59, 102, 0.18);
    box-shadow: var(--tp-shadow-sm);
  }

  .tramites-proto-page .tp-step-row-header {
    display: flex;
    justify-content: space-between;
    gap: 16px;
    align-items: flex-start;
    padding: 16px 18px;
    border-radius: 18px;
    border: 1px solid var(--tp-line);
    background: linear-gradient(180deg, #ffffff 0%, #f7fbff 100%);
  }

  .tramites-proto-page .tp-step-row-copy {
    margin: 6px 0 0;
    color: var(--tp-muted);
    font-size: 0.82rem;
    line-height: 1.5;
  }

  .tramites-proto-page .tp-step-row-tag {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 122px;
    padding: 9px 12px;
    border-radius: 999px;
    border: 1px solid var(--tp-line);
    background: var(--tp-panel-soft);
    color: var(--tp-deep);
    font-size: 0.72rem;
    font-weight: 820;
    text-transform: uppercase;
    letter-spacing: 0.08em;
  }

  .tramites-proto-page .tp-step-row-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.45fr) minmax(280px, 0.95fr) minmax(260px, 0.82fr);
    gap: 18px;
    align-items: start;
  }

  .tramites-proto-page .tp-step-col {
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 16px;
  }

  .tramites-proto-page .tp-phase-divider {
    display: flex;
    align-items: center;
    gap: 12px;
    color: var(--tp-muted);
    font-size: 0.72rem;
    font-weight: 820;
    text-transform: uppercase;
    letter-spacing: 0.1em;
  }

  .tramites-proto-page .tp-phase-divider::before,
  .tramites-proto-page .tp-phase-divider::after {
    content: '';
    flex: 1 1 auto;
    height: 1px;
    background: linear-gradient(90deg, rgba(15, 118, 110, 0.08) 0%, rgba(15, 118, 110, 0.42) 50%, rgba(15, 118, 110, 0.08) 100%);
  }

  .tramites-proto-page .tp-step-right-mirror {
    color: var(--tp-muted);
    font-size: 0.77rem;
    line-height: 1.5;
  }

  .tramites-proto-page .tp-step-right-mirror strong {
    color: var(--tp-text);
  }

  .tramites-proto-page .tp-page-title {
    font-size: 1.76rem;
  }

  .tramites-proto-page .tp-page-copy,
  .tramites-proto-page .tp-side-copy,
  .tramites-proto-page .tp-mini-copy,
  .tramites-proto-page .tp-section-copy,
  .tramites-proto-page .tp-content-copy,
  .tramites-proto-page .tp-step-row-copy {
    font-size: 0.78rem;
    line-height: 1.42;
  }

  .tramites-proto-page .tp-hero-inner {
    padding: 18px 18px 14px;
  }

  .tramites-proto-page .tp-hero-title {
    font-size: 1.3rem;
  }

  .tramites-proto-page .tp-chip,
  .tramites-proto-page .tp-badge,
  .tramites-proto-page .tp-section-tag,
  .tramites-proto-page .tp-step-row-tag {
    font-size: 0.7rem;
  }

  .tramites-proto-page .tp-chip,
  .tramites-proto-page .tp-badge {
    padding: 8px 12px;
  }

  .tramites-proto-page .tp-hero-card,
  .tramites-proto-page .tp-flow-card,
  .tramites-proto-page .tp-step-row-header,
  .tramites-proto-page .tp-section-card,
  .tramites-proto-page .tp-mini-card,
  .tramites-proto-page .tp-side-card,
  .tramites-proto-page .tp-snapshot {
    padding: 14px;
  }

  .tramites-proto-page .tp-main-panel {
    padding: 14px;
  }

  .tramites-proto-page .tp-main-panel.is-operational-base {
    padding: 16px 12px 14px;
  }

  .tramites-proto-page .tp-side-title,
  .tramites-proto-page .tp-mini-title,
  .tramites-proto-page .tp-section-title,
  .tramites-proto-page .tp-content-title,
  .tramites-proto-page .tp-snapshot-title {
    font-size: 0.96rem;
  }

  .tramites-proto-page .tp-field label,
  .tramites-proto-page .tp-tramite-switcher-field label {
    font-size: 0.66rem;
  }

  .tramites-proto-page .tp-field input,
  .tramites-proto-page .tp-field select,
  .tramites-proto-page .tp-field textarea,
  .tramites-proto-page .tp-tramite-switcher-field input,
  .tramites-proto-page .tp-assoc-select {
    min-height: 40px;
    padding: 8px 10px;
    font-size: 0.78rem;
  }

  .tramites-proto-page .tp-field textarea {
    min-height: 92px;
  }

  .tramites-proto-page .tp-btn {
    padding: 8px 12px;
    font-size: 0.74rem;
  }

  .tramites-proto-page .tp-main-panel,
  .tramites-proto-page .tp-side-card,
  .tramites-proto-page .tp-section-card,
  .tramites-proto-page .tp-mini-card {
    border: 1px solid var(--tp-line);
    background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
  }

  .tramites-proto-page [data-operational-anchor] {
    --tp-step-accent: transparent;
    --tp-step-line: var(--tp-line);
    --tp-step-bg: #fbfdff;
    --tp-step-bg-focus: #f6f9fd;
    position: relative;
    border-color: var(--tp-step-line);
    background: linear-gradient(180deg, #ffffff 0%, var(--tp-step-bg) 100%);
    box-shadow: inset 0 3px 0 0 var(--tp-step-accent);
  }

  .tramites-proto-page [data-operational-anchor="1"] {
    --tp-step-accent: #7ca7c7;
    --tp-step-line: #d7e5f0;
    --tp-step-bg: #f8fbff;
    --tp-step-bg-focus: #eef6fc;
  }

  .tramites-proto-page [data-operational-anchor="2"] {
    --tp-step-accent: #99b58a;
    --tp-step-line: #dce7d5;
    --tp-step-bg: #fbfdf8;
    --tp-step-bg-focus: #f2f8ec;
  }

  .tramites-proto-page [data-operational-anchor="3"] {
    --tp-step-accent: #c8ac73;
    --tp-step-line: #ecdfc8;
    --tp-step-bg: #fffbf5;
    --tp-step-bg-focus: #fbf3e6;
  }

  .tramites-proto-page [data-operational-anchor="4"] {
    --tp-step-accent: #8599c8;
    --tp-step-line: #dde3f3;
    --tp-step-bg: #f8f9ff;
    --tp-step-bg-focus: #eef1fb;
  }

  .tramites-proto-page [data-operational-anchor="5"] {
    --tp-step-accent: #c18aa1;
    --tp-step-line: #ecdce3;
    --tp-step-bg: #fff8fb;
    --tp-step-bg-focus: #fceff4;
  }

  .tramites-proto-page .tp-main-panel {
    padding: 18px;
  }

  .tramites-proto-page .tp-main-panel.is-operational-base {
    padding: 20px 16px 18px;
  }

  .tramites-proto-page .tp-topbar,
  .tramites-proto-page .tp-section-header,
  .tramites-proto-page .tp-footer {
    display: flex;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
  }

  .tramites-proto-page .tp-topbar,
  .tramites-proto-page .tp-section-header {
    align-items: flex-start;
    margin-bottom: 18px;
  }

  .tramites-proto-page .tp-step-tabs {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
  }

  .tramites-proto-page .tp-topbar-actions {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 12px;
  }

  .tramites-proto-page .tp-tramite-switcher {
    display: flex;
    align-items: flex-end;
    gap: 10px;
    flex-wrap: wrap;
    padding: 12px 14px;
    border-radius: 18px;
    border: 1px solid var(--tp-line);
    background: var(--tp-panel-soft);
  }

  .tramites-proto-page .tp-tramite-switcher-field {
    min-width: 180px;
  }

  .tramites-proto-page .tp-tramite-switcher-field label {
    display: block;
    margin-bottom: 6px;
    font-size: 0.68rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--tp-muted);
  }

  .tramites-proto-page .tp-tramite-switcher-field input {
    width: 100%;
    padding: 11px 12px;
    border-radius: 14px;
    border: 1px solid var(--tp-line);
    background: #fff;
    color: var(--tp-text);
    font: inherit;
  }

  .tramites-proto-page .tp-tramite-switcher-copy {
    margin: 0;
    color: var(--tp-muted);
    font-size: 0.74rem;
    line-height: 1.4;
  }

  .tramites-proto-page .tp-step-tab {
    min-width: 130px;
    padding: 12px 14px;
    border-radius: 18px;
    border: 1px solid var(--tp-line);
    background: var(--tp-panel-soft);
  }

  .tramites-proto-page .tp-step-tab small {
    display: block;
    margin-bottom: 3px;
    color: var(--tp-muted);
    font-size: 0.68rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.06em;
  }

  .tramites-proto-page .tp-step-tab strong {
    display: block;
    font-size: 0.82rem;
    font-weight: 820;
  }

  .tramites-proto-page .tp-step-tab.active {
    background: linear-gradient(180deg, #fff6ef 0%, var(--tp-accent-soft) 100%);
    border-color: #ffd0b6;
    box-shadow: var(--tp-shadow-md);
  }

  .tramites-proto-page .tp-content-layout {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 250px;
    gap: 18px;
  }

  .tramites-proto-page .tp-content-layout.is-operational-base {
    grid-template-columns: minmax(0, 2.15fr) minmax(290px, 0.95fr) minmax(250px, 0.68fr);
    align-items: start;
    gap: 20px;
  }

  .tramites-proto-page .tp-stack,
  .tramites-proto-page .tp-inner-side,
  .tramites-proto-page .tp-summary-rail,
  .tramites-proto-page .tp-activity-rail,
  .tramites-proto-page .tp-side-stack,
  .tramites-proto-page .tp-mini-list,
  .tramites-proto-page .tp-action-list,
  .tramites-proto-page .tp-notes {
    display: flex;
    flex-direction: column;
    gap: 16px;
  }

  .tramites-proto-page .tp-stack {
    min-width: 0;
  }

  .tramites-proto-page .tp-content-layout.is-operational-base .tp-stack {
    display: flex;
    flex-direction: column;
    align-items: start;
    gap: 20px;
  }

  .tramites-proto-page .tp-operational-summary-block {
    min-width: 0;
  }

  .tramites-proto-page .tp-summary-rail {
    min-width: 0;
  }

  .tramites-proto-page .tp-activity-rail {
    min-width: 0;
  }

  .tramites-proto-page .tp-section-card,
  .tramites-proto-page .tp-mini-card,
  .tramites-proto-page .tp-side-card {
    padding: 18px;
    border-radius: var(--tp-radius-lg);
    box-shadow: var(--tp-shadow-md);
  }

  .tramites-proto-page .tp-section-tag {
    padding: 7px 10px;
    border-radius: 999px;
    border: 1px solid var(--tp-line);
    background: var(--tp-panel-soft);
    font-size: 0.72rem;
    font-weight: 800;
    color: var(--tp-deep);
    white-space: nowrap;
  }

  .tramites-proto-page .tp-compact-side-card {
    display: flex;
    flex-direction: column;
    gap: 16px;
  }

  .tramites-proto-page .tp-compact-side-section {
    display: flex;
    flex-direction: column;
    gap: 12px;
  }

  .tramites-proto-page .tp-summary-card {
    display: flex;
    flex-direction: column;
    gap: 16px;
  }

  .tramites-proto-page .tp-summary-section {
    display: flex;
    flex-direction: column;
    gap: 12px;
  }

  .tramites-proto-page .tp-summary-section + .tp-summary-section {
    padding-top: 16px;
    border-top: 1px solid var(--tp-line);
  }

  .tramites-proto-page .tp-compact-side-section + .tp-compact-side-section {
    padding-top: 16px;
    border-top: 1px solid var(--tp-line);
  }

  .tramites-proto-page .tp-compact-side-kicker {
    display: inline-flex;
    align-items: center;
    width: fit-content;
    padding: 6px 10px;
    border-radius: 999px;
    border: 1px solid var(--tp-line);
    background: var(--tp-panel-soft);
    color: var(--tp-muted);
    font-size: 0.68rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.08em;
  }

  .tramites-proto-page .tp-field-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
  }

  .tramites-proto-page .tp-split-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.2fr) minmax(280px, 0.8fr);
    gap: 14px;
  }

  .tramites-proto-page .tp-step1-identity-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr);
    gap: 14px;
  }

  .tramites-proto-page .tp-step1-linked,
  .tramites-proto-page .tp-step1-meta,
  .tramites-proto-page .tp-step1-service-box,
  .tramites-proto-page .tp-doc-note-box {
    padding: 14px;
    border-radius: 18px;
    border: 1px solid var(--tp-line);
    background: linear-gradient(180deg, #ffffff 0%, #f9fbfd 100%);
  }

  .tramites-proto-page .tp-step1-linked {
    margin-bottom: 12px;
  }

  .tramites-proto-page .tp-step1-linked.is-compact {
    margin-bottom: 0;
    padding: 12px;
    border-radius: 16px;
  }

  .tramites-proto-page .tp-step4-compact-stack {
    display: flex;
    flex-direction: column;
    gap: 10px;
  }

  .tramites-proto-page .tp-step4-compact-stack .tp-service-summary {
    gap: 8px;
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .tramites-proto-page .tp-step4-compact-stack .tp-service-item {
    padding: 9px 10px;
    border-radius: 12px;
  }

  .tramites-proto-page .tp-step4-compact-stack .tp-service-item span {
    margin-bottom: 2px;
    font-size: 0.62rem;
    letter-spacing: 0.06em;
  }

  .tramites-proto-page .tp-step4-compact-stack .tp-service-item strong {
    font-size: 0.8rem;
    line-height: 1.25;
  }

  .tramites-proto-page .tp-step1-box-title {
    margin: 0 0 6px;
    font-size: 0.9rem;
    font-weight: 830;
  }

  .tramites-proto-page .tp-step1-box-copy {
    margin: 0 0 12px;
    color: var(--tp-muted);
    font-size: 0.79rem;
    line-height: 1.45;
  }

  .tramites-proto-page .tp-step1-linked.is-compact .tp-step1-box-copy {
    margin-bottom: 10px;
    font-size: 0.76rem;
  }

  .tramites-proto-page .tp-step4-compact-fields {
    gap: 10px;
  }

  .tramites-proto-page .tp-step4-compact-fields .tp-field label {
    margin-bottom: 4px;
    font-size: 0.7rem;
  }

  .tramites-proto-page .tp-step4-compact-fields .tp-field input,
  .tramites-proto-page .tp-step4-compact-fields .tp-field select,
  .tramites-proto-page .tp-step4-compact-fields .tp-field textarea {
    min-height: 42px;
    padding: 9px 12px;
  }

  .tramites-proto-page .tp-step4-compact-fields .tp-field textarea {
    min-height: 76px;
  }

  .tramites-proto-page .tp-step4-compact-note {
    margin: 10px 0 0;
    color: var(--tp-muted);
    font-size: 0.76rem;
    line-height: 1.45;
  }

  .tramites-proto-page .tp-step1-meta {
    display: flex;
    flex-direction: column;
    gap: 10px;
  }

  .tramites-proto-page .tp-meta-item {
    padding: 10px 12px;
    border-radius: 14px;
    background: var(--tp-panel-soft);
    border: 1px solid var(--tp-line);
  }

  .tramites-proto-page .tp-meta-item span {
    display: block;
    margin-bottom: 3px;
    color: var(--tp-muted);
    font-size: 0.68rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.08em;
  }

  .tramites-proto-page .tp-meta-item strong {
    display: block;
    font-size: 0.84rem;
    font-weight: 820;
  }

  .tramites-proto-page .tp-meta-item p {
    margin: 6px 0 0;
    color: var(--tp-muted);
    font-size: 0.76rem;
    line-height: 1.4;
  }

  .tramites-proto-page .tp-pill-row {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    margin-bottom: 12px;
  }

  .tramites-proto-page .tp-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 12px;
    border-radius: 999px;
    border: 1px solid var(--tp-line);
    background: var(--tp-panel-soft);
    font-size: 0.76rem;
    font-weight: 790;
  }

  .tramites-proto-page .tp-pill.is-principal {
    background: #eef6ff;
    border-color: #c6daf4;
    color: var(--tp-deep);
  }

  .tramites-proto-page .tp-assoc-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
  }

  .tramites-proto-page .tp-assoc-item {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    padding: 12px 14px;
    border-radius: 16px;
    border: 1px solid var(--tp-line);
    background: var(--tp-panel-soft);
  }

  .tramites-proto-page .tp-assoc-item > div:first-child {
    flex: 1 1 180px;
    min-width: 0;
  }

  .tramites-proto-page .tp-assoc-item small {
    display: block;
    margin-top: 3px;
    color: var(--tp-muted);
    font-size: 0.75rem;
  }

  .tramites-proto-page .tp-assoc-actions {
    color: var(--tp-deep);
    font-size: 0.74rem;
    font-weight: 800;
    white-space: nowrap;
  }

  .tramites-proto-page .tp-assoc-item .tp-topbar-actions {
    flex: 1 1 260px;
    width: 100%;
    flex-direction: row;
    flex-wrap: wrap;
    justify-content: flex-end;
    align-items: center;
    gap: 8px;
  }

  .tramites-proto-page .tp-assoc-select {
    flex: 1 1 220px;
    min-width: 0;
    max-width: 100%;
  }

  .tramites-proto-page .tp-service-summary {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px;
    margin-bottom: 12px;
  }

  .tramites-proto-page .tp-doc-note-box {
    min-height: 180px;
    display: flex;
    flex-direction: column;
    justify-content: center;
  }

  .tramites-proto-page .tp-utility-stack,
  .tramites-proto-page .tp-check-list,
  .tramites-proto-page .tp-quick-links {
    display: flex;
    flex-direction: column;
    gap: 10px;
  }

  .tramites-proto-page .tp-check-item,
  .tramites-proto-page .tp-link-tile {
    padding: 12px 14px;
    border-radius: 16px;
    border: 1px solid var(--tp-line);
    background: var(--tp-panel-soft);
  }

  .tramites-proto-page .tp-check-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 6px;
  }

  .tramites-proto-page .tp-check-head strong {
    font-size: 0.82rem;
    font-weight: 830;
  }

  .tramites-proto-page .tp-check-state {
    display: inline-flex;
    align-items: center;
    padding: 5px 9px;
    border-radius: 999px;
    font-size: 0.68rem;
    font-weight: 820;
    text-transform: uppercase;
    letter-spacing: 0.08em;
  }

  .tramites-proto-page .tp-check-state.is-ok {
    background: #ebfbf5;
    color: var(--tp-success);
  }

  .tramites-proto-page .tp-check-state.is-warn {
    background: #fff6e8;
    color: var(--tp-warning);
  }

  .tramites-proto-page .tp-check-state.is-info {
    background: #eef6ff;
    color: var(--tp-deep);
  }

  .tramites-proto-page .tp-check-item p {
    margin: 0;
    color: var(--tp-muted);
    font-size: 0.78rem;
    line-height: 1.45;
  }

  .tramites-proto-page .tp-link-tile {
    display: block;
    color: inherit;
    text-decoration: none;
    transition: transform 140ms ease, box-shadow 140ms ease, border-color 140ms ease;
  }

  .tramites-proto-page .tp-link-tile:hover {
    transform: translateY(-1px);
    box-shadow: var(--tp-shadow-md);
    border-color: #c6daf4;
  }

  .tramites-proto-page .tp-link-kicker {
    display: block;
    margin-bottom: 4px;
    color: var(--tp-muted);
    font-size: 0.68rem;
    font-weight: 820;
    text-transform: uppercase;
    letter-spacing: 0.08em;
  }

  .tramites-proto-page .tp-link-tile strong {
    display: block;
    margin-bottom: 4px;
    font-size: 0.83rem;
  }

  .tramites-proto-page .tp-link-tile p {
    margin: 0;
    color: var(--tp-muted);
    font-size: 0.78rem;
    line-height: 1.45;
  }

  .tramites-proto-page .tp-action-item .tp-gallery-list {
    margin-top: 10px;
  }

  .tramites-proto-page .tp-subsection-card {
    padding: 16px;
    border-radius: 18px;
    border: 1px solid var(--tp-line);
    background: linear-gradient(180deg, #ffffff 0%, #f9fbfd 100%);
  }

  .tramites-proto-page .tp-subsection-title {
    margin: 0 0 6px;
    font-size: 0.96rem;
    font-weight: 830;
    letter-spacing: -0.02em;
  }

  .tramites-proto-page .tp-subsection-copy {
    margin: 0 0 14px;
    color: var(--tp-muted);
    font-size: 0.82rem;
    line-height: 1.5;
  }

  .tramites-proto-page .tp-service-stack,
  .tramites-proto-page .tp-rule-stack {
    display: grid;
    gap: 10px;
  }

  .tramites-proto-page .tp-phase-grid,
  .tramites-proto-page .tp-operational-doc-grid {
    display: grid;
    gap: 12px;
  }

  .tramites-proto-page .tp-phase-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .tramites-proto-page .tp-phase-grid.is-step2-priority {
    grid-template-columns: minmax(0, 1fr);
    align-items: start;
  }

  .tramites-proto-page .tp-subsection-card.is-step2-form-card {
    grid-column: 1;
    grid-row: 1;
  }

  .tramites-proto-page .tp-step2-form-shell {
    display: block;
  }

  .tramites-proto-page .tp-step2-dropzone-panel {
    border: 1px solid var(--tp-line);
    background: #fff;
    border-radius: 18px;
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    min-height: 100%;
  }

  .tramites-proto-page .tp-step2-dropzone-head {
    display: flex;
    flex-direction: column;
    gap: 6px;
  }

  .tramites-proto-page .tp-step2-dropzone-head strong {
    font-size: 0.92rem;
    font-weight: 830;
    color: var(--tp-deep);
  }

  .tramites-proto-page .tp-step2-dropzone-head p {
    margin: 0;
    color: var(--tp-muted);
    font-size: 0.76rem;
    line-height: 1.45;
  }

  .tramites-proto-page .tp-step2-dropzone-panel .tp-dropzone-box {
    min-height: 150px;
  }

  .tramites-proto-page .tp-step2-dropzone-panel .tp-gallery {
    min-height: 0;
    padding: 0;
    border: 0;
    background: transparent;
  }

  .tramites-proto-page .tp-step2-dropzone-panel .tp-gallery strong {
    display: block;
    margin-bottom: 10px;
    font-size: 0.78rem;
    font-weight: 820;
    color: var(--tp-deep);
  }

  .tramites-proto-page .tp-operational-doc-grid {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }

  .tramites-proto-page .tp-sequence-stack {
    display: flex;
    flex-direction: column;
    gap: 16px;
  }

  .tramites-proto-page .tp-sequence-block {
    padding: 16px;
    border-radius: 20px;
    border: 1px solid var(--tp-line);
    background: linear-gradient(180deg, #ffffff 0%, #f9fbfd 100%);
  }

  .tramites-proto-page .tp-sequence-block.is-focused {
    border-color: var(--tp-step-line, #ffd0b6);
    background: linear-gradient(180deg, #ffffff 0%, var(--tp-step-bg-focus, #fff2e5) 100%);
    box-shadow: inset 0 3px 0 0 var(--tp-step-accent, #ffd0b6), var(--tp-shadow-md);
  }

  .tramites-proto-page .tp-sequence-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 14px;
    flex-wrap: wrap;
  }

  .tramites-proto-page .tp-sequence-kicker {
    display: inline-flex;
    align-items: center;
    padding: 6px 10px;
    border-radius: 999px;
    background: var(--tp-panel-soft);
    border: 1px solid var(--tp-line);
    color: var(--tp-deep);
    font-size: 0.68rem;
    font-weight: 820;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    white-space: nowrap;
  }

  .tramites-proto-page .tp-sequence-title {
    margin: 0;
    font-size: 1rem;
    font-weight: 835;
    letter-spacing: -0.02em;
  }

  .tramites-proto-page .tp-sequence-copy {
    margin: 6px 0 0;
    color: var(--tp-muted);
    font-size: 0.82rem;
    line-height: 1.48;
  }

  .tramites-proto-page .tp-sequence-tail {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
  }

  .tramites-proto-page .tp-sequence-tail span {
    display: inline-flex;
    align-items: center;
    padding: 7px 10px;
    border-radius: 999px;
    background: rgba(18, 59, 102, 0.06);
    border: 1px solid var(--tp-line);
    color: var(--tp-muted);
    font-size: 0.73rem;
    font-weight: 760;
  }

  .tramites-proto-page .tp-substep-strip {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 14px;
  }

  .tramites-proto-page .tp-substep-pill {
    min-width: 170px;
    padding: 12px 14px;
    border-radius: 16px;
    border: 1px solid var(--tp-line);
    background: var(--tp-panel-soft);
    color: inherit;
    text-decoration: none;
  }

  .tramites-proto-page .tp-substep-pill small {
    display: block;
    margin-bottom: 4px;
    color: var(--tp-muted);
    font-size: 0.68rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.08em;
  }

  .tramites-proto-page .tp-substep-pill strong {
    display: block;
    margin-bottom: 4px;
    font-size: 0.82rem;
  }

  .tramites-proto-page .tp-substep-pill p {
    margin: 0;
    color: var(--tp-muted);
    font-size: 0.76rem;
    line-height: 1.4;
  }

  .tramites-proto-page .tp-substep-pill.is-active {
    border-color: #ffd0b6;
    background: linear-gradient(180deg, #fff8f1 0%, #fff2e5 100%);
    box-shadow: var(--tp-shadow-md);
  }

  .tramites-proto-page .tp-subsection-card.is-focused {
    border-color: var(--tp-step-line, #ffd0b6);
    background: linear-gradient(180deg, #ffffff 0%, var(--tp-step-bg-focus, #fff2e5) 100%);
    box-shadow: inset 0 3px 0 0 var(--tp-step-accent, #ffd0b6), var(--tp-shadow-md);
  }

  .tramites-proto-page .tp-service-item,
  .tramites-proto-page .tp-rule-item {
    padding: 12px 14px;
    border-radius: 16px;
    border: 1px solid var(--tp-line);
    background: var(--tp-panel-soft);
  }

  .tramites-proto-page .tp-service-item span,
  .tramites-proto-page .tp-rule-item span {
    display: block;
    margin-bottom: 4px;
    color: var(--tp-muted);
    font-size: 0.68rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.08em;
  }

  .tramites-proto-page .tp-service-item strong,
  .tramites-proto-page .tp-rule-item strong {
    display: block;
    font-size: 0.87rem;
    font-weight: 820;
    line-height: 1.35;
  }

  .tramites-proto-page .tp-service-item p,
  .tramites-proto-page .tp-rule-item p {
    margin: 6px 0 0;
    color: var(--tp-muted);
    font-size: 0.78rem;
    line-height: 1.45;
  }

  .tramites-proto-page .tp-approval-panel {
    padding: 16px;
    border-radius: 18px;
    border: 1px solid var(--tp-line);
  }

  .tramites-proto-page .tp-approval-panel.is-ready {
    background: linear-gradient(180deg, #edf9f3 0%, #def4e8 100%);
    border-color: #9dd5b4;
  }

  .tramites-proto-page .tp-approval-panel.is-pending {
    background: linear-gradient(180deg, #fff8e9 0%, #fff0c7 100%);
    border-color: #ffd27a;
  }

  .tramites-proto-page .tp-approval-panel.is-info {
    background: linear-gradient(180deg, #eef4ff 0%, #dbeafe 100%);
    border-color: #9bbcf8;
  }

  .tramites-proto-page .tp-approval-panel.is-approved-session {
    background: linear-gradient(180deg, #e7fbef 0%, #cff4dc 100%);
    border-color: #6fca94;
    box-shadow: var(--tp-shadow-md);
  }

  .tramites-proto-page .tp-approval-title {
    margin: 0;
    font-size: 0.96rem;
    font-weight: 840;
  }

  .tramites-proto-page .tp-approval-copy {
    margin: 8px 0 0;
    color: var(--tp-muted);
    font-size: 0.8rem;
    line-height: 1.45;
  }

  .tramites-proto-page .tp-approval-missing {
    margin: 12px 0 0;
    padding-left: 18px;
    color: var(--tp-text);
    font-size: 0.8rem;
  }

  .tramites-proto-page .tp-inline-note {
    display: inline-flex;
    align-items: center;
    padding: 6px 10px;
    border-radius: 999px;
    border: 1px solid var(--tp-line);
    background: var(--tp-panel-soft);
    color: var(--tp-muted);
    font-size: 0.72rem;
    font-weight: 780;
  }

  .tramites-proto-page .tp-inline-note.is-approved {
    background: #ecfdf3;
    border-color: #8dd3aa;
    color: #17603c;
  }

  .tramites-proto-page .tp-inline-note.is-urgent {
    background: #fff1f2;
    border-color: #f2a3ad;
    color: #b42318;
  }

  .tramites-proto-page .tp-form-live-box {
    margin-top: 12px;
    padding: 16px;
    border-radius: 18px;
    border: 1px solid var(--tp-line);
    background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
  }

  .tramites-proto-page .tp-form-live-box.is-disabled {
    background: linear-gradient(180deg, #f7f8fa 0%, #eef2f6 100%);
  }

  .tramites-proto-page .tp-form-live-head {
    margin-bottom: 12px;
  }

  .tramites-proto-page .tp-form-live-head h5 {
    margin: 0;
    font-size: 0.94rem;
    font-weight: 840;
  }

  .tramites-proto-page .tp-form-live-head p {
    margin: 6px 0 0;
    color: var(--tp-muted);
    font-size: 0.79rem;
    line-height: 1.45;
  }

  .tramites-proto-page .tp-form-feedback {
    margin-top: 12px;
    padding: 11px 12px;
    border-radius: 14px;
    border: 1px solid var(--tp-line);
    background: #fff;
    color: var(--tp-text);
    font-size: 0.78rem;
    line-height: 1.45;
  }

  .tramites-proto-page .tp-form-feedback.is-success {
    background: #ecfdf3;
    border-color: #8dd3aa;
    color: #17603c;
  }

  .tramites-proto-page .tp-form-feedback.is-error {
    background: #fff3f2;
    border-color: #efb3ac;
    color: #8c2d23;
  }

  .tramites-proto-page .tp-step4-finance-stack {
    display: flex;
    flex-direction: column;
    gap: 14px;
    margin: 14px 0;
  }

  .tramites-proto-page .tp-step4-money-panel {
    border: 1px solid var(--tp-line);
    background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
    border-radius: 18px;
    padding: 14px;
  }

  .tramites-proto-page .tp-step4-money-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    margin-bottom: 12px;
  }

  .tramites-proto-page .tp-step4-money-head h6 {
    margin: 0;
    font-size: 0.9rem;
    font-weight: 830;
  }

  .tramites-proto-page .tp-step4-money-head p {
    margin: 4px 0 0;
    color: var(--tp-muted);
    font-size: 0.77rem;
    line-height: 1.4;
  }

  .tramites-proto-page .tp-step4-save-status {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 84px;
    padding: 6px 10px;
    border-radius: 999px;
    border: 1px solid var(--tp-line);
    background: var(--tp-panel-soft);
    color: var(--tp-muted);
    font-size: 0.7rem;
    font-weight: 800;
    letter-spacing: 0.04em;
    text-transform: uppercase;
  }

  .tramites-proto-page .tp-step4-cost-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
  }

  .tramites-proto-page .tp-step4-cost-item {
    display: grid;
    grid-template-columns: minmax(0, 1.4fr) minmax(120px, 180px) auto auto;
    gap: 10px;
    align-items: center;
    padding: 10px 12px;
    border-radius: 14px;
    border: 1px solid var(--tp-line);
    background: var(--tp-panel-soft);
  }

  .tramites-proto-page .tp-step4-cost-name {
    min-width: 0;
  }

  .tramites-proto-page .tp-step4-cost-name strong {
    display: block;
    font-size: 0.84rem;
    font-weight: 820;
    line-height: 1.35;
  }

  .tramites-proto-page .tp-step4-cost-name span {
    display: block;
    margin-top: 2px;
    color: var(--tp-muted);
    font-size: 0.72rem;
  }

  .tramites-proto-page .tp-step4-cost-input {
    width: 100%;
    min-height: 40px;
    padding: 9px 12px;
    border-radius: 12px;
    border: 1px solid var(--tp-line);
    background: #fff;
    text-align: right;
    font: inherit;
  }

  .tramites-proto-page .tp-step4-cost-action {
    min-width: 98px;
  }

  .tramites-proto-page .tp-step4-cost-icon {
    min-width: 18px;
    color: #1b7f49;
    font-size: 0.86rem;
    text-align: center;
  }

  .tramites-proto-page .tp-step4-cost-row-status {
    color: var(--tp-muted);
    font-size: 0.72rem;
    line-height: 1.3;
    min-width: 86px;
  }

  .tramites-proto-page .tp-step4-cost-item.is-saved {
    border-color: rgba(49, 138, 89, 0.32);
    background: linear-gradient(180deg, #f4fbf7 0%, #ebf8f1 100%);
  }

  .tramites-proto-page .tp-step4-cost-item.is-error {
    border-color: rgba(211, 88, 88, 0.28);
    background: linear-gradient(180deg, #fff9f9 0%, #fff1f1 100%);
  }

  .tramites-proto-page .tp-step4-cost-total {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    margin-top: 12px;
    padding: 12px 14px;
    border-radius: 14px;
    background: #eef5fb;
    border: 1px solid #d6e5f3;
  }

  .tramites-proto-page .tp-step4-cost-total span {
    color: var(--tp-muted);
    font-size: 0.78rem;
    font-weight: 780;
    text-transform: uppercase;
    letter-spacing: 0.05em;
  }

  .tramites-proto-page .tp-step4-cost-total strong {
    font-size: 1rem;
    font-weight: 840;
  }

  .tramites-proto-page .tp-step4-money-display {
    min-height: 44px;
    display: flex;
    align-items: center;
    padding: 10px 12px;
    border-radius: 12px;
    border: 1px solid var(--tp-line);
    background: #fff;
    font-size: 0.96rem;
    font-weight: 820;
  }

  .tramites-proto-page .tp-step4-breakdown {
    display: block;
    margin-top: 6px;
    color: var(--tp-muted);
    font-size: 0.72rem;
    line-height: 1.45;
  }

  .tramites-proto-page .tp-step4-saldo-info {
    display: flex;
    align-items: center;
    min-height: 42px;
    margin-top: 12px;
    padding: 10px 12px;
    border-radius: 12px;
    border: 1px solid var(--tp-line);
    background: var(--tp-panel-soft);
    color: var(--tp-muted);
    font-size: 0.8rem;
    font-weight: 760;
    line-height: 1.4;
  }

  .tramites-proto-page .tp-step4-saldo-info.is-gestor {
    background: rgba(221, 153, 24, 0.14);
    border-color: rgba(221, 153, 24, 0.28);
    color: #8b5b08;
  }

  .tramites-proto-page .tp-step4-saldo-info.is-sgl {
    background: rgba(40, 118, 199, 0.14);
    border-color: rgba(40, 118, 199, 0.25);
    color: #155f9b;
  }

  .tramites-proto-page .tp-step4-saldo-info.is-even {
    background: rgba(60, 122, 88, 0.12);
    border-color: rgba(60, 122, 88, 0.18);
    color: #256447;
  }

  .tramites-proto-page .tp-field.is-urgent input,
  .tramites-proto-page .tp-field.is-urgent select {
    border-color: #efb3ac;
    box-shadow: 0 0 0 3px rgba(180, 35, 24, 0.10);
  }

  .tramites-proto-page .tp-sequence-block.is-approved-handoff {
    border-color: #a8d6b8;
    background: linear-gradient(180deg, #f3fcf6 0%, #e8f7ee 100%);
    box-shadow: var(--tp-shadow-md);
  }

  .tramites-proto-page .tp-sequence-block.is-approved-handoff .tp-sequence-kicker {
    background: #ecfdf3;
    border-color: #8dd3aa;
    color: #17603c;
  }

  .tramites-proto-page .tp-metrics-row {
    grid-template-columns: repeat(3, minmax(0, 1fr));
    margin-top: 14px;
  }

  .tramites-proto-page .tp-dropzone {
    display: grid;
    grid-template-columns: 1.1fr 0.9fr;
    gap: 12px;
  }

  .tramites-proto-page .tp-dropzone-box,
  .tramites-proto-page .tp-gallery {
    min-height: 180px;
    padding: 18px;
    border-radius: 18px;
  }

  .tramites-proto-page .tp-dropzone-box {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    flex-direction: column;
    text-align: center;
    border: 1px dashed #99aabd;
    background: linear-gradient(180deg, #f8fbff 0%, #f1f6fb 100%);
    color: var(--tp-muted);
    font-weight: 780;
  }

  .tramites-proto-page .tp-dropzone-box.is-actionable {
    cursor: pointer;
  }

  .tramites-proto-page .tp-dropzone-box.is-disabled {
    opacity: 0.72;
    cursor: not-allowed;
  }

  .tramites-proto-page .tp-dropzone-box.is-dragover {
    border-color: var(--tp-accent);
    background: linear-gradient(180deg, #fff7ef 0%, #fff1e1 100%);
    color: var(--tp-deep);
  }

  .tramites-proto-page .tp-dropzone-input {
    display: none;
  }

  .tramites-proto-page .tp-dropzone-meta {
    display: block;
    font-size: 0.76rem;
    font-weight: 700;
    color: var(--tp-muted);
  }

  .tramites-proto-page .tp-dropzone-kicker {
    display: inline-flex;
    align-items: center;
    padding: 5px 10px;
    border-radius: 999px;
    border: 1px solid rgba(15, 23, 42, 0.08);
    background: rgba(255, 255, 255, 0.72);
    color: var(--tp-deep);
    font-size: 0.7rem;
    font-weight: 820;
    letter-spacing: 0.04em;
    text-transform: uppercase;
  }

  .tramites-proto-page .tp-dropzone-title {
    display: block;
    max-width: 26rem;
    font-size: 0.95rem;
    line-height: 1.45;
    color: var(--tp-deep);
  }

  .tramites-proto-page .tp-dropzone-copy {
    display: block;
    max-width: 30rem;
    font-size: 0.76rem;
    line-height: 1.5;
    color: var(--tp-muted);
    font-weight: 700;
  }

  .tramites-proto-page .tp-gallery {
    border: 1px solid var(--tp-line);
    background: #fff;
  }

  .tramites-proto-page .tp-gallery strong,
  .tramites-proto-page .tp-action-item strong,
  .tramites-proto-page .tp-mini-item strong,
  .tramites-proto-page .tp-note-item strong {
    display: block;
    margin-bottom: 4px;
    font-size: 0.83rem;
  }

  .tramites-proto-page .tp-gallery-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-top: 10px;
  }

  .tramites-proto-page [data-step4-doc-panel] .tp-gallery-list {
    gap: 8px;
  }

  .tramites-proto-page [data-step4-doc-panel] .tp-gallery-item {
    padding: 9px 10px;
    border-radius: 14px;
  }

  .tramites-proto-page [data-step4-doc-panel] .tp-gallery-item-head {
    align-items: center;
    gap: 8px;
  }

  .tramites-proto-page [data-step4-doc-panel] .tp-gallery-item-head > div:first-child {
    min-width: 0;
    flex: 1 1 auto;
  }

  .tramites-proto-page [data-step4-doc-panel] .tp-gallery-item-meta {
    margin-top: 4px;
    font-size: 0.7rem;
  }

  .tramites-proto-page [data-step4-doc-panel] .tp-gallery-item-link {
    display: block;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  .tramites-proto-page [data-step4-doc-panel] .tp-gallery-item-actions {
    flex: 0 0 auto;
    flex-wrap: nowrap;
    gap: 6px;
  }

  .tramites-proto-page [data-step4-doc-panel] .tp-gallery-item-actions .tp-btn.small {
    padding: 6px 9px;
    white-space: nowrap;
  }

  .tramites-proto-page [data-step4-doc-panel] .tp-gallery-preview-trigger {
    width: min(100%, 132px);
    margin: 0 0 8px;
    border-radius: 10px;
  }

  .tramites-proto-page [data-step4-doc-panel] .tp-gallery-preview-image {
    aspect-ratio: 16 / 9;
  }

  .tramites-proto-page .tp-finance-preview {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
  }

  .tramites-proto-page .tp-finance-card {
    padding: 14px;
    border-radius: 18px;
    border: 1px solid var(--tp-line);
    background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
  }

  .tramites-proto-page .tp-finance-card.is-outgoing {
    background: linear-gradient(180deg, #f6fbff 0%, #edf5fb 100%);
  }

  .tramites-proto-page .tp-finance-card.is-incoming {
    background: linear-gradient(180deg, #fffaf5 0%, #fff2e5 100%);
  }

  .tramites-proto-page .tp-finance-kicker {
    display: block;
    margin-bottom: 5px;
    color: var(--tp-muted);
    font-size: 0.68rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.08em;
  }

  .tramites-proto-page .tp-finance-card strong {
    display: block;
    margin-bottom: 6px;
    font-size: 0.88rem;
  }

  .tramites-proto-page .tp-finance-card p {
    margin: 0;
    color: var(--tp-muted);
    font-size: 0.79rem;
    line-height: 1.45;
  }

  .tramites-proto-page .tp-save-contracts {
    display: flex;
    flex-direction: column;
    gap: 10px;
  }

  .tramites-proto-page .tp-save-item {
    padding: 12px;
    border-radius: 16px;
    border: 1px solid var(--tp-line);
    background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
  }

  .tramites-proto-page .tp-save-kicker {
    display: block;
    margin-bottom: 5px;
    color: var(--tp-muted);
    font-size: 0.68rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.08em;
  }

  .tramites-proto-page .tp-save-endpoint {
    display: block;
    margin-bottom: 6px;
    color: var(--tp-deep);
    font-size: 0.82rem;
    font-weight: 840;
  }

  .tramites-proto-page .tp-save-item p {
    margin: 0;
    color: var(--tp-muted);
    font-size: 0.79rem;
    line-height: 1.45;
  }

  .tramites-proto-page .tp-step4-kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 12px;
    margin-bottom: 14px;
  }

  .tramites-proto-page .tp-step4-kpi {
    padding: 14px;
    border-radius: 18px;
    border: 1px solid var(--tp-line);
    background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
  }

  .tramites-proto-page .tp-step4-kpi span {
    display: block;
    margin-bottom: 5px;
    color: var(--tp-muted);
    font-size: 0.68rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.08em;
  }

  .tramites-proto-page .tp-step4-kpi strong {
    display: block;
    font-size: 0.94rem;
    font-weight: 840;
  }

  .tramites-proto-page .tp-step4-status-row {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 14px;
  }

  .tramites-proto-page .tp-step4-status-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 8px;
    border-radius: 999px;
    border: 1px solid var(--tp-line);
    background: var(--tp-panel-soft);
    color: var(--tp-text);
    font-size: 0.72rem;
    font-weight: 750;
    margin-right: 6px;
  }

  .tramites-proto-page .tp-step4-status-chip.is-success {
    background: #ebfbf5;
    border-color: #b7ead5;
    color: #0f766e;
  }

  .tramites-proto-page .tp-step4-status-chip.is-neutral {
    background: #f5f7fb;
    border-color: #d5deea;
    color: #516173;
  }

  .tramites-proto-page .tp-doc-legend {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    margin-top: 8px;
  }

  .tramites-proto-page .tp-doc-select option[data-doc-configured="1"] {
    color: #0f766e;
    font-weight: 700;
  }

  .tramites-proto-page .tp-doc-select option[data-doc-configured="0"] {
    color: #516173;
  }

  .tramites-proto-page .tp-source-card {
    padding: 14px;
    border-radius: 18px;
    border: 1px solid #cfe0f1;
    background: linear-gradient(180deg, #f8fbff 0%, #eef5fb 100%);
  }

  .tramites-proto-page .tp-source-meta {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px;
    margin-top: 12px;
  }

  .tramites-proto-page .tp-source-meta-item {
    padding: 10px 12px;
    border-radius: 14px;
    border: 1px solid var(--tp-line);
    background: rgba(255,255,255,0.88);
  }

  .tramites-proto-page .tp-source-meta-item span {
    display: block;
    margin-bottom: 3px;
    color: var(--tp-muted);
    font-size: 0.68rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.08em;
  }

  .tramites-proto-page .tp-source-meta-item strong {
    display: block;
    font-size: 0.84rem;
    font-weight: 820;
  }

  .tramites-proto-page .tp-gallery-item,
  .tramites-proto-page .tp-mini-item,
  .tramites-proto-page .tp-action-item,
  .tramites-proto-page .tp-note-item {
    padding: 7px;
    border-radius: 14px;
  }

  .tramites-proto-page .tp-gallery-item-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 8px;
  }

  .tramites-proto-page .tp-gallery-item-link {
    color: var(--tp-deep);
    font-weight: 820;
    text-decoration: none;
    word-break: break-word;
  }

  .tramites-proto-page .tp-gallery-item-link:hover {
    color: var(--tp-accent);
  }

  .tramites-proto-page .tp-gallery-item-meta {
    display: inline;
    margin-top: 0;
    margin-left: 0;
    font-size: 0.72rem;
    color: var(--tp-muted);
  }

  .tramites-proto-page .tp-gallery-item-actions {
    display: flex;
    align-items: flex-start;
    justify-content: flex-end;
    gap: 8px;
    flex-wrap: wrap;
  }

  .tramites-proto-page .tp-gallery-preview-trigger {
    display: block;
    width: min(100%, 170px);
    margin: 0 0 10px;
    padding: 0;
    border: 0;
    border-radius: 12px;
    overflow: hidden;
    background: #edf4fb;
    cursor: pointer;
    box-shadow: inset 0 0 0 1px rgba(18, 59, 102, 0.10);
  }

  .tramites-proto-page .tp-gallery-preview-trigger:focus-visible {
    outline: 2px solid var(--tp-accent);
    outline-offset: 2px;
  }

  .tramites-proto-page .tp-gallery-preview-image {
    display: block;
    width: 100%;
    aspect-ratio: 16 / 10;
    object-fit: cover;
    background: #f7fafc;
  }

  .tramites-proto-page .tp-modal-card.is-media {
    width: min(880px, calc(100vw - 32px));
  }

  .tramites-proto-page .tp-modal-media {
    display: grid;
    gap: 14px;
  }

  .tramites-proto-page .tp-modal-media-image {
    display: block;
    width: 100%;
    max-height: min(70vh, 720px);
    object-fit: contain;
    border-radius: 16px;
    border: 1px solid var(--tp-line);
    background: #f6f9fc;
  }

  .tramites-proto-page .tp-modal-media-link {
    font-size: 0.84rem;
    font-weight: 800;
    color: var(--tp-deep);
    text-decoration: none;
  }

  .tramites-proto-page .tp-modal-media-link:hover {
    color: var(--tp-accent);
  }

  .tramites-proto-page .tp-side-panel {
    position: sticky;
    top: 24px;
  }

  .tramites-proto-page .tp-side-kicker,
  .tramites-proto-page .tp-note-meta {
    display: block;
    color: var(--tp-muted);
  }

  .tramites-proto-page .tp-side-kicker {
    margin: 0 0 4px;
    font-size: 0.68rem;
    font-weight: 820;
    text-transform: uppercase;
    letter-spacing: 0.08em;
  }

  .tramites-proto-page .tp-note-meta {
    margin-bottom: 6px;
    font-size: 0.73rem;
    font-weight: 700;
  }

  .tramites-proto-page .tp-note-body {
    color: var(--tp-deep);
    font-size: 0.8rem;
    line-height: 1.55;
    white-space: pre-wrap;
    word-break: break-word;
  }

  .tramites-proto-page .tp-note-compose {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-bottom: 14px;
  }

  .tramites-proto-page .tp-note-compose textarea {
    width: 100%;
    min-height: 108px;
    resize: vertical;
    padding: 12px 14px;
    border-radius: 16px;
    border: 1px solid var(--tp-line);
    background: #fff;
    color: var(--tp-deep);
    font: inherit;
    line-height: 1.5;
    box-shadow: inset 0 1px 2px rgba(11, 43, 74, 0.04);
  }

  .tramites-proto-page .tp-note-compose textarea:focus {
    outline: none;
    border-color: #c6daf4;
    box-shadow: 0 0 0 3px rgba(78, 143, 214, 0.14);
  }

  .tramites-proto-page .tp-notes-scroll {
    max-height: 520px;
    overflow-y: auto;
    padding-right: 4px;
  }

  .tramites-proto-page .tp-notes-empty {
    padding: 12px;
    border-radius: 16px;
    border: 1px dashed var(--tp-line);
    background: var(--tp-panel-soft);
    color: var(--tp-muted);
    font-size: 0.79rem;
    line-height: 1.45;
  }

  .tramites-proto-page .tp-note-item.tone-success { border-left: 4px solid var(--tp-success); }
  .tramites-proto-page .tp-note-item.tone-warning { border-left: 4px solid var(--tp-warning); }
  .tramites-proto-page .tp-note-item.tone-danger { border-left: 4px solid var(--tp-danger); }
  .tramites-proto-page .tp-note-item.tone-info { border-left: 4px solid var(--tp-deep); }

  .tramites-proto-page .tp-footer {
    align-items: center;
    margin-top: 18px;
  }

  .tramites-proto-page .tp-footer-hint {
    max-width: 560px;
    color: var(--tp-muted);
    font-size: 0.8rem;
    line-height: 1.45;
  }

  @media (max-width: 1260px) {
    .tramites-proto-page .tp-flow-rail,
    .tramites-proto-page .tp-hero,
    .tramites-proto-page .tp-main-grid,
    .tramites-proto-page .tp-content-layout,
    .tramites-proto-page .tp-dropzone {
      grid-template-columns: 1fr;
    }

    .tramites-proto-page .tp-step-row-grid {
      grid-template-columns: 1fr;
    }

    .tramites-proto-page .tp-inner-side {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .tramites-proto-page .tp-side-panel {
      position: static;
    }

    .tramites-proto-page .tp-content-layout.is-operational-base .tp-stack {
      grid-column: auto;
      grid-template-columns: 1fr;
    }
  }

  @media (max-width: 1100px) {
    .tramites-proto-page .tp-hero-grid {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }
  }

  @media (max-width: 820px) {
    .tramites-proto-page .tp-intro,
    .tramites-proto-page .tp-topbar,
    .tramites-proto-page .tp-section-header,
    .tramites-proto-page .tp-footer {
      align-items: flex-start;
    }

    .tramites-proto-page .tp-step-row-header {
      flex-direction: column;
    }

    .tramites-proto-page .tp-step-row-tag {
      min-width: 0;
    }

    .tramites-proto-page .tp-topbar-actions {
      width: 100%;
      align-items: stretch;
    }

    .tramites-proto-page .tp-tramite-switcher {
      width: 100%;
    }

    .tramites-proto-page .tp-hero-grid,
    .tramites-proto-page .tp-field-grid,
    .tramites-proto-page .tp-split-grid,
    .tramites-proto-page .tp-phase-grid,
    .tramites-proto-page .tp-operational-doc-grid,
    .tramites-proto-page .tp-step1-identity-grid,
    .tramites-proto-page .tp-signal-grid,
    .tramites-proto-page .tp-metrics-row,
    .tramites-proto-page .tp-step4-kpi-grid,
    .tramites-proto-page .tp-source-meta,
    .tramites-proto-page .tp-finance-preview,
    .tramites-proto-page .tp-inner-side {
      grid-template-columns: 1fr;
    }
  }
  /* ─── Colores y separadores por bloque de paso dentro del carril principal ─── */
  .tramites-proto-page .tp-sequence-block[data-operational-focus="2"],
  .tramites-proto-page .tp-sequence-block[data-operational-anchor="2"] {
    border-left: 4px solid #1d5f8f;
    background: linear-gradient(180deg, #fafcff 0%, #f0f7ff 100%);
  }

  .tramites-proto-page .tp-sequence-block[data-operational-focus="3"],
  .tramites-proto-page .tp-sequence-block[data-operational-anchor="3"] {
    border-left: 4px solid #2878b0;
    background: linear-gradient(180deg, #fafeff 0%, #f3f9ff 100%);
  }

  .tramites-proto-page .tp-sequence-block[data-operational-anchor="4"] {
    border-left: 4px solid #0f766e;
    background: linear-gradient(180deg, #f8fefc 0%, #edfaf7 100%);
  }

  .tramites-proto-page .tp-section-card[data-operational-anchor="1"] {
    border-left: 4px solid #123b66;
    background: linear-gradient(180deg, #fafcff 0%, #eef5ff 100%);
  }

  /* Notas del paso 4 y 5 en el carril derecho */
  .tramites-proto-page .tp-side-card[data-step-row-notes="4"] {
    border-left: 3px solid #0f766e;
    border-radius: var(--tp-radius-lg);
    background: linear-gradient(180deg, #f8fefc 0%, #edfaf7 100%);
    padding: 16px;
    margin-bottom: 16px;
  }

  .tramites-proto-page .tp-side-card[data-step-row-notes="5"] {
    border-left: 3px solid #0d9488;
    border-radius: var(--tp-radius-lg);
    background: linear-gradient(180deg, #f7fffe 0%, #e8f9f8 100%);
    padding: 16px;
    margin-bottom: 16px;
  }

  /* Asegurar que la section de paso 1 tiene esquinas y espacio */
  .tramites-proto-page .tp-section-card[data-operational-anchor="1"] {
    border-radius: var(--tp-radius-lg);
    margin-bottom: 18px;
  }

  /* Separadores visuales entre los sequence blocks */
  .tramites-proto-page .tp-sequence-block + .tp-sequence-block {
    margin-top: 18px;
  }
</style>

<div class="main-container tramites-proto-page">
  <div class="pd-ltr-20 xs-pd-20-10">
    <div class="min-height-200px tp-canvas" data-operational-base-root="<?= $isOperationalBasePhase ? '1' : '0' ?>">
      <div class="page-header">
        <div class="row">
          <div class="col-md-8 col-sm-12">
            <div class="title">
              <h4>Detalle de tramites</h4>
            </div>
            <nav aria-label="breadcrumb" role="navigation">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url('/deskapp/dashboard') ?>">Home</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('/deskapp/tramitesn/tramite') ?>">Tramites</a></li>
                <li class="breadcrumb-item active" aria-current="page">Detalle</li>
              </ol>
            </nav>
          </div>
        </div>
      </div>

      <section class="tp-intro-card">
        <div class="tp-intro">
          <div>
            <h1 class="tp-page-title">Detalle de Tramites</h1>
            <p class="tp-page-copy">La pantalla concentra la lectura operativa del expediente para trabajar datos generales, gestion, cierre documental y fases posteriores dentro de una misma experiencia.</p>
          </div>
          <div class="tp-badge"><?= $hasRealStepContext ? 'Base real · Tramite ' . (int) ($prototypeReadOnlyTramite['id'] ?? $prototypeTramiteId) . ' · ' . $prototypeCurrentSurfaceMode : 'Vista integrada · Datos demo' ?></div>
        </div>
      </section>

      <section class="tp-hero">
        <article class="tp-hero-panel">
          <div class="tp-hero-inner">
            <div class="tp-hero-top">
              <div>
                <span class="tp-eyebrow">Tramite activo</span>
                <h2 class="tp-hero-title"><?= esc($prototypeHeroTitle) ?></h2>
              </div>
              <div class="tp-chip-row">
                <span class="tp-chip status" data-operational-text="hero-chip"><?= esc($displayStepLabel) ?></span>
                <span class="tp-chip warning" data-operational-text="hero-meta"><?= esc($isOperationalBasePhase ? ('Paso ' . $activeStep . ' de 3 · Operacion base') : 'Normal · 2 dias') ?></span>
              </div>
            </div>

            <div class="tp-chip-row" style="margin-bottom: 14px;">
              <?php foreach ($prototypeHeroDetailChips as $detailChip): ?>
                <span class="tp-chip">
                  <span class="tp-chip-label"><?= esc((string) ($detailChip['label'] ?? 'Dato')) ?></span>
                  <span class="tp-chip-value"><?= esc((string) ($detailChip['value'] ?? '--')) ?></span>
                </span>
              <?php endforeach; ?>
            </div>

            <div class="tp-hero-grid" data-operational-hero-grid>
              <?php foreach ($currentHeroCards as $heroCard): ?>
                <div class="tp-hero-card">
                  <span class="tp-hero-label"><?= esc($heroCard['label']) ?></span>
                  <span class="tp-hero-value"><?= esc($heroCard['value']) ?></span>
                  <?php if (!empty($heroCard['note'])): ?>
                    <small class="tp-hero-note"><?= esc($heroCard['note']) ?></small>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </article>
      </section>

      <div class="tp-step-rows" data-unified-layout="1">
        <!-- Switcher de trámite -->
        <div style="padding: 10px 18px 0; display: flex; justify-content: flex-end;">
          <form method="get" action="<?= esc($currentPrototypeStepUrl, 'attr') ?>" class="tp-tramite-switcher">
            <div class="tp-tramite-switcher-field">
              <label for="tp_tramite_id_switcher">Trámite activo</label>
              <input id="tp_tramite_id_switcher" type="number" min="1" step="1" name="tramite_id" value="<?= (int) $prototypeTramiteId ?>">
            </div>
            <div class="tp-btn-row">
              <button type="submit" class="tp-btn primary">Cargar</button>
            </div>
          </form>
        </div>

        <?php if ($isOperationalBasePhase): ?>
          <div class="tp-unified-layout" data-operational-base-root="1">
            <section class="tp-step-row<?= $activeStep === 1 ? ' is-active' : '' ?>" data-step-row="1">
              <div class="tp-step-row-header">
                <div>
                  <span class="tp-sequence-kicker">Paso 1</span>
                  <h3 class="tp-section-title">Informacion general y composicion del servicio</h3>
                  <p class="tp-step-row-copy">La fila inicial concentra identidad del expediente, cliente, ejecutivo, tipo principal y tipos ligados. El expediente se edita aqui y la bitacora general queda al mismo nivel de lectura.</p>
                </div>
                <span class="tp-step-row-tag">Base operativa</span>
              </div>

              <div class="tp-step-row-grid">
                <div class="tp-step-col">
                  <section class="tp-section-card" data-operational-anchor="1">
                    <div class="tp-section-header">
                      <div>
                        <h3 class="tp-section-title">Identidad del expediente</h3>
                        <p class="tp-section-copy">Cliente, ejecutivo, identidad base y composicion del servicio viven en el mismo bloque para no fragmentar el paso.</p>
                      </div>
                      <span class="tp-section-tag">Paso 1</span>
                    </div>

                    <div class="tp-step1-identity-grid">
                      <div>
                        <div class="tp-step1-linked">
                          <?php if (!$prototypeStep1CanEdit && $prototypeStep1BlockedReason !== ''): ?>
                            <div style="margin-bottom: 10px;">
                              <span class="tp-inline-note"><?= esc($prototypeStep1BlockedReason) ?></span>
                            </div>
                          <?php endif; ?>
                          <div class="tp-form-live-box<?= $prototypeStep1CanEdit ? '' : ' is-disabled' ?>" data-step1-live-form>
                            <div class="tp-field-grid">
                              <div class="tp-field">
                                <label for="tp_step1_cli_directo_id">Cliente</label>
                                <select id="tp_step1_cli_directo_id" data-step1-input="cli_directo_id"<?= $prototypeStep1CanEdit ? '' : ' disabled' ?>>
                                  <option value="">Seleccione un cliente</option>
                                  <?php foreach (($prototypeStep1Form['options']['cliente'] ?? []) as $optionValue => $optionLabel): ?>
                                    <option value="<?= esc((string) $optionValue, 'attr') ?>"<?= (string) $optionValue === (string) ($prototypeStep1Form['values']['cli_directo_id'] ?? '') ? ' selected' : '' ?>><?= esc((string) $optionLabel) ?></option>
                                  <?php endforeach; ?>
                                </select>
                              </div>
                              <div class="tp-field">
                                <label for="tp_step1_cli_directo_ejecutivo_id">Ejecutivo</label>
                                <select id="tp_step1_cli_directo_ejecutivo_id" data-step1-input="cli_directo_ejecutivo_id"<?= $prototypeStep1CanEdit ? '' : ' disabled' ?>>
                                  <option value="">Seleccione un ejecutivo</option>
                                  <?php foreach (($prototypeStep1Form['options']['ejecutivo'] ?? []) as $optionValue => $optionLabel): ?>
                                    <option value="<?= esc((string) $optionValue, 'attr') ?>"<?= (string) $optionValue === (string) ($prototypeStep1Form['values']['cli_directo_ejecutivo_id'] ?? '') ? ' selected' : '' ?>><?= esc((string) $optionLabel) ?></option>
                                  <?php endforeach; ?>
                                </select>
                              </div>
                              <div class="tp-field">
                                <label for="tp_step1_contrato">Contrato</label>
                                <input id="tp_step1_contrato" value="<?= esc((string) ($prototypeStep1Form['values']['contrato'] ?? ''), 'attr') ?>" data-step1-input="contrato"<?= $prototypeStep1CanEdit ? '' : ' disabled' ?>>
                              </div>
                              <div class="tp-field">
                                <label for="tp_step1_unidad">Unidad</label>
                                <input id="tp_step1_unidad" value="<?= esc((string) ($prototypeStep1Form['values']['unidad'] ?? ''), 'attr') ?>" data-step1-input="unidad"<?= $prototypeStep1CanEdit ? '' : ' disabled' ?>>
                              </div>
                              <div class="tp-field">
                                <label for="tp_step1_serie">Serie</label>
                                <input id="tp_step1_serie" value="<?= esc((string) ($prototypeStep1Form['values']['serie'] ?? ''), 'attr') ?>" data-step1-input="serie"<?= $prototypeStep1CanEdit ? '' : ' disabled' ?>>
                              </div>
                              <div class="tp-field">
                                <label for="tp_step1_placas">Placas</label>
                                <input id="tp_step1_placas" value="<?= esc((string) ($prototypeStep1Form['values']['placas'] ?? ''), 'attr') ?>" data-step1-input="placas"<?= $prototypeStep1CanEdit ? '' : ' disabled' ?>>
                              </div>
                              <div class="tp-field">
                                <label for="tp_step1_entidad_id">Entidad</label>
                                <select id="tp_step1_entidad_id" data-step1-input="entidad_id"<?= $prototypeStep1CanEdit ? '' : ' disabled' ?>>
                                  <option value="">Seleccione una entidad</option>
                                  <?php foreach (($prototypeStep1Form['options']['entidad'] ?? []) as $optionValue => $optionLabel): ?>
                                    <option value="<?= esc((string) $optionValue, 'attr') ?>"<?= (string) $optionValue === (string) ($prototypeStep1Form['values']['entidad_id'] ?? '') ? ' selected' : '' ?>><?= esc((string) $optionLabel) ?></option>
                                  <?php endforeach; ?>
                                </select>
                              </div>
                              <div class="tp-field wide">
                                <label for="tp_step1_observaciones">Observaciones</label>
                                <textarea id="tp_step1_observaciones" data-step1-input="observaciones"<?= $prototypeStep1CanEdit ? '' : ' disabled' ?>><?= esc((string) ($prototypeStep1Form['values']['observaciones'] ?? '')) ?></textarea>
                              </div>
                            </div>
                            <?php if ($prototypeStep1CanEdit): ?>
                              <div class="tp-btn-row" style="margin-top: 12px;">
                                <button type="button" class="tp-btn primary" data-step1-save>Guardar datos base</button>
                              </div>
                            <?php endif; ?>
                            <div class="tp-form-feedback" data-step1-feedback hidden></div>
                          </div>
                        </div>

                        <div class="tp-step1-linked">
                          <?php if (!empty($prototypeStep1ServicesBlockedReason)): ?>
                            <div style="margin-bottom: 10px;">
                              <span class="tp-inline-note"><?= esc($prototypeStep1ServicesBlockedReason) ?></span>
                            </div>
                          <?php endif; ?>
                          <div class="tp-form-live-box<?= !empty($prototypeStep1ServicesForm['canManageBase']) ? '' : ' is-disabled' ?>" data-step1-services-form>
                            <div class="tp-field-grid">
                              <div class="tp-field wide">
                                <label for="tp_step1_principal_tipo_id">Tipo principal</label>
                                <select id="tp_step1_principal_tipo_id" data-step1-service-input="principal_tipo_id"<?= !empty($prototypeStep1ServicesForm['canEditPrincipal']) ? '' : ' disabled' ?>>
                                  <option value="">Seleccione un tipo</option>
                                  <?php foreach (($prototypeStep1ServicesForm['options']['traTipos'] ?? []) as $optionValue => $optionLabel): ?>
                                    <option value="<?= esc((string) $optionValue, 'attr') ?>"<?= (string) $optionValue === (string) ($prototypeStep1ServicesForm['principalTipoId'] ?? '') ? ' selected' : '' ?>><?= esc((string) $optionLabel) ?></option>
                                  <?php endforeach; ?>
                                </select>
                              </div>
                            </div>
                            <?php if (!empty($prototypeStep1ServicesForm['canEditPrincipal'])): ?>
                              <div class="tp-btn-row" style="margin-top: 12px;">
                                <button type="button" class="tp-btn primary" data-step1-principal-save>Guardar tipo principal</button>
                              </div>
                            <?php endif; ?>
                            <div class="tp-field-grid" style="margin-top: 12px;">
                              <div class="tp-field wide">
                                <label for="tp_step1_add_tipo_id">Agregar tipos ligados</label>
                                <select id="tp_step1_add_tipo_id" data-step1-service-input="add_tipo_id" multiple size="6"<?= !empty($prototypeStep1ServicesForm['canManageBase']) ? '' : ' disabled' ?>>
                                  <?php foreach (($prototypeStep1ServicesForm['options']['traTipos'] ?? []) as $optionValue => $optionLabel): ?>
                                    <option value="<?= esc((string) $optionValue, 'attr') ?>"><?= esc((string) $optionLabel) ?></option>
                                  <?php endforeach; ?>
                                </select>
                                <span class="tp-field-help">Puedes ligar varios en una sola accion. El backend impide repetir el principal o asociar dos veces el mismo tipo.</span>
                              </div>
                            </div>
                            <?php if (!empty($prototypeStep1ServicesForm['canManageBase'])): ?>
                              <div class="tp-btn-row" style="margin-top: 12px;">
                                <button type="button" class="tp-btn primary" data-step1-associated-add>Agregar tipos ligados</button>
                              </div>
                            <?php endif; ?>
                            <div class="tp-assoc-list" data-step1-services-list style="margin-top: 12px;">
                              <?php if (!empty($prototypeStep1ServicesForm['services']) && is_array($prototypeStep1ServicesForm['services'])): ?>
                                <?php foreach ($prototypeStep1ServicesForm['services'] as $serviceRow): ?>
                                  <div class="tp-assoc-item" data-step1-asociado-id="<?= (int) ($serviceRow['asociado_id'] ?? 0) ?>">
                                    <div>
                                      <strong><?= esc((string) ($serviceRow['label'] ?? 'Sin tipo')) ?></strong>
                                      <small><?= !empty($serviceRow['is_principal']) ? 'Principal' : 'Asociado editable' ?></small>
                                    </div>
                                    <div class="tp-topbar-actions">
                                      <?php if (empty($serviceRow['is_principal']) && !empty($prototypeStep1ServicesForm['canEditAsociado'])): ?>
                                        <select class="tp-assoc-select" data-step1-associated-select="<?= (int) ($serviceRow['asociado_id'] ?? 0) ?>">
                                          <option value="">Seleccione un tipo</option>
                                          <?php foreach (($prototypeStep1ServicesForm['options']['traTipos'] ?? []) as $optionValue => $optionLabel): ?>
                                            <option value="<?= esc((string) $optionValue, 'attr') ?>"<?= (string) $optionValue === (string) ($serviceRow['tra_tipos_id'] ?? '') ? ' selected' : '' ?>><?= esc((string) $optionLabel) ?></option>
                                          <?php endforeach; ?>
                                        </select>
                                        <button type="button" class="tp-btn secondary" data-step1-associated-save="<?= (int) ($serviceRow['asociado_id'] ?? 0) ?>">Actualizar</button>
                                      <?php endif; ?>
                                      <?php if (empty($serviceRow['is_principal']) && !empty($prototypeStep1ServicesForm['canDeleteAsociado'])): ?>
                                        <button type="button" class="tp-btn ghost" data-step1-associated-delete="<?= (int) ($serviceRow['asociado_id'] ?? 0) ?>">Eliminar</button>
                                      <?php endif; ?>
                                      <?php if (!empty($serviceRow['is_principal'])): ?>
                                        <span class="tp-pill is-principal">Principal</span>
                                      <?php endif; ?>
                                    </div>
                                  </div>
                                <?php endforeach; ?>
                              <?php else: ?>
                                <span class="tp-inline-note">No hay tipos ligados registrados todavia.</span>
                              <?php endif; ?>
                            </div>
                            <div class="tp-form-feedback" data-step1-services-feedback hidden></div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </section>
                </div>

                <div class="tp-step-col">
                  <section class="tp-section-card" data-operational-anchor="1">
                    <div class="tp-section-header">
                      <div>
                        <h3 class="tp-section-title">Documentos del expediente</h3>
                        <p class="tp-section-copy">El catálogo documental del paso 1 vive junto a sus validaciones de completitud para que no se vuelva un rail global.</p>
                      </div>
                      <span class="tp-section-tag">Carril centro</span>
                    </div>

                    <section class="tp-upload-panel tp-step2-dropzone-panel" data-step1-doc-panel style="margin-top: 0;">
                      <div class="tp-step4-status-row" style="margin-bottom: 12px;">
                        <span class="tp-step4-status-chip" data-step1-doc-count><?= esc($step1DocProgressLabel) ?></span>
                        <span class="tp-step4-status-chip" data-step1-doc-uploaded-total><?= esc((string) $step1UploadedTotalDocs) ?> cargado(s)</span>
                        <span class="tp-step4-status-chip<?= $prototypeStep1DocsCanUpload ? ' is-success' : '' ?>"><?= esc($prototypeStep1DocsCanUpload ? 'Editable' : 'Solo lectura') ?></span>
                      </div>
                      <div class="tp-field" style="margin-bottom: 12px;">
                        <label for="tp_step1_documento_id">Documento a cargar</label>
                        <select id="tp_step1_documento_id" class="tp-doc-select" data-step1-doc-type<?= $prototypeStep1DocsCanUpload ? '' : ' disabled' ?>>
                          <option value="">Selecciona un documento</option>
                          <?php foreach (($prototypeStep1DocsForm['options']['documentTypes'] ?? []) as $optionValue => $optionLabel): ?>
                            <?php $optionMeta = $prototypeStep1DocsForm['options']['documentTypeMeta'][$optionValue] ?? []; ?>
                            <?php $optionOriginLabel = !empty($optionMeta['isConfigured']) ? 'Ligado' : 'Catalogo'; ?>
                            <option
                              value="<?= esc((string) $optionValue, 'attr') ?>"
                              data-doc-name="<?= esc((string) ($optionMeta['documento_nombre'] ?? $optionLabel), 'attr') ?>"
                              data-doc-configured="<?= !empty($optionMeta['isConfigured']) ? '1' : '0' ?>"
                              data-doc-badge="<?= esc((string) ($optionMeta['sourceBadge'] ?? 'Catalogo general'), 'attr') ?>"
                              data-doc-tone="<?= esc((string) ($optionMeta['sourceTone'] ?? 'neutral'), 'attr') ?>"
                            ><?= esc((string) $optionLabel . ' · ' . $optionOriginLabel) ?></option>
                          <?php endforeach; ?>
                        </select>
                        <div class="tp-doc-legend">
                          <span class="tp-step4-status-chip is-success">Ligado al tipo</span>
                          <span class="tp-step4-status-chip is-neutral">Catalogo general</span>
                        </div>
                      </div>
                      <div class="tp-dropzone-box<?= $prototypeStep1DocsCanUpload ? ' is-actionable' : ' is-disabled' ?>" data-step1-doc-dropzone>
                        <input class="tp-dropzone-input" type="file" data-step1-doc-file<?= $prototypeStep1DocsCanUpload ? '' : ' disabled' ?>>
                        <span class="tp-dropzone-kicker">Paso 1</span>
                        <strong class="tp-dropzone-title">Arrastra aqui el documento o haz clic para elegir un archivo</strong>
                        <span class="tp-dropzone-copy">Cada archivo se valida contra el documento_id permitido por los tipos ligados al expediente.</span>
                        <span class="tp-dropzone-meta" data-step1-doc-selected>Sin archivo seleccionado.</span>
                      </div>
                      <?php if ($prototypeStep1DocsCanUpload): ?>
                        <div class="tp-btn-row" style="margin-top: 12px;">
                          <button type="button" class="tp-btn primary" data-step1-doc-upload>Subir documento</button>
                        </div>
                      <?php elseif ($prototypeStep1DocsBlockedReason !== ''): ?>
                        <div style="margin-top: 12px;">
                          <span class="tp-inline-note"><?= esc($prototypeStep1DocsBlockedReason) ?></span>
                        </div>
                      <?php endif; ?>
                      <div class="tp-form-feedback" data-step1-doc-feedback hidden></div>
                      <div class="tp-gallery">
                        <strong>Catalogo actual</strong>
                        <div class="tp-gallery-list" data-step1-doc-gallery style="margin-top: 0;">
                          <?php if (!empty($prototypeStep1DocsForm['documents']) && is_array($prototypeStep1DocsForm['documents'])): ?>
                            <?php foreach ($prototypeStep1DocsForm['documents'] as $documentItem): ?>
                              <?php
                                $documentId = (int) ($documentItem['documento_id'] ?? 0);
                                $hasFile = !empty($documentItem['has_file']);
                                $fileName = (string) ($documentItem['file'] ?? '');
                                $fileUrl = (string) ($documentItem['file_url'] ?? '');
                                $sourceBadge = trim((string) ($documentItem['source_badge'] ?? 'Catalogo general'));
                                $sourceTone = !empty($documentItem['source_tone']) && (string) $documentItem['source_tone'] === 'success' ? ' is-success' : ' is-neutral';
                                $docMetaParts = [];
                                $docMetaParts[] = !empty($documentItem['is_required']) ? 'Obligatorio' : 'Opcional';
                                if (!empty($documentItem['source_types_label'])) {
                                  $docMetaParts[] = (string) $documentItem['source_types_label'];
                                }
                                $docMetaParts[] = $hasFile ? ((string) ($documentItem['status_label'] ?? 'Cargado')) : 'Pendiente';
                              ?>
                              <div class="tp-gallery-item">
                                <div class="tp-gallery-item-head">
                                  <div>
                                    <?php if ($hasFile && $fileUrl !== ''): ?>
                                      <a class="tp-gallery-item-link" href="<?= esc($fileUrl, 'attr') ?>" target="_blank" rel="noreferrer"><?= esc($fileName) ?></a>
                                    <?php else: ?>
                                      <strong><?= esc((string) ($documentItem['documento_nombre'] ?? 'Documento')) ?></strong>
                                    <?php endif; ?>
                                    <span class="tp-step4-status-chip<?= $sourceTone ?>"><?= esc($sourceBadge) ?></span><span class="tp-gallery-item-meta"><?= esc(implode(' · ', array_filter($docMetaParts))) ?></span>
                                  </div>
                                  <?php if ($prototypeStep1DocsCanDelete && $hasFile && $documentId > 0 && $fileName !== ''): ?>
                                    <button type="button" class="tp-btn secondary small" data-step1-doc-delete="<?= esc($fileName, 'attr') ?>" data-step1-doc-id="<?= $documentId ?>">Eliminar</button>
                                  <?php endif; ?>
                                </div>
                              </div>
                            <?php endforeach; ?>
                          <?php else: ?>
                            <div class="tp-gallery-item">Sin catalogo documental configurado para los tipos ligados del expediente.</div>
                          <?php endif; ?>
                        </div>
                      </div>
                      <?php if (!$prototypeStep1DocsCanDelete && $prototypeStep1DocsDeleteBlockedReason !== ''): ?>
                        <div style="margin-top: 10px;">
                          <span class="tp-inline-note"><?= esc($prototypeStep1DocsDeleteBlockedReason) ?></span>
                        </div>
                      <?php endif; ?>
                    </section>

                    <div class="tp-mini-list" style="margin-top: 14px;">
                      <?php foreach ($step1OperationalChecks as $checkItem): ?>
                        <div class="tp-mini-item tp-mini-item-status is-<?= esc((string) ($checkItem['state'] ?? 'info')) ?>">
                          <span class="tp-mini-status-square" aria-hidden="true"></span>
                          <div class="tp-mini-item-copy">
                            <strong><?= esc((string) ($checkItem['label'] ?? 'Revision')) ?></strong>
                            <span><?= esc((string) ($checkItem['detail'] ?? '')) ?></span>
                          </div>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  </section>
                </div>

                <div class="tp-step-col">
                  <section class="tp-section-card" data-operational-anchor="1">
                    <span class="tp-side-kicker">Bitacora general</span>
                    <h3 class="tp-side-title">Comentarios del expediente</h3>
                    <p class="tp-side-copy">La bitacora general se mantiene en la misma fila del expediente para que el contexto vivo no se vaya a un carril global.</p>

                    <?php if ($prototypeEvidenceCanAdd): ?>
                      <div class="tp-note-compose" data-prototype-evidence-form>
                        <textarea placeholder="Escribe aqui un comentario operativo para el expediente" data-prototype-evidence-input></textarea>
                        <div class="tp-btn-row">
                          <button type="button" class="tp-btn primary" data-prototype-evidence-save>Guardar comentario</button>
                        </div>
                      </div>
                    <?php elseif ($prototypeEvidenceBlockedReason !== ''): ?>
                      <div style="margin-bottom: 14px;">
                        <span class="tp-inline-note"><?= esc($prototypeEvidenceBlockedReason) ?></span>
                      </div>
                    <?php endif; ?>

                    <div class="tp-form-feedback" data-prototype-evidence-feedback hidden></div>

                    <div class="tp-notes tp-notes-scroll" data-prototype-evidence-list<?= empty($prototypeEvidenceItems) ? ' hidden' : '' ?>>
                      <?php foreach ($prototypeEvidenceItems as $noteItem): ?>
                        <div class="tp-note-item tone-info">
                          <span class="tp-note-meta"><?= esc((string) ($noteItem['createdAtLabel'] ?? 'Sin fecha') . ' · ' . (string) ($noteItem['author'] ?? 'Sistema')) ?></span>
                          <span class="tp-note-body"><?= esc((string) ($noteItem['comment'] ?? '')) ?></span>
                        </div>
                      <?php endforeach; ?>
                    </div>
                    <div class="tp-notes-empty" data-prototype-evidence-empty<?= empty($prototypeEvidenceItems) ? '' : ' hidden' ?>>Todavia no hay comentarios operativos guardados en este expediente.</div>
                  </section>
                </div>
              </div>
            </section>

            <section class="tp-step-row<?= $activeStep === 2 ? ' is-active' : '' ?>" data-step-row="2">
              <div class="tp-step-row-header">
                <div>
                  <span class="tp-sequence-kicker">Paso 2</span>
                  <h3 class="tp-section-title">Gestion y pago de derechos</h3>
                  <p class="tp-step-row-copy">Esta fila concentra la asignacion operativa, el guardado de derechos, su zona documental y la aprobacion que libera el cierre documental.</p>
                </div>
                <span class="tp-step-row-tag">Operacion</span>
              </div>

              <div class="tp-step-row-grid">
                <div class="tp-step-col">
                  <section class="tp-section-card" data-operational-anchor="2">
                    <div class="tp-section-header">
                      <div>
                        <h3 class="tp-section-title">Formulario operativo</h3>
                        <p class="tp-section-copy">La asignacion de gestor y el pago de derechos se leen como un mismo frente de trabajo.</p>
                      </div>
                      <span class="tp-section-tag">Paso 2</span>
                    </div>

                    <div class="tp-form-live-box<?= $prototypeStep2CanEdit ? '' : ' is-disabled' ?>" data-step2-live-form>
                      <div class="tp-form-live-head">
                        <h5>Editar y guardar sobre contratos reales</h5>
                        <p>Guarda primero asignacion de gestor y despues pago de derechos, sin duplicar la pantalla legacy completa.</p>
                      </div>
                      <div class="tp-field-grid">
                        <div class="tp-field">
                          <label for="tp_step2_empresa_gestora">Empresa gestora</label>
                          <select id="tp_step2_empresa_gestora" data-step2-input="empresa_gestora_id"<?= $prototypeStep2CanEdit ? '' : ' disabled' ?>>
                            <option value="">Seleccione una empresa</option>
                            <?php foreach (($prototypeStep2Form['options']['empresaGestora'] ?? []) as $optionValue => $optionLabel): ?>
                              <option value="<?= esc((string) $optionValue, 'attr') ?>"<?= (string) $optionValue === (string) ($prototypeStep2Form['values']['empresa_gestora_id'] ?? '') ? ' selected' : '' ?>><?= esc((string) $optionLabel) ?></option>
                            <?php endforeach; ?>
                          </select>
                        </div>
                        <div class="tp-field">
                          <label for="tp_step2_gestor">Gestor</label>
                          <select id="tp_step2_gestor" data-step2-input="gestor_id"<?= $prototypeStep2CanEdit ? '' : ' disabled' ?>>
                            <option value="">Seleccione un gestor</option>
                            <?php foreach (($prototypeStep2Form['options']['gestor'] ?? []) as $optionValue => $optionLabel): ?>
                              <option value="<?= esc((string) $optionValue, 'attr') ?>"<?= (string) $optionValue === (string) ($prototypeStep2Form['values']['gestor_id'] ?? '') ? ' selected' : '' ?>><?= esc((string) $optionLabel) ?></option>
                            <?php endforeach; ?>
                          </select>
                          <span class="tp-field-help">La lista de gestores depende de la empresa seleccionada.</span>
                        </div>
                        <div class="tp-field">
                          <label for="tp_step2_derechos_tramite">Monto pago de derechos</label>
                          <input id="tp_step2_derechos_tramite" type="number" step="0.01" min="0" value="<?= esc((string) ($prototypeStep2Form['values']['derechos_tramite'] ?? ''), 'attr') ?>" data-step2-input="derechos_tramite"<?= $prototypeStep2CanEdit ? '' : ' disabled' ?>>
                        </div>
                        <div class="tp-field">
                          <label for="tp_step2_derechos_pago_sitio">Pago</label>
                          <select id="tp_step2_derechos_pago_sitio" data-step2-input="derechos_pago_sitio"<?= $prototypeStep2CanEdit ? '' : ' disabled' ?>>
                            <option value="">Seleccione una opcion</option>
                            <?php foreach (($prototypeStep2Form['options']['derechosPagoSitio'] ?? []) as $optionValue => $optionLabel): ?>
                              <option value="<?= esc((string) $optionValue, 'attr') ?>"<?= (string) $optionValue === (string) ($prototypeStep2Form['values']['derechos_pago_sitio'] ?? '') ? ' selected' : '' ?>><?= esc((string) $optionLabel) ?></option>
                            <?php endforeach; ?>
                          </select>
                        </div>
                        <div class="tp-field<?= $prototypeStep2VigenciaIsUrgent ? ' is-urgent' : '' ?>" data-step2-vigencia-field>
                          <label for="tp_step2_derechos_vigencia">Fecha vigencia</label>
                          <input id="tp_step2_derechos_vigencia" type="datetime-local" value="<?= esc($prototypeStep2VigenciaInputValue, 'attr') ?>" data-step2-input="derechos_vigencia"<?= $prototypeStep2CanEdit ? '' : ' disabled' ?>>
                          <span class="tp-inline-note is-urgent" data-step2-vigencia-warning<?= $prototypeStep2VigenciaWarningText === '' ? ' hidden' : '' ?>><?= esc($prototypeStep2VigenciaWarningText) ?></span>
                        </div>
                        <div class="tp-field">
                          <label for="tp_step2_derechos_revol_cliente">Forma de pago</label>
                          <select id="tp_step2_derechos_revol_cliente" data-step2-input="derechos_revol_cliente"<?= $prototypeStep2CanEdit ? '' : ' disabled' ?>>
                            <option value="">Seleccione una opcion</option>
                            <?php foreach (($prototypeStep2Form['options']['derechosRevolCliente'] ?? []) as $optionValue => $optionLabel): ?>
                              <option value="<?= esc((string) $optionValue, 'attr') ?>"<?= (string) $optionValue === (string) ($prototypeStep2Form['values']['derechos_revol_cliente'] ?? '') ? ' selected' : '' ?>><?= esc((string) $optionLabel) ?></option>
                            <?php endforeach; ?>
                          </select>
                        </div>
                        <div class="tp-field wide">
                          <label for="tp_step2_derechos_refer_banc">Referencia bancaria</label>
                          <input id="tp_step2_derechos_refer_banc" type="text" value="<?= esc((string) ($prototypeStep2Form['values']['derechos_refer_banc'] ?? ''), 'attr') ?>" data-step2-input="derechos_refer_banc"<?= $prototypeStep2CanEdit ? '' : ' disabled' ?>>
                        </div>
                      </div>
                      <?php if ($prototypeStep2CanEdit): ?>
                        <div class="tp-btn-row" style="margin-top: 12px;">
                          <button type="button" class="tp-btn primary" data-step2-save>Guardar Paso 2</button>
                        </div>
                      <?php else: ?>
                        <div style="margin-top: 12px;">
                          <span class="tp-inline-note"><?= esc($prototypeStep2BlockedReason !== '' ? $prototypeStep2BlockedReason : 'Este perfil no tiene permisos completos para editar el Paso 2 desde el prototipo.') ?></span>
                        </div>
                      <?php endif; ?>
                      <div class="tp-form-feedback" data-step2-feedback hidden></div>
                    </div>
                  </section>
                </div>

                <div class="tp-step-col">
                  <section class="tp-section-card" data-operational-anchor="2">
                    <div class="tp-section-header">
                      <div>
                        <h3 class="tp-section-title">Comprobantes y checklist</h3>
                        <p class="tp-section-copy">El carril central soporta la evidencia de derechos y el checklist operativo del mismo paso.</p>
                      </div>
                      <span class="tp-section-tag">Carril centro</span>
                    </div>

                    <div class="tp-upload-panel tp-step2-dropzone-panel" style="margin-top: 0;">
                      <div class="tp-upload-head tp-step2-dropzone-head">
                        <strong>Dropzone pago de derechos</strong>
                      </div>
                      <div class="tp-dropzone-box<?= $prototypeStep2CanUploadDocs ? ' is-actionable' : ' is-disabled' ?>" data-step2-doc-dropzone>
                        <input class="tp-dropzone-input" type="file" data-step2-doc-file<?= $prototypeStep2CanUploadDocs ? '' : ' disabled' ?>>
                        <span class="tp-dropzone-kicker">Pago de derechos</span>
                        <strong class="tp-dropzone-title">Arrastra aqui comprobantes o haz clic para elegir un archivo</strong>
                        <span class="tp-dropzone-copy">La zona documental mantiene upload inmediato sobre el endpoint real.</span>
                        <span class="tp-dropzone-meta" data-step2-doc-selected>Sin archivo seleccionado.</span>
                      </div>
                      <?php if ($prototypeStep2CanUploadDocs): ?>
                        <div class="tp-btn-row" style="margin-top: 12px;">
                          <button type="button" class="tp-btn primary" data-step2-doc-upload>Subir comprobante</button>
                        </div>
                      <?php elseif ($prototypeStep2DocsBlockedReason !== ''): ?>
                        <div style="margin-top: 12px;">
                          <span class="tp-inline-note"><?= esc($prototypeStep2DocsBlockedReason) ?></span>
                        </div>
                      <?php endif; ?>
                      <div class="tp-form-feedback" data-step2-doc-feedback hidden></div>
                      <div class="tp-gallery">
                        <strong>Soporte actual</strong>
                        <div class="tp-gallery-list" data-step2-doc-gallery style="margin-top: 0;">
                          <?php foreach ($step2DocPreviewItems as $docItem): ?>
                            <div class="tp-gallery-item"><?= esc($docItem) ?></div>
                          <?php endforeach; ?>
                        </div>
                      </div>
                      <?php if (!$prototypeStep2CanDeleteDocs && $prototypeStep2DeleteBlockedReason !== ''): ?>
                        <div style="margin-top: 10px;">
                          <span class="tp-inline-note"><?= esc($prototypeStep2DeleteBlockedReason) ?></span>
                        </div>
                      <?php endif; ?>
                    </div>

                    <div class="tp-mini-list" style="margin-top: 14px;">
                      <?php foreach ($step2CompletionSignals as $signal): ?>
                        <div class="tp-mini-item">
                          <strong><?= esc((string) ($signal['label'] ?? 'Revision')) ?></strong>
                          <span><?= esc((string) ($signal['value'] ?? '')) ?></span>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  </section>
                </div>

                <div class="tp-step-col">
                  <section class="tp-section-card" data-operational-anchor="2">
                    <span class="tp-side-kicker">Aprobacion</span>
                    <h3 class="tp-side-title">Salida del paso 2</h3>
                    <p class="tp-side-copy">La aprobacion y la vigencia viven en el mismo carril derecho del paso para dejar claro lo que libera el cierre documental.</p>

                    <div class="tp-approval-panel<?= $step2ApprovalReady ? ($prototypeCanApproveStep2 ? ' is-ready' : ' is-info') : ' is-pending' ?>" data-operational-step2-panel>
                      <h5 class="tp-approval-title" data-operational-step2-title><?= esc($step2ApprovalTitleText) ?></h5>
                      <p class="tp-approval-copy" data-operational-step2-copy><?= esc($step2ApprovalCopyText) ?></p>
                      <div data-step2-approval-actions>
                        <?php if ($step2ApprovalReady && $prototypeCanApproveStep2): ?>
                          <div class="tp-btn-row" style="margin-top: 12px;">
                            <button type="button" class="tp-btn primary" data-operational-step-link="3" data-operational-approve="1">Aprobar tramite</button>
                          </div>
                          <div style="margin-top: 8px;">
                            <span class="tp-inline-note" data-operational-step2-note>Al aprobar, la siguiente lectura operativa es cerrar evidencias finales en Paso 3.</span>
                          </div>
                          <div class="tp-btn-row" style="margin-top: 10px;" data-operational-reset-row hidden>
                            <a href="javascript:void(0)" class="tp-btn secondary" data-operational-reset-approval="1">Limpiar aprobacion local</a>
                          </div>
                        <?php elseif ($step2ApprovalReady): ?>
                          <div style="margin-top: 12px;">
                            <span class="tp-inline-note"><?= esc($step2ApprovalInfoText) ?></span>
                          </div>
                        <?php else: ?>
                          <ul class="tp-approval-missing">
                            <?php foreach ($step2ApprovalMissing as $missingField): ?>
                              <li><?= esc($missingField) ?></li>
                            <?php endforeach; ?>
                          </ul>
                        <?php endif; ?>
                      </div>
                    </div>

                    <div class="tp-mini-list" style="margin-top: 14px;">
                      <div class="tp-mini-item">
                        <strong>Vigencia</strong>
                        <span><?= esc($prototypeStep2VigenciaWarningText !== '' ? $prototypeStep2VigenciaWarningText : 'Sin alerta de vigencia para esta referencia.') ?></span>
                      </div>
                      <div class="tp-mini-item">
                        <strong>Soportes</strong>
                        <span><?= esc($step2SupportCountLabel) ?></span>
                      </div>
                    </div>
                  </section>
                </div>
              </div>
            </section>

            <section class="tp-step-row<?= $activeStep === 3 ? ' is-active' : '' ?>" data-step-row="3" data-operational-step3-sequence>
              <div class="tp-step-row-header">
                <div>
                  <span class="tp-sequence-kicker">Paso 3</span>
                  <h3 class="tp-section-title">Evidencias finales y cierre documental</h3>
                  <p class="tp-step-row-copy">Esta fila ya no mezcla formularios largos: solo vive el cierre documental que destraba la fase financiera posterior.</p>
                </div>
                <span class="tp-step-row-tag">Cierre</span>
              </div>

              <div class="tp-step-row-grid">
                <div class="tp-step-col">
                  <section class="tp-section-card" data-operational-anchor="3">
                    <div class="tp-section-header">
                      <div>
                        <h3 class="tp-section-title">Evidencias finales</h3>
                        <p class="tp-section-copy">Dropzone puntual para tramite entregado por gestor y acuse de recibo del cliente.</p>
                      </div>
                      <span class="tp-section-tag">Paso 3</span>
                    </div>

                    <div class="tp-step4-status-row" data-step3-status-row>
                      <?php foreach ($step3EvidenceChips as $chip): ?>
                        <?php
                          $step3ChipKey = 'generic';
                          if (($chip['label'] ?? '') === 'Tramite Entregado por Gestor') {
                            $step3ChipKey = 'tramite_recibido';
                          } elseif (($chip['label'] ?? '') === 'Acuse de Recibo del Cliente') {
                            $step3ChipKey = 'acuse_recibo_cliente';
                          }
                        ?>
                        <span class="tp-step4-status-chip<?= !empty($chip['isSuccess']) ? ' is-success' : '' ?>" data-step3-chip="<?= esc($step3ChipKey, 'attr') ?>"><?= esc($chip['label']) ?></span>
                      <?php endforeach; ?>
                    </div>

                    <div class="tp-form-live-box<?= $prototypeStep3CanUpload ? '' : ' is-disabled' ?>" data-step3-live-form style="margin-top: 12px;">
                      <div class="tp-form-live-head">
                        <h5>Subir evidencias finales reales</h5>
                        <p>Este bloque usa el upload real del sistema para registrar las dos evidencias del cierre operativo.</p>
                      </div>
                      <div class="tp-field-grid">
                        <div class="tp-field">
                          <label for="tp_step3_comprobante_final">Tipo de evidencia</label>
                          <select id="tp_step3_comprobante_final" data-step3-input="comprobante_final"<?= $prototypeStep3CanUpload ? '' : ' disabled' ?>>
                            <?php foreach (($prototypeStep3Form['options']['comprobanteFinal'] ?? []) as $optionValue => $optionLabel): ?>
                              <option value="<?= esc((string) $optionValue, 'attr') ?>"><?= esc((string) $optionLabel) ?></option>
                            <?php endforeach; ?>
                          </select>
                        </div>
                      </div>
                      <div class="tp-upload-panel" style="margin-top: 12px;">
                        <div class="tp-upload-head">
                          <strong>Dropzone evidencias finales</strong>
                          <p>Una sola superficie clara y la galeria en la misma lectura del paso.</p>
                        </div>
                        <div class="tp-dropzone-box<?= $prototypeStep3CanUpload ? ' is-actionable' : ' is-disabled' ?>" data-step3-dropzone>
                          <input id="tp_step3_file" class="tp-dropzone-input" type="file" data-step3-file<?= $prototypeStep3CanUpload ? '' : ' disabled' ?>>
                          <span class="tp-dropzone-kicker">Evidencia final</span>
                          <strong class="tp-dropzone-title">Arrastra aqui el archivo final o haz clic para seleccionarlo</strong>
                          <span class="tp-dropzone-copy">Primero elige el tipo de evidencia y despues carga el archivo.</span>
                          <span class="tp-dropzone-meta" data-step3-file-selected>Sin archivo seleccionado.</span>
                        </div>
                      </div>
                      <?php if ($prototypeStep3CanUpload): ?>
                        <div class="tp-btn-row" style="margin-top: 12px;">
                          <button type="button" class="tp-btn primary" data-step3-upload>Subir evidencia</button>
                        </div>
                      <?php elseif ($prototypeStep3BlockedReason !== ''): ?>
                        <div style="margin-top: 12px;">
                          <span class="tp-inline-note"><?= esc($prototypeStep3BlockedReason) ?></span>
                        </div>
                      <?php endif; ?>
                      <?php if (!$prototypeStep3CanDelete && $prototypeStep3DeleteBlockedReason !== ''): ?>
                        <div style="margin-top: 12px;">
                          <span class="tp-inline-note"><?= esc($prototypeStep3DeleteBlockedReason) ?></span>
                        </div>
                      <?php endif; ?>
                      <div class="tp-form-feedback" data-step3-feedback hidden></div>
                      <div class="tp-gallery" style="margin-top: 12px;">
                        <strong>Evidencias registradas</strong>
                        <div class="tp-gallery-list" data-step3-gallery style="margin-top: 0;">
                          <?php foreach ($step3EvidenceDocItems as $docItem): ?>
                            <div class="tp-gallery-item"><?= esc($docItem) ?></div>
                          <?php endforeach; ?>
                        </div>
                      </div>
                    </div>
                  </section>
                </div>

                <div class="tp-step-col">
                  <section class="tp-section-card" data-operational-anchor="3">
                    <div class="tp-section-header">
                      <div>
                        <h3 class="tp-section-title">Gate del cierre</h3>
                        <p class="tp-section-copy">Aqui debe quedar claro que este cierre es el que habilita la siguiente fase financiera.</p>
                      </div>
                      <span class="tp-section-tag">Carril centro</span>
                    </div>

                    <div class="tp-service-item" style="margin-bottom: 12px;">
                      <span data-operational-step3-tail>Esperando aprobacion del Paso 2</span>
                      <strong>Salida del cierre documental</strong>
                        <p data-operational-step3-note>Si Paso 2 ya fue aprobado, aqui ya no se valida derechos: aqui se documenta el cierre que destraba a la par Pago a gestor y Cobro a cliente.</p>
                    </div>

                    <div class="tp-service-stack">
                      <div class="tp-service-item">
                        <span>Persistencia del tramo</span>
                        <strong>Registro de evidencias finales</strong>
                        <p data-step3-evidence-note><?= esc($step3EvidenceNote) ?></p>
                      </div>
                      <div class="tp-service-item">
                        <span>Lo que habilita</span>
                        <strong>Paso 4 y 5 · Frentes financieros</strong>
                        <p>Las dos filas financieras posteriores necesitan leer estas evidencias como cierre valido de la fase operativa.</p>
                      </div>
                    </div>

                    <div class="tp-approval-panel<?= $step3GateReady ? ' is-ready' : ' is-pending' ?>" style="margin-top: 12px;" data-step3-gate-panel>
                      <h5 class="tp-approval-title" data-step3-gate-title><?= esc($step3GateReady ? 'Pago a gestor y Cobro a cliente ya pueden abrirse' : 'Las filas financieras siguen bloqueadas') ?></h5>
                      <p class="tp-approval-copy" data-step3-gate-copy>
                        <?= esc($step3GateReady
                          ? 'Las dos evidencias finales ya estan presentes. Este cierre documental ya habilita Pago a gestor y Cobro a cliente como frentes independientes.'
                          : 'Mientras falte una de las dos evidencias finales, ni Pago a gestor ni Cobro a cliente deberian abrirse como siguientes acciones validas.') ?>
                      </p>
                      <div data-step3-gate-actions>
                        <?php if ($step3GateReady): ?>
                          <div style="margin-top: 12px;">
                            <span class="tp-inline-note">Las dos filas financieras ya pueden leerse en esta misma pantalla, aunque cada una siga perteneciendo a roles distintos.</span>
                          </div>
                        <?php else: ?>
                          <ul class="tp-approval-missing">
                            <?php foreach ($step3GateMissing as $missingField): ?>
                              <li><?= esc($missingField) ?></li>
                            <?php endforeach; ?>
                          </ul>
                        <?php endif; ?>
                      </div>
                    </div>
                  </section>
                </div>

                <div class="tp-step-col">
                  <section class="tp-section-card" data-operational-anchor="3">
                    <span class="tp-side-kicker">Bitacora compartida</span>
                    <h3 class="tp-side-title">Comentarios generales del expediente</h3>
                    <p class="tp-side-copy">El paso 3 comparte la misma bitacora general del expediente. Se replica como lectura para no sacar al usuario de la secuencia por filas.</p>
                    <div class="tp-step-right-mirror">
                      <strong><?= count($prototypeEvidenceItems) ?></strong> comentario(s) visibles en la bitacora general.
                    </div>
                    <div class="tp-notes tp-notes-scroll" style="margin-top: 12px;">
                      <?php if (!empty($prototypeEvidenceItems)): ?>
                        <?php foreach ($prototypeEvidenceItems as $noteItem): ?>
                          <div class="tp-note-item tone-info">
                            <span class="tp-note-meta"><?= esc((string) ($noteItem['createdAtLabel'] ?? 'Sin fecha') . ' · ' . (string) ($noteItem['author'] ?? 'Sistema')) ?></span>
                            <span class="tp-note-body"><?= esc((string) ($noteItem['comment'] ?? '')) ?></span>
                          </div>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <div class="tp-notes-empty">Todavia no hay comentarios operativos guardados en este expediente.</div>
                      <?php endif; ?>
                    </div>
                  </section>
                </div>
              </div>
            </section>

            <div class="tp-phase-divider">Fase operativa a fase financiera</div>

            <section class="tp-step-row<?= $activeStep === 4 ? ' is-active' : '' ?>" data-step-row="4" data-operational-step4-inline<?= ($step3GateReady || $activeStep >= 4) ? '' : ' hidden' ?>>
              <div class="tp-step-row-header">
                <div>
                  <span class="tp-sequence-kicker">Paso 4</span>
                  <h3 class="tp-section-title">Pago a gestor</h3>
                  <p class="tp-step-row-copy">La primera fila financiera queda inmediatamente debajo del cierre documental para que el handoff no dependa de otra pantalla.</p>
                </div>
                <span class="tp-step-row-tag">Financiero</span>
              </div>

              <div class="tp-step-row-grid">
                <div class="tp-step-col">
                  <section class="tp-section-card" data-operational-anchor="4">
                    <div class="tp-approval-panel is-info" style="margin-bottom: 14px;" data-operational-step4-handoff-panel>
                      <h5 class="tp-approval-title" data-operational-step4-title>Fase financiera esperando handoff operativo</h5>
                      <p class="tp-approval-copy" data-operational-step4-copy>Pago a gestor debe leer el cierre de la fase base antes de sentirse como frente habilitado. La disponibilidad real sigue dependiendo del cierre documental y permisos.</p>
                      <div style="margin-top: 10px;">
                        <span class="tp-inline-note" data-operational-step4-note>Sin marca local de aprobacion de Paso 2 en esta sesion.</span>
                      </div>
                    </div>

                    <div class="tp-step4-kpi-grid">
                      <div class="tp-step4-kpi">
                        <span>Deposito a gestor</span>
                        <strong><?= esc($formatMoney($step4VisualData['fields']['deposito_gestor'] ?? 0)) ?></strong>
                      </div>
                      <div class="tp-step4-kpi">
                        <span>Saldo pendiente</span>
                        <strong><?= esc($formatMoney($step4VisualData['fields']['col_a_favor'] ?? 0)) ?></strong>
                      </div>
                      <div class="tp-step4-kpi">
                        <span>Pago total</span>
                        <strong><?= esc($formatMoney($step4VisualData['fields']['gestor_total_pago'] ?? 0)) ?></strong>
                      </div>
                      <div class="tp-step4-kpi">
                        <span>Estatus</span>
                        <strong><?= esc($step4VisualData['pago_gestor_status_label'] ?? 'Sin definir') ?></strong>
                      </div>
                    </div>

                    <div class="tp-form-live-box<?= $prototypeStep4CanEdit ? '' : ' is-disabled' ?>" data-step4-live-form>
                      <div class="tp-form-live-head">
                        <h5>Captura financiera del pago a gestor</h5>
                        <p>Ajusta costos por tramite, confirma deposito y deja listo el cierre financiero con datos reales del expediente.</p>
                      </div>
                      <input id="tp_step4_costo_tramite" type="hidden" value="<?= esc((string) ($prototypeStep4Form['values']['costo_tramite'] ?? ''), 'attr') ?>" data-step4-input="costo_tramite">
                      <input id="tp_step4_col_a_favor" type="hidden" value="<?= esc((string) ($prototypeStep4Form['values']['col_a_favor'] ?? ''), 'attr') ?>" data-step4-input="col_a_favor">
                      <input id="tp_step4_gestor_total_pago" type="hidden" value="<?= esc((string) ($prototypeStep4Form['values']['gestor_total_pago'] ?? ''), 'attr') ?>" data-step4-input="gestor_total_pago">
                      <div class="tp-field-grid">
                        <div class="tp-field"><label>Gestor</label><input value="<?= esc($step4VisualData['gestor_name'] ?? 'Sin asignar', 'attr') ?>" disabled></div>
                        <div class="tp-field"><label for="tp_step4_num_factura_gestor">Numero de factura</label><input id="tp_step4_num_factura_gestor" type="text" value="<?= esc((string) ($prototypeStep4Form['values']['num_factura_gestor'] ?? ''), 'attr') ?>" data-step4-input="num_factura_gestor"<?= $prototypeStep4CanEdit ? '' : ' disabled' ?>></div>
                        <div class="tp-field"><label for="tp_step4_deposito_gestor">Deposito a gestor</label><input id="tp_step4_deposito_gestor" type="number" step="0.01" value="<?= esc((string) ($prototypeStep4Form['values']['deposito_gestor'] ?? ''), 'attr') ?>" data-step4-input="deposito_gestor"<?= $prototypeStep4CanEdit ? '' : ' disabled' ?>></div>
                      </div>

                      <div class="tp-step4-saldo-info is-even" data-step4-saldo-info>Sin saldo pendiente</div>

                      <div class="tp-step4-finance-stack">
                        <section class="tp-step4-money-panel">
                          <div class="tp-step4-money-head">
                            <div>
                              <h6>Costos por tramite</h6>
                              <p>Ajusta cada servicio y guarda por fila. El total alimenta automaticamente el pago al gestor.</p>
                            </div>
                            <span class="tp-step4-save-status" data-step4-cost-status>Guardado</span>
                          </div>
                          <div class="tp-step4-cost-list" data-step4-cost-list>
                            <span class="tp-inline-note">Cargando costos del expediente...</span>
                          </div>
                          <div class="tp-step4-cost-total">
                            <span>Total de costos</span>
                            <strong data-step4-cost-total>$0.00</strong>
                          </div>
                        </section>

                        <section class="tp-step4-money-panel">
                          <div class="tp-step4-money-head">
                            <div>
                              <h6>Gastos y pago total</h6>
                              <p>El pago total se recalcula en vivo con honorarios, gratificacion y paqueteria.</p>
                            </div>
                          </div>
                          <div class="tp-field-grid">
                            <div class="tp-field"><label for="tp_step4_impuesto_gestoria">Honorarios de gestoria</label><input id="tp_step4_impuesto_gestoria" type="number" step="0.01" value="<?= esc((string) ($prototypeStep4Form['values']['impuesto_gestoria'] ?? ''), 'attr') ?>" data-step4-input="impuesto_gestoria"<?= $prototypeStep4CanEdit ? '' : ' disabled' ?>></div>
                            <div class="tp-field"><label for="tp_step4_gestoria_comision">Gratificacion</label><input id="tp_step4_gestoria_comision" type="number" step="0.01" value="<?= esc((string) ($prototypeStep4Form['values']['gestoria_comision'] ?? ''), 'attr') ?>" data-step4-input="gestoria_comision"<?= $prototypeStep4CanEdit ? '' : ' disabled' ?>></div>
                            <div class="tp-field"><label for="tp_step4_costo_paqueteria">Costo paqueteria</label><input id="tp_step4_costo_paqueteria" type="number" step="0.01" value="<?= esc((string) ($prototypeStep4Form['values']['costo_paqueteria'] ?? ''), 'attr') ?>" data-step4-input="costo_paqueteria"<?= $prototypeStep4CanEdit ? '' : ' disabled' ?>></div>
                            <div class="tp-field">
                              <label>Pago total</label>
                              <div class="tp-step4-money-display" data-step4-total-text>$0.00</div>
                              <small class="tp-step4-breakdown" data-step4-total-breakdown></small>
                            </div>
                          </div>
                        </section>
                      </div>

                      <div class="tp-field-grid">
                        <div class="tp-field"><label for="tp_step4_pago_gestor_st_id">Estatus del pago</label><select id="tp_step4_pago_gestor_st_id" data-step4-input="pago_gestor_st_id"<?= $prototypeStep4CanEdit ? '' : ' disabled' ?>><?php foreach (($prototypeStep4Form['options']['pagoGestorStatus'] ?? []) as $optionValue => $optionLabel): ?><option value="<?= esc((string) $optionValue, 'attr') ?>"<?= (string) $optionValue === (string) ($prototypeStep4Form['values']['pago_gestor_st_id'] ?? '') ? ' selected' : '' ?>><?= esc((string) $optionLabel) ?></option><?php endforeach; ?></select></div>
                        <div class="tp-field"><label for="tp_step4_status_doctos_gestor">Estatus de documentos</label><select id="tp_step4_status_doctos_gestor" data-step4-input="status_doctos_gestor"<?= $prototypeStep4CanEdit ? '' : ' disabled' ?>><?php foreach (($prototypeStep4Form['options']['statusDoctosGestor'] ?? []) as $optionValue => $optionLabel): ?><option value="<?= esc((string) $optionValue, 'attr') ?>"<?= (string) $optionValue === (string) ($prototypeStep4Form['values']['status_doctos_gestor'] ?? '') ? ' selected' : '' ?>><?= esc((string) $optionLabel) ?></option><?php endforeach; ?></select></div>
                        <div class="tp-field"><label for="tp_step4_reembolso_status_id">Estatus del reembolso</label><select id="tp_step4_reembolso_status_id" data-step4-input="reembolso_status_id"<?= $prototypeStep4CanEdit ? '' : ' disabled' ?>><?php foreach (($prototypeStep4Form['options']['reembolsoStatus'] ?? []) as $optionValue => $optionLabel): ?><option value="<?= esc((string) $optionValue, 'attr') ?>"<?= (string) $optionValue === (string) ($prototypeStep4Form['values']['reembolso_status_id'] ?? '') ? ' selected' : '' ?>><?= esc((string) $optionLabel) ?></option><?php endforeach; ?></select></div>
                      </div>
                      <?php if ($prototypeStep4CanEdit): ?>
                        <div class="tp-btn-row" style="margin-top: 12px;">
                          <button type="button" class="tp-btn primary" data-step4-save>Guardar Pago a gestor</button>
                        </div>
                      <?php else: ?>
                        <div style="margin-top: 12px;">
                          <span class="tp-inline-note"><?= esc($prototypeStep4BlockedReason !== '' ? $prototypeStep4BlockedReason : 'Pago a gestor no esta editable para este tramite y este perfil.') ?></span>
                        </div>
                      <?php endif; ?>
                      <div class="tp-form-feedback" data-step4-feedback hidden></div>
                    </div>
                  </section>
                </div>

                <div class="tp-step-col">
                  <section class="tp-section-card" data-operational-anchor="4" data-step4-doc-panel>
                    <div class="tp-section-header">
                      <div>
                        <h3 class="tp-section-title">Documentos de pago a gestor</h3>
                        <p class="tp-section-copy">Factura del gestor y comprobante de pago viven en el mismo carril documental del paso 4.</p>
                      </div>
                      <span class="tp-section-tag">Carril centro</span>
                    </div>

                    <div class="tp-step4-status-row" style="margin-top: 12px; margin-bottom: 12px;">
                      <span class="tp-step4-status-chip<?= count($step4VisualData['payment_docs'] ?? []) >= 2 ? ' is-success' : '' ?>" data-step4-doc-count><?= count($step4VisualData['payment_docs'] ?? []) ?>/2</span>
                      <span class="tp-step4-status-chip<?= $prototypeStep4CanUploadDocs ? ' is-success' : '' ?>"><?= esc($prototypeStep4CanUploadDocs ? 'Editable' : 'Solo lectura') ?></span>
                      <span class="tp-step4-status-chip"><?= esc($step4VisualData['status_doctos_gestor_label'] ?? 'En Proceso') ?></span>
                      <span class="tp-step4-status-chip"><?= esc($step4VisualData['reembolso_status_label'] ?? 'Pendiente') ?></span>
                    </div>

                    <div class="tp-field-grid" style="margin-top: 12px;">
                      <div class="tp-field">
                        <label for="tp_step4_comprobante_final">Tipo de documento de pago</label>
                        <select id="tp_step4_comprobante_final" data-step4-doc-type<?= $prototypeStep4CanUploadDocs ? '' : ' disabled' ?>>
                          <?php foreach (($prototypeStep4Form['options']['comprobanteFinal'] ?? []) as $optionValue => $optionLabel): ?>
                            <option value="<?= esc((string) $optionValue, 'attr') ?>"><?= esc((string) $optionLabel) ?></option>
                          <?php endforeach; ?>
                        </select>
                      </div>
                    </div>

                    <div class="tp-upload-panel" style="margin-top: 12px;">
                      <div class="tp-upload-head">
                        <strong>Carga documental del pago</strong>
                        <p>Se esperan dos soportes: factura del gestor y comprobante de pago.</p>
                      </div>
                      <div class="tp-dropzone-box<?= $prototypeStep4CanUploadDocs ? ' is-actionable' : ' is-disabled' ?>" data-step4-doc-dropzone>
                        <input id="tp_step4_doc_file" class="tp-dropzone-input" type="file" data-step4-doc-file<?= $prototypeStep4CanUploadDocs ? '' : ' disabled' ?>>
                        <span class="tp-dropzone-kicker">Pago a gestor</span>
                        <strong class="tp-dropzone-title">Arrastra aqui factura o comprobante de pago, o haz clic para seleccionarlo</strong>
                        <span class="tp-dropzone-copy">El tipo de documento se toma del selector superior y viaja al endpoint real de Pago a Gestor.</span>
                        <span class="tp-dropzone-meta" data-step4-doc-selected>Sin archivo seleccionado.</span>
                      </div>
                    </div>
                    <?php if ($prototypeStep4CanUploadDocs): ?>
                      <div class="tp-btn-row" style="margin-top: 12px;">
                        <button type="button" class="tp-btn primary" data-step4-doc-upload>Subir documento</button>
                      </div>
                    <?php elseif ($prototypeStep4UploadBlockedReason !== ''): ?>
                      <div style="margin-top: 12px;">
                        <span class="tp-inline-note"><?= esc($prototypeStep4UploadBlockedReason) ?></span>
                      </div>
                    <?php endif; ?>
                    <div class="tp-form-feedback" data-step4-doc-feedback hidden></div>
                    <div class="tp-gallery" style="margin-top: 12px;">
                      <strong>Archivos vigentes del pago</strong>
                      <div class="tp-gallery-list" data-step4-doc-gallery style="margin-top: 0;">
                        <?php if (!empty($prototypeStep4Form['docs']) && is_array($prototypeStep4Form['docs'])): ?>
                          <?php foreach ($prototypeStep4Form['docs'] as $doc): ?>
                            <?php
                              $step4DocFile = (string) ($doc['file'] ?? '');
                              $step4DocType = (string) ($doc['comprobante_final'] ?? '');
                              $step4DocBaseUrl = rtrim((string) ($prototypeStep4Form['fileBaseUrl'] ?? ''), '/');
                              $step4DocUrl = $step4DocBaseUrl !== '' ? $step4DocBaseUrl . '/' . rawurlencode($step4DocFile) : '#';
                              $step4DocLabel = (string) (($prototypeStep4Form['options']['comprobanteFinal'][$step4DocType] ?? '') ?: ($step4DocType !== '' ? $step4DocType : 'Documento de pago'));
                              $step4IsImagePreview = (bool) preg_match('/\.(png|jpe?g|gif|webp|bmp|svg)$/i', $step4DocFile);
                            ?>
                            <div class="tp-gallery-item">
                              <?php if ($step4IsImagePreview && $step4DocUrl !== '#'): ?>
                                <button
                                  type="button"
                                  class="tp-gallery-preview-trigger"
                                  data-doc-preview-url="<?= esc($step4DocUrl, 'attr') ?>"
                                  data-doc-preview-name="<?= esc($step4DocFile, 'attr') ?>"
                                  data-doc-preview-meta="<?= esc($step4DocLabel, 'attr') ?>">
                                  <img class="tp-gallery-preview-image" src="<?= esc($step4DocUrl, 'attr') ?>" alt="<?= esc($step4DocFile, 'attr') ?>" loading="lazy">
                                </button>
                              <?php endif; ?>
                              <div class="tp-gallery-item-head">
                                <div>
                                  <a class="tp-gallery-item-link" href="<?= esc($step4DocUrl, 'attr') ?>" target="_blank" rel="noreferrer" title="<?= esc($step4DocFile, 'attr') ?>"><?= esc($step4DocFile) ?></a>
                                  <span class="tp-gallery-item-meta"><?= esc($step4DocLabel) ?></span>
                                </div>
                                <div class="tp-gallery-item-actions">
                                  <?php if ($step4IsImagePreview && $step4DocUrl !== '#'): ?>
                                    <button
                                      type="button"
                                      class="tp-btn ghost small"
                                      title="Vista previa de <?= esc($step4DocFile, 'attr') ?>"
                                      data-doc-preview-url="<?= esc($step4DocUrl, 'attr') ?>"
                                      data-doc-preview-name="<?= esc($step4DocFile, 'attr') ?>"
                                      data-doc-preview-meta="<?= esc($step4DocLabel, 'attr') ?>">Ver imagen</button>
                                  <?php endif; ?>
                                  <?php if ($prototypeStep4CanDeleteDocs): ?>
                                    <button type="button" class="tp-btn secondary small" data-step4-doc-delete-btn="<?= esc($step4DocFile, 'attr') ?>">Eliminar</button>
                                  <?php endif; ?>
                                </div>
                              </div>
                            </div>
                          <?php endforeach; ?>
                        <?php else: ?>
                          <div class="tp-gallery-item">Sin documentos de pago a gestor registrados</div>
                        <?php endif; ?>
                      </div>
                    </div>
                  </section>
                </div>

                <div class="tp-step-col">
                  <?php if ($prototypeStep4NotesCanView): ?>
                    <section class="tp-section-card" data-operational-anchor="4" data-step-row-notes="4">
                      <span class="tp-side-kicker">Seguimiento interno · Paso 4</span>
                      <h3 class="tp-side-title">Notas de Pago a gestor</h3>

                      <?php if ($prototypeStep4NotesCanAdd): ?>
                        <div class="tp-note-compose" data-step4-note-form>
                          <textarea placeholder="Escribe aqui una nota interna de seguimiento para Pago a gestor" data-step4-note-input></textarea>
                          <div class="tp-btn-row">
                            <button type="button" class="tp-btn primary" data-step4-note-save>Guardar nota interna</button>
                          </div>
                        </div>
                      <?php elseif ($prototypeStep4NotesBlockedReason !== ''): ?>
                        <div style="margin-bottom: 14px;">
                          <span class="tp-inline-note"><?= esc($prototypeStep4NotesBlockedReason) ?></span>
                        </div>
                      <?php endif; ?>

                      <div class="tp-form-feedback" data-step4-note-feedback hidden></div>

                      <div class="tp-notes tp-notes-scroll" data-step4-note-list<?= empty($prototypeStep4NotesItems) ? ' hidden' : '' ?>>
                        <?php foreach ($prototypeStep4NotesItems as $noteItem): ?>
                          <div class="tp-note-item tone-info">
                            <span class="tp-note-meta"><?= esc((string) ($noteItem['createdAtLabel'] ?? 'Sin fecha') . ' · ' . (string) ($noteItem['author'] ?? 'Sistema')) ?></span>
                            <span class="tp-note-body"><?= esc((string) ($noteItem['comment'] ?? '')) ?></span>
                          </div>
                        <?php endforeach; ?>
                      </div>
                      <div class="tp-notes-empty" data-step4-note-empty<?= empty($prototypeStep4NotesItems) ? '' : ' hidden' ?>>Todavia no hay notas internas de Pago a gestor registradas.</div>
                    </section>
                  <?php endif; ?>
                </div>
              </div>
            </section>

            <?php if ($step3GateReady || $activeStep >= 5): ?>
            <section class="tp-step-row<?= $activeStep === 5 ? ' is-active' : '' ?>" data-step-row="5">
              <div class="tp-step-row-header">
                <div>
                  <span class="tp-sequence-kicker">Paso 5</span>
                  <h3 class="tp-section-title">Cobro a cliente</h3>
                  <p class="tp-step-row-copy">La recuperacion hacia cliente mantiene su formulario, su uploader y sus notas sin mezclarse con Pago a gestor.</p>
                </div>
                <span class="tp-step-row-tag">Recuperacion</span>
              </div>

              <div class="tp-step-row-grid">
                <div class="tp-step-col">
                  <section class="tp-section-card" data-operational-anchor="5">
                    <div class="tp-step4-kpi-grid">
                      <div class="tp-step4-kpi">
                        <span>Estatus de cobro</span>
                        <strong><?= esc($step4VisualData['cobro_status_label'] ?? 'Sin definir') ?></strong>
                      </div>
                      <div class="tp-step4-kpi">
                        <span>Estatus de reembolso</span>
                        <strong><?= esc($step4VisualData['reembolso_status_label'] ?? 'Sin definir') ?></strong>
                      </div>
                      <div class="tp-step4-kpi">
                        <span>Soportes esperados</span>
                        <strong><?= esc($stepDocCounts[5]) ?></strong>
                      </div>
                      <div class="tp-step4-kpi">
                        <span>Notas internas</span>
                        <strong><?= count($prototypeStep5NotesItems) ?></strong>
                      </div>
                    </div>

                    <div class="tp-form-live-box<?= $prototypeStep5CanEdit ? '' : ' is-disabled' ?>" data-step5-live-form>
                      <div class="tp-form-live-head">
                        <h5>Captura financiera de Cobro a cliente</h5>
                        <p>Este bloque usa el guardado real de la recuperacion hacia cliente y mantiene separada esta fase de la salida financiera del gestor.</p>
                      </div>
                      <div class="tp-field-grid">
                        <div class="tp-field"><label for="tp_step5_id_give_cliente">ID que da el cliente</label><input id="tp_step5_id_give_cliente" data-step5-input="id_give_cliente" value="<?= esc((string) ($prototypeStep5Form['values']['id_give_cliente'] ?? ''), 'attr') ?>"<?= $prototypeStep5CanEdit ? '' : ' disabled' ?>></div>
                        <div class="tp-field"><label for="tp_step5_cobro_status_id">Estatus del cobro</label><select id="tp_step5_cobro_status_id" data-step5-input="cobro_status_id"<?= $prototypeStep5CanEdit ? '' : ' disabled' ?>><?php foreach (($prototypeStep5Form['options']['cobroStatus'] ?? []) as $optionValue => $optionLabel): ?><option value="<?= esc((string) $optionValue, 'attr') ?>"<?= (string) $optionValue === (string) ($prototypeStep5Form['values']['cobro_status_id'] ?? '') ? ' selected' : '' ?>><?= esc((string) $optionLabel) ?></option><?php endforeach; ?></select></div>
                        <div class="tp-field"><label for="tp_step5_numero_factura">Numero de factura</label><input id="tp_step5_numero_factura" data-step5-input="numero_factura" value="<?= esc((string) ($prototypeStep5Form['values']['numero_factura'] ?? ''), 'attr') ?>"<?= $prototypeStep5CanEdit ? '' : ' disabled' ?>></div>
                        <div class="tp-field"><label for="tp_step5_numero_refactura">Numero de refactura</label><input id="tp_step5_numero_refactura" data-step5-input="numero_refactura" value="<?= esc((string) ($prototypeStep5Form['values']['numero_refactura'] ?? ''), 'attr') ?>"<?= $prototypeStep5CanEdit ? '' : ' disabled' ?>></div>
                      </div>
                      <div class="tp-field" style="margin-top: 12px;">
                        <label for="tp_step5_evidencia_cobro_txt">Evidencia de cobro</label>
                        <textarea id="tp_step5_evidencia_cobro_txt" data-step5-input="evidencia_cobro_txt"<?= $prototypeStep5CanEdit ? '' : ' disabled' ?>><?= esc((string) ($prototypeStep5Form['values']['evidencia_cobro_txt'] ?? '')) ?></textarea>
                      </div>
                      <div class="tp-field-grid" style="margin-top: 12px;">
                        <div class="tp-field"><label for="tp_step5_costo_gestoria">Sumatoria de derechos</label><input id="tp_step5_costo_gestoria" data-step5-input="costo_gestoria" value="<?= esc((string) ($prototypeStep5Form['values']['costo_gestoria'] ?? '0.00'), 'attr') ?>" disabled></div>
                        <div class="tp-field"><label for="tp_step5_costo_pago_cliente">Honorarios del tramite</label><input id="tp_step5_costo_pago_cliente" type="number" step="0.01" data-step5-input="costo_pago_cliente" value="<?= esc((string) ($prototypeStep5Form['values']['costo_pago_cliente'] ?? '0'), 'attr') ?>"<?= $prototypeStep5CanEdit ? '' : ' disabled' ?>></div>
                        <div class="tp-field"><label for="tp_step5_comision_derechos">Comision de derechos</label><input id="tp_step5_comision_derechos" type="number" step="0.01" data-step5-input="comision_derechos" value="<?= esc((string) ($prototypeStep5Form['values']['comision_derechos'] ?? '0'), 'attr') ?>"<?= $prototypeStep5CanEdit ? '' : ' disabled' ?>></div>
                        <div class="tp-field"><label for="tp_step5_iva">IVA</label><input id="tp_step5_iva" type="number" step="0.01" data-step5-input="iva" value="<?= esc((string) ($prototypeStep5Form['values']['iva'] ?? '0.00'), 'attr') ?>"<?= $prototypeStep5CanEdit ? '' : ' disabled' ?>></div>
                        <div class="tp-field"><label for="tp_step5_costo_total">Costo total</label><input id="tp_step5_costo_total" data-step5-input="costo_total" value="<?= esc((string) ($prototypeStep5Form['values']['costo_total'] ?? '0.00'), 'attr') ?>" disabled></div>
                      </div>
                      <div class="tp-step4-status-row" style="margin-top: 12px;">
                        <span class="tp-step4-status-chip">Cliente: <?= esc($step4VisualData['cliente_name'] ?? 'Sin cliente') ?></span>
                        <span class="tp-step4-status-chip">Contrato: <?= esc($step4VisualData['contrato'] ?? '--') ?></span>
                        <span class="tp-step4-status-chip">Folio: <?= esc($step4VisualData['folio'] ?? '--') ?></span>
                      </div>
                      <?php if ($prototypeStep5CanEdit): ?>
                        <div class="tp-btn-row" style="margin-top: 12px;">
                          <button type="button" class="tp-btn primary" data-step5-save>Guardar Cobro a cliente</button>
                        </div>
                      <?php else: ?>
                        <div style="margin-top: 12px;">
                          <span class="tp-inline-note"><?= esc($prototypeStep5BlockedReason !== '' ? $prototypeStep5BlockedReason : 'Cobro a cliente no esta editable para este tramite y este perfil.') ?></span>
                        </div>
                      <?php endif; ?>
                      <div class="tp-form-feedback" data-step5-feedback hidden></div>
                    </div>
                  </section>
                </div>

                <div class="tp-step-col">
                  <section class="tp-section-card" data-operational-anchor="5" data-step5-doc-panel>
                    <div class="tp-section-header">
                      <div>
                        <h3 class="tp-section-title">Evidencias de cobro</h3>
                        <p class="tp-section-copy">El carril central mantiene los soportes reales del cobro y su clasificacion documental.</p>
                      </div>
                      <span class="tp-section-tag">Carril centro</span>
                    </div>

                    <div class="tp-step4-status-row" style="margin-top: 12px; margin-bottom: 12px;">
                      <span class="tp-step4-status-chip" data-step5-doc-count><?= count($prototypeStep5Form['docs'] ?? []) ?> soporte(s)</span>
                      <span class="tp-step4-status-chip<?= $prototypeStep5CanUploadDocs ? ' is-success' : '' ?>"><?= esc($prototypeStep5CanUploadDocs ? 'Editable' : 'Solo lectura') ?></span>
                      <span class="tp-step4-status-chip"><?= esc($stepNextActions[5]) ?></span>
                    </div>
                    <div class="tp-field-grid" style="margin-top: 12px;">
                      <div class="tp-field">
                        <label for="tp_step5_cobro_correcto">Tipo de soporte</label>
                        <select id="tp_step5_cobro_correcto" data-step5-doc-type<?= $prototypeStep5CanUploadDocs ? '' : ' disabled' ?>>
                          <?php foreach (($prototypeStep5Form['options']['cobroCorrecto'] ?? []) as $optionValue => $optionLabel): ?>
                            <option value="<?= esc((string) $optionValue, 'attr') ?>"><?= esc((string) $optionLabel) ?></option>
                          <?php endforeach; ?>
                        </select>
                      </div>
                    </div>
                    <div class="tp-upload-panel" style="margin-top: 12px;">
                      <div class="tp-upload-head">
                        <strong>Carga documental del cobro</strong>
                        <p>Clasifica cada evidencia como cobro parcial, completo u otro soporte del cierre.</p>
                      </div>
                      <div class="tp-dropzone-box<?= $prototypeStep5CanUploadDocs ? ' is-actionable' : ' is-disabled' ?>" data-step5-doc-dropzone>
                        <input id="tp_step5_doc_file" class="tp-dropzone-input" type="file" data-step5-doc-file<?= $prototypeStep5CanUploadDocs ? '' : ' disabled' ?>>
                        <span class="tp-dropzone-kicker">Cobro a cliente</span>
                        <strong class="tp-dropzone-title">Arrastra aqui la evidencia o haz clic para seleccionarla</strong>
                        <span class="tp-dropzone-copy">El tipo de soporte se toma del selector superior y viaja al endpoint real de Cobro a cliente.</span>
                        <span class="tp-dropzone-meta" data-step5-doc-selected>Sin archivo seleccionado.</span>
                      </div>
                    </div>
                    <?php if ($prototypeStep5CanUploadDocs): ?>
                      <div class="tp-btn-row" style="margin-top: 12px;">
                        <button type="button" class="tp-btn primary" data-step5-doc-upload>Subir evidencia</button>
                      </div>
                    <?php elseif ($prototypeStep5UploadBlockedReason !== ''): ?>
                      <div style="margin-top: 12px;">
                        <span class="tp-inline-note"><?= esc($prototypeStep5UploadBlockedReason) ?></span>
                      </div>
                    <?php endif; ?>
                    <div class="tp-form-feedback" data-step5-doc-feedback hidden></div>
                    <div class="tp-gallery" style="margin-top: 12px;">
                      <strong>Archivos vigentes del cobro</strong>
                      <div class="tp-gallery-list" data-step5-doc-gallery style="margin-top: 0;">
                        <?php if (!empty($prototypeStep5Form['docs']) && is_array($prototypeStep5Form['docs'])): ?>
                          <?php foreach ($prototypeStep5Form['docs'] as $doc): ?>
                            <?php
                              $step5DocFile = (string) ($doc['file'] ?? '');
                              $step5DocType = (string) ($doc['cobro_correcto'] ?? 'otro');
                              $step5DocBaseUrl = rtrim((string) ($prototypeStep5Form['fileBaseUrl'] ?? ''), '/');
                              $step5DocUrl = $step5DocBaseUrl !== '' ? $step5DocBaseUrl . '/' . rawurlencode($step5DocFile) : '#';
                              $step5DocLabel = (string) (($prototypeStep5Form['options']['cobroCorrecto'][$step5DocType] ?? '') ?: ($step5DocType !== '' ? $step5DocType : 'Soporte de cobro'));
                              $step5IsImagePreview = (bool) preg_match('/\.(png|jpe?g|gif|webp|bmp|svg)$/i', $step5DocFile);
                            ?>
                            <div class="tp-gallery-item">
                              <?php if ($step5IsImagePreview && $step5DocUrl !== '#'): ?>
                                <button
                                  type="button"
                                  class="tp-gallery-preview-trigger"
                                  data-doc-preview-url="<?= esc($step5DocUrl, 'attr') ?>"
                                  data-doc-preview-name="<?= esc($step5DocFile, 'attr') ?>"
                                  data-doc-preview-meta="<?= esc($step5DocLabel, 'attr') ?>">
                                  <img class="tp-gallery-preview-image" src="<?= esc($step5DocUrl, 'attr') ?>" alt="<?= esc($step5DocFile, 'attr') ?>" loading="lazy">
                                </button>
                              <?php endif; ?>
                              <div class="tp-gallery-item-head">
                                <div>
                                  <a class="tp-gallery-item-link" href="<?= esc($step5DocUrl, 'attr') ?>" target="_blank" rel="noreferrer" title="<?= esc($step5DocFile, 'attr') ?>"><?= esc($step5DocFile) ?></a>
                                  <span class="tp-gallery-item-meta"><?= esc($step5DocLabel) ?></span>
                                </div>
                                <div class="tp-gallery-item-actions">
                                  <?php if ($step5IsImagePreview && $step5DocUrl !== '#'): ?>
                                    <button
                                      type="button"
                                      class="tp-btn ghost small"
                                      title="Vista previa de <?= esc($step5DocFile, 'attr') ?>"
                                      data-doc-preview-url="<?= esc($step5DocUrl, 'attr') ?>"
                                      data-doc-preview-name="<?= esc($step5DocFile, 'attr') ?>"
                                      data-doc-preview-meta="<?= esc($step5DocLabel, 'attr') ?>">Ver imagen</button>
                                  <?php endif; ?>
                                  <?php if ($prototypeStep5CanDeleteDocs): ?>
                                    <button type="button" class="tp-btn secondary small" data-step5-doc-delete="<?= esc($step5DocFile, 'attr') ?>">Eliminar</button>
                                  <?php endif; ?>
                                </div>
                              </div>
                            </div>
                          <?php endforeach; ?>
                        <?php else: ?>
                          <div class="tp-gallery-item">Sin evidencias de cobro registradas</div>
                        <?php endif; ?>
                      </div>
                    </div>
                    <?php if (!$prototypeStep5CanDeleteDocs && $prototypeStep5DeleteBlockedReason !== ''): ?>
                      <div style="margin-top: 10px;">
                        <span class="tp-inline-note"><?= esc($prototypeStep5DeleteBlockedReason) ?></span>
                      </div>
                    <?php endif; ?>
                  </section>
                </div>

                <div class="tp-step-col">
                  <?php if ($prototypeStep5NotesCanView): ?>
                    <section class="tp-section-card" data-operational-anchor="5" data-step-row-notes="5">
                      <span class="tp-side-kicker">Seguimiento interno · Paso 5</span>
                      <h3 class="tp-side-title">Notas de Cobro a cliente</h3>

                      <?php if ($prototypeStep5NotesCanAdd): ?>
                        <div class="tp-note-compose" data-step5-note-form>
                          <textarea placeholder="Escribe aqui una nota interna de seguimiento para Cobro a cliente" data-step5-note-input></textarea>
                          <div class="tp-btn-row">
                            <button type="button" class="tp-btn primary" data-step5-note-save>Guardar nota interna</button>
                          </div>
                        </div>
                      <?php elseif ($prototypeStep5NotesBlockedReason !== ''): ?>
                        <div style="margin-bottom: 14px;">
                          <span class="tp-inline-note"><?= esc($prototypeStep5NotesBlockedReason) ?></span>
                        </div>
                      <?php endif; ?>

                      <div class="tp-form-feedback" data-step5-note-feedback hidden></div>

                      <div class="tp-notes tp-notes-scroll" data-step5-note-list<?= empty($prototypeStep5NotesItems) ? ' hidden' : '' ?>>
                        <?php foreach ($prototypeStep5NotesItems as $noteItem): ?>
                          <div class="tp-note-item tone-info">
                            <span class="tp-note-meta"><?= esc((string) ($noteItem['createdAtLabel'] ?? 'Sin fecha') . ' · ' . (string) ($noteItem['author'] ?? 'Sistema')) ?></span>
                            <span class="tp-note-body"><?= esc((string) ($noteItem['comment'] ?? '')) ?></span>
                          </div>
                        <?php endforeach; ?>
                      </div>
                      <div class="tp-notes-empty" data-step5-note-empty<?= empty($prototypeStep5NotesItems) ? '' : ' hidden' ?>>Todavia no hay notas internas de Cobro a cliente registradas.</div>
                    </section>
                  <?php endif; ?>
                </div>
              </div>
            </section>
            <?php else: ?>
            <section class="tp-step-row" data-step-row="5">
              <div class="tp-step-row-header">
                <div>
                  <span class="tp-sequence-kicker">Paso 5</span>
                  <h3 class="tp-section-title">Cobro a cliente</h3>
                  <p class="tp-step-row-copy">Esta fase comparte el mismo gate documental que Pago a gestor. Hasta completar Evidencias finales, ninguna de las dos filas financieras debe abrirse.</p>
                </div>
                <span class="tp-step-row-tag">Siguiente fase</span>
              </div>

              <div class="tp-step-row-grid">
                <div class="tp-step-col" style="grid-column: 1 / -1;">
                  <section class="tp-section-card" data-operational-anchor="5">
                    <div class="tp-section-header">
                      <div>
                        <h3 class="tp-section-title">Cobro a cliente espera el mismo gate que Pago a gestor</h3>
                        <p class="tp-section-copy">Se deja visible como referencia de continuidad, pero sin abrir el formulario completo hasta que Evidencias finales complete el handoff financiero compartido.</p>
                      </div>
                      <span class="tp-section-tag">Bloqueado por secuencia</span>
                    </div>

                    <div class="tp-step4-status-row" style="margin-bottom: 12px;">
                      <span class="tp-step4-status-chip is-success">Paso 2 aprobado</span>
                      <span class="tp-step4-status-chip<?= $step3GateReady ? ' is-success' : '' ?>">Paso 3 · Evidencias finales</span>
                      <span class="tp-step4-status-chip">Paso 4 · Pago a gestor</span>
                      <span class="tp-step4-status-chip">Paso 5 · Cobro a cliente</span>
                    </div>

                    <div class="tp-mini-list">
                      <div class="tp-mini-item">
                        <strong>Siguiente lectura valida</strong>
                        <span>Completar evidencias finales para habilitar simultaneamente Pago a gestor y Cobro a cliente.</span>
                      </div>
                      <div class="tp-mini-item">
                        <strong>Cobro a cliente</strong>
                        <span>Se mostrara como fila completa al mismo tiempo que Pago a gestor cuando el cierre documental quede completo.</span>
                      </div>
                    </div>
                  </section>
                </div>
              </div>
            </section>
            <?php endif; ?>

            <div class="tp-footer">
              <p class="tp-footer-hint">Cada fila mantiene su propio frente principal, su soporte documental y su contexto lateral. El objetivo es dejar de depender de un rail global que mezcle pasos distintos.</p>
              <div class="tp-btn-row">
                <a href="<?= base_url('/deskapp/tramitesn/tramite') ?>" class="tp-btn secondary">Volver al listado</a>
                <a href="<?= esc($prototypeRealUpdateUrl, 'attr') ?>" class="tp-btn primary">Comparar con vista real</a>
              </div>
            </div>
          </div>
        <?php else: ?>
        <div class="tp-main-grid is-operational-base">
          <main class="tp-main-panel is-operational-base">
          <div class="tp-content-layout is-operational-base">
            <div class="tp-stack">
              <section class="tp-section-card" data-operational-anchor="1">
                <div class="tp-section-header">
                  <div>
                    <h3 class="tp-section-title"><?= esc($baseSectionDisplayTitle) ?></h3>
                    <p class="tp-section-copy"><?= esc($baseSectionDisplayCopy) ?></p>
                  </div>
                  <span class="tp-section-tag"><?= esc($baseSectionDisplayTag) ?></span>
                </div>

                <?php if ($isOperationalBasePhase): ?>
                  <div class="tp-step1-identity-grid">
                    <div>
                      <div class="tp-step1-linked">
                        <?php if (!$prototypeStep1CanEdit && $prototypeStep1BlockedReason !== ''): ?>
                          <div style="margin-bottom: 10px;">
                            <span class="tp-inline-note"><?= esc($prototypeStep1BlockedReason) ?></span>
                          </div>
                        <?php endif; ?>
                        <div class="tp-form-live-box<?= $prototypeStep1CanEdit ? '' : ' is-disabled' ?>" data-step1-live-form>
                          <div class="tp-field-grid">
                            <div class="tp-field">
                              <label for="tp_step1_cli_directo_id">Cliente</label>
                              <select id="tp_step1_cli_directo_id" data-step1-input="cli_directo_id"<?= $prototypeStep1CanEdit ? '' : ' disabled' ?>>
                                <option value="">Seleccione un cliente</option>
                                <?php foreach (($prototypeStep1Form['options']['cliente'] ?? []) as $optionValue => $optionLabel): ?>
                                  <option value="<?= esc((string) $optionValue, 'attr') ?>"<?= (string) $optionValue === (string) ($prototypeStep1Form['values']['cli_directo_id'] ?? '') ? ' selected' : '' ?>><?= esc((string) $optionLabel) ?></option>
                                <?php endforeach; ?>
                              </select>
                            </div>
                            <div class="tp-field">
                              <label for="tp_step1_cli_directo_ejecutivo_id">Ejecutivo</label>
                              <select id="tp_step1_cli_directo_ejecutivo_id" data-step1-input="cli_directo_ejecutivo_id"<?= $prototypeStep1CanEdit ? '' : ' disabled' ?>>
                                <option value="">Seleccione un ejecutivo</option>
                                <?php foreach (($prototypeStep1Form['options']['ejecutivo'] ?? []) as $optionValue => $optionLabel): ?>
                                  <option value="<?= esc((string) $optionValue, 'attr') ?>"<?= (string) $optionValue === (string) ($prototypeStep1Form['values']['cli_directo_ejecutivo_id'] ?? '') ? ' selected' : '' ?>><?= esc((string) $optionLabel) ?></option>
                                <?php endforeach; ?>
                              </select>
                            </div>
                            <div class="tp-field">
                              <label for="tp_step1_contrato">Contrato</label>
                              <input id="tp_step1_contrato" value="<?= esc((string) ($prototypeStep1Form['values']['contrato'] ?? ''), 'attr') ?>" data-step1-input="contrato"<?= $prototypeStep1CanEdit ? '' : ' disabled' ?>>
                            </div>
                            <div class="tp-field">
                              <label for="tp_step1_unidad">Unidad</label>
                              <input id="tp_step1_unidad" value="<?= esc((string) ($prototypeStep1Form['values']['unidad'] ?? ''), 'attr') ?>" data-step1-input="unidad"<?= $prototypeStep1CanEdit ? '' : ' disabled' ?>>
                            </div>
                            <div class="tp-field">
                              <label for="tp_step1_serie">Serie</label>
                              <input id="tp_step1_serie" value="<?= esc((string) ($prototypeStep1Form['values']['serie'] ?? ''), 'attr') ?>" data-step1-input="serie"<?= $prototypeStep1CanEdit ? '' : ' disabled' ?>>
                            </div>
                            <div class="tp-field">
                              <label for="tp_step1_placas">Placas</label>
                              <input id="tp_step1_placas" value="<?= esc((string) ($prototypeStep1Form['values']['placas'] ?? ''), 'attr') ?>" data-step1-input="placas"<?= $prototypeStep1CanEdit ? '' : ' disabled' ?>>
                            </div>
                            <div class="tp-field">
                              <label for="tp_step1_entidad_id">Entidad</label>
                              <select id="tp_step1_entidad_id" data-step1-input="entidad_id"<?= $prototypeStep1CanEdit ? '' : ' disabled' ?>>
                                <option value="">Seleccione una entidad</option>
                                <?php foreach (($prototypeStep1Form['options']['entidad'] ?? []) as $optionValue => $optionLabel): ?>
                                  <option value="<?= esc((string) $optionValue, 'attr') ?>"<?= (string) $optionValue === (string) ($prototypeStep1Form['values']['entidad_id'] ?? '') ? ' selected' : '' ?>><?= esc((string) $optionLabel) ?></option>
                                <?php endforeach; ?>
                              </select>
                            </div>
                            <div class="tp-field wide">
                              <label for="tp_step1_observaciones">Observaciones</label>
                              <textarea id="tp_step1_observaciones" data-step1-input="observaciones"<?= $prototypeStep1CanEdit ? '' : ' disabled' ?>><?= esc((string) ($prototypeStep1Form['values']['observaciones'] ?? '')) ?></textarea>
                            </div>
                          </div>
                          <?php if ($prototypeStep1CanEdit): ?>
                            <div class="tp-btn-row" style="margin-top: 12px;">
                              <button type="button" class="tp-btn primary" data-step1-save>Guardar datos base</button>
                            </div>
                          <?php endif; ?>
                          <div class="tp-form-feedback" data-step1-feedback hidden></div>
                        </div>
                      </div>

                      <div class="tp-step1-linked">
                        <?php if (!empty($prototypeStep1ServicesBlockedReason)): ?>
                          <div style="margin-bottom: 10px;">
                            <span class="tp-inline-note"><?= esc($prototypeStep1ServicesBlockedReason) ?></span>
                          </div>
                        <?php endif; ?>
                        <div class="tp-form-live-box<?= !empty($prototypeStep1ServicesForm['canManageBase']) ? '' : ' is-disabled' ?>" data-step1-services-form>
                          <div class="tp-field-grid">
                            <div class="tp-field wide">
                              <label for="tp_step1_principal_tipo_id">Tipo principal</label>
                              <select id="tp_step1_principal_tipo_id" data-step1-service-input="principal_tipo_id"<?= !empty($prototypeStep1ServicesForm['canEditPrincipal']) ? '' : ' disabled' ?>>
                                <option value="">Seleccione un tipo</option>
                                <?php foreach (($prototypeStep1ServicesForm['options']['traTipos'] ?? []) as $optionValue => $optionLabel): ?>
                                  <option value="<?= esc((string) $optionValue, 'attr') ?>"<?= (string) $optionValue === (string) ($prototypeStep1ServicesForm['principalTipoId'] ?? '') ? ' selected' : '' ?>><?= esc((string) $optionLabel) ?></option>
                                <?php endforeach; ?>
                              </select>
                            </div>
                          </div>
                          <?php if (!empty($prototypeStep1ServicesForm['canEditPrincipal'])): ?>
                            <div class="tp-btn-row" style="margin-top: 12px;">
                              <button type="button" class="tp-btn primary" data-step1-principal-save>Guardar tipo principal</button>
                            </div>
                          <?php endif; ?>
                          <div class="tp-field-grid" style="margin-top: 12px;">
                            <div class="tp-field wide">
                              <label for="tp_step1_add_tipo_id">Agregar tipos ligados</label>
                              <select id="tp_step1_add_tipo_id" data-step1-service-input="add_tipo_id" multiple size="6"<?= !empty($prototypeStep1ServicesForm['canManageBase']) ? '' : ' disabled' ?>>
                                <?php foreach (($prototypeStep1ServicesForm['options']['traTipos'] ?? []) as $optionValue => $optionLabel): ?>
                                  <option value="<?= esc((string) $optionValue, 'attr') ?>"><?= esc((string) $optionLabel) ?></option>
                                <?php endforeach; ?>
                              </select>
                              <span class="tp-field-help">Puedes ligar varios en una sola accion. El backend impide repetir el principal o asociar dos veces el mismo tipo.</span>
                            </div>
                          </div>
                          <?php if (!empty($prototypeStep1ServicesForm['canManageBase'])): ?>
                            <div class="tp-btn-row" style="margin-top: 12px;">
                              <button type="button" class="tp-btn primary" data-step1-associated-add>Agregar tipos ligados</button>
                            </div>
                          <?php endif; ?>
                          <div class="tp-assoc-list" data-step1-services-list style="margin-top: 12px;">
                            <?php if (!empty($prototypeStep1ServicesForm['services']) && is_array($prototypeStep1ServicesForm['services'])): ?>
                              <?php foreach ($prototypeStep1ServicesForm['services'] as $serviceRow): ?>
                                <div class="tp-assoc-item" data-step1-asociado-id="<?= (int) ($serviceRow['asociado_id'] ?? 0) ?>">
                                  <div>
                                    <strong><?= esc((string) ($serviceRow['label'] ?? 'Sin tipo')) ?></strong>
                                    <small><?= !empty($serviceRow['is_principal']) ? 'Principal' : 'Asociado editable' ?></small>
                                  </div>
                                  <div class="tp-topbar-actions">
                                    <?php if (empty($serviceRow['is_principal']) && !empty($prototypeStep1ServicesForm['canEditAsociado'])): ?>
                                      <select class="tp-assoc-select" data-step1-associated-select="<?= (int) ($serviceRow['asociado_id'] ?? 0) ?>">
                                        <option value="">Seleccione un tipo</option>
                                        <?php foreach (($prototypeStep1ServicesForm['options']['traTipos'] ?? []) as $optionValue => $optionLabel): ?>
                                          <option value="<?= esc((string) $optionValue, 'attr') ?>"<?= (string) $optionValue === (string) ($serviceRow['tra_tipos_id'] ?? '') ? ' selected' : '' ?>><?= esc((string) $optionLabel) ?></option>
                                        <?php endforeach; ?>
                                      </select>
                                      <button type="button" class="tp-btn secondary" data-step1-associated-save="<?= (int) ($serviceRow['asociado_id'] ?? 0) ?>">Actualizar</button>
                                    <?php endif; ?>
                                    <?php if (empty($serviceRow['is_principal']) && !empty($prototypeStep1ServicesForm['canDeleteAsociado'])): ?>
                                      <button type="button" class="tp-btn ghost" data-step1-associated-delete="<?= (int) ($serviceRow['asociado_id'] ?? 0) ?>">Eliminar</button>
                                    <?php endif; ?>
                                    <?php if (!empty($serviceRow['is_principal'])): ?>
                                      <span class="tp-pill is-principal">Principal</span>
                                    <?php endif; ?>
                                  </div>
                                </div>
                              <?php endforeach; ?>
                            <?php else: ?>
                              <span class="tp-inline-note">No hay tipos ligados registrados todavia.</span>
                            <?php endif; ?>
                          </div>
                          <div class="tp-form-feedback" data-step1-services-feedback hidden></div>
                        </div>
                      </div>
                    </div>

                  </div>
                <?php elseif ($activeStep === 4): ?>
                  <div class="tp-step4-compact-stack">
                    <div class="tp-step1-linked is-compact">
                      <div class="tp-service-summary" style="margin-bottom: 0;">
                        <?php foreach ($step4CompactSummaryItems as $summaryItem): ?>
                          <div class="tp-service-item">
                            <span><?= esc($summaryItem['label']) ?></span>
                            <strong><?= esc($summaryItem['value']) ?></strong>
                          </div>
                        <?php endforeach; ?>
                      </div>
                    </div>

                    <div class="tp-step1-linked is-compact">
                      <div class="tp-field-grid tp-step4-compact-fields">
                        <?php foreach ($step4CompactIdentityFields as $field): ?>
                          <div class="tp-field<?= !empty($field['wide']) ? ' wide' : '' ?>">
                            <label><?= esc((string) ($field['label'] ?? 'Dato')) ?></label>
                            <?php if (($field['type'] ?? '') === 'select'): ?>
                              <select disabled><option><?= esc((string) ($field['value'] ?? '')) ?></option></select>
                            <?php elseif (($field['type'] ?? '') === 'textarea'): ?>
                              <textarea disabled><?= esc((string) ($field['value'] ?? '')) ?></textarea>
                            <?php else: ?>
                              <input value="<?= esc((string) ($field['value'] ?? ''), 'attr') ?>" disabled>
                            <?php endif; ?>
                          </div>
                        <?php endforeach; ?>
                      </div>
                      <?php if ($step4CompactObservationPreview !== ''): ?>
                        <p class="tp-step4-compact-note">Observaciones: <?= esc($step4CompactObservationPreview) ?></p>
                      <?php endif; ?>
                    </div>
                  </div>
                <?php else: ?>
                  <div class="tp-field-grid">
                    <?php foreach ($step1IdentityFields as $field): ?>
                      <div class="tp-field<?= !empty($field['wide']) ? ' wide' : '' ?>">
                        <label><?= esc($field['label']) ?></label>
                        <?php if (($field['type'] ?? '') === 'select'): ?>
                          <select><option><?= esc($field['value']) ?></option></select>
                        <?php elseif (($field['type'] ?? '') === 'textarea'): ?>
                          <textarea><?= esc($field['value']) ?></textarea>
                        <?php else: ?>
                          <input value="<?= esc($field['value'], 'attr') ?>">
                        <?php endif; ?>
                      </div>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>

                <?php if (!$useThreeRailLayout): ?>
                  <div class="tp-metrics-row">
                    <div class="tp-metric"><span>Estatus</span><strong>Pago de derechos</strong></div>
                    <div class="tp-metric"><span>Semaforo</span><strong>Normal</strong></div>
                    <div class="tp-metric"><span>Tiempo en paso</span><strong>2 dias</strong></div>
                  </div>
                <?php endif; ?>
              </section>

              <section class="tp-section-card"<?= $isOperationalBasePhase ? '' : ' data-operational-anchor="' . (int) $activeStep . '"' ?>>
                <div class="tp-section-header">
                  <div>
                    <h3 class="tp-section-title"><?= esc($isOperationalBasePhase ? 'Pasos 2 y 3 · Gestion, derechos y cierre documental' : ('Paso ' . (int) $activeStep . ': ' . $currentStepLabel)) ?></h3>
                    <p class="tp-section-copy"><?= esc($isOperationalBasePhase ? 'Despues del expediente, la pantalla debe leerse como una continuidad: primero gestion y derechos; despues el cierre documental con evidencias finales.' : $displayStepCopy) ?></p>
                  </div>
                  <span class="tp-section-tag"><?= esc($isOperationalBasePhase ? 'Secuencia 2 → 3' : 'Paso activo') ?></span>
                </div>

                <?php if ($isOperationalBasePhase): ?>
                  <div class="tp-sequence-stack">
                    <div class="tp-sequence-block<?= $activeStep === 2 ? ' is-focused' : '' ?>" data-operational-focus="2" data-operational-anchor="2">
                      <div class="tp-sequence-head">
                        <div>
                          <span class="tp-sequence-kicker">Paso 2</span>
                          <h4 class="tp-sequence-title">Gestion del tramite y pago de derechos</h4>
                          <p class="tp-sequence-copy">Primero se asigna la operacion y enseguida se resuelven los campos financieros y documentales de derechos dentro del mismo frente.</p>
                        </div>
                        <div class="tp-sequence-tail">
                          <span>Asignacion operativa</span>
                          <span>Pago de derechos</span>
                        </div>
                      </div>

                      <div class="tp-phase-grid is-step2-priority">
                        <div class="tp-subsection-card<?= $activeStep === 2 ? ' is-focused' : '' ?> is-step2-form-card" data-operational-focus="2">
                          <h4 class="tp-subsection-title">Formulario operativo</h4>
                          <p class="tp-subsection-copy">Este es el frente principal del Paso 2. Aqui se asigna gestor y se guardan los datos de derechos sin competir con tarjetas de resumen arriba del formulario.</p>
                          <div class="tp-step2-form-shell">
                            <div class="tp-form-live-box<?= $prototypeStep2CanEdit ? '' : ' is-disabled' ?>" data-step2-live-form>
                              <div class="tp-form-live-head">
                                <h5>Editar y guardar sobre contratos reales</h5>
                                <p>Este bloque usa los guardados reales del flujo actual. Guarda primero asignacion de gestor y despues pago de derechos, sin duplicar el formulario legacy completo.</p>
                              </div>
                              <div class="tp-field-grid">
                                <div class="tp-field">
                                  <label for="tp_step2_empresa_gestora">Empresa gestora</label>
                                  <select id="tp_step2_empresa_gestora" data-step2-input="empresa_gestora_id"<?= $prototypeStep2CanEdit ? '' : ' disabled' ?>>
                                    <option value="">Seleccione una empresa</option>
                                    <?php foreach (($prototypeStep2Form['options']['empresaGestora'] ?? []) as $optionValue => $optionLabel): ?>
                                      <option value="<?= esc((string) $optionValue, 'attr') ?>"<?= (string) $optionValue === (string) ($prototypeStep2Form['values']['empresa_gestora_id'] ?? '') ? ' selected' : '' ?>><?= esc((string) $optionLabel) ?></option>
                                    <?php endforeach; ?>
                                  </select>
                                </div>
                                <div class="tp-field">
                                  <label for="tp_step2_gestor">Gestor</label>
                                  <select id="tp_step2_gestor" data-step2-input="gestor_id"<?= $prototypeStep2CanEdit ? '' : ' disabled' ?>>
                                    <option value="">Seleccione un gestor</option>
                                    <?php foreach (($prototypeStep2Form['options']['gestor'] ?? []) as $optionValue => $optionLabel): ?>
                                      <option value="<?= esc((string) $optionValue, 'attr') ?>"<?= (string) $optionValue === (string) ($prototypeStep2Form['values']['gestor_id'] ?? '') ? ' selected' : '' ?>><?= esc((string) $optionLabel) ?></option>
                                    <?php endforeach; ?>
                                  </select>
                                  <span class="tp-field-help">La lista de gestores depende de la empresa seleccionada.</span>
                                </div>
                                <div class="tp-field">
                                  <label for="tp_step2_derechos_tramite">Monto pago de derechos</label>
                                  <input id="tp_step2_derechos_tramite" type="number" step="0.01" min="0" value="<?= esc((string) ($prototypeStep2Form['values']['derechos_tramite'] ?? ''), 'attr') ?>" data-step2-input="derechos_tramite"<?= $prototypeStep2CanEdit ? '' : ' disabled' ?>>
                                </div>
                                <div class="tp-field">
                                  <label for="tp_step2_derechos_pago_sitio">Pago</label>
                                  <select id="tp_step2_derechos_pago_sitio" data-step2-input="derechos_pago_sitio"<?= $prototypeStep2CanEdit ? '' : ' disabled' ?>>
                                    <option value="">Seleccione una opcion</option>
                                    <?php foreach (($prototypeStep2Form['options']['derechosPagoSitio'] ?? []) as $optionValue => $optionLabel): ?>
                                      <option value="<?= esc((string) $optionValue, 'attr') ?>"<?= (string) $optionValue === (string) ($prototypeStep2Form['values']['derechos_pago_sitio'] ?? '') ? ' selected' : '' ?>><?= esc((string) $optionLabel) ?></option>
                                    <?php endforeach; ?>
                                  </select>
                                </div>
                                <div class="tp-field<?= $prototypeStep2VigenciaIsUrgent ? ' is-urgent' : '' ?>" data-step2-vigencia-field>
                                  <label for="tp_step2_derechos_vigencia">Fecha vigencia</label>
                                  <input id="tp_step2_derechos_vigencia" type="datetime-local" value="<?= esc($prototypeStep2VigenciaInputValue, 'attr') ?>" data-step2-input="derechos_vigencia"<?= $prototypeStep2CanEdit ? '' : ' disabled' ?>>
                                  <span class="tp-inline-note is-urgent" data-step2-vigencia-warning<?= $prototypeStep2VigenciaWarningText === '' ? ' hidden' : '' ?>><?= esc($prototypeStep2VigenciaWarningText) ?></span>
                                </div>
                                <div class="tp-field">
                                  <label for="tp_step2_derechos_revol_cliente">Forma de pago</label>
                                  <select id="tp_step2_derechos_revol_cliente" data-step2-input="derechos_revol_cliente"<?= $prototypeStep2CanEdit ? '' : ' disabled' ?>>
                                    <option value="">Seleccione una opcion</option>
                                    <?php foreach (($prototypeStep2Form['options']['derechosRevolCliente'] ?? []) as $optionValue => $optionLabel): ?>
                                      <option value="<?= esc((string) $optionValue, 'attr') ?>"<?= (string) $optionValue === (string) ($prototypeStep2Form['values']['derechos_revol_cliente'] ?? '') ? ' selected' : '' ?>><?= esc((string) $optionLabel) ?></option>
                                    <?php endforeach; ?>
                                  </select>
                                </div>
                                <div class="tp-field wide">
                                  <label for="tp_step2_derechos_refer_banc">Referencia bancaria</label>
                                  <input id="tp_step2_derechos_refer_banc" type="text" value="<?= esc((string) ($prototypeStep2Form['values']['derechos_refer_banc'] ?? ''), 'attr') ?>" data-step2-input="derechos_refer_banc"<?= $prototypeStep2CanEdit ? '' : ' disabled' ?>>
                                </div>
                              </div>
                              <?php if ($prototypeStep2CanEdit): ?>
                                <div class="tp-btn-row" style="margin-top: 12px;">
                                  <button type="button" class="tp-btn primary" data-step2-save>Guardar Paso 2</button>
                                </div>
                              <?php else: ?>
                                <div style="margin-top: 12px;">
                                  <span class="tp-inline-note"><?= esc($prototypeStep2BlockedReason !== '' ? $prototypeStep2BlockedReason : 'Este perfil no tiene permisos completos para editar el Paso 2 desde el prototipo.') ?></span>
                                </div>
                              <?php endif; ?>
                              <div class="tp-form-feedback" data-step2-feedback hidden></div>
                            </div>

                          </div>
                          <div class="tp-approval-panel<?= $step2ApprovalReady ? ($prototypeCanApproveStep2 ? ' is-ready' : ' is-info') : ' is-pending' ?>" style="margin-top: 12px;" data-operational-step2-panel>
                            <h5 class="tp-approval-title" data-operational-step2-title><?= esc($step2ApprovalTitleText) ?></h5>
                            <p class="tp-approval-copy" data-operational-step2-copy>
                              <?= esc($step2ApprovalCopyText) ?>
                            </p>
                            <div data-step2-approval-actions>
                              <?php if ($step2ApprovalReady && $prototypeCanApproveStep2): ?>
                                <div class="tp-btn-row" style="margin-top: 12px;">
                                  <button type="button" class="tp-btn primary" data-operational-step-link="3" data-operational-approve="1">Aprobar tramite</button>
                                </div>
                                <div style="margin-top: 8px;">
                                  <span class="tp-inline-note" data-operational-step2-note>Al aprobar, la siguiente lectura operativa es cerrar evidencias finales en Paso 3.</span>
                                </div>
                                <div class="tp-btn-row" style="margin-top: 10px;" data-operational-reset-row hidden>
                                  <a href="javascript:void(0)" class="tp-btn secondary" data-operational-reset-approval="1">Limpiar aprobacion local</a>
                                </div>
                              <?php elseif ($step2ApprovalReady): ?>
                                <div style="margin-top: 12px;">
                                  <span class="tp-inline-note"><?= esc($step2ApprovalInfoText) ?></span>
                                </div>
                              <?php else: ?>
                                <ul class="tp-approval-missing">
                                  <?php foreach ($step2ApprovalMissing as $missingField): ?>
                                    <li><?= esc($missingField) ?></li>
                                  <?php endforeach; ?>
                                </ul>
                              <?php endif; ?>
                            </div>
                          </div>
                          <div style="margin-top: 12px;">
                            <span class="tp-inline-note">Comprobantes de linea de captura: opcionales</span>
                          </div>
                        </div>
                      </div>
                    </div>

                    <div class="tp-sequence-block<?= $activeStep === 3 ? ' is-focused' : '' ?>" data-operational-focus="3" data-operational-anchor="3" data-operational-step3-sequence>
                      <div class="tp-sequence-head">
                        <div>
                          <span class="tp-sequence-kicker">Paso 3</span>
                          <h4 class="tp-sequence-title">Evidencias finales y cierre documental</h4>
                          <p class="tp-sequence-copy">Este tramo ya no es un formulario amplio. Su funcion es cerrar la operacion con dos evidencias muy concretas antes de pasar a finanzas.</p>
                        </div>
                        <div class="tp-sequence-tail">
                          <span data-operational-step3-tail>Esperando aprobacion del Paso 2</span>
                          <span>Registro de evidencias</span>
                        </div>
                      </div>

                      <div class="tp-split-grid">
                        <div class="tp-subsection-card<?= $activeStep === 3 ? ' is-focused' : '' ?>" data-operational-focus="3">
                          <h4 class="tp-subsection-title">Evidencias finales</h4>
                          <p class="tp-subsection-copy">En el flujo actual este tramo ya no es una forma larga. Son dropzones muy concretos para tramite entregado por gestor y acuse de recibo del cliente.</p>
                          <div class="tp-step4-status-row" data-step3-status-row>
                            <?php foreach ($step3EvidenceChips as $chip): ?>
                              <?php
                                $step3ChipKey = 'generic';
                                if (($chip['label'] ?? '') === 'Tramite Entregado por Gestor') {
                                  $step3ChipKey = 'tramite_recibido';
                                } elseif (($chip['label'] ?? '') === 'Acuse de Recibo del Cliente') {
                                  $step3ChipKey = 'acuse_recibo_cliente';
                                }
                              ?>
                              <span class="tp-step4-status-chip<?= !empty($chip['isSuccess']) ? ' is-success' : '' ?>" data-step3-chip="<?= esc($step3ChipKey, 'attr') ?>"><?= esc($chip['label']) ?></span>
                            <?php endforeach; ?>
                          </div>
                          <div class="tp-form-live-box<?= $prototypeStep3CanUpload ? '' : ' is-disabled' ?>" data-step3-live-form style="margin-top: 12px;">
                            <div class="tp-form-live-head">
                              <h5>Subir evidencias finales reales</h5>
                              <p>Este bloque usa el upload real del sistema para registrar tramite entregado por gestor y acuse de recibo del cliente.</p>
                            </div>
                            <div class="tp-field-grid">
                              <div class="tp-field">
                                <label for="tp_step3_comprobante_final">Tipo de evidencia</label>
                                <select id="tp_step3_comprobante_final" data-step3-input="comprobante_final"<?= $prototypeStep3CanUpload ? '' : ' disabled' ?>>
                                  <?php foreach (($prototypeStep3Form['options']['comprobanteFinal'] ?? []) as $optionValue => $optionLabel): ?>
                                    <option value="<?= esc((string) $optionValue, 'attr') ?>"><?= esc((string) $optionLabel) ?></option>
                                  <?php endforeach; ?>
                                </select>
                              </div>
                            </div>
                            <div class="tp-upload-panel" style="margin-top: 12px;">
                              <div class="tp-upload-head">
                                <strong>Dropzone evidencias finales</strong>
                                <p>Usa el mismo patrón visual que el Paso 2: una sola superficie clara, feedback inmediato y la galería en la misma lectura.</p>
                              </div>
                              <div class="tp-dropzone-box<?= $prototypeStep3CanUpload ? ' is-actionable' : ' is-disabled' ?>" data-step3-dropzone>
                                <input id="tp_step3_file" class="tp-dropzone-input" type="file" data-step3-file<?= $prototypeStep3CanUpload ? '' : ' disabled' ?>>
                                <span class="tp-dropzone-kicker">Evidencia final</span>
                                <strong class="tp-dropzone-title">Arrastra aqui el archivo final o haz clic para seleccionarlo</strong>
                                <span class="tp-dropzone-copy">Primero elige el tipo de evidencia y despues carga el archivo con el mismo patrón visual del pago de derechos.</span>
                                <span class="tp-dropzone-meta" data-step3-file-selected>Sin archivo seleccionado.</span>
                              </div>
                            </div>
                            <?php if ($prototypeStep3CanUpload): ?>
                              <div class="tp-btn-row" style="margin-top: 12px;">
                                <button type="button" class="tp-btn primary" data-step3-upload>Subir evidencia</button>
                              </div>
                            <?php elseif ($prototypeStep3BlockedReason !== ''): ?>
                              <div style="margin-top: 12px;">
                                <span class="tp-inline-note"><?= esc($prototypeStep3BlockedReason) ?></span>
                              </div>
                            <?php endif; ?>
                            <?php if (!$prototypeStep3CanDelete && $prototypeStep3DeleteBlockedReason !== ''): ?>
                              <div style="margin-top: 12px;">
                                <span class="tp-inline-note"><?= esc($prototypeStep3DeleteBlockedReason) ?></span>
                              </div>
                            <?php endif; ?>
                            <div class="tp-form-feedback" data-step3-feedback hidden></div>
                            <div class="tp-gallery" style="margin-top: 12px;">
                              <strong>Evidencias registradas</strong>
                              <div class="tp-gallery-list" data-step3-gallery style="margin-top: 0;">
                                <?php foreach ($step3EvidenceDocItems as $docItem): ?>
                                  <div class="tp-gallery-item"><?= esc($docItem) ?></div>
                                <?php endforeach; ?>
                              </div>
                            </div>
                          </div>
                        </div>

                        <div class="tp-subsection-card<?= $activeStep === 3 ? ' is-focused' : '' ?>" data-operational-focus="3">
                          <h4 class="tp-subsection-title">Salida del cierre</h4>
                          <p class="tp-subsection-copy">Aqui debe quedar clarisimo que este cierre no es ornamental: prepara la lectura que despues necesitan Pago a gestor y Cobro a cliente.</p>
                          <div style="margin-bottom: 12px;">
                            <span class="tp-inline-note" data-operational-step3-note>Si Paso 2 ya fue aprobado, aqui ya no se valida derechos: aqui se documenta el cierre que destraba a la par Pago a gestor y Cobro a cliente.</span>
                          </div>
                          <div class="tp-btn-row" style="margin-bottom: 12px;" data-operational-reset-row hidden>
                            <a href="javascript:void(0)" class="tp-btn secondary" data-operational-reset-approval="1">Limpiar aprobacion local</a>
                          </div>
                          <div class="tp-service-stack">
                            <div class="tp-service-item">
                              <span>Persistencia del tramo</span>
                              <strong>Registro de evidencias finales</strong>
                              <p data-step3-evidence-note><?= esc($step3EvidenceNote) ?></p>
                            </div>
                            <div class="tp-service-item">
                              <span>Lo que habilita</span>
                              <strong>Paso 4 y 5 · Frentes financieros</strong>
                              <p>Las dos filas financieras posteriores necesitan leer estas evidencias como cierre valido de la fase operativa.</p>
                            </div>
                          </div>
                          <div class="tp-approval-panel<?= $step3GateReady ? ' is-ready' : ' is-pending' ?>" style="margin-top: 12px;" data-step3-gate-panel>
                            <h5 class="tp-approval-title" data-step3-gate-title><?= esc($step3GateReady ? 'Pago a gestor y Cobro a cliente ya pueden abrirse' : 'Pago a gestor y Cobro a cliente siguen bloqueados') ?></h5>
                            <p class="tp-approval-copy" data-step3-gate-copy>
                              <?= esc($step3GateReady
                                ? 'Las dos evidencias finales ya estan presentes. Este cierre documental ya habilita en paralelo Pago a gestor y Cobro a cliente.'
                                : 'Mientras falte una de las dos evidencias finales, el cierre no debe leerse como terminado y ni Pago a gestor ni Cobro a cliente deberian abrirse como siguiente accion valida.') ?>
                            </p>
                            <div data-step3-gate-actions>
                              <?php if ($step3GateReady): ?>
                                <div style="margin-top: 12px;">
                                  <span class="tp-inline-note">Pago a gestor y Cobro a cliente ya no deben abrirse como otra pantalla del prototipo; ambas salidas financieras deben leerse dentro de la misma superficie de servicio.</span>
                                </div>
                              <?php else: ?>
                                <ul class="tp-approval-missing">
                                  <?php foreach ($step3GateMissing as $missingField): ?>
                                    <li><?= esc($missingField) ?></li>
                                  <?php endforeach; ?>
                                </ul>
                              <?php endif; ?>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>

                    <?php if ($prototypeStep4CanView): ?>
                      <div class="tp-sequence-block" data-operational-anchor="4" data-operational-step4-inline>
                        <div class="tp-sequence-head">
                          <div>
                            <span class="tp-sequence-kicker">Paso 4</span>
                            <h4 class="tp-sequence-title">Pago a gestor</h4>
                            <p class="tp-sequence-copy">La fase financiera ya vive debajo del cierre operativo para que el handoff no dependa de otra pantalla del prototipo.</p>
                          </div>
                          <div class="tp-sequence-tail">
                            <span>Captura financiera</span>
                            <span><?= esc((string) ($step4VisualData['pago_gestor_status_label'] ?? 'Sin definir')) ?></span>
                          </div>
                        </div>

                        <div class="tp-approval-panel is-info" style="margin-bottom: 14px;" data-operational-step4-handoff-panel>
                          <h5 class="tp-approval-title" data-operational-step4-title>Fase financiera esperando handoff operativo</h5>
                          <p class="tp-approval-copy" data-operational-step4-copy>Pago a gestor debe leer el cierre de la fase base antes de sentirse como frente habilitado. Esta franja solo interpreta la marca local del prototipo; la disponibilidad real sigue dependiendo del cierre documental y permisos.</p>
                          <div style="margin-top: 10px;">
                            <span class="tp-inline-note" data-operational-step4-note>Sin marca local de aprobacion de Paso 2 en esta sesion.</span>
                          </div>
                        </div>

                        <div class="tp-subsection-card" style="margin-bottom: 14px;">
                          <h4 class="tp-subsection-title">Lo que Pago a Gestor necesita de la fase base</h4>
                          <p class="tp-subsection-copy">Este frente financiero no vive aislado. Necesita leer identidad, gestion, derechos y evidencias finales antes de capturar pagos y comprobantes.</p>
                          <div class="tp-service-summary">
                            <?php foreach ($step4DependencyCards as $dependencyCard): ?>
                              <div class="tp-service-item">
                                <span><?= esc($dependencyCard['label']) ?></span>
                                <strong><?= esc($dependencyCard['value']) ?></strong>
                              </div>
                            <?php endforeach; ?>
                          </div>
                          <div class="tp-step4-status-row" style="margin-bottom: 0;">
                            <?php foreach ($step4DependencyChips as $chip): ?>
                              <span class="tp-step4-status-chip<?= !empty($chip['isSuccess']) ? ' is-success' : '' ?>"><?= esc($chip['label']) ?></span>
                            <?php endforeach; ?>
                          </div>
                        </div>

                        <div class="tp-step4-kpi-grid">
                          <div class="tp-step4-kpi">
                            <span>Deposito a gestor</span>
                            <strong><?= esc($formatMoney($step4VisualData['fields']['deposito_gestor'] ?? 0)) ?></strong>
                          </div>
                          <div class="tp-step4-kpi">
                            <span>Saldo pendiente</span>
                            <strong><?= esc($formatMoney($step4VisualData['fields']['col_a_favor'] ?? 0)) ?></strong>
                          </div>
                          <div class="tp-step4-kpi">
                            <span>Pago total</span>
                            <strong><?= esc($formatMoney($step4VisualData['fields']['gestor_total_pago'] ?? 0)) ?></strong>
                          </div>
                          <div class="tp-step4-kpi">
                            <span>Documentos listos</span>
                            <strong data-step4-doc-count><?= count($step4VisualData['payment_docs'] ?? []) ?>/2</strong>
                          </div>
                        </div>

                        <div class="tp-step4-status-row">
                          <span class="tp-step4-status-chip<?= !empty($step4VisualData['has_factura_gestor']) ? ' is-success' : '' ?>">Factura gestor</span>
                          <span class="tp-step4-status-chip<?= !empty($step4VisualData['has_comprobante_pago']) ? ' is-success' : '' ?>">Comprobante de pago</span>
                          <span class="tp-step4-status-chip"><?= esc($step4VisualData['status_doctos_gestor_label'] ?? 'En Proceso') ?></span>
                          <span class="tp-step4-status-chip"><?= esc($step4VisualData['reembolso_status_label'] ?? 'Pendiente') ?></span>
                        </div>

                        <div class="tp-form-live-box<?= $prototypeStep4CanEdit ? '' : ' is-disabled' ?>" data-step4-live-form>
                          <div class="tp-form-live-head">
                            <h5>Captura financiera del pago a gestor</h5>
                            <p>Confirma costos, deposito y estados con datos reales del expediente antes de cerrar esta salida financiera.</p>
                          </div>
                          <div class="tp-field-grid">
                            <div class="tp-field"><label>Gestor</label><input value="<?= esc($step4VisualData['gestor_name'] ?? 'Sin asignar', 'attr') ?>" disabled></div>
                            <div class="tp-field"><label for="tp_step4_num_factura_gestor">Numero de factura</label><input id="tp_step4_num_factura_gestor" type="text" value="<?= esc((string) ($prototypeStep4Form['values']['num_factura_gestor'] ?? ''), 'attr') ?>" data-step4-input="num_factura_gestor"<?= $prototypeStep4CanEdit ? '' : ' disabled' ?>></div>
                            <div class="tp-field"><label for="tp_step4_costo_tramite">Costo del tramite</label><input id="tp_step4_costo_tramite" type="number" step="0.01" value="<?= esc((string) ($prototypeStep4Form['values']['costo_tramite'] ?? ''), 'attr') ?>" data-step4-input="costo_tramite"<?= $prototypeStep4CanEdit ? '' : ' disabled' ?>></div>
                            <div class="tp-field"><label for="tp_step4_deposito_gestor">Deposito a gestor</label><input id="tp_step4_deposito_gestor" type="number" step="0.01" value="<?= esc((string) ($prototypeStep4Form['values']['deposito_gestor'] ?? ''), 'attr') ?>" data-step4-input="deposito_gestor"<?= $prototypeStep4CanEdit ? '' : ' disabled' ?>></div>
                            <div class="tp-field"><label for="tp_step4_col_a_favor">Saldo pendiente</label><input id="tp_step4_col_a_favor" type="number" step="0.01" value="<?= esc((string) ($prototypeStep4Form['values']['col_a_favor'] ?? ''), 'attr') ?>" data-step4-input="col_a_favor"<?= $prototypeStep4CanEdit ? '' : ' disabled' ?>></div>
                            <div class="tp-field"><label for="tp_step4_impuesto_gestoria">Honorarios de gestoria</label><input id="tp_step4_impuesto_gestoria" type="number" step="0.01" value="<?= esc((string) ($prototypeStep4Form['values']['impuesto_gestoria'] ?? ''), 'attr') ?>" data-step4-input="impuesto_gestoria"<?= $prototypeStep4CanEdit ? '' : ' disabled' ?>></div>
                            <div class="tp-field"><label for="tp_step4_gestoria_comision">Gratificacion</label><input id="tp_step4_gestoria_comision" type="number" step="0.01" value="<?= esc((string) ($prototypeStep4Form['values']['gestoria_comision'] ?? ''), 'attr') ?>" data-step4-input="gestoria_comision"<?= $prototypeStep4CanEdit ? '' : ' disabled' ?>></div>
                            <div class="tp-field"><label for="tp_step4_costo_paqueteria">Costo paqueteria</label><input id="tp_step4_costo_paqueteria" type="number" step="0.01" value="<?= esc((string) ($prototypeStep4Form['values']['costo_paqueteria'] ?? ''), 'attr') ?>" data-step4-input="costo_paqueteria"<?= $prototypeStep4CanEdit ? '' : ' disabled' ?>></div>
                            <div class="tp-field"><label for="tp_step4_gestor_total_pago">Pago total</label><input id="tp_step4_gestor_total_pago" type="number" step="0.01" value="<?= esc((string) ($prototypeStep4Form['values']['gestor_total_pago'] ?? ''), 'attr') ?>" data-step4-input="gestor_total_pago"<?= $prototypeStep4CanEdit ? '' : ' disabled' ?>></div>
                            <div class="tp-field"><label for="tp_step4_pago_gestor_st_id">Estatus del pago</label><select id="tp_step4_pago_gestor_st_id" data-step4-input="pago_gestor_st_id"<?= $prototypeStep4CanEdit ? '' : ' disabled' ?>><?php foreach (($prototypeStep4Form['options']['pagoGestorStatus'] ?? []) as $optionValue => $optionLabel): ?><option value="<?= esc((string) $optionValue, 'attr') ?>"<?= (string) $optionValue === (string) ($prototypeStep4Form['values']['pago_gestor_st_id'] ?? '') ? ' selected' : '' ?>><?= esc((string) $optionLabel) ?></option><?php endforeach; ?></select></div>
                            <div class="tp-field"><label for="tp_step4_status_doctos_gestor">Estatus de documentos</label><select id="tp_step4_status_doctos_gestor" data-step4-input="status_doctos_gestor"<?= $prototypeStep4CanEdit ? '' : ' disabled' ?>><?php foreach (($prototypeStep4Form['options']['statusDoctosGestor'] ?? []) as $optionValue => $optionLabel): ?><option value="<?= esc((string) $optionValue, 'attr') ?>"<?= (string) $optionValue === (string) ($prototypeStep4Form['values']['status_doctos_gestor'] ?? '') ? ' selected' : '' ?>><?= esc((string) $optionLabel) ?></option><?php endforeach; ?></select></div>
                            <div class="tp-field"><label for="tp_step4_reembolso_status_id">Estatus del reembolso</label><select id="tp_step4_reembolso_status_id" data-step4-input="reembolso_status_id"<?= $prototypeStep4CanEdit ? '' : ' disabled' ?>><?php foreach (($prototypeStep4Form['options']['reembolsoStatus'] ?? []) as $optionValue => $optionLabel): ?><option value="<?= esc((string) $optionValue, 'attr') ?>"<?= (string) $optionValue === (string) ($prototypeStep4Form['values']['reembolso_status_id'] ?? '') ? ' selected' : '' ?>><?= esc((string) $optionLabel) ?></option><?php endforeach; ?></select></div>
                          </div>
                          <?php if ($prototypeStep4CanEdit): ?>
                            <div class="tp-btn-row" style="margin-top: 12px;">
                              <button type="button" class="tp-btn primary" data-step4-save>Guardar Pago a gestor</button>
                            </div>
                          <?php else: ?>
                            <div style="margin-top: 12px;">
                              <span class="tp-inline-note"><?= esc($prototypeStep4BlockedReason !== '' ? $prototypeStep4BlockedReason : 'Pago a gestor no esta editable para este tramite y este perfil.') ?></span>
                            </div>
                          <?php endif; ?>
                          <div class="tp-form-feedback" data-step4-feedback hidden></div>

                          <!-- Uploader documentos Pago a Gestor -->
                          <div class="tp-upload-panel" style="margin-top: 16px;">
                            <div class="tp-field" style="margin-bottom: 12px;">
                              <label for="tp_step4_comprobante_final">Tipo de comprobante</label>
                              <select id="tp_step4_comprobante_final" data-step4-doc-type<?= $prototypeStep4CanUploadDocs ? '' : ' disabled' ?>>
                                <?php foreach (($prototypeStep4Form['options']['comprobanteFinal'] ?? []) as $optionValue => $optionLabel): ?>
                                  <option value="<?= esc((string) $optionValue, 'attr') ?>"><?= esc((string) $optionLabel) ?></option>
                                <?php endforeach; ?>
                              </select>
                            </div>
                            <div class="tp-dropzone-box<?= $prototypeStep4CanUploadDocs ? ' is-actionable' : ' is-disabled' ?>" data-step4-doc-dropzone>
                              <input class="tp-dropzone-input" type="file" data-step4-doc-file<?= $prototypeStep4CanUploadDocs ? '' : ' disabled' ?>>
                              <span class="tp-dropzone-kicker">Paso 4</span>
                              <strong class="tp-dropzone-title">Arrastra aquí la factura o comprobante del gestor</strong>
                              <span class="tp-dropzone-meta" data-step4-doc-selected>Sin archivo seleccionado.</span>
                            </div>
                            <?php if ($prototypeStep4CanUploadDocs): ?>
                              <div class="tp-btn-row" style="margin-top: 12px;">
                                <button type="button" class="tp-btn primary" data-step4-doc-upload>Subir comprobante</button>
                              </div>
                            <?php elseif (!empty($prototypeStep4UploadBlockedReason)): ?>
                              <div style="margin-top: 12px;">
                                <span class="tp-inline-note"><?= esc($prototypeStep4UploadBlockedReason) ?></span>
                              </div>
                            <?php endif; ?>
                            <div class="tp-form-feedback" data-step4-doc-feedback hidden></div>
                            <div class="tp-gallery" style="margin-top: 12px;">
                              <strong>Comprobantes registrados</strong>
                              <div class="tp-gallery-list" data-step4-doc-gallery style="margin-top: 0;">
                                <?php if (!empty($prototypeStep4Form['docs']) && is_array($prototypeStep4Form['docs'])): ?>
                                  <?php foreach ($prototypeStep4Form['docs'] as $docRow): ?>
                                    <div class="tp-gallery-item">
                                      <div class="tp-gallery-item-head">
                                        <div>
                                          <a class="tp-gallery-item-link" href="<?= esc(($prototypeStep4Form['fileBaseUrl'] ?? '') . rawurlencode((string) ($docRow['file'] ?? '')), 'attr') ?>" target="_blank" rel="noreferrer" data-doc-preview-url="<?= esc(($prototypeStep4Form['fileBaseUrl'] ?? '') . rawurlencode((string) ($docRow['file'] ?? '')), 'attr') ?>" data-doc-preview-name="<?= esc((string) ($docRow['file'] ?? ''), 'attr') ?>" data-doc-preview-meta="<?= esc((string) ($docRow['comprobante_final'] ?? ''), 'attr') ?>"><?= esc((string) ($docRow['file'] ?? 'Sin nombre')) ?></a>
                                          <span class="tp-gallery-item-meta"><?= esc((string) ($docRow['comprobante_final'] ?? 'Sin tipo')) ?></span>
                                        </div>
                                        <?php if ($prototypeStep4CanDeleteDocs): ?>
                                          <button type="button" class="tp-btn secondary small" data-step4-doc-delete-btn="<?= esc((string) ($docRow['file'] ?? ''), 'attr') ?>">Eliminar</button>
                                        <?php endif; ?>
                                      </div>
                                    </div>
                                  <?php endforeach; ?>
                                <?php else: ?>
                                  <div class="tp-gallery-item">Sin comprobantes de pago a gestor registrados</div>
                                <?php endif; ?>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    <?php endif; ?>
                  </div>
                <?php elseif ($activeStep === 4): ?>
                  <div class="tp-approval-panel is-info" style="margin-bottom: 14px;" data-operational-step4-handoff-panel>
                    <h5 class="tp-approval-title" data-operational-step4-title>Fase financiera esperando handoff operativo</h5>
                    <p class="tp-approval-copy" data-operational-step4-copy>Pago a gestor debe leer el cierre de la fase base antes de sentirse como frente habilitado. Esta franja solo interpreta la marca local del prototipo; la disponibilidad real sigue dependiendo del cierre documental y permisos.</p>
                    <div style="margin-top: 10px;">
                      <span class="tp-inline-note" data-operational-step4-note>Sin marca local de aprobacion de Paso 2 en esta sesion.</span>
                    </div>
                  </div>

                  <div class="tp-subsection-card" style="margin-bottom: 14px;">
                    <h4 class="tp-subsection-title">Lo que Pago a Gestor necesita de la fase base</h4>
                    <p class="tp-subsection-copy">Este frente financiero no vive aislado. Necesita leer identidad, gestion, derechos y evidencias finales antes de capturar pagos y comprobantes.</p>
                    <div class="tp-service-summary">
                      <?php foreach ($step4DependencyCards as $dependencyCard): ?>
                        <div class="tp-service-item">
                          <span><?= esc($dependencyCard['label']) ?></span>
                          <strong><?= esc($dependencyCard['value']) ?></strong>
                        </div>
                      <?php endforeach; ?>
                    </div>
                    <div class="tp-step4-status-row" style="margin-bottom: 0;">
                      <?php foreach ($step4DependencyChips as $chip): ?>
                        <span class="tp-step4-status-chip<?= !empty($chip['isSuccess']) ? ' is-success' : '' ?>"><?= esc($chip['label']) ?></span>
                      <?php endforeach; ?>
                    </div>
                  </div>

                  <div class="tp-step4-kpi-grid">
                    <div class="tp-step4-kpi">
                      <span>Deposito a gestor</span>
                      <strong><?= esc($formatMoney($step4VisualData['fields']['deposito_gestor'] ?? 0)) ?></strong>
                    </div>
                    <div class="tp-step4-kpi">
                      <span>Saldo pendiente</span>
                      <strong><?= esc($formatMoney($step4VisualData['fields']['col_a_favor'] ?? 0)) ?></strong>
                    </div>
                    <div class="tp-step4-kpi">
                      <span>Pago total</span>
                      <strong><?= esc($formatMoney($step4VisualData['fields']['gestor_total_pago'] ?? 0)) ?></strong>
                    </div>
                    <div class="tp-step4-kpi">
                      <span>Documentos listos</span>
                      <strong data-step4-doc-count><?= count($step4VisualData['payment_docs'] ?? []) ?>/2</strong>
                    </div>
                  </div>

                  <div class="tp-step4-status-row">
                    <span class="tp-step4-status-chip<?= !empty($step4VisualData['has_factura_gestor']) ? ' is-success' : '' ?>" data-step4-chip="factura_gestor">Factura gestor</span>
                    <span class="tp-step4-status-chip<?= !empty($step4VisualData['has_comprobante_pago']) ? ' is-success' : '' ?>" data-step4-chip="comprobante_pago">Comprobante de pago</span>
                    <span class="tp-step4-status-chip"><?= esc($step4VisualData['status_doctos_gestor_label'] ?? 'En Proceso') ?></span>
                    <span class="tp-step4-status-chip"><?= esc($step4VisualData['reembolso_status_label'] ?? 'Pendiente') ?></span>
                  </div>

                  <div class="tp-form-live-box<?= $prototypeStep4CanEdit ? '' : ' is-disabled' ?>" data-step4-live-form>
                    <div class="tp-form-live-head">
                      <h5>Captura financiera del pago a gestor</h5>
                      <p>Ajusta costos por tramite, confirma deposito y deja listo el cierre financiero con datos reales del expediente.</p>
                    </div>
                    <input id="tp_step4_costo_tramite" type="hidden" value="<?= esc((string) ($prototypeStep4Form['values']['costo_tramite'] ?? ''), 'attr') ?>" data-step4-input="costo_tramite">
                    <input id="tp_step4_col_a_favor" type="hidden" value="<?= esc((string) ($prototypeStep4Form['values']['col_a_favor'] ?? ''), 'attr') ?>" data-step4-input="col_a_favor">
                    <input id="tp_step4_gestor_total_pago" type="hidden" value="<?= esc((string) ($prototypeStep4Form['values']['gestor_total_pago'] ?? ''), 'attr') ?>" data-step4-input="gestor_total_pago">
                    <div class="tp-field-grid">
                      <div class="tp-field"><label>Gestor</label><input value="<?= esc($step4VisualData['gestor_name'] ?? 'Sin asignar', 'attr') ?>" disabled></div>
                      <div class="tp-field"><label for="tp_step4_num_factura_gestor">Numero de factura</label><input id="tp_step4_num_factura_gestor" type="text" value="<?= esc((string) ($prototypeStep4Form['values']['num_factura_gestor'] ?? ''), 'attr') ?>" data-step4-input="num_factura_gestor"<?= $prototypeStep4CanEdit ? '' : ' disabled' ?>></div>
                      <div class="tp-field"><label for="tp_step4_deposito_gestor">Deposito a gestor</label><input id="tp_step4_deposito_gestor" type="number" step="0.01" value="<?= esc((string) ($prototypeStep4Form['values']['deposito_gestor'] ?? ''), 'attr') ?>" data-step4-input="deposito_gestor"<?= $prototypeStep4CanEdit ? '' : ' disabled' ?>></div>
                    </div>

                    <div class="tp-step4-saldo-info is-even" data-step4-saldo-info>Sin saldo pendiente</div>

                    <div class="tp-step4-finance-stack">
                      <section class="tp-step4-money-panel">
                        <div class="tp-step4-money-head">
                          <div>
                            <h6>Costos por tramite</h6>
                            <p>Ajusta cada servicio y guarda por fila. El total alimenta automaticamente el pago al gestor.</p>
                          </div>
                          <span class="tp-step4-save-status" data-step4-cost-status>Guardado</span>
                        </div>
                        <div class="tp-step4-cost-list" data-step4-cost-list>
                          <span class="tp-inline-note">Cargando costos del expediente...</span>
                        </div>
                        <div class="tp-step4-cost-total">
                          <span>Total de costos</span>
                          <strong data-step4-cost-total>$0.00</strong>
                        </div>
                      </section>

                      <section class="tp-step4-money-panel">
                        <div class="tp-step4-money-head">
                          <div>
                            <h6>Gastos y pago total</h6>
                            <p>El pago total se recalcula en vivo con honorarios, gratificacion y paqueteria.</p>
                          </div>
                        </div>
                        <div class="tp-field-grid">
                          <div class="tp-field"><label for="tp_step4_impuesto_gestoria">Honorarios de gestoria</label><input id="tp_step4_impuesto_gestoria" type="number" step="0.01" value="<?= esc((string) ($prototypeStep4Form['values']['impuesto_gestoria'] ?? ''), 'attr') ?>" data-step4-input="impuesto_gestoria"<?= $prototypeStep4CanEdit ? '' : ' disabled' ?>></div>
                          <div class="tp-field"><label for="tp_step4_gestoria_comision">Gratificacion</label><input id="tp_step4_gestoria_comision" type="number" step="0.01" value="<?= esc((string) ($prototypeStep4Form['values']['gestoria_comision'] ?? ''), 'attr') ?>" data-step4-input="gestoria_comision"<?= $prototypeStep4CanEdit ? '' : ' disabled' ?>></div>
                          <div class="tp-field"><label for="tp_step4_costo_paqueteria">Costo paqueteria</label><input id="tp_step4_costo_paqueteria" type="number" step="0.01" value="<?= esc((string) ($prototypeStep4Form['values']['costo_paqueteria'] ?? ''), 'attr') ?>" data-step4-input="costo_paqueteria"<?= $prototypeStep4CanEdit ? '' : ' disabled' ?>></div>
                          <div class="tp-field">
                            <label>Pago total</label>
                            <div class="tp-step4-money-display" data-step4-total-text>$0.00</div>
                            <small class="tp-step4-breakdown" data-step4-total-breakdown></small>
                          </div>
                        </div>
                      </section>
                    </div>

                    <div class="tp-field-grid">
                      <div class="tp-field"><label for="tp_step4_pago_gestor_st_id">Estatus del pago</label><select id="tp_step4_pago_gestor_st_id" data-step4-input="pago_gestor_st_id"<?= $prototypeStep4CanEdit ? '' : ' disabled' ?>><?php foreach (($prototypeStep4Form['options']['pagoGestorStatus'] ?? []) as $optionValue => $optionLabel): ?><option value="<?= esc((string) $optionValue, 'attr') ?>"<?= (string) $optionValue === (string) ($prototypeStep4Form['values']['pago_gestor_st_id'] ?? '') ? ' selected' : '' ?>><?= esc((string) $optionLabel) ?></option><?php endforeach; ?></select></div>
                      <div class="tp-field"><label for="tp_step4_status_doctos_gestor">Estatus de documentos</label><select id="tp_step4_status_doctos_gestor" data-step4-input="status_doctos_gestor"<?= $prototypeStep4CanEdit ? '' : ' disabled' ?>><?php foreach (($prototypeStep4Form['options']['statusDoctosGestor'] ?? []) as $optionValue => $optionLabel): ?><option value="<?= esc((string) $optionValue, 'attr') ?>"<?= (string) $optionValue === (string) ($prototypeStep4Form['values']['status_doctos_gestor'] ?? '') ? ' selected' : '' ?>><?= esc((string) $optionLabel) ?></option><?php endforeach; ?></select></div>
                      <div class="tp-field"><label for="tp_step4_reembolso_status_id">Estatus del reembolso</label><select id="tp_step4_reembolso_status_id" data-step4-input="reembolso_status_id"<?= $prototypeStep4CanEdit ? '' : ' disabled' ?>><?php foreach (($prototypeStep4Form['options']['reembolsoStatus'] ?? []) as $optionValue => $optionLabel): ?><option value="<?= esc((string) $optionValue, 'attr') ?>"<?= (string) $optionValue === (string) ($prototypeStep4Form['values']['reembolso_status_id'] ?? '') ? ' selected' : '' ?>><?= esc((string) $optionLabel) ?></option><?php endforeach; ?></select></div>
                    </div>
                    <?php if ($prototypeStep4CanEdit): ?>
                      <div class="tp-btn-row" style="margin-top: 12px;">
                        <button type="button" class="tp-btn primary" data-step4-save>Guardar Pago a gestor</button>
                      </div>
                    <?php else: ?>
                      <div style="margin-top: 12px;">
                        <span class="tp-inline-note"><?= esc($prototypeStep4BlockedReason !== '' ? $prototypeStep4BlockedReason : 'Pago a gestor no esta editable para este tramite y este perfil.') ?></span>
                      </div>
                    <?php endif; ?>
                    <div class="tp-form-feedback" data-step4-feedback hidden></div>
                  </div>
                <?php else: ?>
                  <div class="tp-step4-kpi-grid">
                    <div class="tp-step4-kpi">
                      <span>Estatus de cobro</span>
                      <strong><?= esc($step4VisualData['cobro_status_label'] ?? 'Sin definir') ?></strong>
                    </div>
                    <div class="tp-step4-kpi">
                      <span>Estatus de reembolso</span>
                      <strong><?= esc($step4VisualData['reembolso_status_label'] ?? 'Sin definir') ?></strong>
                    </div>
                    <div class="tp-step4-kpi">
                      <span>Soportes esperados</span>
                      <strong><?= esc($stepDocCounts[5]) ?></strong>
                    </div>
                    <div class="tp-step4-kpi">
                      <span>Notas internas</span>
                      <strong><?= count($prototypeStep5NotesItems) ?></strong>
                    </div>
                  </div>

                  <div class="tp-step4-status-row">
                    <span class="tp-step4-status-chip"><?= esc($step4VisualData['cobro_status_label'] ?? 'Sin definir') ?></span>
                    <span class="tp-step4-status-chip"><?= esc($step4VisualData['reembolso_status_label'] ?? 'Sin definir') ?></span>
                    <span class="tp-step4-status-chip"><?= esc($stepNextActions[5]) ?></span>
                    <span class="tp-step4-status-chip"><?= esc($stepRisk[5]) ?></span>
                  </div>

                  <div class="tp-form-live-box<?= $prototypeStep5CanEdit ? '' : ' is-disabled' ?>" data-step5-live-form>
                    <div class="tp-form-live-head">
                      <h5>Captura financiera de Cobro a cliente</h5>
                      <p>Este bloque ya usa el guardado real de la recuperacion hacia cliente y mantiene separada esta fase de la salida financiera del gestor.</p>
                    </div>
                    <div class="tp-field-grid">
                      <div class="tp-field"><label for="tp_step5_id_give_cliente">ID que da el cliente</label><input id="tp_step5_id_give_cliente" data-step5-input="id_give_cliente" value="<?= esc((string) ($prototypeStep5Form['values']['id_give_cliente'] ?? ''), 'attr') ?>"<?= $prototypeStep5CanEdit ? '' : ' disabled' ?>></div>
                      <div class="tp-field"><label for="tp_step5_cobro_status_id">Estatus del cobro</label><select id="tp_step5_cobro_status_id" data-step5-input="cobro_status_id"<?= $prototypeStep5CanEdit ? '' : ' disabled' ?>><?php foreach (($prototypeStep5Form['options']['cobroStatus'] ?? []) as $optionValue => $optionLabel): ?><option value="<?= esc((string) $optionValue, 'attr') ?>"<?= (string) $optionValue === (string) ($prototypeStep5Form['values']['cobro_status_id'] ?? '') ? ' selected' : '' ?>><?= esc((string) $optionLabel) ?></option><?php endforeach; ?></select></div>
                      <div class="tp-field"><label for="tp_step5_numero_factura">Numero de factura</label><input id="tp_step5_numero_factura" data-step5-input="numero_factura" value="<?= esc((string) ($prototypeStep5Form['values']['numero_factura'] ?? ''), 'attr') ?>"<?= $prototypeStep5CanEdit ? '' : ' disabled' ?>></div>
                      <div class="tp-field"><label for="tp_step5_numero_refactura">Numero de refactura</label><input id="tp_step5_numero_refactura" data-step5-input="numero_refactura" value="<?= esc((string) ($prototypeStep5Form['values']['numero_refactura'] ?? ''), 'attr') ?>"<?= $prototypeStep5CanEdit ? '' : ' disabled' ?>></div>
                    </div>
                    <div class="tp-field" style="margin-top: 12px;">
                      <label for="tp_step5_evidencia_cobro_txt">Evidencia de cobro</label>
                      <textarea id="tp_step5_evidencia_cobro_txt" data-step5-input="evidencia_cobro_txt"<?= $prototypeStep5CanEdit ? '' : ' disabled' ?>><?= esc((string) ($prototypeStep5Form['values']['evidencia_cobro_txt'] ?? '')) ?></textarea>
                    </div>
                    <div class="tp-field-grid" style="margin-top: 12px;">
                      <div class="tp-field"><label for="tp_step5_costo_gestoria">Sumatoria de derechos</label><input id="tp_step5_costo_gestoria" data-step5-input="costo_gestoria" value="<?= esc((string) ($prototypeStep5Form['values']['costo_gestoria'] ?? '0.00'), 'attr') ?>" disabled></div>
                      <div class="tp-field"><label for="tp_step5_costo_pago_cliente">Honorarios del tramite</label><input id="tp_step5_costo_pago_cliente" type="number" step="0.01" data-step5-input="costo_pago_cliente" value="<?= esc((string) ($prototypeStep5Form['values']['costo_pago_cliente'] ?? '0'), 'attr') ?>"<?= $prototypeStep5CanEdit ? '' : ' disabled' ?>></div>
                      <div class="tp-field"><label for="tp_step5_comision_derechos">Comision de derechos</label><input id="tp_step5_comision_derechos" type="number" step="0.01" data-step5-input="comision_derechos" value="<?= esc((string) ($prototypeStep5Form['values']['comision_derechos'] ?? '0'), 'attr') ?>"<?= $prototypeStep5CanEdit ? '' : ' disabled' ?>></div>
                      <div class="tp-field"><label for="tp_step5_iva">IVA</label><input id="tp_step5_iva" type="number" step="0.01" data-step5-input="iva" value="<?= esc((string) ($prototypeStep5Form['values']['iva'] ?? '0.00'), 'attr') ?>"<?= $prototypeStep5CanEdit ? '' : ' disabled' ?>></div>
                      <div class="tp-field"><label for="tp_step5_costo_total">Costo total</label><input id="tp_step5_costo_total" data-step5-input="costo_total" value="<?= esc((string) ($prototypeStep5Form['values']['costo_total'] ?? '0.00'), 'attr') ?>" disabled></div>
                    </div>
                    <div class="tp-step4-status-row" style="margin-top: 12px;">
                      <span class="tp-step4-status-chip">Cliente: <?= esc($step4VisualData['cliente_name'] ?? 'Sin cliente') ?></span>
                      <span class="tp-step4-status-chip">Contrato: <?= esc($step4VisualData['contrato'] ?? '--') ?></span>
                      <span class="tp-step4-status-chip">Folio: <?= esc($step4VisualData['folio'] ?? '--') ?></span>
                    </div>
                    <?php if ($prototypeStep5CanEdit): ?>
                      <div class="tp-btn-row" style="margin-top: 12px;">
                        <button type="button" class="tp-btn primary" data-step5-save>Guardar Cobro a cliente</button>
                      </div>
                    <?php else: ?>
                      <div style="margin-top: 12px;">
                        <span class="tp-inline-note"><?= esc($prototypeStep5BlockedReason !== '' ? $prototypeStep5BlockedReason : 'Cobro a cliente no esta editable para este tramite y este perfil.') ?></span>
                      </div>
                    <?php endif; ?>
                    <div class="tp-form-feedback" data-step5-feedback hidden></div>

                    <!-- Uploader evidencias Cobro a Cliente -->
                    <div class="tp-upload-panel" style="margin-top: 16px;" data-step5-doc-panel>
                      <div class="tp-field" style="margin-bottom: 12px;">
                        <label for="tp_step5_cobro_correcto">Tipo de evidencia</label>
                        <select id="tp_step5_cobro_correcto" data-step5-doc-type<?= $prototypeStep5CanUploadDocs ? '' : ' disabled' ?>>
                          <?php foreach (($prototypeStep5Form['options']['cobroCorrecto'] ?? []) as $optionValue => $optionLabel): ?>
                            <option value="<?= esc((string) $optionValue, 'attr') ?>"><?= esc((string) $optionLabel) ?></option>
                          <?php endforeach; ?>
                        </select>
                      </div>
                      <div class="tp-dropzone-box<?= $prototypeStep5CanUploadDocs ? ' is-actionable' : ' is-disabled' ?>" data-step5-doc-dropzone>
                        <input class="tp-dropzone-input" type="file" data-step5-doc-file<?= $prototypeStep5CanUploadDocs ? '' : ' disabled' ?>>
                        <span class="tp-dropzone-kicker">Paso 5</span>
                        <strong class="tp-dropzone-title">Arrastra aquí la evidencia de cobro o haz clic para seleccionar</strong>
                        <span class="tp-dropzone-meta" data-step5-doc-selected>Sin archivo seleccionado.</span>
                      </div>
                      <?php if ($prototypeStep5CanUploadDocs): ?>
                        <div class="tp-btn-row" style="margin-top: 12px;">
                          <button type="button" class="tp-btn primary" data-step5-doc-upload>Subir evidencia de cobro</button>
                        </div>
                      <?php elseif (!empty($prototypeStep5UploadBlockedReason)): ?>
                        <div style="margin-top: 12px;">
                          <span class="tp-inline-note"><?= esc($prototypeStep5UploadBlockedReason) ?></span>
                        </div>
                      <?php endif; ?>
                      <div class="tp-form-feedback" data-step5-doc-feedback hidden></div>
                      <div class="tp-gallery" style="margin-top: 12px;">
                        <strong>Evidencias de cobro registradas</strong>
                        <div class="tp-gallery-list" data-step5-doc-gallery style="margin-top: 0;">
                          <?php if (!empty($prototypeStep5Form['docs']) && is_array($prototypeStep5Form['docs'])): ?>
                            <?php foreach ($prototypeStep5Form['docs'] as $docRow): ?>
                              <div class="tp-gallery-item">
                                <div class="tp-gallery-item-head">
                                  <div>
                                    <a class="tp-gallery-item-link" href="<?= esc(($prototypeStep5Form['fileBaseUrl'] ?? '') . rawurlencode((string) ($docRow['file'] ?? '')), 'attr') ?>" target="_blank" rel="noreferrer" data-doc-preview-url="<?= esc(($prototypeStep5Form['fileBaseUrl'] ?? '') . rawurlencode((string) ($docRow['file'] ?? '')), 'attr') ?>" data-doc-preview-name="<?= esc((string) ($docRow['file'] ?? ''), 'attr') ?>" data-doc-preview-meta="<?= esc((string) ($docRow['cobro_correcto'] ?? ''), 'attr') ?>"><?= esc((string) ($docRow['file'] ?? 'Sin nombre')) ?></a>
                                    <span class="tp-gallery-item-meta"><?= esc((string) ($docRow['cobro_correcto'] ?? 'Sin tipo')) ?></span>
                                  </div>
                                  <?php if ($prototypeStep5CanDeleteDocs): ?>
                                    <button type="button" class="tp-btn secondary small" data-step5-doc-delete-btn="<?= esc((string) ($docRow['file'] ?? ''), 'attr') ?>">Eliminar</button>
                                  <?php endif; ?>
                                </div>
                              </div>
                            <?php endforeach; ?>
                          <?php else: ?>
                            <div class="tp-gallery-item">Sin evidencias de cobro registradas</div>
                          <?php endif; ?>
                        </div>
                      </div>
                    </div>
                  </div>
                <?php endif; ?>
              </section>

              <?php if (!$useThreeRailLayout && $activeStep === 1): ?>
                  <div class="tp-dropzone">
                    <div class="tp-utility-stack">
                      <div class="tp-doc-note-box tp-subsection-card">
                        <h4 class="tp-step1-box-title">Paso 1 hoy no sube archivos aqui</h4>
                        <p class="tp-step1-box-copy">La pantalla operativa actual no muestra un uploader propio en esta etapa. Este bloque solo comunica referencias base del expediente para no inventar una superficie documental inexistente.</p>
                      </div>
                      <div class="tp-subsection-card">
                        <h4 class="tp-step1-box-title">Atajos del paso</h4>
                        <p class="tp-step1-box-copy">Sirven para comparar rapido el prototipo contra la pantalla viva o seguir recorriendo el flujo sin perder el contexto.</p>
                        <div class="tp-quick-links">
                          <a href="<?= esc($prototypeRealUpdateUrl, 'attr') ?>" class="tp-link-tile">
                            <span class="tp-link-kicker">Comparacion</span>
                            <strong>Abrir vista real del tramite</strong>
                            <p>Salta al detalle operativo del tramite <?= (int) $prototypeTramiteId ?> para contrastar estructura y jerarquia.</p>
                          </a>
                          <a href="<?= esc($stepPrototypeUrl(2), 'attr') ?>" class="tp-link-tile">
                            <span class="tp-link-kicker">Recorrido</span>
                            <strong>Ir al Paso 2 del prototipo</strong>
                            <p>Continua con la etapa operativa y su zona documental propia.</p>
                          </a>
                          <a href="<?= esc($prototypeListUrl, 'attr') ?>" class="tp-link-tile">
                            <span class="tp-link-kicker">Navegacion</span>
                            <strong>Volver al listado</strong>
                            <p>Regresa a la bandeja sin depender del footer para ubicarse.</p>
                          </a>
                        </div>
                      </div>
                    </div>
                    <div class="tp-gallery">
                      <strong>Referencias visibles</strong>
                      <div class="tp-gallery-list">
                        <?php foreach ($currentStepDocFiles as $fileName): ?>
                          <div class="tp-gallery-item"><?= esc($fileName) ?></div>
                        <?php endforeach; ?>
                      </div>
                    </div>
                  </div>
              <?php elseif (!$useThreeRailLayout): ?>
                  <div class="tp-dropzone">
                    <div class="tp-dropzone-box">Area dummy para dropzone / uploader / evidencias del paso actual</div>
                    <div class="tp-gallery">
                      <strong>Archivos ya cargados</strong>
                      <div class="tp-gallery-list">
                        <?php foreach ($currentStepDocFiles as $fileName): ?>
                          <div class="tp-gallery-item"><?= esc($fileName) ?></div>
                        <?php endforeach; ?>
                      </div>
                    </div>
                  </div>
              <?php endif; ?>

              <div class="tp-footer">
                <p class="tp-footer-hint">La idea es que la accion principal se lea al final del cuerpo y que el panel lateral complemente con contexto y navegacion, sin robar peso a la captura.</p>
                <div class="tp-btn-row">
                  <a href="<?= base_url('/deskapp/tramitesn/tramite') ?>" class="tp-btn secondary">Volver al listado</a>
                  <a href="<?= esc($prototypeRealUpdateUrl, 'attr') ?>" class="tp-btn primary">Comparar con vista real</a>
                </div>
              </div>
            </div>

            <?php if ($useThreeRailLayout): ?>
              <aside class="tp-summary-rail tp-operational-summary-block">
                <?php if ($isOperationalBasePhase): ?>
                  <section class="tp-mini-card tp-summary-card">
                    <div class="tp-summary-section">
                      <?php if ($hasRealStepContext): ?>
                        <div class="tp-source-meta">
                          <div class="tp-source-meta-item"><span>Folio</span><strong><?= esc($prototypeReadOnlyTramite['folio'] ?? '--') ?></strong></div>
                          <div class="tp-source-meta-item"><span>Estatus</span><strong><?= esc($prototypeReadOnlyTramite['tra_status_label'] ?? 'Sin estatus') ?></strong></div>
                          <div class="tp-source-meta-item"><span>Modo</span><strong><?= esc($prototypeCurrentSurfaceMode) ?></strong></div>
                        </div>
                      <?php endif; ?>
                    </div>
                  </section>

                  <section class="tp-mini-card">
                    <h3 class="tp-mini-title" data-operational-text="checklist-title">Checklist del paso</h3>
                    <div class="tp-mini-list" data-operational-checklist-list>
                      <?php foreach ($displayChecklist as $checkItem): ?>
                        <?php $checkStatus = in_array(($checkItem['status'] ?? ''), ['done', 'pending', 'warning', 'info'], true) ? (string) $checkItem['status'] : ''; ?>
                        <div class="tp-mini-item<?= $checkStatus !== '' ? ' tp-mini-item-status is-' . esc($checkStatus) : '' ?>">
                          <?php if ($checkStatus !== ''): ?>
                            <span class="tp-mini-status-square" aria-hidden="true"></span>
                          <?php endif; ?>
                          <div class="tp-mini-item-copy">
                            <strong><?= esc($checkItem['label']) ?></strong>
                            <span><?= esc($checkItem['value']) ?></span>
                          </div>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  </section>

                  <section class="tp-mini-card">
                    <?php if ($activeStep === 1): ?>
                      <section class="tp-upload-panel tp-step2-dropzone-panel" data-step1-doc-panel style="margin-top: 0;">
                        <div class="tp-step4-status-row" style="margin-bottom: 12px;">
                          <span class="tp-step4-status-chip" data-step1-doc-count><?= esc($step1DocProgressLabel) ?></span>
                          <span class="tp-step4-status-chip" data-step1-doc-uploaded-total><?= esc((string) $step1UploadedTotalDocs) ?> cargado(s)</span>
                          <span class="tp-step4-status-chip<?= $prototypeStep1DocsCanUpload ? ' is-success' : '' ?>"><?= esc($prototypeStep1DocsCanUpload ? 'Editable' : 'Solo lectura') ?></span>
                        </div>
                        <div class="tp-field" style="margin-bottom: 12px;">
                          <label for="tp_step1_documento_id">Documento a cargar</label>
                          <select id="tp_step1_documento_id" class="tp-doc-select" data-step1-doc-type<?= $prototypeStep1DocsCanUpload ? '' : ' disabled' ?>>
                            <option value="">Selecciona un documento</option>
                            <?php foreach (($prototypeStep1DocsForm['options']['documentTypes'] ?? []) as $optionValue => $optionLabel): ?>
                              <?php $optionMeta = $prototypeStep1DocsForm['options']['documentTypeMeta'][$optionValue] ?? []; ?>
                              <?php $optionOriginLabel = !empty($optionMeta['isConfigured']) ? 'Ligado' : 'Catálogo'; ?>
                              <option
                                value="<?= esc((string) $optionValue, 'attr') ?>"
                                data-doc-name="<?= esc((string) ($optionMeta['documento_nombre'] ?? $optionLabel), 'attr') ?>"
                                data-doc-configured="<?= !empty($optionMeta['isConfigured']) ? '1' : '0' ?>"
                                data-doc-badge="<?= esc((string) ($optionMeta['sourceBadge'] ?? 'Catálogo general'), 'attr') ?>"
                                data-doc-tone="<?= esc((string) ($optionMeta['sourceTone'] ?? 'neutral'), 'attr') ?>"
                              ><?= esc((string) $optionLabel . ' · ' . $optionOriginLabel) ?></option>
                            <?php endforeach; ?>
                          </select>
                          <div class="tp-doc-legend">
                            <span class="tp-step4-status-chip is-success">Ligado al tipo</span>
                            <span class="tp-step4-status-chip is-neutral">Catálogo general</span>
                          </div>
                        </div>
                        <div class="tp-dropzone-box<?= $prototypeStep1DocsCanUpload ? ' is-actionable' : ' is-disabled' ?>" data-step1-doc-dropzone>
                          <input class="tp-dropzone-input" type="file" data-step1-doc-file<?= $prototypeStep1DocsCanUpload ? '' : ' disabled' ?>>
                          <span class="tp-dropzone-kicker">Paso 1</span>
                          <strong class="tp-dropzone-title">Arrastra aqui el documento o haz clic para elegir un archivo</strong>
                          <span class="tp-dropzone-copy">Cada archivo queda validado contra el documento_id permitido por los tipos actualmente ligados al expediente.</span>
                          <span class="tp-dropzone-meta" data-step1-doc-selected>Sin archivo seleccionado.</span>
                        </div>
                        <?php if ($prototypeStep1DocsCanUpload): ?>
                          <div class="tp-btn-row" style="margin-top: 12px;">
                            <button type="button" class="tp-btn primary" data-step1-doc-upload>Subir documento</button>
                          </div>
                        <?php elseif ($prototypeStep1DocsBlockedReason !== ''): ?>
                          <div style="margin-top: 12px;">
                            <span class="tp-inline-note"><?= esc($prototypeStep1DocsBlockedReason) ?></span>
                          </div>
                        <?php endif; ?>
                        <div class="tp-form-feedback" data-step1-doc-feedback hidden></div>
                        <div class="tp-gallery">
                          <strong>Catálogo actual</strong>
                          <div class="tp-gallery-list" data-step1-doc-gallery style="margin-top: 0;">
                            <?php if (!empty($prototypeStep1DocsForm['documents']) && is_array($prototypeStep1DocsForm['documents'])): ?>
                              <?php foreach ($prototypeStep1DocsForm['documents'] as $documentItem): ?>
                                <?php
                                  $documentId = (int) ($documentItem['documento_id'] ?? 0);
                                  $hasFile = !empty($documentItem['has_file']);
                                  $fileName = (string) ($documentItem['file'] ?? '');
                                  $fileUrl = (string) ($documentItem['file_url'] ?? '');
                                  $sourceBadge = trim((string) ($documentItem['source_badge'] ?? 'Catálogo general'));
                                  $sourceTone = !empty($documentItem['source_tone']) && (string) $documentItem['source_tone'] === 'success' ? ' is-success' : ' is-neutral';
                                  $docMetaParts = [];
                                  $docMetaParts[] = !empty($documentItem['is_required']) ? 'Obligatorio' : 'Opcional';
                                  if (!empty($documentItem['source_types_label'])) {
                                    $docMetaParts[] = (string) $documentItem['source_types_label'];
                                  }
                                  $docMetaParts[] = $hasFile ? ((string) ($documentItem['status_label'] ?? 'Cargado')) : 'Pendiente';
                                ?>
                                <div class="tp-gallery-item">
                                  <div class="tp-gallery-item-head">
                                    <div>
                                      <?php if ($hasFile && $fileUrl !== ''): ?>
                                        <a class="tp-gallery-item-link" href="<?= esc($fileUrl, 'attr') ?>" target="_blank" rel="noreferrer"><?= esc($fileName) ?></a>
                                      <?php else: ?>
                                        <strong><?= esc((string) ($documentItem['documento_nombre'] ?? 'Documento')) ?></strong>
                                      <?php endif; ?>
                                      <span class="tp-step4-status-chip<?= $sourceTone ?>" style="margin-top: 6px;"><?= esc($sourceBadge) ?></span>
                                      <span class="tp-gallery-item-meta"><?= esc(implode(' · ', array_filter($docMetaParts))) ?></span>
                                    </div>
                                    <?php if ($prototypeStep1DocsCanDelete && $hasFile && $documentId > 0 && $fileName !== ''): ?>
                                      <button type="button" class="tp-btn secondary small" data-step1-doc-delete="<?= esc($fileName, 'attr') ?>" data-step1-doc-id="<?= $documentId ?>">Eliminar</button>
                                    <?php endif; ?>
                                  </div>
                                </div>
                              <?php endforeach; ?>
                            <?php else: ?>
                              <div class="tp-gallery-item">Sin catálogo documental configurado para los tipos ligados del expediente.</div>
                            <?php endif; ?>
                          </div>
                        </div>
                        <?php if (!$prototypeStep1DocsCanDelete && $prototypeStep1DocsDeleteBlockedReason !== ''): ?>
                          <div style="margin-top: 10px;">
                            <span class="tp-inline-note"><?= esc($prototypeStep1DocsDeleteBlockedReason) ?></span>
                          </div>
                        <?php endif; ?>
                      </section>
                    <?php else: ?>
                      <div class="tp-upload-panel tp-step2-dropzone-panel" style="margin-top: 0;">
                        <div class="tp-upload-head tp-step2-dropzone-head">
                          <strong>Dropzone pago de derechos</strong>
                        </div>
                        <div class="tp-dropzone-box<?= $prototypeStep2CanUploadDocs ? ' is-actionable' : ' is-disabled' ?>" data-step2-doc-dropzone>
                          <input class="tp-dropzone-input" type="file" data-step2-doc-file<?= $prototypeStep2CanUploadDocs ? '' : ' disabled' ?>>
                          <span class="tp-dropzone-kicker">Pago de derechos</span>
                          <strong class="tp-dropzone-title">Arrastra aqui comprobantes o haz clic para elegir un archivo</strong>
                          <span class="tp-dropzone-copy">La zona documental mantiene upload inmediato sobre el endpoint real y replica el mismo lenguaje visual del resto del flujo.</span>
                          <span class="tp-dropzone-meta" data-step2-doc-selected>Sin archivo seleccionado.</span>
                        </div>
                        <?php if ($prototypeStep2CanUploadDocs): ?>
                          <div class="tp-btn-row" style="margin-top: 12px;">
                            <button type="button" class="tp-btn primary" data-step2-doc-upload>Subir comprobante</button>
                          </div>
                        <?php elseif ($prototypeStep2DocsBlockedReason !== ''): ?>
                          <div style="margin-top: 12px;">
                            <span class="tp-inline-note"><?= esc($prototypeStep2DocsBlockedReason) ?></span>
                          </div>
                        <?php endif; ?>
                        <div class="tp-form-feedback" data-step2-doc-feedback hidden></div>
                        <div class="tp-gallery">
                          <strong>Soporte actual</strong>
                          <div class="tp-gallery-list" data-step2-doc-gallery style="margin-top: 0;">
                            <?php foreach ($step2DocPreviewItems as $docItem): ?>
                              <div class="tp-gallery-item"><?= esc($docItem) ?></div>
                            <?php endforeach; ?>
                          </div>
                        </div>
                        <?php if (!$prototypeStep2CanDeleteDocs && $prototypeStep2DeleteBlockedReason !== ''): ?>
                          <div style="margin-top: 10px;">
                            <span class="tp-inline-note"><?= esc($prototypeStep2DeleteBlockedReason) ?></span>
                          </div>
                        <?php endif; ?>
                      </div>
                    <?php endif; ?>
                  </section>

                  <section class="tp-mini-card">
                    <h3 class="tp-mini-title">Siguientes fases</h3>
                    <p class="tp-mini-copy">El centro ahora acompana la misma altura del bloque operativo izquierdo, para que Paso 2 y Paso 3 tengan apoyo en la misma banda visual.</p>
                    <div class="tp-finance-preview">
                      <div class="tp-finance-card is-outgoing">
                        <span class="tp-finance-kicker">Paso 4</span>
                        <strong>Pago a gestor</strong>
                        <p>Se habilita despues de evidencias finales y se trata como salida financiera independiente.</p>
                      </div>
                      <div class="tp-finance-card is-incoming">
                        <span class="tp-finance-kicker">Paso 5</span>
                        <strong>Cobro a cliente</strong>
                        <p>Zona posterior de recuperacion, visual y conceptualmente separada del pago a gestor.</p>
                      </div>
                    </div>
                    <div class="tp-quick-links" style="margin-top: 12px;">
                      <a href="<?= esc($prototypeRealUpdateUrl, 'attr') ?>" class="tp-link-tile">
                        <span class="tp-link-kicker">Comparacion</span>
                        <strong>Abrir vista real del tramite</strong>
                        <p>Contrasta esta fase integrada contra la pantalla operativa actual del tramite <?= (int) $prototypeTramiteId ?>.</p>
                      </a>
                      <a href="<?= esc($stepPrototypeUrl(4), 'attr') ?>" class="tp-link-tile">
                        <span class="tp-link-kicker">Siguiente fase</span>
                        <strong>Ir a Pago a gestor</strong>
                        <p>Salta directamente a la primera fase financiera una vez cerrado el frente operativo.</p>
                      </a>
                      <a href="<?= esc($prototypeListUrl, 'attr') ?>" class="tp-link-tile">
                        <span class="tp-link-kicker">Navegacion</span>
                        <strong>Volver al listado</strong>
                        <p>Regresa a la bandeja sin depender del footer para ubicarse.</p>
                      </a>
                    </div>
                  </section>
                <?php else: ?>
                  <section class="tp-mini-card tp-summary-card">
                    <div class="tp-summary-section">
                      <h3 class="tp-mini-title">Contexto previo listo</h3>
                      <p class="tp-mini-copy">Aqui se concentra lo indispensable de los pasos 1 a 3 para que la captura financiera no pierda contexto.</p>
                      <div class="tp-save-contracts">
                        <?php foreach ($step4PreviousStageCards as $stageCard): ?>
                          <div class="tp-save-item">
                            <span class="tp-save-kicker"><?= esc($stageCard['label']) ?></span>
                            <strong class="tp-save-endpoint"><?= esc($stageCard['endpoint']) ?></strong>
                            <p><?= esc($stageCard['note']) ?></p>
                          </div>
                        <?php endforeach; ?>
                      </div>
                    </div>

                    <?php if ($hasRealStepContext): ?>
                      <div class="tp-summary-section tp-source-card">
                        <h3 class="tp-mini-title">Contexto real del tramite</h3>
                        <p class="tp-mini-copy"><?= esc($prototypeRealContextCopy) ?></p>
                        <div class="tp-source-meta">
                          <div class="tp-source-meta-item"><span>ID</span><strong><?= (int) ($prototypeReadOnlyTramite['id'] ?? $prototypeTramiteId) ?></strong></div>
                          <div class="tp-source-meta-item"><span>Folio</span><strong><?= esc($prototypeReadOnlyTramite['folio'] ?? '--') ?></strong></div>
                          <div class="tp-source-meta-item"><span>Estatus</span><strong><?= esc($prototypeReadOnlyTramite['tra_status_label'] ?? 'Sin estatus') ?></strong></div>
                          <div class="tp-source-meta-item"><span>Contrato</span><strong><?= esc($prototypeReadOnlyTramite['contrato'] ?? '--') ?></strong></div>
                          <div class="tp-source-meta-item"><span>Documentos previos</span><strong><?= esc((string) count($step4VisualData['evidence_docs'] ?? [])) ?></strong></div>
                        </div>
                      </div>
                    <?php endif; ?>
                  </section>

                  <section class="tp-mini-card">
                    <h3 class="tp-mini-title" data-operational-text="checklist-title"><?= esc($displayChecklistTitle) ?></h3>
                    <p class="tp-mini-copy">Checklist financiero para validar captura, documentos y salida posterior sin romper el hilo del expediente.</p>
                    <div class="tp-mini-list" data-operational-checklist-list>
                      <?php foreach ($displayChecklist as $checkItem): ?>
                        <?php $checkStatus = in_array(($checkItem['status'] ?? ''), ['done', 'pending', 'warning', 'info'], true) ? (string) $checkItem['status'] : ''; ?>
                        <div class="tp-mini-item<?= $checkStatus !== '' ? ' tp-mini-item-status is-' . esc($checkStatus) : '' ?>">
                          <?php if ($checkStatus !== ''): ?>
                            <span class="tp-mini-status-square" aria-hidden="true"></span>
                          <?php endif; ?>
                          <div class="tp-mini-item-copy">
                            <strong><?= esc($checkItem['label']) ?></strong>
                            <span><?= esc($checkItem['value']) ?></span>
                          </div>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  </section>

                  <section class="tp-mini-card">
                    <h3 class="tp-mini-title" data-operational-text="save-title"><?= esc($displaySaveContractsTitle) ?></h3>
                    <p class="tp-mini-copy">Estos son los guardados reales que impactan esta fase y su continuidad hacia cobranza.</p>
                    <div class="tp-save-contracts" data-operational-save-list>
                      <?php foreach ($displayStepSaveContracts as $saveContract): ?>
                        <div class="tp-save-item">
                          <span class="tp-save-kicker"><?= esc($saveContract['label']) ?></span>
                          <strong class="tp-save-endpoint"><?= esc($saveContract['endpoint']) ?></strong>
                          <p><?= esc($saveContract['note']) ?></p>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  </section>

                  <?php if ($activeStep === 4): ?>
                    <section class="tp-mini-card" data-step4-doc-panel>
                      <span class="tp-compact-side-kicker">Dropzone del paso</span>
                      <h3 class="tp-mini-title">Documentos de pago a gestor</h3>
                      <p class="tp-mini-copy">Sube aqui los dos soportes del pago al gestor y revisalos sin salir de esta misma pantalla.</p>
                      <div class="tp-step4-status-row" style="margin-top: 12px; margin-bottom: 12px;">
                        <span class="tp-step4-status-chip<?= !empty($step4VisualData['has_factura_gestor']) ? ' is-success' : '' ?>" data-step4-chip="factura_gestor">Factura gestor</span>
                        <span class="tp-step4-status-chip<?= !empty($step4VisualData['has_comprobante_pago']) ? ' is-success' : '' ?>" data-step4-chip="comprobante_pago">Comprobante de pago</span>
                        <span class="tp-step4-status-chip<?= count($step4VisualData['payment_docs'] ?? []) >= 2 ? ' is-success' : '' ?>" data-step4-doc-count><?= count($step4VisualData['payment_docs'] ?? []) ?>/2</span>
                        <span class="tp-step4-status-chip<?= $prototypeStep4CanUploadDocs ? ' is-success' : '' ?>"><?= esc($prototypeStep4CanUploadDocs ? 'Editable' : 'Solo lectura') ?></span>
                      </div>
                      <div class="tp-field-grid" style="margin-top: 12px;">
                        <div class="tp-field">
                          <label for="tp_step4_comprobante_final">Tipo de documento de pago</label>
                          <select id="tp_step4_comprobante_final" data-step4-doc-type<?= $prototypeStep4CanUploadDocs ? '' : ' disabled' ?>>
                            <?php foreach (($prototypeStep4Form['options']['comprobanteFinal'] ?? []) as $optionValue => $optionLabel): ?>
                              <option value="<?= esc((string) $optionValue, 'attr') ?>"><?= esc((string) $optionLabel) ?></option>
                            <?php endforeach; ?>
                          </select>
                        </div>
                      </div>
                      <div class="tp-upload-panel" style="margin-top: 12px;">
                        <div class="tp-upload-head">
                          <strong>Carga documental del pago</strong>
                          <p>Se esperan dos soportes: factura del gestor y comprobante de pago.</p>
                        </div>
                        <div class="tp-dropzone-box<?= $prototypeStep4CanUploadDocs ? ' is-actionable' : ' is-disabled' ?>" data-step4-doc-dropzone>
                          <input id="tp_step4_doc_file" class="tp-dropzone-input" type="file" data-step4-doc-file<?= $prototypeStep4CanUploadDocs ? '' : ' disabled' ?>>
                          <span class="tp-dropzone-kicker">Pago a gestor</span>
                          <strong class="tp-dropzone-title">Arrastra aqui factura o comprobante de pago, o haz clic para seleccionarlo</strong>
                          <span class="tp-dropzone-copy">El tipo de documento se toma del selector superior y viaja al endpoint real de Pago a Gestor.</span>
                          <span class="tp-dropzone-meta" data-step4-doc-selected>Sin archivo seleccionado.</span>
                        </div>
                      </div>
                      <?php if ($prototypeStep4CanUploadDocs): ?>
                        <div class="tp-btn-row" style="margin-top: 12px;">
                          <button type="button" class="tp-btn primary" data-step4-doc-upload>Subir documento</button>
                        </div>
                      <?php elseif ($prototypeStep4UploadBlockedReason !== ''): ?>
                        <div style="margin-top: 12px;">
                          <span class="tp-inline-note"><?= esc($prototypeStep4UploadBlockedReason) ?></span>
                        </div>
                      <?php endif; ?>
                      <div class="tp-form-feedback" data-step4-doc-feedback hidden></div>
                      <div class="tp-gallery" style="margin-top: 12px;">
                        <strong>Archivos vigentes del pago</strong>
                        <div class="tp-gallery-list" data-step4-doc-gallery style="margin-top: 0;">
                          <?php if (!empty($prototypeStep4Form['docs']) && is_array($prototypeStep4Form['docs'])): ?>
                            <?php foreach ($prototypeStep4Form['docs'] as $doc): ?>
                              <?php
                                $step4DocFile = (string) ($doc['file'] ?? '');
                                $step4DocType = (string) ($doc['comprobante_final'] ?? '');
                                $step4DocBaseUrl = rtrim((string) ($prototypeStep4Form['fileBaseUrl'] ?? ''), '/');
                                $step4DocUrl = $step4DocBaseUrl !== '' ? $step4DocBaseUrl . '/' . rawurlencode($step4DocFile) : '#';
                                $step4DocLabel = (string) (($prototypeStep4Form['options']['comprobanteFinal'][$step4DocType] ?? '') ?: ($step4DocType !== '' ? $step4DocType : 'Documento de pago'));
                                $step4IsImagePreview = (bool) preg_match('/\.(png|jpe?g|gif|webp|bmp|svg)$/i', $step4DocFile);
                              ?>
                              <div class="tp-gallery-item">
                                <?php if ($step4IsImagePreview && $step4DocUrl !== '#'): ?>
                                  <button
                                    type="button"
                                    class="tp-gallery-preview-trigger"
                                    data-doc-preview-url="<?= esc($step4DocUrl, 'attr') ?>"
                                    data-doc-preview-name="<?= esc($step4DocFile, 'attr') ?>"
                                    data-doc-preview-meta="<?= esc($step4DocLabel, 'attr') ?>">
                                    <img class="tp-gallery-preview-image" src="<?= esc($step4DocUrl, 'attr') ?>" alt="<?= esc($step4DocFile, 'attr') ?>" loading="lazy">
                                  </button>
                                <?php endif; ?>
                                <div class="tp-gallery-item-head">
                                  <div>
                                    <a class="tp-gallery-item-link" href="<?= esc($step4DocUrl, 'attr') ?>" target="_blank" rel="noreferrer" title="<?= esc($step4DocFile, 'attr') ?>"><?= esc($step4DocFile) ?></a>
                                    <span class="tp-gallery-item-meta"><?= esc($step4DocLabel) ?></span>
                                  </div>
                                  <div class="tp-gallery-item-actions">
                                    <?php if ($step4IsImagePreview && $step4DocUrl !== '#'): ?>
                                      <button
                                        type="button"
                                        class="tp-btn ghost small"
                                        title="Vista previa de <?= esc($step4DocFile, 'attr') ?>"
                                        data-doc-preview-url="<?= esc($step4DocUrl, 'attr') ?>"
                                        data-doc-preview-name="<?= esc($step4DocFile, 'attr') ?>"
                                        data-doc-preview-meta="<?= esc($step4DocLabel, 'attr') ?>">Ver imagen</button>
                                    <?php endif; ?>
                                  </div>
                                </div>
                              </div>
                            <?php endforeach; ?>
                          <?php else: ?>
                            <div class="tp-gallery-item">Sin documentos de pago a gestor registrados</div>
                          <?php endif; ?>
                        </div>
                      </div>
                    </section>
                  <?php else: ?>
                    <section class="tp-mini-card" data-step5-doc-panel>
                      <span class="tp-compact-side-kicker">Dropzone del paso</span>
                      <h3 class="tp-mini-title">Recuperacion y soportes</h3>
                      <p class="tp-mini-copy">Sube aqui las evidencias reales del cobro a cliente y revisalas sin salir de esta misma pantalla.</p>
                      <div class="tp-step4-status-row" style="margin-top: 12px; margin-bottom: 12px;">
                        <span class="tp-step4-status-chip" data-step5-doc-count><?= count($prototypeStep5Form['docs'] ?? []) ?> soporte(s)</span>
                        <span class="tp-step4-status-chip<?= $prototypeStep5CanUploadDocs ? ' is-success' : '' ?>"><?= esc($prototypeStep5CanUploadDocs ? 'Editable' : 'Solo lectura') ?></span>
                        <span class="tp-step4-status-chip"><?= esc($stepNextActions[5]) ?></span>
                      </div>
                      <div class="tp-field-grid" style="margin-top: 12px;">
                        <div class="tp-field">
                          <label for="tp_step5_cobro_correcto">Tipo de soporte</label>
                          <select id="tp_step5_cobro_correcto" data-step5-doc-type<?= $prototypeStep5CanUploadDocs ? '' : ' disabled' ?>>
                            <?php foreach (($prototypeStep5Form['options']['cobroCorrecto'] ?? []) as $optionValue => $optionLabel): ?>
                              <option value="<?= esc((string) $optionValue, 'attr') ?>"><?= esc((string) $optionLabel) ?></option>
                            <?php endforeach; ?>
                          </select>
                        </div>
                      </div>
                      <div class="tp-upload-panel" style="margin-top: 12px;">
                        <div class="tp-upload-head">
                          <strong>Carga documental del cobro</strong>
                          <p>Clasifica cada evidencia como cobro parcial, completo u otro soporte del cierre.</p>
                        </div>
                        <div class="tp-dropzone-box<?= $prototypeStep5CanUploadDocs ? ' is-actionable' : ' is-disabled' ?>" data-step5-doc-dropzone>
                          <input id="tp_step5_doc_file" class="tp-dropzone-input" type="file" data-step5-doc-file<?= $prototypeStep5CanUploadDocs ? '' : ' disabled' ?>>
                          <span class="tp-dropzone-kicker">Cobro a cliente</span>
                          <strong class="tp-dropzone-title">Arrastra aqui la evidencia o haz clic para seleccionarla</strong>
                          <span class="tp-dropzone-copy">El tipo de soporte se toma del selector superior y viaja al endpoint real de Cobro a cliente.</span>
                          <span class="tp-dropzone-meta" data-step5-doc-selected>Sin archivo seleccionado.</span>
                        </div>
                      </div>
                      <?php if ($prototypeStep5CanUploadDocs): ?>
                        <div class="tp-btn-row" style="margin-top: 12px;">
                          <button type="button" class="tp-btn primary" data-step5-doc-upload>Subir evidencia</button>
                        </div>
                      <?php elseif ($prototypeStep5UploadBlockedReason !== ''): ?>
                        <div style="margin-top: 12px;">
                          <span class="tp-inline-note"><?= esc($prototypeStep5UploadBlockedReason) ?></span>
                        </div>
                      <?php endif; ?>
                      <div class="tp-form-feedback" data-step5-doc-feedback hidden></div>
                      <div class="tp-gallery" style="margin-top: 12px;">
                        <strong>Archivos vigentes del cobro</strong>
                        <div class="tp-gallery-list" data-step5-doc-gallery style="margin-top: 0;">
                          <?php if (!empty($prototypeStep5Form['docs']) && is_array($prototypeStep5Form['docs'])): ?>
                            <?php foreach ($prototypeStep5Form['docs'] as $doc): ?>
                              <?php
                                $step5DocFile = (string) ($doc['file'] ?? '');
                                $step5DocType = (string) ($doc['cobro_correcto'] ?? 'otro');
                                $step5DocBaseUrl = rtrim((string) ($prototypeStep5Form['fileBaseUrl'] ?? ''), '/');
                                $step5DocUrl = $step5DocBaseUrl !== '' ? $step5DocBaseUrl . '/' . rawurlencode($step5DocFile) : '#';
                                $step5DocLabel = (string) (($prototypeStep5Form['options']['cobroCorrecto'][$step5DocType] ?? '') ?: ($step5DocType !== '' ? $step5DocType : 'Soporte de cobro'));
                                $step5IsImagePreview = (bool) preg_match('/\.(png|jpe?g|gif|webp|bmp|svg)$/i', $step5DocFile);
                              ?>
                              <div class="tp-gallery-item">
                                <?php if ($step5IsImagePreview && $step5DocUrl !== '#'): ?>
                                  <button
                                    type="button"
                                    class="tp-gallery-preview-trigger"
                                    data-doc-preview-url="<?= esc($step5DocUrl, 'attr') ?>"
                                    data-doc-preview-name="<?= esc($step5DocFile, 'attr') ?>"
                                    data-doc-preview-meta="<?= esc($step5DocLabel, 'attr') ?>">
                                    <img class="tp-gallery-preview-image" src="<?= esc($step5DocUrl, 'attr') ?>" alt="<?= esc($step5DocFile, 'attr') ?>" loading="lazy">
                                  </button>
                                <?php endif; ?>
                                <div class="tp-gallery-item-head">
                                  <div>
                                    <a class="tp-gallery-item-link" href="<?= esc($step5DocUrl, 'attr') ?>" target="_blank" rel="noreferrer" title="<?= esc($step5DocFile, 'attr') ?>"><?= esc($step5DocFile) ?></a>
                                    <span class="tp-gallery-item-meta"><?= esc($step5DocLabel) ?></span>
                                  </div>
                                  <div class="tp-gallery-item-actions">
                                    <?php if ($step5IsImagePreview && $step5DocUrl !== '#'): ?>
                                      <button
                                        type="button"
                                        class="tp-btn ghost small"
                                        title="Vista previa de <?= esc($step5DocFile, 'attr') ?>"
                                        data-doc-preview-url="<?= esc($step5DocUrl, 'attr') ?>"
                                        data-doc-preview-name="<?= esc($step5DocFile, 'attr') ?>"
                                        data-doc-preview-meta="<?= esc($step5DocLabel, 'attr') ?>">Ver imagen</button>
                                    <?php endif; ?>
                                    <?php if ($prototypeStep5CanDeleteDocs): ?>
                                      <button type="button" class="tp-btn secondary small" data-step5-doc-delete="<?= esc($step5DocFile, 'attr') ?>">Eliminar</button>
                                    <?php endif; ?>
                                  </div>
                                </div>
                              </div>
                            <?php endforeach; ?>
                          <?php else: ?>
                            <div class="tp-gallery-item">Sin evidencias de cobro registradas</div>
                          <?php endif; ?>
                        </div>
                      </div>
                      <?php if (!$prototypeStep5CanDeleteDocs && $prototypeStep5DeleteBlockedReason !== ''): ?>
                        <div style="margin-top: 10px;">
                          <span class="tp-inline-note"><?= esc($prototypeStep5DeleteBlockedReason) ?></span>
                        </div>
                      <?php endif; ?>
                    </section>
                  <?php endif; ?>

                  <section class="tp-mini-card">
                    <span class="tp-compact-side-kicker"><?= esc($activeStep === 5 ? 'Continuidad del cobro' : 'Apoyo del paso') ?></span>
                    <h3 class="tp-mini-title">Documentos y atajos</h3>
                    <p class="tp-mini-copy"><?= esc($activeStep === 5
                      ? 'El carril central conserva referencias previas y accesos para revisar el expediente sin volver a mezclar cobranza con pago a gestor.'
                      : 'El carril central sigue mostrando soportes previos y accesos de continuidad para que la fase financiera no pierda el hilo del expediente.') ?></p>
                    <div class="tp-gallery">
                      <strong><?= esc($activeStep === 5 ? 'Documentos acumulados antes del cobro' : 'Documentos acumulados del tramo previo') ?></strong>
                      <div class="tp-gallery-list" style="margin-top: 0;">
                        <?php foreach ($step3EvidenceDocItems as $docItem): ?>
                          <div class="tp-gallery-item"><?= esc($docItem) ?></div>
                        <?php endforeach; ?>
                      </div>
                    </div>
                    <div class="tp-quick-links" style="margin-top: 12px;">
                      <a href="<?= esc($prototypeRealUpdateUrl, 'attr') ?>" class="tp-link-tile">
                        <span class="tp-link-kicker">Comparacion</span>
                        <strong>Abrir vista real del tramite</strong>
                        <p><?= esc($activeStep === 5
                          ? 'Contrasta esta lectura de recuperacion contra la pantalla operativa actual del tramite ' . (int) $prototypeTramiteId . '.'
                          : 'Contrasta este frente financiero contra la pantalla operativa actual del tramite ' . (int) $prototypeTramiteId . '.') ?></p>
                      </a>
                      <?php if ($activeStep === 5): ?>
                        <a href="<?= esc($stepPrototypeUrl(4), 'attr') ?>" class="tp-link-tile">
                          <span class="tp-link-kicker">Fase previa</span>
                          <strong>Volver a Pago a gestor</strong>
                          <p>Regresa a la salida financiera previa para revisar comprobantes y handoff antes de cobranza.</p>
                        </a>
                      <?php endif; ?>
                      <a href="<?= esc($prototypeListUrl, 'attr') ?>" class="tp-link-tile">
                        <span class="tp-link-kicker">Navegacion</span>
                        <strong>Volver al listado</strong>
                        <p>Regresa a la bandeja sin perder el contexto del expediente actual.</p>
                      </a>
                    </div>
                  </section>
                <?php endif; ?>
              </aside>
            <?php endif; ?>

            <?php if ($useThreeRailLayout): ?>
              <aside class="tp-activity-rail">
                <?php if ($prototypeStep4NotesCanView): ?>
                  <section class="tp-side-card" data-step-row-notes="4">
                    <span class="tp-side-kicker">Seguimiento interno · Paso 4</span>
                    <h3 class="tp-side-title">Notas de Pago a gestor</h3>

                    <?php if ($prototypeStep4NotesCanAdd): ?>
                      <div class="tp-note-compose" data-step4-note-form>
                        <textarea placeholder="Escribe aqui una nota interna de seguimiento para Pago a gestor" data-step4-note-input></textarea>
                        <div class="tp-btn-row">
                          <button type="button" class="tp-btn primary" data-step4-note-save>Guardar nota interna</button>
                        </div>
                      </div>
                    <?php elseif ($prototypeStep4NotesBlockedReason !== ''): ?>
                      <div style="margin-bottom: 14px;">
                        <span class="tp-inline-note"><?= esc($prototypeStep4NotesBlockedReason) ?></span>
                      </div>
                    <?php endif; ?>

                    <div class="tp-form-feedback" data-step4-note-feedback hidden></div>

                    <div class="tp-notes tp-notes-scroll" data-step4-note-list<?= empty($prototypeStep4NotesItems) ? ' hidden' : '' ?>>
                      <?php foreach ($prototypeStep4NotesItems as $noteItem): ?>
                        <div class="tp-note-item tone-info">
                          <span class="tp-note-meta"><?= esc((string) ($noteItem['createdAtLabel'] ?? 'Sin fecha') . ' · ' . (string) ($noteItem['author'] ?? 'Sistema')) ?></span>
                          <span class="tp-note-body"><?= esc((string) ($noteItem['comment'] ?? '')) ?></span>
                        </div>
                      <?php endforeach; ?>
                    </div>
                    <div class="tp-notes-empty" data-step4-note-empty<?= empty($prototypeStep4NotesItems) ? '' : ' hidden' ?>>Todavia no hay notas internas de Pago a gestor registradas.</div>
                  </section>
                <?php endif; ?>

                <?php if ($prototypeStep5NotesCanView): ?>
                  <section class="tp-side-card" data-step-row-notes="5">
                    <span class="tp-side-kicker">Seguimiento interno · Paso 5</span>
                    <h3 class="tp-side-title">Notas de Cobro a cliente</h3>

                    <?php if ($prototypeStep5NotesCanAdd): ?>
                      <div class="tp-note-compose" data-step5-note-form>
                        <textarea placeholder="Escribe aqui una nota interna de seguimiento para Cobro a cliente" data-step5-note-input></textarea>
                        <div class="tp-btn-row">
                          <button type="button" class="tp-btn primary" data-step5-note-save>Guardar nota interna</button>
                        </div>
                      </div>
                    <?php elseif ($prototypeStep5NotesBlockedReason !== ''): ?>
                      <div style="margin-bottom: 14px;">
                        <span class="tp-inline-note"><?= esc($prototypeStep5NotesBlockedReason) ?></span>
                      </div>
                    <?php endif; ?>

                    <div class="tp-form-feedback" data-step5-note-feedback hidden></div>

                    <div class="tp-notes tp-notes-scroll" data-step5-note-list<?= empty($prototypeStep5NotesItems) ? ' hidden' : '' ?>>
                      <?php foreach ($prototypeStep5NotesItems as $noteItem): ?>
                        <div class="tp-note-item tone-info">
                          <span class="tp-note-meta"><?= esc((string) ($noteItem['createdAtLabel'] ?? 'Sin fecha') . ' · ' . (string) ($noteItem['author'] ?? 'Sistema')) ?></span>
                          <span class="tp-note-body"><?= esc((string) ($noteItem['comment'] ?? '')) ?></span>
                        </div>
                      <?php endforeach; ?>
                    </div>
                    <div class="tp-notes-empty" data-step5-note-empty<?= empty($prototypeStep5NotesItems) ? '' : ' hidden' ?>>Todavia no hay notas internas de Cobro a cliente registradas.</div>
                  </section>
                <?php endif; ?>

                <section class="tp-side-card">
                  <span class="tp-side-kicker"><?= !$isOperationalBasePhase && ($activeStep === 4 || $activeStep === 5) ? 'Bitacora general' : 'Bitacora viva' ?></span>
                  <h3 class="tp-side-title"><?= !$isOperationalBasePhase && ($activeStep === 4 || $activeStep === 5) ? 'Comentarios generales del tramite' : 'Comentarios del tramite' ?></h3>
                  <p class="tp-side-copy"><?= !$isOperationalBasePhase && ($activeStep === 4 || $activeStep === 5)
                    ? 'Este bloque sigue leyendo tra_evidencias del expediente real para no perder el contexto operativo general del servicio.'
                    : 'Este carril derecho ya lee tra_evidencias del expediente real y permite dejar observaciones sin salir de la pantalla unificada.' ?></p>

                  <?php if ($prototypeEvidenceCanAdd): ?>
                    <div class="tp-note-compose" data-prototype-evidence-form>
                      <textarea placeholder="Escribe aqui un comentario operativo para el expediente" data-prototype-evidence-input></textarea>
                      <div class="tp-btn-row">
                        <button type="button" class="tp-btn primary" data-prototype-evidence-save>Guardar comentario</button>
                      </div>
                    </div>
                  <?php elseif ($prototypeEvidenceBlockedReason !== ''): ?>
                    <div style="margin-bottom: 14px;">
                      <span class="tp-inline-note"><?= esc($prototypeEvidenceBlockedReason) ?></span>
                    </div>
                  <?php endif; ?>

                  <div class="tp-form-feedback" data-prototype-evidence-feedback hidden></div>

                  <div class="tp-notes tp-notes-scroll" data-prototype-evidence-list<?= empty($prototypeEvidenceItems) ? ' hidden' : '' ?>>
                    <?php foreach ($prototypeEvidenceItems as $noteItem): ?>
                      <div class="tp-note-item tone-info">
                        <span class="tp-note-meta"><?= esc((string) ($noteItem['createdAtLabel'] ?? 'Sin fecha') . ' · ' . (string) ($noteItem['author'] ?? 'Sistema')) ?></span>
                        <span class="tp-note-body"><?= esc((string) ($noteItem['comment'] ?? '')) ?></span>
                      </div>
                    <?php endforeach; ?>
                  </div>
                  <div class="tp-notes-empty" data-prototype-evidence-empty<?= empty($prototypeEvidenceItems) ? '' : ' hidden' ?>>Todavia no hay comentarios operativos guardados en este expediente.</div>
                </section>

              </aside>
            <?php else: ?>
            <div class="tp-inner-side">
                <section class="tp-mini-card">
                  <h3 class="tp-mini-title" data-operational-text="summary-title"><?= esc($displaySummaryTitle) ?></h3>
                  <p class="tp-mini-copy">Mini panel dentro del cuerpo para destacar lo que falta sin mandar todo al lateral principal.</p>
                  <div class="tp-mini-list" data-operational-summary-list>
                    <?php foreach ($displayMiniSummary as $summaryItem): ?>
                      <div class="tp-mini-item"><strong><?= esc($summaryItem['label']) ?></strong><span><?= esc($summaryItem['value']) ?></span></div>
                    <?php endforeach; ?>
                  </div>
                </section>

                <?php if ($hasRealStepContext): ?>
                  <section class="tp-mini-card tp-source-card">
                    <h3 class="tp-mini-title">Contexto real del tramite</h3>
                    <p class="tp-mini-copy"><?= esc($prototypeRealContextCopy) ?></p>
                    <div class="tp-source-meta">
                      <div class="tp-source-meta-item"><span>ID</span><strong><?= (int) ($prototypeReadOnlyTramite['id'] ?? $prototypeTramiteId) ?></strong></div>
                      <div class="tp-source-meta-item"><span>Folio</span><strong><?= esc($prototypeReadOnlyTramite['folio'] ?? '--') ?></strong></div>
                      <div class="tp-source-meta-item"><span>Estatus</span><strong><?= esc($prototypeReadOnlyTramite['tra_status_label'] ?? 'Sin estatus') ?></strong></div>
                      <div class="tp-source-meta-item"><span><?= $isOperationalBasePhase ? 'Modo' : ($activeStep === 4 ? 'Contrato' : 'Contrato') ?></span><strong><?= esc($isOperationalBasePhase ? $prototypeCurrentSurfaceMode : ($prototypeReadOnlyTramite['contrato'] ?? '--')) ?></strong></div>
                      <div class="tp-source-meta-item"><span><?= $isOperationalBasePhase ? 'Superficies' : 'Documentos previos' ?></span><strong><?= esc($isOperationalBasePhase ? 'tramite + asociados + cierre' : (string) count($step4VisualData['evidence_docs'] ?? [])) ?></strong></div>
                    </div>
                  </section>
                <?php endif; ?>

                <section class="tp-mini-card">
                  <h3 class="tp-mini-title" data-operational-text="checklist-title"><?= esc($displayChecklistTitle) ?></h3>
                  <p class="tp-mini-copy">Ayuda corta, no invasiva.</p>
                  <div class="tp-mini-list" data-operational-checklist-list>
                    <?php foreach ($displayChecklist as $checkItem): ?>
                      <?php $checkStatus = in_array(($checkItem['status'] ?? ''), ['done', 'pending', 'warning', 'info'], true) ? (string) $checkItem['status'] : ''; ?>
                      <div class="tp-mini-item<?= $checkStatus !== '' ? ' tp-mini-item-status is-' . esc($checkStatus) : '' ?>">
                        <?php if ($checkStatus !== ''): ?>
                          <span class="tp-mini-status-square" aria-hidden="true"></span>
                        <?php endif; ?>
                        <div class="tp-mini-item-copy">
                          <strong><?= esc($checkItem['label']) ?></strong>
                          <span><?= esc($checkItem['value']) ?></span>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  </div>
                </section>

                <section class="tp-mini-card">
                  <h3 class="tp-mini-title" data-operational-text="save-title"><?= esc($displaySaveContractsTitle) ?></h3>
                  <p class="tp-mini-copy">Cada tramo conserva sus guardados reales. La UI puede cambiar; la secuencia operativa no debe inventarse.</p>
                  <div class="tp-save-contracts" data-operational-save-list>
                    <?php foreach ($displayStepSaveContracts as $saveContract): ?>
                      <div class="tp-save-item">
                        <span class="tp-save-kicker"><?= esc($saveContract['label']) ?></span>
                        <strong class="tp-save-endpoint"><?= esc($saveContract['endpoint']) ?></strong>
                        <p><?= esc($saveContract['note']) ?></p>
                      </div>
                    <?php endforeach; ?>
                  </div>
                </section>

                <section class="tp-mini-card">
                  <h3 class="tp-mini-title">Zonas financieras posteriores</h3>
                  <p class="tp-mini-copy">Preview intencional para que el flujo ya comunique que las dos etapas financieras existen, pero no se mezclan.</p>
                  <div class="tp-finance-preview">
                    <div class="tp-finance-card is-outgoing">
                      <span class="tp-finance-kicker">Paso 4</span>
                      <strong>Pago a gestor</strong>
                      <p>Se habilita despues de evidencias finales y se trata como salida financiera independiente.</p>
                    </div>
                    <div class="tp-finance-card is-incoming">
                      <span class="tp-finance-kicker">Paso 5</span>
                      <strong>Cobro a cliente</strong>
                      <p>Zona posterior de recuperacion, visual y conceptualmente separada del pago a gestor.</p>
                    </div>
                  </div>
                </section>
            </div>
            <?php endif; ?>
          </div>
        </main>

        <?php if (!$useThreeRailLayout): ?>
        <aside class="tp-side-panel tp-side-stack">
          <section class="tp-side-card">
            <span class="tp-side-kicker">Zona dropdown</span>
            <h3 class="tp-side-title">Acciones y navegacion</h3>
            <p class="tp-side-copy">El panel lateral acompana, pero no pelea por protagonismo con el formulario.</p>

            <div class="tp-select-mock">Vista completa del tramite</div>

            <div class="tp-action-list">
              <div class="tp-action-item"><strong>Ir a seccion</strong><span>Generales, Gestion, Evidencias finales, Pago a gestor, Cobro a cliente</span></div>
              <div class="tp-action-item"><strong>Documentos del paso</strong><span>Visor, historial y descargas de la etapa activa</span></div>
              <div class="tp-action-item"><strong>Cambio de estatus</strong><span>Accion condicionada por disponibilidad y completitud</span></div>
              <div class="tp-action-item"><strong>Accion rapida</strong><span>Enviar a validacion o dejar observacion</span></div>
            </div>
          </section>

          <section class="tp-side-card">
            <span class="tp-side-kicker">Zona historico</span>
            <h3 class="tp-side-title">Notas y bitacora</h3>
            <p class="tp-side-copy">Formato de timeline compacto y legible. Lo importante es entender que paso y quien lo hizo.</p>

            <div class="tp-notes">
              <div class="tp-note-item tone-success">
                <span class="tp-note-meta">07/06/2026 · 09:32 · Luisa Flores</span>
                <strong>Gestion actualizada</strong>
                <span>Se confirmo la asignacion del gestor y el tramite quedo listo para completar documentos de la etapa.</span>
              </div>
              <div class="tp-note-item tone-warning">
                <span class="tp-note-meta">06/06/2026 · 18:10 · Nora Medrano</span>
                <strong>Documento pendiente</strong>
                <span>Falta adjuntar el comprobante oficial para liberar el paso de evidencias finales.</span>
              </div>
              <div class="tp-note-item tone-danger">
                <span class="tp-note-meta">05/06/2026 · 11:24 · Sistema</span>
                <strong>Observacion operativa</strong>
                <span>Pago a gestor y cobro a cliente deben seguir separados como zonas financieras posteriores al cierre documental.</span>
              </div>
            </div>
          </section>
        </aside>
        <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <dialog class="tp-modal" data-step2-approve-modal>
    <div class="tp-modal-card">
      <div class="tp-modal-head">
        <div>
          <strong>Confirmar aprobacion</strong>
          <p>Esta accion autorizara el tramite y actualizara su estatus a Pago a gestor.</p>
        </div>
        <button type="button" class="tp-btn ghost small" data-step2-approve-close>Cerrar</button>
      </div>
      <div class="tp-modal-copy">
        <p>Estas de acuerdo con aprobar este tramite?</p>
        <p>Al continuar, el flujo saltara al siguiente frente y los pasos operativos quedaran en el estatus ya autorizado.</p>
      </div>
      <div class="tp-btn-row tp-modal-actions">
        <button type="button" class="tp-btn secondary" data-step2-approve-cancel>Cancelar</button>
        <button type="button" class="tp-btn primary" data-step2-approve-confirm>Si, aprobar tramite</button>
      </div>
    </div>
  </dialog>
  <dialog class="tp-modal" data-step4-doc-delete-modal>
    <div class="tp-modal-card">
      <div class="tp-modal-head">
        <div>
          <strong>Confirmar eliminacion</strong>
          <p>Esta accion borrara el archivo y tambien eliminara su registro en la base de datos.</p>
        </div>
        <button type="button" class="tp-btn ghost small" data-step4-doc-delete-close>Cerrar</button>
      </div>
      <div class="tp-modal-copy">
        <p>Estas seguro de eliminar este documento de pago a gestor?</p>
        <p><strong data-step4-doc-delete-name>Sin documento seleccionado.</strong></p>
      </div>
      <div class="tp-btn-row tp-modal-actions">
        <button type="button" class="tp-btn secondary" data-step4-doc-delete-cancel>Cancelar</button>
        <button type="button" class="tp-btn primary" data-step4-doc-delete-confirm>Si, eliminar documento</button>
      </div>
    </div>
  </dialog>
  <dialog class="tp-modal" data-step5-doc-delete-modal>
    <div class="tp-modal-card">
      <div class="tp-modal-head">
        <div>
          <strong>Confirmar eliminacion</strong>
          <p>Esta accion borrara la evidencia de cobro y tambien eliminara su registro en la base de datos.</p>
        </div>
        <button type="button" class="tp-btn ghost small" data-step5-doc-delete-close>Cerrar</button>
      </div>
      <div class="tp-modal-copy">
        <p>Estas seguro de eliminar esta evidencia de cobro?</p>
        <p><strong data-step5-doc-delete-name>Sin documento seleccionado.</strong></p>
      </div>
      <div class="tp-btn-row tp-modal-actions">
        <button type="button" class="tp-btn secondary" data-step5-doc-delete-cancel>Cancelar</button>
        <button type="button" class="tp-btn primary" data-step5-doc-delete-confirm>Si, eliminar evidencia</button>
      </div>
    </div>
  </dialog>
  <dialog class="tp-modal" data-doc-preview-modal>
    <div class="tp-modal-card is-media">
      <div class="tp-modal-head">
        <div>
          <strong data-doc-preview-title>Vista previa del documento</strong>
          <p data-doc-preview-meta>Revision visual del archivo cargado en el expediente.</p>
        </div>
        <button type="button" class="tp-btn ghost small" data-doc-preview-close>Cerrar</button>
      </div>
      <div class="tp-modal-copy tp-modal-media">
        <img class="tp-modal-media-image" data-doc-preview-image alt="Vista previa del documento">
        <a class="tp-modal-media-link" data-doc-preview-link href="#" target="_blank" rel="noreferrer">Abrir archivo completo en una pestaña nueva</a>
      </div>
      <div class="tp-btn-row tp-modal-actions">
        <button type="button" class="tp-btn secondary" data-doc-preview-cancel>Cerrar</button>
      </div>
    </div>
  </dialog>
<?php if ($isOperationalBasePhase && !empty($operationalBaseClientState)): ?>
<script>
  (function() {
    console.log('[SGL Prototype] Script operativo iniciado');
    const storageKey = 'sglPrototypeOperationalStep';
    const approvalStorageKey = 'sglPrototypeStep2Approved:<?= (int) ($prototypeReadOnlyTramite['id'] ?? $prototypeTramiteId) ?>';
    const canApproveStep2 = <?= $prototypeCanApproveStep2 ? 'true' : 'false' ?>;
    const step1FormConfig = <?= json_encode($prototypeStep1Form, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE) ?: '{}' ?>;
    const step1ServicesConfig = <?= json_encode($prototypeStep1ServicesForm, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE) ?: '{}' ?>;
    const step1DocsFormConfig = <?= json_encode($prototypeStep1DocsForm, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE) ?: '{}' ?>;
    const step2FormConfig = <?= json_encode($prototypeStep2Form, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE) ?: '{}' ?>;
    const step3FormConfig = <?= json_encode($prototypeStep3Form, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE) ?: '{}' ?>;
    const evidenceFormConfig = <?= json_encode($prototypeEvidenceForm, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE) ?: '{}' ?>;
    const state = <?= json_encode($operationalBaseClientState, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE) ?: '{}' ?>;
    const root = document.querySelector('[data-operational-base-root="1"]');
    if (!root || !state || !state.steps) {
      return;
    }

    const step2Panel = root.querySelector('[data-operational-step2-panel]');
    const step2Title = root.querySelector('[data-operational-step2-title]');
    const step2Copy = root.querySelector('[data-operational-step2-copy]');
    const step2ApprovalActions = root.querySelector('[data-step2-approval-actions]');
    const step1SaveButton = root.querySelector('[data-step1-save]');
    const step1Feedback = root.querySelector('[data-step1-feedback]');
    const step1ServicesFeedback = root.querySelector('[data-step1-services-feedback]');
    const step1PrincipalSaveButton = root.querySelector('[data-step1-principal-save]');
    const step1AssociatedAddButton = root.querySelector('[data-step1-associated-add]');
    const step1DocPanel = root.querySelector('[data-step1-doc-panel]');
    const step1DocTypeSelect = root.querySelector('[data-step1-doc-type]');
    const step1DocDropzone = root.querySelector('[data-step1-doc-dropzone]');
    const step1DocFileInput = root.querySelector('[data-step1-doc-file]');
    const step1DocUploadButton = root.querySelector('[data-step1-doc-upload]');
    const step1DocFeedback = root.querySelector('[data-step1-doc-feedback]');
    const step1DocSelected = root.querySelector('[data-step1-doc-selected]');
    const step1DocGallery = root.querySelector('[data-step1-doc-gallery]');
    const step1DocCount = root.querySelector('[data-step1-doc-count]');
    const step1DocUploadedTotal = root.querySelector('[data-step1-doc-uploaded-total]');
    const step2FormNode = root.querySelector('[data-step2-live-form]');
    const step2SaveButton = root.querySelector('[data-step2-save]');
    const step2Feedback = root.querySelector('[data-step2-feedback]');
    const step2DocDropzone = root.querySelector('[data-step2-doc-dropzone]');
    const step2DocFileInput = root.querySelector('[data-step2-doc-file]');
    const step2DocUploadButton = root.querySelector('[data-step2-doc-upload]');
    const step2DocFeedback = root.querySelector('[data-step2-doc-feedback]');
    const step2DocSelected = root.querySelector('[data-step2-doc-selected]');
    const step2DocGallery = root.querySelector('[data-step2-doc-gallery]');
    const step3FormNode = root.querySelector('[data-step3-live-form]');
    const step3Dropzone = root.querySelector('[data-step3-dropzone]');
    const step3UploadButton = root.querySelector('[data-step3-upload]');
    const step3Feedback = root.querySelector('[data-step3-feedback]');
    const step3Selected = root.querySelector('[data-step3-file-selected]');
    const step3Sequence = root.querySelector('[data-operational-step3-sequence]');
    const step3Tail = root.querySelector('[data-operational-step3-tail]');
    const step3Note = root.querySelector('[data-operational-step3-note]');
    const step3GatePanel = root.querySelector('[data-step3-gate-panel]');
    const step3GateTitle = root.querySelector('[data-step3-gate-title]');
    const step3GateCopy = root.querySelector('[data-step3-gate-copy]');
    const step3GateActions = root.querySelector('[data-step3-gate-actions]');
    const step4InlinePanel = root.querySelector('[data-operational-step4-inline]');
    const evidenceInput = root.querySelector('[data-prototype-evidence-input]');
    const evidenceSaveButton = root.querySelector('[data-prototype-evidence-save]');
    const evidenceFeedback = root.querySelector('[data-prototype-evidence-feedback]');
    const evidenceList = root.querySelector('[data-prototype-evidence-list]');
    const evidenceEmpty = root.querySelector('[data-prototype-evidence-empty]');
    const step2ApproveModal = document.querySelector('[data-step2-approve-modal]');
    const step2ApproveConfirmButton = step2ApproveModal ? step2ApproveModal.querySelector('[data-step2-approve-confirm]') : null;
    const step2ApproveCancelButton = step2ApproveModal ? step2ApproveModal.querySelector('[data-step2-approve-cancel]') : null;
    const step2ApproveCloseButton = step2ApproveModal ? step2ApproveModal.querySelector('[data-step2-approve-close]') : null;
    const docPreviewModal = document.querySelector('[data-doc-preview-modal]');
    const docPreviewTitle = docPreviewModal ? docPreviewModal.querySelector('[data-doc-preview-title]') : null;
    const docPreviewMeta = docPreviewModal ? docPreviewModal.querySelector('[data-doc-preview-meta]') : null;
    const docPreviewImage = docPreviewModal ? docPreviewModal.querySelector('[data-doc-preview-image]') : null;
    const docPreviewLink = docPreviewModal ? docPreviewModal.querySelector('[data-doc-preview-link]') : null;
    const docPreviewCloseButton = docPreviewModal ? docPreviewModal.querySelector('[data-doc-preview-close]') : null;
    const docPreviewCancelButton = docPreviewModal ? docPreviewModal.querySelector('[data-doc-preview-cancel]') : null;
    const step2Inputs = {
      empresa_gestora_id: root.querySelector('[data-step2-input="empresa_gestora_id"]'),
      gestor_id: root.querySelector('[data-step2-input="gestor_id"]'),
      derechos_tramite: root.querySelector('[data-step2-input="derechos_tramite"]'),
      derechos_pago_sitio: root.querySelector('[data-step2-input="derechos_pago_sitio"]'),
      derechos_vigencia: root.querySelector('[data-step2-input="derechos_vigencia"]'),
      derechos_revol_cliente: root.querySelector('[data-step2-input="derechos_revol_cliente"]'),
      derechos_refer_banc: root.querySelector('[data-step2-input="derechos_refer_banc"]'),
    };
    const step2VigenciaField = root.querySelector('[data-step2-vigencia-field]');
    const step2VigenciaWarning = root.querySelector('[data-step2-vigencia-warning]');
    const step1Inputs = {
      cli_directo_id: root.querySelector('[data-step1-input="cli_directo_id"]'),
      cli_directo_ejecutivo_id: root.querySelector('[data-step1-input="cli_directo_ejecutivo_id"]'),
      contrato: root.querySelector('[data-step1-input="contrato"]'),
      unidad: root.querySelector('[data-step1-input="unidad"]'),
      serie: root.querySelector('[data-step1-input="serie"]'),
      placas: root.querySelector('[data-step1-input="placas"]'),
      entidad_id: root.querySelector('[data-step1-input="entidad_id"]'),
      observaciones: root.querySelector('[data-step1-input="observaciones"]'),
    };
    const step1ServiceInputs = {
      principal_tipo_id: root.querySelector('[data-step1-service-input="principal_tipo_id"]'),
      add_tipo_id: root.querySelector('[data-step1-service-input="add_tipo_id"]'),
    };
    const step3Inputs = {
      comprobante_final: root.querySelector('[data-step3-input="comprobante_final"]'),
      file: root.querySelector('[data-step3-file]'),
    };
    const step1ServicesState = {
      principalTipoId: Number(step1ServicesConfig.principalTipoId || 0),
      services: Array.isArray(step1ServicesConfig.services) ? step1ServicesConfig.services.map((service) => ({
        asociado_id: Number(service.asociado_id || 0),
        tra_tipos_id: Number(service.tra_tipos_id || 0),
        label: String(service.label || ''),
        is_principal: Boolean(service.is_principal),
      })) : [],
    };
    const step1DocState = {
      docs: Array.isArray(step1DocsFormConfig.documents) ? step1DocsFormConfig.documents.map((doc) => ({
        documento_id: Number(doc.documento_id || 0),
        documento_nombre: String(doc.documento_nombre || ''),
        is_required: Boolean(doc.is_required),
        source_types_label: String(doc.source_types_label || ''),
        has_file: Boolean(doc.has_file),
        file: String(doc.file || ''),
        file_url: String(doc.file_url || ''),
        status_label: String(doc.status_label || ''),
        comentario: String(doc.comentario || ''),
      })) : [],
      pendingFile: null,
    };
    const step2DocState = {
      docs: Array.isArray(step2FormConfig.docs) ? step2FormConfig.docs.map((doc) => ({
        file: String(doc.file || ''),
      })) : [],
      pendingFile: null,
    };
    const step3EvidenceState = {
      docs: Array.isArray(step3FormConfig.docs) ? step3FormConfig.docs.map((doc) => ({
        file: String(doc.file || ''),
        comprobante_final: String(doc.comprobante_final || ''),
      })) : [],
      hasTramiteRecibido: Boolean(step3FormConfig.hasTramiteRecibido),
      hasAcuseRecibo: Boolean(step3FormConfig.hasAcuseRecibo),
      pendingFile: null,
    };
    const isStep3FinancialGateReady = () => step3EvidenceState.hasTramiteRecibido && step3EvidenceState.hasAcuseRecibo;
    const evidenceState = {
      items: Array.isArray(evidenceFormConfig.items) ? evidenceFormConfig.items.map((item) => ({
        id: Number(item.id || 0),
        comment: String(item.comment || ''),
        author: String(item.author || 'Sistema'),
        createdAt: String(item.createdAt || ''),
        createdAtLabel: String(item.createdAtLabel || 'Sin fecha'),
      })) : [],
    };

    const getApprovalState = () => {
      return isStep2PostApprovalStage();
    };

    const setApprovalState = (value) => {
      try {
        window.sessionStorage.setItem(approvalStorageKey, value ? '1' : '0');
      } catch (error) {
      }
    };

    const setStep2ApprovalModalBusy = (isBusy) => {
      if (step2ApproveConfirmButton) {
        step2ApproveConfirmButton.disabled = isBusy;
        step2ApproveConfirmButton.textContent = isBusy ? 'Aprobando...' : 'Si, aprobar tramite';
      }
      if (step2ApproveCancelButton) {
        step2ApproveCancelButton.disabled = isBusy;
      }
      if (step2ApproveCloseButton) {
        step2ApproveCloseButton.disabled = isBusy;
      }
    };

    const closeStep2ApproveModal = () => {
      if (!step2ApproveModal) {
        return;
      }
      if (typeof step2ApproveModal.close === 'function') {
        step2ApproveModal.close();
      }
    };

    const approveStep2Real = async () => {
      const snapshot = getStep2ApprovalSnapshot();
      if (!snapshot.ready || !canApproveStep2) {
        return;
      }

      setStep2ApprovalModalBusy(true);
      setFeedback('', '');
      try {
        const formData = new FormData();
        formData.append(step2FormConfig.csrfName, step2FormConfig.csrfHash || '');
        formData.append('tramite_id', String(step2FormConfig.tramiteId || '0'));
        formData.append('status_id', String(step2FormConfig.approvalStatusId || 23));

        const response = await fetch(step2FormConfig.urls.authorize, {
          method: 'POST',
          body: formData,
          credentials: 'same-origin',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });
        const result = await response.json();
        if (!response.ok || !result.success) {
          throw new Error(buildErrorMessage(result, 'No se pudo aprobar el tramite.'));
        }

        closeStep2ApproveModal();
        setFeedback('Tramite aprobado correctamente. Redirigiendo a Pago a gestor...', 'success');
        window.location.assign(String((step2FormConfig.urls && step2FormConfig.urls.afterApprove) || window.location.href));
      } catch (error) {
        setFeedback(error.message || 'No se pudo aprobar el tramite.', 'error');
      } finally {
        setStep2ApprovalModalBusy(false);
      }
    };

    const openStep2ApproveModal = () => {
      const snapshot = getStep2ApprovalSnapshot();
      if (!snapshot.ready || !canApproveStep2) {
        return;
      }

      if (!step2ApproveModal || typeof step2ApproveModal.showModal !== 'function') {
        if (window.confirm('Estas de acuerdo con aprobar el tramite? Esta accion actualizara el estatus a Pago a gestor.')) {
          approveStep2Real();
        }
        return;
      }

      setStep2ApprovalModalBusy(false);
      step2ApproveModal.showModal();
    };

    const normalizeChecklistStatus = (status) => {
      const normalized = String(status || '').trim();
      return ['done', 'pending', 'warning', 'info'].includes(normalized) ? normalized : '';
    };

    const hasSavedValue = (value) => {
      const normalized = String(value ?? '').trim();
      return normalized !== '' && normalized !== '0';
    };

    const getSavedStep2Value = (name) => String((step2FormConfig.values && step2FormConfig.values[name]) || '').trim();

    const getSavedStep2ApprovalSnapshot = () => {
      const monto = getSavedStep2Value('derechos_tramite');
      const formaPago = getSavedStep2Value('derechos_revol_cliente');
      const referencia = getSavedStep2Value('derechos_refer_banc');
      const missing = [];
      if (monto === '' || monto === '0' || monto === '0.00') {
        missing.push('Monto pago de derechos');
      }
      if (formaPago === '') {
        missing.push('Forma de pago');
      }
      if (referencia === '') {
        missing.push('Referencia bancaria');
      }
      return {
        ready: missing.length === 0,
        missing,
      };
    };

    const isStep2PostApprovalStage = () => Number(step2FormConfig.currentStep || 0) > 3 || Boolean(step2FormConfig.isApprovedLock);

    const getStep2ApprovalDisplay = (snapshot, isApproved = false) => {
      if (!snapshot.ready) {
        return {
          title: 'Falta aprobacion para continuar',
          copy: 'El paso no se destraba por subir comprobantes. Primero debe quedar completo el bloque obligatorio de derechos y despues mostrarse el boton de aprobacion para continuar.',
          note: 'Completa los campos obligatorios para habilitar la aprobacion.',
          status: 'pending',
          value: 'Pendiente',
        };
      }

      if (canApproveStep2) {
        return {
          title: isApproved ? 'Aprobado en esta sesion' : 'Listo para aprobacion',
          copy: isApproved
            ? 'La aprobacion ya se marco dentro de esta sesion del prototipo. El siguiente frente operativo es cerrar evidencias finales para liberar en paralelo Pago a gestor y Cobro a cliente.'
            : 'El tramo ya cumple los campos obligatorios reales. Los comprobantes de linea de captura pueden seguir subiendose aqui como soporte opcional, pero el siguiente gesto esperado es aprobar el tramite.',
          note: isApproved
            ? 'Marca visual local del prototipo: la persistencia real sigue viviendo en el flujo original.'
            : 'Al aprobar, la siguiente lectura operativa es cerrar evidencias finales en Paso 3.',
          status: isApproved ? 'done' : 'warning',
          value: isApproved ? 'Aprobado' : 'Listo para aprobar',
        };
      }

      if (Boolean(step2FormConfig.isLockedStatus)) {
        return {
          title: 'Tramite cerrado',
          copy: 'El tramite esta concluido o cancelado. La autorizacion ya no aplica en este frente.',
          note: 'Tramite en modo de solo lectura.',
          status: 'info',
          value: 'Cerrado',
        };
      }

      if (isStep2PostApprovalStage()) {
        return {
          title: 'Tramite ya autorizado',
          copy: 'El tramite ya paso a Pago a gestor o a una etapa posterior. La autorizacion ya no aplica en este frente.',
          note: 'Autorizacion ya aplicada en una etapa previa.',
          status: 'done',
          value: 'Ya autorizado',
        };
      }

      return {
        title: 'Listo pero sin autorizacion disponible',
        copy: 'El tramo ya esta listo para autorizacion, pero este perfil no puede ejecutar esa accion desde esta pantalla. Los comprobantes de linea de captura siguen siendo opcionales y no cambian ese bloqueo.',
        note: 'Aprobacion lista, pendiente de un perfil autorizado.',
        status: 'info',
        value: 'Sin autorizacion',
      };
    };

    const buildStep2ChecklistItems = () => {
      const approvalSnapshot = getSavedStep2ApprovalSnapshot();
      const assignmentReady = hasSavedValue(getSavedStep2Value('empresa_gestora_id')) && hasSavedValue(getSavedStep2Value('gestor_id'));
      const docsCount = step2DocState.docs.length;
      const isApproved = approvalSnapshot.ready && canApproveStep2 && getApprovalState();
      const approvalDisplay = getStep2ApprovalDisplay(approvalSnapshot, isApproved);

      return [
        {
          label: '1. Asignacion de Gestor',
          value: assignmentReady ? 'Listo' : 'Pendiente',
          status: assignmentReady ? 'done' : 'pending',
        },
        {
          label: '2. Datos de pagos de derechos',
          value: approvalSnapshot.ready ? 'Listo' : 'Pendiente',
          status: approvalSnapshot.ready ? 'done' : 'pending',
        },
        {
          label: '3. Documentos del paso',
          value: docsCount > 0 ? String(docsCount) + ' documento(s)' : 'Pendiente',
          status: docsCount > 0 ? 'done' : 'pending',
        },
        {
          label: '4. Aprobacion',
          value: approvalDisplay.value,
          status: approvalDisplay.status,
        },
      ];
    };

    const buildStep3ChecklistItems = () => {
      const approvalSnapshot = getSavedStep2ApprovalSnapshot();
      const assignmentReady = hasSavedValue(getSavedStep2Value('empresa_gestora_id')) && hasSavedValue(getSavedStep2Value('gestor_id'));
      const docsCount = step3EvidenceState.docs.length;
      const docsReady = step3EvidenceState.hasTramiteRecibido && step3EvidenceState.hasAcuseRecibo;
      const isApproved = approvalSnapshot.ready && canApproveStep2 && getApprovalState();
      const approvalDisplay = getStep2ApprovalDisplay(approvalSnapshot, isApproved);

      return [
        {
          label: '1. Asignacion de Gestor',
          value: assignmentReady ? 'Listo' : 'Pendiente',
          status: assignmentReady ? 'done' : 'pending',
        },
        {
          label: '2. Datos de pagos de derechos',
          value: approvalSnapshot.ready ? 'Listo' : 'Pendiente',
          status: approvalSnapshot.ready ? 'done' : 'pending',
        },
        {
          label: '3. Documentos del paso',
          value: docsReady ? 'Completo' : (docsCount > 0 ? 'Falta evidencia obligatoria' : 'Pendiente'),
          status: docsReady ? 'done' : (docsCount > 0 ? 'warning' : 'pending'),
        },
        {
          label: '4. Aprobacion',
          value: approvalDisplay.value,
          status: approvalDisplay.status,
        },
      ];
    };

    const syncOperationalChecklistState = () => {
      if (state.steps && state.steps['2']) {
        state.steps['2'].checklistItems = buildStep2ChecklistItems();
      }
      if (state.steps && state.steps['3']) {
        state.steps['3'].checklistItems = buildStep3ChecklistItems();
      }
      const currentStep = Number(root.dataset.operationalActiveStep || state.activeStep || 1);
      const stepState = state.steps[String(currentStep)] || state.steps[currentStep];
      const checklistList = root.querySelector('[data-operational-checklist-list]');
      if (checklistList && stepState) {
        checklistList.innerHTML = renderMiniItems(stepState.checklistItems || []);
      }
    };

    const canAccessOperationalStep3 = () => {
      const snapshot = getStep2ApprovalSnapshot();
      return snapshot.ready && canApproveStep2 && getApprovalState();
    };

    const setFeedback = (message, tone) => {
      if (!step2Feedback) {
        return;
      }
      if (!message) {
        step2Feedback.hidden = true;
        step2Feedback.textContent = '';
        step2Feedback.classList.remove('is-success', 'is-error');
        return;
      }
      step2Feedback.hidden = false;
      step2Feedback.textContent = message;
      step2Feedback.classList.toggle('is-success', tone === 'success');
      step2Feedback.classList.toggle('is-error', tone === 'error');
    };

    const setStep1Feedback = (message, tone) => {
      if (!step1Feedback) {
        return;
      }
      if (!message) {
        step1Feedback.hidden = true;
        step1Feedback.textContent = '';
        step1Feedback.classList.remove('is-success', 'is-error');
        return;
      }
      step1Feedback.hidden = false;
      step1Feedback.textContent = message;
      step1Feedback.classList.toggle('is-success', tone === 'success');
      step1Feedback.classList.toggle('is-error', tone === 'error');
    };

    const setStep1ServicesFeedback = (message, tone) => {
      if (!step1ServicesFeedback) {
        return;
      }
      if (!message) {
        step1ServicesFeedback.hidden = true;
        step1ServicesFeedback.textContent = '';
        step1ServicesFeedback.classList.remove('is-success', 'is-error');
        return;
      }
      step1ServicesFeedback.hidden = false;
      step1ServicesFeedback.textContent = message;
      step1ServicesFeedback.classList.toggle('is-success', tone === 'success');
      step1ServicesFeedback.classList.toggle('is-error', tone === 'error');
    };

    const setStep1DocFeedback = (message, tone) => {
      if (!step1DocFeedback) {
        return;
      }
      if (!message) {
        step1DocFeedback.hidden = true;
        step1DocFeedback.textContent = '';
        step1DocFeedback.classList.remove('is-success', 'is-error');
        return;
      }
      step1DocFeedback.hidden = false;
      step1DocFeedback.textContent = message;
      step1DocFeedback.classList.toggle('is-success', tone === 'success');
      step1DocFeedback.classList.toggle('is-error', tone === 'error');
    };

    const setStep3Feedback = (message, tone) => {
      if (!step3Feedback) {
        return;
      }
      if (!message) {
        step3Feedback.hidden = true;
        step3Feedback.textContent = '';
        step3Feedback.classList.remove('is-success', 'is-error');
        return;
      }
      step3Feedback.hidden = false;
      step3Feedback.textContent = message;
      step3Feedback.classList.toggle('is-success', tone === 'success');
      step3Feedback.classList.toggle('is-error', tone === 'error');
    };

    const setStep2DocFeedback = (message, tone) => {
      if (!step2DocFeedback) {
        return;
      }
      if (!message) {
        step2DocFeedback.hidden = true;
        step2DocFeedback.textContent = '';
        step2DocFeedback.classList.remove('is-success', 'is-error');
        return;
      }
      step2DocFeedback.hidden = false;
      step2DocFeedback.textContent = message;
      step2DocFeedback.classList.toggle('is-success', tone === 'success');
      step2DocFeedback.classList.toggle('is-error', tone === 'error');
    };

    const setStep1Saving = (isSaving) => {
      if (step1SaveButton) {
        step1SaveButton.disabled = isSaving;
        step1SaveButton.textContent = isSaving ? 'Guardando...' : 'Guardar Paso 1';
      }
      Object.values(step1Inputs).forEach((input) => {
        if (input) {
          input.disabled = isSaving || !step1FormConfig.canEdit;
        }
      });
    };

    const setStep1ServicesSaving = (isSaving) => {
      if (step1PrincipalSaveButton) {
        step1PrincipalSaveButton.disabled = isSaving;
        step1PrincipalSaveButton.textContent = isSaving ? 'Guardando...' : 'Guardar tipo principal';
      }
      if (step1AssociatedAddButton) {
        step1AssociatedAddButton.disabled = isSaving;
        step1AssociatedAddButton.textContent = isSaving ? 'Guardando...' : 'Agregar tipo ligado';
      }
      Object.values(step1ServiceInputs).forEach((input) => {
        if (input) {
          const inputName = input.getAttribute('data-step1-service-input');
          if (inputName === 'principal_tipo_id') {
            input.disabled = isSaving || !step1ServicesConfig.canEditPrincipal;
          } else {
            input.disabled = isSaving || !step1ServicesConfig.canManageBase;
          }
        }
      });
      root.querySelectorAll('[data-step1-associated-select], [data-step1-associated-save], [data-step1-associated-delete]').forEach((node) => {
        if (node.hasAttribute('data-step1-associated-select')) {
          node.disabled = isSaving || !step1ServicesConfig.canEditAsociado;
          return;
        }
        if (node.hasAttribute('data-step1-associated-save')) {
          node.disabled = isSaving || !step1ServicesConfig.canEditAsociado;
          return;
        }
        node.disabled = isSaving || !step1ServicesConfig.canDeleteAsociado;
      });
    };

    const updateSharedStep1CsrfHash = (hash) => {
      if (!hash) {
        return;
      }

      step1FormConfig.csrfHash = hash;
      step1ServicesConfig.csrfHash = hash;
      step1DocsFormConfig.csrfHash = hash;
    };

    const updateStep1CsrfHash = (hash) => {
      updateSharedStep1CsrfHash(hash);
    };

    const updateStep1ServicesCsrfHash = (hash) => {
      updateSharedStep1CsrfHash(hash);
    };

    const updateStep1DocsCsrfHash = (hash) => {
      updateSharedStep1CsrfHash(hash);
    };

    const setStep1DocSaving = (isSaving) => {
      if (step1DocUploadButton) {
        step1DocUploadButton.disabled = isSaving || !step1DocsFormConfig.canUpload;
        step1DocUploadButton.textContent = isSaving ? 'Subiendo...' : 'Subir documento';
      }
      if (step1DocTypeSelect) {
        step1DocTypeSelect.disabled = isSaving || !step1DocsFormConfig.canUpload;
      }
      if (step1DocFileInput) {
        step1DocFileInput.disabled = isSaving || !step1DocsFormConfig.canUpload;
      }
      if (step1DocDropzone) {
        step1DocDropzone.classList.toggle('is-disabled', isSaving || !step1DocsFormConfig.canUpload);
      }
    };

    const setStep2Saving = (isSaving) => {
      if (step2SaveButton) {
        step2SaveButton.disabled = isSaving;
        step2SaveButton.textContent = isSaving ? 'Guardando...' : 'Guardar Paso 2';
      }
      Object.values(step2Inputs).forEach((input) => {
        if (input) {
          input.disabled = isSaving || !step2FormConfig.canEdit;
        }
      });
    };

    const setStep3Saving = (isSaving) => {
      if (step3UploadButton) {
        step3UploadButton.disabled = isSaving || !step3FormConfig.canUpload;
        step3UploadButton.textContent = isSaving ? 'Subiendo...' : 'Subir evidencia';
      }
      if (step3Inputs.comprobante_final) {
        step3Inputs.comprobante_final.disabled = isSaving || !step3FormConfig.canUpload;
      }
      if (step3Inputs.file) {
        step3Inputs.file.disabled = isSaving || !step3FormConfig.canUpload;
      }
      if (step3Dropzone) {
        step3Dropzone.classList.toggle('is-disabled', isSaving || !step3FormConfig.canUpload);
      }
    };

    const setStep2DocSaving = (isSaving) => {
      if (step2DocUploadButton) {
        step2DocUploadButton.disabled = isSaving || !step2FormConfig.canUploadDocs;
        step2DocUploadButton.textContent = isSaving ? 'Subiendo...' : 'Subir comprobante';
      }
      if (step2DocFileInput) {
        step2DocFileInput.disabled = isSaving || !step2FormConfig.canUploadDocs;
      }
      if (step2DocDropzone) {
        step2DocDropzone.classList.toggle('is-disabled', isSaving || !step2FormConfig.canUploadDocs);
      }
    };

    const updateCsrfHash = (hash) => {
      if (hash) {
        step2FormConfig.csrfHash = hash;
      }
    };

    const commitStep2FormValues = () => {
      Object.keys(step2Inputs).forEach((fieldName) => {
        step2FormConfig.values[fieldName] = getInputValue(fieldName);
      });
    };

    const getInputValue = (name) => {
      const input = step2Inputs[name];
      return input ? String(input.value || '').trim() : '';
    };

    const getStep1InputValue = (name) => {
      const input = step1Inputs[name];
      return input ? String(input.value || '').trim() : '';
    };

    const getStep1ServiceInputValue = (name) => {
      const input = step1ServiceInputs[name];
      return input ? String(input.value || '').trim() : '';
    };

    const getStep1ServiceInputValues = (name) => {
      const input = step1ServiceInputs[name];
      if (!input) {
        return [];
      }
      if (input instanceof HTMLSelectElement && input.multiple) {
        return Array.from(input.selectedOptions || [])
          .map((option) => String(option.value || '').trim())
          .filter((value) => value !== '');
      }
      const value = String(input.value || '').trim();
      return value === '' ? [] : [value];
    };

    const getSelectedText = (name, emptyLabel) => {
      const input = step2Inputs[name];
      if (!input || !(input instanceof HTMLSelectElement)) {
        return emptyLabel;
      }
      const option = input.options[input.selectedIndex];
      return option ? option.textContent.trim() || emptyLabel : emptyLabel;
    };

    const getStep1SelectedText = (name, emptyLabel) => {
      const input = step1Inputs[name];
      if (!input || !(input instanceof HTMLSelectElement)) {
        return emptyLabel;
      }
      const option = input.options[input.selectedIndex];
      return option ? option.textContent.trim() || emptyLabel : emptyLabel;
    };

    const setCardValue = (key, value, note) => {
      const valueNode = root.querySelector('[data-step2-card-value="' + key + '"]');
      const noteNode = root.querySelector('[data-step2-card-note="' + key + '"]');
      if (valueNode) {
        valueNode.textContent = value;
      }
      if (noteNode && typeof note === 'string') {
        noteNode.textContent = note;
      }
    };

    const syncStep2CardsFromInputs = () => {
      if (!step2FormNode) {
        return;
      }
      const empresaText = getSelectedText('empresa_gestora_id', 'Sin empresa');
      const gestorText = getSelectedText('gestor_id', 'Sin asignar');
      const montoRaw = getInputValue('derechos_tramite');
      const monto = montoRaw !== '' && !Number.isNaN(Number(montoRaw)) ? Number(montoRaw).toFixed(2) : '0.00';
      const pagoText = getSelectedText('derechos_pago_sitio', 'Sin definir');
      const formaText = getSelectedText('derechos_revol_cliente', 'Sin definir');
      const vigenciaText = getInputValue('derechos_vigencia') || 'Sin vigencia';
      const referenciaText = getInputValue('derechos_refer_banc') || 'Sin referencia bancaria';
      const hasAssignment = getInputValue('empresa_gestora_id') !== '' && getInputValue('gestor_id') !== '';
      const hasDerechos = monto !== '0.00' && getInputValue('derechos_revol_cliente') !== '' && getInputValue('derechos_refer_banc') !== '';
      const vigenciaWarning = getStep2VigenciaWarning(vigenciaText);

      setCardValue('empresa_gestora', empresaText, 'Define quien absorbe la operacion del tramite.');
      setCardValue('gestor_asignado', gestorText, 'Responsable directo del tramo operativo.');
      setCardValue('monto_derechos', monto, 'Monto base del bloque de derechos.');
      setCardValue('pago_forma', [pagoText, formaText].filter(Boolean).join(' · '), 'Aqui se decide como baja el pago y con que flujo interno se resuelve.');
      setCardValue('vigencia_referencia', [vigenciaText, referenciaText].filter(Boolean).join(' · '), vigenciaWarning.message || 'Los dos datos ayudan a validar soporte y conciliacion del tramo.');
      setCardValue('asignacion', hasAssignment ? 'Gestor listo' : 'Pendiente de asignar', 'Depende de empresa gestora + gestor dentro del mismo frente operativo.');
      setCardValue('pago_derechos', hasDerechos ? 'Campos completos' : 'Faltan obligatorios', 'El minimo real del bloque es monto, forma de pago y referencia bancaria.');

      if (step2VigenciaField) {
        step2VigenciaField.classList.toggle('is-urgent', vigenciaWarning.isUrgent);
      }
      if (step2VigenciaWarning) {
        step2VigenciaWarning.hidden = !vigenciaWarning.isUrgent;
        step2VigenciaWarning.textContent = vigenciaWarning.message;
      }
    };

    const getStep2VigenciaWarning = (rawValue) => {
      const nextValue = String(rawValue || '').trim();
      if (nextValue === '' || nextValue === 'Sin vigencia') {
        return { isUrgent: false, message: '' };
      }

      const vigenciaDate = new Date(nextValue);
      if (Number.isNaN(vigenciaDate.getTime())) {
        return { isUrgent: false, message: '' };
      }

      const msRemaining = vigenciaDate.getTime() - Date.now();
      const daysRemaining = Math.floor(msRemaining / 86400000);
      if (msRemaining < 0) {
        return {
          isUrgent: true,
          message: 'La referencia ya vencio. Se requiere atencion inmediata.',
        };
      }
      if (daysRemaining <= 15) {
        return {
          isUrgent: true,
          message: 'La referencia vence en ' + String(daysRemaining) + ' dia(s). Se requiere premura antes de que venza.',
        };
      }
      return { isUrgent: false, message: '' };
    };

    const getStep3DocLabel = (docType) => {
      const options = step3FormConfig.options && step3FormConfig.options.comprobanteFinal ? step3FormConfig.options.comprobanteFinal : {};
      return options[String(docType || '')] || String(docType || 'Archivo');
    };

    const buildStep2FileUrl = (fileName) => {
      const base = String(step2FormConfig.fileBaseUrl || '');
      if (base === '') {
        return '#';
      }
      const normalizedBase = base.endsWith('/') ? base : base + '/';
      return normalizedBase + encodeURIComponent(String(fileName || ''));
    };

    const buildStep1FileUrl = (fileName) => {
      const base = String(step1DocsFormConfig.fileBaseUrl || '');
      if (base === '') {
        return '#';
      }
      const normalizedBase = base.endsWith('/') ? base : base + '/';
      return normalizedBase + encodeURIComponent(String(fileName || ''));
    };

    const setStep1PendingDocFile = (file) => {
      step1DocState.pendingFile = file || null;
      if (step1DocSelected) {
        step1DocSelected.textContent = file ? String(file.name || 'Archivo listo') : 'Sin archivo seleccionado.';
      }
    };

    const getStep1SelectedDocMeta = () => {
      if (!step1DocTypeSelect || !step1DocTypeSelect.options) {
        return null;
      }

      const selectedOption = step1DocTypeSelect.options[step1DocTypeSelect.selectedIndex];
      if (!selectedOption || !String(selectedOption.value || '').trim()) {
        return null;
      }

      return {
        documentoNombre: String(selectedOption.dataset.docName || selectedOption.textContent || '').trim().replace(/\s*\(opcional\)$/i, ''),
        isConfigured: String(selectedOption.dataset.docConfigured || '0') === '1',
        isRequired: String(selectedOption.textContent || '').toLowerCase().indexOf('(opcional)') === -1,
        sourceBadge: String(selectedOption.dataset.docBadge || 'Catálogo general').trim() || 'Catálogo general',
        sourceTone: String(selectedOption.dataset.docTone || 'neutral').trim() || 'neutral',
      };
    };

    const normalizeStep1DocState = () => {
      step1DocState.docs = step1DocState.docs
        .filter((doc) => Number(doc.documento_id || 0) > 0)
        .sort((left, right) => {
          const configuredCompare = Number(Boolean(right.is_configured)) - Number(Boolean(left.is_configured));
          if (configuredCompare !== 0) {
            return configuredCompare;
          }

          const requiredCompare = Number(Boolean(right.is_required)) - Number(Boolean(left.is_required));
          if (requiredCompare !== 0) {
            return requiredCompare;
          }
          return String(left.documento_nombre || '').localeCompare(String(right.documento_nombre || ''));
        });
    };

    const getStep1DocCountLabel = () => {
      let requiredTotal = 0;
      let uploadedRequired = 0;
      let uploadedTotal = 0;
      step1DocState.docs.forEach((doc) => {
        const hasFile = Boolean(doc.has_file && String(doc.file || '').trim() !== '');
        if (hasFile) {
          uploadedTotal += 1;
        }
        if (doc.is_required) {
          requiredTotal += 1;
          if (hasFile) {
            uploadedRequired += 1;
          }
        }
      });

      return {
        requiredTotal,
        uploadedRequired,
        uploadedTotal,
        label: requiredTotal > 0
          ? String(uploadedRequired) + '/' + String(requiredTotal) + ' obligatorios'
          : (uploadedTotal > 0 ? String(uploadedTotal) + ' cargado(s)' : 'sin documentos'),
      };
    };

    const renderStep1DocUI = () => {
      normalizeStep1DocState();
      const counts = getStep1DocCountLabel();

      if (step1DocCount) {
        step1DocCount.textContent = counts.label;
      }
      if (step1DocUploadedTotal) {
        step1DocUploadedTotal.textContent = String(counts.uploadedTotal) + ' cargado(s)';
      }
      if (state.steps && state.steps['1']) {
        state.steps['1'].docCount = counts.label;
      }
      if (Number(root.dataset.operationalActiveStep || state.activeStep || 1) === 1) {
        setText('signal-doc-count', counts.label);
      }

      if (!step1DocGallery) {
        return;
      }

      if (step1DocState.docs.length === 0) {
        const hasGeneralCatalog = step1DocTypeSelect && step1DocTypeSelect.options && step1DocTypeSelect.options.length > 1;
        step1DocGallery.innerHTML = hasGeneralCatalog
          ? '<div class="tp-gallery-item">Aún no hay documentos cargados en este expediente. Usa Documento a cargar para subir el primero.</div>'
          : '<div class="tp-gallery-item">Sin catálogo documental configurado para los tipos ligados del expediente.</div>';
        return;
      }

      step1DocGallery.innerHTML = step1DocState.docs.map((doc) => {
        const hasFile = Boolean(doc.has_file && String(doc.file || '').trim() !== '');
        const metaParts = [doc.is_required ? 'Obligatorio' : 'Opcional'];
        if (String(doc.source_types_label || '').trim() !== '') {
          metaParts.push(String(doc.source_types_label || '').trim());
        }
        metaParts.push(hasFile ? (String(doc.status_label || 'Cargado').trim() || 'Cargado') : 'Pendiente');
        const sourceBadge = String(doc.source_badge || (doc.is_configured ? 'Ligado al tipo' : 'Catálogo general')).trim() || 'Catálogo general';
        const sourceTone = String(doc.source_tone || (doc.is_configured ? 'success' : 'neutral')).trim() === 'success' ? ' is-success' : ' is-neutral';

        const titleHtml = hasFile
          ? '<a class="tp-gallery-item-link" href="' + escapeHtml(buildStep1FileUrl(doc.file)) + '" target="_blank" rel="noreferrer" title="' + escapeHtml(doc.file) + '">' + escapeHtml(doc.file) + '</a>'
          : '<strong>' + escapeHtml(doc.documento_nombre || 'Documento') + '</strong>';
        const deleteHtml = (step1DocsFormConfig.canDelete && hasFile)
          ? '<button type="button" class="tp-btn secondary small" data-step1-doc-delete="' + escapeHtml(doc.file) + '" data-step1-doc-id="' + String(doc.documento_id || 0) + '">Eliminar</button>'
          : '';
        const previewHtml = (hasFile && doc.file)
          ? '<button type="button" class="tp-btn ghost small" title="Vista previa de ' + escapeHtml(doc.file) + '" data-doc-preview-url="' + escapeHtml(buildStep1FileUrl(doc.file)) + '" data-doc-preview-name="' + escapeHtml(doc.file) + '" data-doc-preview-meta="' + escapeHtml(metaParts.join(' · ')) + '">Ver</button>'
          : '';
        return ''
          + '<div class="tp-gallery-item">'
          + '  <div class="tp-gallery-item-head">'
          + '    <div>'
          +        titleHtml
          + '      <span class="tp-step4-status-chip' + sourceTone + '" style="margin-top: 6px;">' + escapeHtml(sourceBadge) + '</span>'
          + '      <span class="tp-gallery-item-meta">' + escapeHtml(metaParts.join(' · ')) + '</span>'
          + '    </div>'
          + '    <div>' + previewHtml + deleteHtml + '</div>'
          + '  </div>'
          + '</div>';
      }).join('');
    };

    const buildStep3FileUrl = (fileName) => {
      const base = String(step3FormConfig.fileBaseUrl || '');
      if (base === '') {
        return '#';
      }
      const normalizedBase = base.endsWith('/') ? base : base + '/';
      return normalizedBase + encodeURIComponent(String(fileName || ''));
    };

    const isPreviewableImage = (fileName) => /\.(png|jpe?g|gif|webp|bmp|svg)$/i.test(String(fileName || ''));

    const closeDocPreviewModal = () => {
      if (!docPreviewModal) {
        return;
      }
      if (typeof docPreviewModal.close === 'function') {
        docPreviewModal.close();
      }
    };

    const openDocPreviewModal = (fileName, fileUrl, metaLabel) => {
      const normalizedUrl = String(fileUrl || '').trim();
      if (!normalizedUrl || normalizedUrl === '#') {
        return;
      }

      if (!docPreviewModal || typeof docPreviewModal.showModal !== 'function' || !docPreviewImage || !docPreviewLink) {
        window.open(normalizedUrl, '_blank', 'noopener');
        return;
      }

      if (docPreviewTitle) {
        docPreviewTitle.textContent = String(fileName || 'Vista previa del documento');
      }
      if (docPreviewMeta) {
        docPreviewMeta.textContent = String(metaLabel || 'Revision visual del archivo cargado en el expediente.');
      }
      docPreviewImage.src = normalizedUrl;
      docPreviewImage.alt = String(fileName || 'Vista previa del documento');
      docPreviewLink.href = normalizedUrl;
      docPreviewLink.textContent = 'Abrir ' + String(fileName || 'archivo completo') + ' en una pestaña nueva';
      docPreviewModal.showModal();
    };

    const buildPreviewTriggerHtml = (fileName, fileUrl, metaLabel) => {
      if (!isPreviewableImage(fileName)) {
        return '';
      }

      return ''
        + '<button type="button" class="tp-gallery-preview-trigger"'
        + ' data-doc-preview-url="' + escapeHtml(fileUrl) + '"'
        + ' data-doc-preview-name="' + escapeHtml(fileName) + '"'
        + ' data-doc-preview-meta="' + escapeHtml(metaLabel) + '">'
        + '<img class="tp-gallery-preview-image" src="' + escapeHtml(fileUrl) + '" alt="' + escapeHtml(fileName) + '" loading="lazy">'
        + '</button>';
    };

    const setStep2PendingFile = (file) => {
      step2DocState.pendingFile = file || null;
      if (step2DocSelected) {
        step2DocSelected.textContent = file ? String(file.name || 'Archivo listo') : 'Sin archivo seleccionado.';
      }
    };

    const setStep3PendingFile = (file) => {
      step3EvidenceState.pendingFile = file || null;
      if (step3Selected) {
        step3Selected.textContent = file ? String(file.name || 'Archivo listo') : 'Sin archivo seleccionado.';
      }
    };

    const normalizeStep2DocState = () => {
      step2DocState.docs = step2DocState.docs.filter((doc) => String(doc.file || '').trim() !== '');
    };

    const getStep2DocCountLabel = () => {
      normalizeStep2DocState();
      return step2DocState.docs.length === 0 ? 'sin documentos' : String(step2DocState.docs.length) + ' soporte(s)';
    };

    const renderStep2DocUI = () => {
      normalizeStep2DocState();
      if (!step2DocGallery) {
        return;
      }

      if (step2DocState.docs.length === 0) {
        step2DocGallery.innerHTML = '<div class="tp-gallery-item">Sin archivos de derechos registrados</div>';
      } else {
        step2DocGallery.innerHTML = step2DocState.docs.map((doc) => (
          '<div class="tp-gallery-item">'
            + buildPreviewTriggerHtml(doc.file, buildStep2FileUrl(doc.file), 'Comprobante de linea de captura')
            + '<div class="tp-gallery-item-head">'
              + '<div>'
                + '<a class="tp-gallery-item-link" href="' + escapeHtml(buildStep2FileUrl(doc.file)) + '" target="_blank" rel="noreferrer">' + escapeHtml(doc.file) + '</a>'
                + '<span class="tp-gallery-item-meta">Comprobante de linea de captura</span>'
              + '</div>'
              + '<div class="tp-gallery-item-actions">'
                + (isPreviewableImage(doc.file)
                  ? '<button type="button" class="tp-btn ghost small" data-doc-preview-url="' + escapeHtml(buildStep2FileUrl(doc.file)) + '" data-doc-preview-name="' + escapeHtml(doc.file) + '" data-doc-preview-meta="Comprobante de linea de captura">Ver imagen</button>'
                  : '')
                + (step2FormConfig.canDeleteDocs
                  ? '<button type="button" class="tp-btn secondary small" data-step2-doc-delete="' + escapeHtml(doc.file) + '">Eliminar</button>'
                  : '')
              + '</div>'
            + '</div>'
          + '</div>'
        )).join('');
      }

      setCardValue('soporte_documental', getStep2DocCountLabel(), 'Los comprobantes de linea de captura pueden adjuntarse aqui, pero no bloquean la aprobacion del tramite.');
      if (state.steps && state.steps['2']) {
        state.steps['2'].docCount = getStep2DocCountLabel();
      }
      syncOperationalChecklistState();
    };

    const normalizeStep3EvidenceState = () => {
      step3EvidenceState.docs = step3EvidenceState.docs.filter((doc) => String(doc.file || '').trim() !== '');
      step3EvidenceState.hasTramiteRecibido = step3EvidenceState.docs.some((doc) => String(doc.comprobante_final || '') === 'tramite_recibido');
      step3EvidenceState.hasAcuseRecibo = step3EvidenceState.docs.some((doc) => String(doc.comprobante_final || '') === 'acuse_recibo_cliente');
    };

    const renderStep3EvidenceUI = () => {
      normalizeStep3EvidenceState();
      const isComplete = isStep3FinancialGateReady();

      root.querySelectorAll('[data-step3-chip]').forEach((chip) => {
        const chipType = chip.getAttribute('data-step3-chip');
        const isSuccess = (chipType === 'tramite_recibido' && step3EvidenceState.hasTramiteRecibido)
          || (chipType === 'acuse_recibo_cliente' && step3EvidenceState.hasAcuseRecibo);
        chip.classList.toggle('is-success', isSuccess);
      });

      const gallery = root.querySelector('[data-step3-gallery]');
      if (gallery) {
        if (step3EvidenceState.docs.length === 0) {
          gallery.innerHTML = '<div class="tp-gallery-item">Sin evidencias finales registradas</div>';
        } else {
          gallery.innerHTML = step3EvidenceState.docs.map((doc) => (
            '<div class="tp-gallery-item">'
              + buildPreviewTriggerHtml(doc.file, buildStep3FileUrl(doc.file), getStep3DocLabel(doc.comprobante_final))
              + '<div class="tp-gallery-item-head">'
                + '<div>'
                  + '<a class="tp-gallery-item-link" href="' + escapeHtml(buildStep3FileUrl(doc.file)) + '" target="_blank" rel="noreferrer">' + escapeHtml(doc.file) + '</a>'
                  + '<span class="tp-gallery-item-meta">' + escapeHtml(getStep3DocLabel(doc.comprobante_final)) + '</span>'
                + '</div>'
                + '<div class="tp-gallery-item-actions">'
                  + (isPreviewableImage(doc.file)
                    ? '<button type="button" class="tp-btn ghost small" data-doc-preview-url="' + escapeHtml(buildStep3FileUrl(doc.file)) + '" data-doc-preview-name="' + escapeHtml(doc.file) + '" data-doc-preview-meta="' + escapeHtml(getStep3DocLabel(doc.comprobante_final)) + '">Ver imagen</button>'
                    : '')
                  + (step3FormConfig.canDelete
                    ? '<button type="button" class="tp-btn secondary small" data-step3-doc-delete="' + escapeHtml(doc.file) + '">Eliminar</button>'
                    : '')
                + '</div>'
              + '</div>'
            + '</div>'
          )).join('');
        }
      }

      if (state.steps && state.steps['3']) {
        state.steps['3'].docCount = step3EvidenceState.docs.length === 0 ? 'sin evidencias' : String(step3EvidenceState.docs.length) + ' evidencia(s)';
      }

      const evidenceNote = root.querySelector('[data-step3-evidence-note]');
      if (evidenceNote) {
        evidenceNote.textContent = isComplete
          ? 'Las dos evidencias finales ya estan presentes y dejan abiertos en paralelo Pago a gestor y Cobro a cliente.'
          : 'Falta al menos una de las dos evidencias finales que desbloquean la lectura financiera posterior.';
      }

      if (step3GatePanel) {
        step3GatePanel.classList.remove('is-ready', 'is-pending');
        step3GatePanel.classList.add(isComplete ? 'is-ready' : 'is-pending');
      }
      if (step3GateTitle) {
        step3GateTitle.textContent = isComplete ? 'Pago a gestor y Cobro a cliente ya pueden abrirse' : 'Las filas financieras siguen bloqueadas';
      }
      if (step3GateCopy) {
        step3GateCopy.textContent = isComplete
          ? 'Las dos evidencias finales ya estan presentes. Este cierre documental ya habilita Pago a gestor y Cobro a cliente como frentes independientes.'
          : 'Mientras falte una de las dos evidencias finales, ni Pago a gestor ni Cobro a cliente deberian abrirse como siguientes acciones validas.';
      }
      if (step3GateActions) {
        if (isComplete) {
          step3GateActions.innerHTML = '<div style="margin-top: 12px;"><span class="tp-inline-note">Las dos filas financieras ya pueden leerse en esta misma pantalla, aunque cada una siga perteneciendo a roles distintos.</span></div>';
        } else {
          const missing = [];
          if (!step3EvidenceState.hasTramiteRecibido) {
            missing.push('Tramite entregado por gestor');
          }
          if (!step3EvidenceState.hasAcuseRecibo) {
            missing.push('Acuse de recibo del cliente');
          }
          step3GateActions.innerHTML = '<ul class="tp-approval-missing">' + missing.map((item) => '<li>' + escapeHtml(item) + '</li>').join('') + '</ul>';
        }
      }
      if (step4InlinePanel) {
        step4InlinePanel.hidden = !isComplete && Number(root.dataset.operationalActiveStep || state.activeStep || 1) < 4;
      }
      syncOperationalChecklistState();
    };

    const getStep2ApprovalSnapshot = () => {
      const monto = getInputValue('derechos_tramite');
      const formaPago = getInputValue('derechos_revol_cliente');
      const referencia = getInputValue('derechos_refer_banc');
      const missing = [];
      if (monto === '') {
        missing.push('Monto pago de derechos');
      }
      if (formaPago === '') {
        missing.push('Forma de pago');
      }
      if (referencia === '') {
        missing.push('Referencia bancaria');
      }
      return {
        ready: missing.length === 0,
        missing,
      };
    };

    const renderStep2ApprovalActions = (snapshot, isApproved) => {
      const approvalDisplay = getStep2ApprovalDisplay(snapshot, isApproved);

      if (!step2ApprovalActions) {
        return;
      }

      if (snapshot.ready && canApproveStep2) {
        step2ApprovalActions.innerHTML = ''
          + '<div class="tp-btn-row" style="margin-top: 12px;">'
          + '<button type="button" class="tp-btn primary" data-operational-step-link="3" data-operational-approve="1">' + (isApproved ? 'Volver a cierre operativo' : 'Aprobar tramite') + '</button>'
          + '</div>'
          + '<div style="margin-top: 8px;">'
          + '<span class="tp-inline-note' + (isApproved ? ' is-approved' : '') + '" data-operational-step2-note>'
          + approvalDisplay.note
          + '</span>'
          + '</div>'
          + '<div class="tp-btn-row" style="margin-top: 10px;" data-operational-reset-row' + (isApproved ? '' : ' hidden') + '>'
          + '<a href="javascript:void(0)" class="tp-btn secondary" data-operational-reset-approval="1">Limpiar aprobacion local</a>'
          + '</div>';
        return;
      }

      if (snapshot.ready) {
        step2ApprovalActions.innerHTML = '<div style="margin-top: 12px;"><span class="tp-inline-note">' + escapeHtml(approvalDisplay.note) + '</span></div>';
        return;
      }

      step2ApprovalActions.innerHTML = '<ul class="tp-approval-missing">'
        + snapshot.missing.map((item) => '<li>' + escapeHtml(item) + '</li>').join('')
        + '</ul>';
    };

    const loadGestores = async (empresaId) => {
      const gestorSelect = step2Inputs.gestor_id;
      if (!gestorSelect) {
        return;
      }

      if (!empresaId) {
        gestorSelect.innerHTML = '<option value="">Seleccione un gestor</option>';
        gestorSelect.value = '';
        syncStep2CardsFromInputs();
        return;
      }

      gestorSelect.disabled = true;
      gestorSelect.innerHTML = '<option value="">Cargando...</option>';
      try {
        const response = await fetch(step2FormConfig.urls.getGestoresByEmpresaIdBase + '/' + encodeURIComponent(String(empresaId)), {
          credentials: 'same-origin',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });
        const result = await response.json();
        gestorSelect.innerHTML = '<option value="">Seleccione un gestor</option>';
        Object.entries(result || {}).forEach(([value, label]) => {
          const option = document.createElement('option');
          option.value = String(value);
          option.textContent = String(label);
          gestorSelect.appendChild(option);
        });
      } catch (error) {
        gestorSelect.innerHTML = '<option value="">Error al cargar gestores</option>';
      } finally {
        gestorSelect.disabled = !step2FormConfig.canEdit;
      }
    };

    const loadEjecutivos = async (clienteId) => {
      const ejecutivoSelect = step1Inputs.cli_directo_ejecutivo_id;
      if (!ejecutivoSelect) {
        return;
      }

      if (!clienteId) {
        ejecutivoSelect.innerHTML = '<option value="">Seleccione un ejecutivo</option>';
        ejecutivoSelect.value = '';
        return;
      }

      ejecutivoSelect.disabled = true;
      ejecutivoSelect.innerHTML = '<option value="">Cargando...</option>';
      try {
        const response = await fetch(step1FormConfig.urls.getEjecutivosByClienteIdBase + '/' + encodeURIComponent(String(clienteId)), {
          credentials: 'same-origin',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });
        const result = await response.json();
        ejecutivoSelect.innerHTML = '<option value="">Seleccione un ejecutivo</option>';
        Object.entries(result || {}).forEach(([value, label]) => {
          const option = document.createElement('option');
          option.value = String(value);
          option.textContent = String(label);
          ejecutivoSelect.appendChild(option);
        });
      } catch (error) {
        ejecutivoSelect.innerHTML = '<option value="">Error al cargar ejecutivos</option>';
      } finally {
        ejecutivoSelect.disabled = !step1FormConfig.canEdit;
      }
    };

    const buildErrorMessage = (result, fallback) => {
      if (result && result.errors) {
        return Object.values(result.errors).join(' | ');
      }
      return (result && result.message) ? result.message : fallback;
    };

    const setEvidenceFeedback = (message, tone) => {
      if (!evidenceFeedback) {
        return;
      }
      if (!message) {
        evidenceFeedback.hidden = true;
        evidenceFeedback.textContent = '';
        evidenceFeedback.classList.remove('is-success', 'is-error');
        return;
      }
      evidenceFeedback.hidden = false;
      evidenceFeedback.textContent = message;
      evidenceFeedback.classList.toggle('is-success', tone === 'success');
      evidenceFeedback.classList.toggle('is-error', tone === 'error');
    };

    const setEvidenceSaving = (isSaving) => {
      if (evidenceSaveButton) {
        evidenceSaveButton.disabled = isSaving || !evidenceFormConfig.canAdd;
        evidenceSaveButton.textContent = isSaving ? 'Guardando...' : 'Guardar comentario';
      }
      if (evidenceInput) {
        evidenceInput.disabled = isSaving || !evidenceFormConfig.canAdd;
      }
    };

    const renderEvidenceItem = (item) => {
      return '<div class="tp-note-item tone-info">'
        + '<span class="tp-note-meta">' + escapeHtml(String(item.createdAtLabel || 'Sin fecha') + ' · ' + String(item.author || 'Sistema')) + '</span>'
        + '<span class="tp-note-body">' + escapeHtml(item.comment || '') + '</span>'
      + '</div>';
    };

    const renderEvidenceList = () => {
      if (!evidenceList || !evidenceEmpty) {
        return;
      }

      const items = evidenceState.items.filter((item) => String(item.comment || '').trim() !== '');
      evidenceList.innerHTML = items.map((item) => renderEvidenceItem(item)).join('');
      evidenceList.hidden = items.length === 0;
      evidenceEmpty.hidden = items.length !== 0;
    };

    const saveEvidenceComment = async () => {
      if (!evidenceFormConfig.canAdd || !evidenceInput) {
        return;
      }

      const comment = String(evidenceInput.value || '').trim();
      if (comment.length < 3) {
        setEvidenceFeedback('Escribe un comentario de al menos 3 caracteres.', 'error');
        return;
      }

      setEvidenceFeedback('', '');
      setEvidenceSaving(true);
      try {
        const formData = new FormData();
        formData.append(evidenceFormConfig.csrfName, evidenceFormConfig.csrfHash || '');
        formData.append('comentario', comment);

        const response = await fetch(evidenceFormConfig.urls.create, {
          method: 'POST',
          body: formData,
          credentials: 'same-origin',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });
        const result = await response.json();
        if (result && result.csrfHash) {
          evidenceFormConfig.csrfHash = result.csrfHash;
        }
        if (!response.ok || !result.success) {
          throw new Error(buildErrorMessage(result, 'No se pudo guardar el comentario.'));
        }

        evidenceState.items.unshift({
          id: Number(result.item && result.item.id ? result.item.id : 0),
          comment: String(result.item && result.item.comment ? result.item.comment : comment),
          author: String(result.item && result.item.author ? result.item.author : 'Sistema'),
          createdAt: String(result.item && result.item.createdAt ? result.item.createdAt : ''),
          createdAtLabel: String(result.item && result.item.createdAtLabel ? result.item.createdAtLabel : 'Sin fecha'),
        });
        evidenceInput.value = '';
        renderEvidenceList();
        setEvidenceFeedback('Comentario guardado correctamente.', 'success');
      } catch (error) {
        setEvidenceFeedback(error.message || 'No se pudo guardar el comentario.', 'error');
      } finally {
        setEvidenceSaving(false);
      }
    };

    const escapeStep1Html = (value) => String(value ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');

    const getTipoLabel = (tipoId) => {
      const options = step1ServicesConfig.options && step1ServicesConfig.options.traTipos ? step1ServicesConfig.options.traTipos : {};
      const key = String(tipoId || '');
      return options[key] || options[Number(tipoId || 0)] || ('Tipo #' + key);
    };

    const normalizeStep1ServicesState = () => {
      step1ServicesState.services = step1ServicesState.services
        .filter((service) => Number(service.tra_tipos_id || 0) > 0)
        .map((service) => ({
          asociado_id: Number(service.asociado_id || 0),
          tra_tipos_id: Number(service.tra_tipos_id || 0),
          label: String(service.label || getTipoLabel(service.tra_tipos_id) || ''),
          is_principal: Number(service.tra_tipos_id || 0) === Number(step1ServicesState.principalTipoId || 0),
        }));

      if (Number(step1ServicesState.principalTipoId || 0) > 0 && !step1ServicesState.services.some((service) => service.is_principal)) {
        step1ServicesState.services.unshift({
          asociado_id: 0,
          tra_tipos_id: Number(step1ServicesState.principalTipoId || 0),
          label: getTipoLabel(step1ServicesState.principalTipoId),
          is_principal: true,
        });
      }
    };

    const renderStep1AddTypeOptions = (preferredValues) => {
      const addSelect = step1ServiceInputs.add_tipo_id;
      if (!addSelect || !(addSelect instanceof HTMLSelectElement)) {
        return;
      }

      normalizeStep1ServicesState();
      const selectedValues = new Set(
        (Array.isArray(preferredValues) ? preferredValues : getStep1ServiceInputValues('add_tipo_id'))
          .map((value) => String(value || '').trim())
          .filter((value) => value !== '')
      );
      const linkedTipoIds = new Set(
        step1ServicesState.services
          .map((service) => String(service.tra_tipos_id || '').trim())
          .filter((value) => value !== '')
      );

      addSelect.innerHTML = Object.entries(step1ServicesConfig.options.traTipos || {}).map(([value, label]) => {
        const normalizedValue = String(value || '').trim();
        const isSelected = selectedValues.has(normalizedValue);
        const isLinked = linkedTipoIds.has(normalizedValue);
        return '<option value="' + escapeStep1Html(normalizedValue) + '"'
          + (isSelected ? ' selected' : '')
          + ((isLinked && !isSelected) ? ' disabled' : '')
          + '>' + escapeStep1Html(label) + '</option>';
      }).join('');
    };

    const renderStep1ServicesSummary = () => {
      normalizeStep1ServicesState();
      const principalService = step1ServicesState.services.find((service) => service.is_principal) || null;
      const associatedServices = step1ServicesState.services.filter((service) => !service.is_principal);
      const principalLabel = principalService ? principalService.label : 'Sin tipo principal';
      const associatedCount = associatedServices.length;
      const associatedSummary = associatedCount > 0
        ? associatedServices.map((service) => service.label).join(', ')
        : 'Sin tipos ligados';

      const principalValueNode = root.querySelector('[data-step1-detail-value="principal"]');
      const principalNoteNode = root.querySelector('[data-step1-detail-note="principal"]');
      const ligadosValueNode = root.querySelector('[data-step1-detail-value="ligados"]');
      const ligadosNoteNode = root.querySelector('[data-step1-detail-note="ligados"]');
      if (principalValueNode) {
        principalValueNode.textContent = principalLabel;
      }
      if (principalNoteNode) {
        principalNoteNode.textContent = 'Es la base del expediente y marca la regla de duplicados.';
      }
      if (ligadosValueNode) {
        ligadosValueNode.textContent = associatedCount > 0 ? String(associatedCount) + ' activo(s)' : 'Sin ligados';
      }
      if (ligadosNoteNode) {
        ligadosNoteNode.textContent = associatedSummary;
      }

      const pillRow = root.querySelector('[data-step1-pill-row]');
      if (pillRow) {
        pillRow.innerHTML = step1ServicesState.services.map((service) => {
          const toneClass = service.is_principal ? ' is-principal' : '';
          const prefix = service.is_principal ? 'Principal ' : 'Ligado ';
          return '<span class="tp-pill' + toneClass + '">' + prefix + escapeStep1Html(service.label) + '</span>';
        }).join('');
      }

      const assocSummary = root.querySelector('[data-step1-assoc-summary]');
      if (assocSummary) {
        if (associatedServices.length === 0) {
          assocSummary.innerHTML = '<div class="tp-assoc-item"><div><strong>Sin asociados registrados</strong><small>Solo tipo principal</small></div><span class="tp-assoc-actions">--</span></div>';
        } else {
          assocSummary.innerHTML = associatedServices.map((service) => (
            '<div class="tp-assoc-item">'
              + '<div><strong>' + escapeStep1Html(service.label) + '</strong><small>Asociado editable</small></div>'
              + '<span class="tp-assoc-actions">Cambiar / Eliminar</span>'
            + '</div>'
          )).join('');
        }
      }
    };

    const renderStep1ServicesList = () => {
      const servicesList = root.querySelector('[data-step1-services-list]');
      if (!servicesList) {
        return;
      }
      normalizeStep1ServicesState();
      renderStep1AddTypeOptions();
      if (step1ServiceInputs.principal_tipo_id) {
        step1ServiceInputs.principal_tipo_id.value = String(step1ServicesState.principalTipoId || '');
      }

      if (step1ServicesState.services.length === 0) {
        servicesList.innerHTML = '<span class="tp-inline-note">No hay tipos ligados registrados todavia.</span>';
        return;
      }

      servicesList.innerHTML = step1ServicesState.services.map((service) => {
        const serviceLabel = escapeStep1Html(service.label);
        if (service.is_principal) {
          return '<div class="tp-assoc-item" data-step1-asociado-id="' + String(service.asociado_id || 0) + '">'
            + '<div><strong>' + serviceLabel + '</strong><small>Principal</small></div>'
            + '<div class="tp-topbar-actions"><span class="tp-pill is-principal">Principal</span></div>'
          + '</div>';
        }

        const optionsHtml = ['<option value="">Seleccione un tipo</option>'].concat(
          Object.entries(step1ServicesConfig.options.traTipos || {}).map(([value, label]) => (
            '<option value="' + escapeStep1Html(value) + '"' + (String(value) === String(service.tra_tipos_id) ? ' selected' : '') + '>' + escapeStep1Html(label) + '</option>'
          ))
        ).join('');

        let actionsHtml = '';
        if (step1ServicesConfig.canEditAsociado) {
          actionsHtml += '<select class="tp-assoc-select" data-step1-associated-select="' + String(service.asociado_id || 0) + '">' + optionsHtml + '</select>';
          actionsHtml += '<button type="button" class="tp-btn secondary" data-step1-associated-save="' + String(service.asociado_id || 0) + '">Actualizar</button>';
        }
        if (step1ServicesConfig.canDeleteAsociado) {
          actionsHtml += '<button type="button" class="tp-btn ghost" data-step1-associated-delete="' + String(service.asociado_id || 0) + '">Eliminar</button>';
        }

        return '<div class="tp-assoc-item" data-step1-asociado-id="' + String(service.asociado_id || 0) + '">'
          + '<div><strong>' + serviceLabel + '</strong><small>Asociado editable</small></div>'
          + '<div class="tp-topbar-actions">' + actionsHtml + '</div>'
        + '</div>';
      }).join('');
    };

    const refreshStep1ServicesUI = () => {
      renderStep1ServicesSummary();
      renderStep1ServicesList();
      syncStep1Diagnostics();
    };

    const setStep1ControlCard = (key, value, note) => {
      root.querySelectorAll('[data-step1-control-value="' + key + '"]').forEach((node) => {
        node.textContent = value;
      });
      root.querySelectorAll('[data-step1-control-note="' + key + '"]').forEach((node) => {
        node.textContent = note;
      });
    };

    const setStep1MetaSignal = (key, value, note) => {
      root.querySelectorAll('[data-step1-meta-value="' + key + '"]').forEach((node) => {
        node.textContent = value;
      });
      root.querySelectorAll('[data-step1-meta-note="' + key + '"]').forEach((node) => {
        node.textContent = note;
      });
    };

    const setStep1Check = (key, state, detail) => {
      root.querySelectorAll('[data-step1-check-state="' + key + '"]').forEach((node) => {
        node.classList.remove('is-ok', 'is-warn', 'is-info');
        node.classList.add('is-' + state);
        node.textContent = state === 'ok' ? 'Listo' : (state === 'warn' ? 'Revisar' : 'Contexto');
      });
      root.querySelectorAll('[data-step1-check-detail="' + key + '"]').forEach((node) => {
        node.textContent = detail;
      });
    };

    const commitStep1FormValues = () => {
      Object.keys(step1Inputs).forEach((fieldName) => {
        step1FormConfig.values[fieldName] = getStep1InputValue(fieldName);
      });
    };

    function syncStep1Diagnostics() {
      const clienteText = getStep1SelectedText('cli_directo_id', 'Sin cliente');
      const ejecutivoText = getStep1SelectedText('cli_directo_ejecutivo_id', 'Sin ejecutivo');
      const entidadText = getStep1SelectedText('entidad_id', 'Sin entidad');
      const contrato = getStep1InputValue('contrato');
      const serie = getStep1InputValue('serie');
      const principalService = step1ServicesState.services.find((service) => service.is_principal) || null;
      const principalLabel = principalService ? principalService.label : 'Sin tipo principal';
      const asociados = step1ServicesState.services.filter((service) => !service.is_principal);

      const missingBase = [
        ['contrato', contrato],
        ['serie', serie],
        ['entidad', entidadText !== 'Sin entidad' ? entidadText : ''],
      ].filter(([, value]) => String(value || '').trim() === '').map(([label]) => label);

      const stepComplete = contrato !== '' && entidadText !== 'Sin entidad';

      setStep1MetaSignal('folio', String(step1FormConfig.values.folio || '--'), 'Se conserva oculto en el guardado real.');
      setStep1MetaSignal(
        'completa',
        stepComplete ? 'Contrato + Entidad listos' : 'Pendiente de completar',
        'Regla actual de step1_complete.'
      );
      setStep1MetaSignal('persistencia', 'Datos base del expediente', 'Guarda identidad del expediente en tramite y ahora rehidrata localmente el Paso 1.');

      setStep1ControlCard(
        'guardado',
        missingBase.length === 0 ? 'Listo para guardar' : 'Faltan ' + String(missingBase.length) + ' obligatorios',
        missingBase.length === 0
          ? 'Contrato, serie, placas, entidad y observaciones ya tienen contexto suficiente para guardar.'
          : 'Pendiente: ' + missingBase.join(', ') + '. Placas, unidad y observaciones siguen siendo contexto util.'
      );

      setStep1ControlCard(
        'regla',
        principalLabel !== 'Sin tipo principal' && serie !== '' ? principalLabel + ' + ' + serie : 'Falta tipo o serie',
        serie !== ''
          ? 'Se evalua contra el historial reciente del tipo principal seleccionado.'
          : 'Sin serie no se puede anticipar la validacion de duplicado tipo + serie.'
      );

      const canTouchServices = Boolean(step1ServicesConfig.canEditPrincipal || step1ServicesConfig.canManageBase || step1ServicesConfig.canEditAsociado || step1ServicesConfig.canDeleteAsociado);
      let edicionValue = 'Solo lectura';
      if (step1FormConfig.canEdit && canTouchServices) {
        edicionValue = 'Base y composicion habilitadas';
      } else if (step1FormConfig.canEdit) {
        edicionValue = 'Solo guardado base';
      } else if (canTouchServices) {
        edicionValue = 'Solo composicion del servicio';
      }
      setStep1ControlCard(
        'permisos',
        edicionValue,
        'Identidad base, tipo principal y ligados pueden habilitarse de forma independiente segun el tramo actual.'
      );

      setStep1Check(
        'identidad',
        stepComplete ? 'ok' : 'warn',
        stepComplete
          ? 'Contrato ' + contrato + ' y entidad ' + entidadText + ' visibles en el mismo bloque.'
          : 'Aun falta la dupla contrato + entidad antes del guardado base.'
      );

      setStep1Check(
        'cliente_ejecutivo',
        clienteText !== 'Sin cliente' && ejecutivoText !== 'Sin ejecutivo' ? 'ok' : 'warn',
        clienteText !== 'Sin cliente' && ejecutivoText !== 'Sin ejecutivo'
          ? 'Cliente ' + clienteText + ' y ejecutivo ' + ejecutivoText + ' ya quedaron enlazados en pantalla.'
          : 'Conviene revisar cliente o ejecutivo antes de guardar.'
      );

      setStep1Check(
        'duplicado',
        principalLabel !== 'Sin tipo principal' && serie !== '' ? 'ok' : 'warn',
        principalLabel !== 'Sin tipo principal' && serie !== ''
          ? 'La regla se evaluara con ' + principalLabel + ' + ' + serie + '.'
          : 'Sin tipo principal o sin serie no se puede validar bien la regla de duplicados.'
      );

      const composicionState = principalLabel === 'Sin tipo principal' ? 'warn' : (asociados.length > 0 ? 'ok' : 'info');
      setStep1Check(
        'composicion',
        composicionState,
        principalLabel === 'Sin tipo principal'
          ? 'Hace falta definir el tipo principal antes de revisar ligados.'
          : (asociados.length > 0
            ? 'Hay ' + String(asociados.length) + ' tipo(s) ligado(s): ' + asociados.map((service) => service.label).join(', ') + '.'
            : 'Solo existe el tipo principal; no hay asociados registrados.')
      );
    }

    const postStep1ServiceAction = async (url, payload, fallbackMessage) => {
      const formData = new FormData();
      formData.append(step1ServicesConfig.csrfName, step1ServicesConfig.csrfHash || '');
      Object.entries(payload).forEach(([key, value]) => {
        formData.append(key, value);
      });

      const response = await fetch(url, {
        method: 'POST',
        body: formData,
        credentials: 'same-origin',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
      });
      let result;
      try {
        result = await response.json();
      } catch (parseError) {
        console.error('[postStep1ServiceAction] Response not JSON:', response.status, url);
        throw new Error('El servidor no respondió con JSON. Status: ' + response.status);
      }
      updateStep1ServicesCsrfHash(result.csrfHash || '');
      if (!response.ok || result.status !== 'success') {
        console.error('[postStep1ServiceAction] Server error:', result);
        throw new Error(buildErrorMessage(result, fallbackMessage));
      }
      return result;
    };

    const postStep2Section = async (url, payload) => {
      const formData = new FormData();
      formData.append(step2FormConfig.csrfName, step2FormConfig.csrfHash || '');
      Object.entries(payload).forEach(([key, value]) => {
        formData.append(key, value);
      });

      const response = await fetch(url, {
        method: 'POST',
        body: formData,
        credentials: 'same-origin',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
      });
      const result = await response.json();
      updateCsrfHash(result.csrfHash || '');
      if (!response.ok || !result.success) {
        throw new Error(buildErrorMessage(result, 'No se pudo guardar el bloque.'));
      }
      return result;
    };

    const saveStep1RealForm = async () => {
      if (!step1FormConfig.canEdit) {
        return;
      }

      setStep1Feedback('', '');
      setStep1Saving(true);
      try {
        const formData = new FormData();
        formData.append(step1FormConfig.csrfName, step1FormConfig.csrfHash || '');
        formData.append('folio', String(step1FormConfig.values.folio || ''));
        formData.append('current_step', String(step1FormConfig.values.current_step || 1));
        [
          'cli_directo_id',
          'cli_directo_ejecutivo_id',
          'contrato',
          'unidad',
          'serie',
          'placas',
          'entidad_id',
          'observaciones'
        ].forEach((fieldName) => {
          formData.append(fieldName, getStep1InputValue(fieldName));
        });

        const response = await fetch(step1FormConfig.urls.updateSave, {
          method: 'POST',
          body: formData,
          credentials: 'same-origin',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });
        const result = await response.json();
        updateStep1CsrfHash(result.csrfHash || '');
        if (!response.ok || !result.success) {
          throw new Error(buildErrorMessage(result, 'No se pudo guardar el Paso 1.'));
        }

        commitStep1FormValues();
        setApprovalState(false);
        syncStep1Diagnostics();
        setStep1Feedback('Guardado real completado. Estado local sincronizado sin recargar la pantalla.', 'success');
      } catch (error) {
        setStep1Feedback(error.message || 'No se pudo guardar el Paso 1.', 'error');
      } finally {
        setStep1Saving(false);
      }
    };

    const saveStep1PrincipalType = async () => {
      if (!step1ServicesConfig.canEditPrincipal) {
        return;
      }
      const nextTipoId = getStep1ServiceInputValue('principal_tipo_id');
      if (nextTipoId === '') {
        setStep1ServicesFeedback('Selecciona un tipo principal.', 'error');
        return;
      }

      setStep1ServicesFeedback('', '');
      setStep1ServicesSaving(true);
      try {
        const result = await postStep1ServiceAction(step1ServicesConfig.urls.principalUpdate, {
          tramite_id: String(step1ServicesConfig.tramiteId || '0'),
          tra_tipos_id: nextTipoId,
        }, 'No se pudo actualizar el tipo principal.');
        const oldPrincipalId = Number(step1ServicesState.principalTipoId || 0);
        const nextPrincipalId = Number(result.tra_tipos_id || nextTipoId || 0);
        step1ServicesState.principalTipoId = nextPrincipalId;
        step1ServicesConfig.principalTipoId = nextPrincipalId;
        step1ServicesState.services = step1ServicesState.services.filter((service) => Number(service.tra_tipos_id || 0) !== oldPrincipalId);
        const existingNext = step1ServicesState.services.find((service) => Number(service.tra_tipos_id || 0) === nextPrincipalId);
        if (existingNext) {
          existingNext.label = String(result.label || getTipoLabel(nextPrincipalId));
          existingNext.is_principal = true;
        } else {
          step1ServicesState.services.unshift({
            asociado_id: Number(result.asociado_id || 0),
            tra_tipos_id: nextPrincipalId,
            label: String(result.label || getTipoLabel(nextPrincipalId)),
            is_principal: true,
          });
        }
        step1ServicesState.services = step1ServicesState.services.map((service) => ({
          ...service,
          is_principal: Number(service.tra_tipos_id || 0) === nextPrincipalId,
        }));
        refreshStep1ServicesUI();
        setStep1ServicesFeedback('Tipo principal actualizado.', 'success');
      } catch (error) {
        setStep1ServicesFeedback(error.message || 'No se pudo actualizar el tipo principal.', 'error');
      } finally {
        setStep1ServicesSaving(false);
      }
    };

    const addStep1AssociatedType = async () => {
      if (!step1ServicesConfig.canManageBase) {
        return;
      }
      const nextTipoIds = getStep1ServiceInputValues('add_tipo_id');
      if (nextTipoIds.length === 0) {
        setStep1ServicesFeedback('Selecciona al menos un tipo para agregarlo como ligado.', 'error');
        return;
      }

      setStep1ServicesFeedback('', '');
      setStep1ServicesSaving(true);
      try {
        const addedLabels = [];
        for (const nextTipoId of nextTipoIds) {
          const result = await postStep1ServiceAction(step1ServicesConfig.urls.add, {
            tramite_id: String(step1ServicesConfig.tramiteId || '0'),
            tra_tipos_id: nextTipoId,
          }, 'No se pudo agregar el tipo ligado.');
          const nextLabel = getTipoLabel(result.tra_tipos_id || nextTipoId || 0);
          step1ServicesState.services.push({
            asociado_id: Number(result.asociado_id || 0),
            tra_tipos_id: Number(result.tra_tipos_id || nextTipoId || 0),
            label: nextLabel,
            is_principal: false,
          });
          addedLabels.push(nextLabel);
        }
        if (step1ServiceInputs.add_tipo_id && step1ServiceInputs.add_tipo_id instanceof HTMLSelectElement) {
          Array.from(step1ServiceInputs.add_tipo_id.options).forEach((option) => {
            option.selected = false;
          });
        }
        refreshStep1ServicesUI();
        setStep1ServicesFeedback(
          addedLabels.length === 1
            ? 'Tipo ligado agregado.'
            : 'Se agregaron ' + String(addedLabels.length) + ' tipos ligados.',
          'success'
        );
      } catch (error) {
        setStep1ServicesFeedback(error.message || 'No se pudo agregar el tipo ligado.', 'error');
      } finally {
        setStep1ServicesSaving(false);
      }
    };

    const updateStep1AssociatedType = async (asociadoId) => {
      if (!step1ServicesConfig.canEditAsociado) {
        return;
      }
      const select = root.querySelector('[data-step1-associated-select="' + String(asociadoId) + '"]');
      const nextTipoId = select ? String(select.value || '').trim() : '';
      if (nextTipoId === '') {
        setStep1ServicesFeedback('Selecciona el nuevo tipo asociado.', 'error');
        return;
      }

      setStep1ServicesFeedback('', '');
      setStep1ServicesSaving(true);
      try {
        const result = await postStep1ServiceAction(step1ServicesConfig.urls.update, {
          tramite_id: String(step1ServicesConfig.tramiteId || '0'),
          asociado_id: String(asociadoId),
          tra_tipos_id: nextTipoId,
        }, 'No se pudo actualizar el tipo asociado.');
        step1ServicesState.services = step1ServicesState.services.map((service) => {
          if (String(service.asociado_id || 0) !== String(asociadoId)) {
            return service;
          }
          return {
            ...service,
            tra_tipos_id: Number(result.tra_tipos_id || nextTipoId || 0),
            label: String(result.label || getTipoLabel(result.tra_tipos_id || nextTipoId || 0)),
          };
        });
        refreshStep1ServicesUI();
        setStep1ServicesFeedback('Tipo asociado actualizado.', 'success');
      } catch (error) {
        setStep1ServicesFeedback(error.message || 'No se pudo actualizar el tipo asociado.', 'error');
      } finally {
        setStep1ServicesSaving(false);
      }
    };

    const deleteStep1AssociatedType = async (asociadoId) => {
      if (!step1ServicesConfig.canDeleteAsociado) {
        return;
      }

      setStep1ServicesFeedback('', '');
      setStep1ServicesSaving(true);
      try {
        await postStep1ServiceAction(step1ServicesConfig.urls.delete, {
          tramite_id: String(step1ServicesConfig.tramiteId || '0'),
          asociado_id: String(asociadoId),
        }, 'No se pudo eliminar el tipo asociado.');
        step1ServicesState.services = step1ServicesState.services.filter((service) => String(service.asociado_id || 0) !== String(asociadoId));
        refreshStep1ServicesUI();
        setStep1ServicesFeedback('Tipo asociado eliminado.', 'success');
      } catch (error) {
        setStep1ServicesFeedback(error.message || 'No se pudo eliminar el tipo asociado.', 'error');
      } finally {
        setStep1ServicesSaving(false);
      }
    };

    const saveStep2RealForm = async () => {
      if (!step2FormConfig.canEdit) {
        return;
      }

      setFeedback('', '');
      setStep2Saving(true);
      try {
        await postStep2Section(step2FormConfig.urls.updateGestorSave, {
          empresa_gestora_id: getInputValue('empresa_gestora_id'),
          gestor_id: getInputValue('gestor_id'),
        });

        await postStep2Section(step2FormConfig.urls.updateDerechosSave, {
          derechos_tramite: getInputValue('derechos_tramite'),
          derechos_pago_sitio: getInputValue('derechos_pago_sitio'),
          derechos_vigencia: getInputValue('derechos_vigencia'),
          derechos_revol_cliente: getInputValue('derechos_revol_cliente'),
          derechos_refer_banc: getInputValue('derechos_refer_banc'),
        });

        commitStep2FormValues();
        setApprovalState(false);
        syncStep2CardsFromInputs();
        syncApprovalState();
        setFeedback('Guardado real completado. Estado local sincronizado sin recargar la pantalla.', 'success');
      } catch (error) {
        setFeedback(error.message || 'No se pudo guardar el Paso 2.', 'error');
      } finally {
        setStep2Saving(false);
      }
    };

    const uploadStep3Evidence = async () => {
      if (!step3FormConfig.canUpload) {
        return;
      }

      const file = step3EvidenceState.pendingFile || (step3Inputs.file && step3Inputs.file.files ? step3Inputs.file.files[0] : null);
      const comprobanteFinal = step3Inputs.comprobante_final ? String(step3Inputs.comprobante_final.value || '').trim() : '';
      if (!comprobanteFinal) {
        setStep3Feedback('Selecciona el tipo de evidencia final.', 'error');
        return;
      }
      if (!file) {
        setStep3Feedback('Selecciona un archivo para subirlo como evidencia final.', 'error');
        return;
      }

      setStep3Feedback('', '');
      setStep3Saving(true);
      try {
        const formData = new FormData();
        formData.append(step3FormConfig.csrfName, step3FormConfig.csrfHash || '');
        formData.append('comprobante_final', comprobanteFinal);
        formData.append('file', file);

        const response = await fetch(step3FormConfig.urls.upload, {
          method: 'POST',
          body: formData,
          credentials: 'same-origin',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });
        const result = await response.json();
        if (result && result.csrfHash) {
          step3FormConfig.csrfHash = result.csrfHash;
        }
        if (!response.ok || !result.success) {
          throw new Error(buildErrorMessage(result, 'No se pudo subir la evidencia final.'));
        }

        step3EvidenceState.docs.unshift({
          file: String(result.fileName || file.name || ''),
          comprobante_final: String(result.comprobanteFinal || comprobanteFinal),
        });
        if (step3Inputs.file) {
          step3Inputs.file.value = '';
        }
        setStep3PendingFile(null);
        renderStep3EvidenceUI();
        setStep3Feedback('Evidencia final subida correctamente.', 'success');
      } catch (error) {
        setStep3Feedback(error.message || 'No se pudo subir la evidencia final.', 'error');
      } finally {
        setStep3Saving(false);
      }
    };

    const uploadStep2Document = async () => {
      if (!step2FormConfig.canUploadDocs) {
        return;
      }

      if (!step2DocState.pendingFile) {
        setStep2DocFeedback('Selecciona un archivo para subirlo como comprobante de derechos.', 'error');
        return;
      }

      setStep2DocFeedback('', '');
      setStep2DocSaving(true);
      try {
        const formData = new FormData();
        formData.append(step2FormConfig.csrfName, step2FormConfig.csrfHash || '');
        formData.append('file', step2DocState.pendingFile);

        const response = await fetch(step2FormConfig.urls.uploadDoc, {
          method: 'POST',
          body: formData,
          credentials: 'same-origin',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });
        const result = await response.json();
        updateCsrfHash(result.csrfHash || '');
        if (!response.ok || !result.success) {
          throw new Error(buildErrorMessage(result, 'No se pudo subir el comprobante de derechos.'));
        }

        step2DocState.docs.unshift({
          file: String(result.fileName || result.file || step2DocState.pendingFile.name || ''),
        });
        if (step2DocFileInput) {
          step2DocFileInput.value = '';
        }
        setStep2PendingFile(null);
        renderStep2DocUI();
        setStep2DocFeedback('Comprobante de derechos subido correctamente.', 'success');
      } catch (error) {
        setStep2DocFeedback(error.message || 'No se pudo subir el comprobante de derechos.', 'error');
      } finally {
        setStep2DocSaving(false);
      }
    };

    const deleteStep2Document = async (fileName) => {
      if (!step2FormConfig.canDeleteDocs || !fileName) {
        return;
      }

      setStep2DocFeedback('', '');
      setStep2DocSaving(true);
      try {
        const formData = new FormData();
        formData.append(step2FormConfig.csrfName, step2FormConfig.csrfHash || '');
        formData.append('tramite_id', String(step2FormConfig.tramiteId || '0'));
        formData.append('file', String(fileName));

        const response = await fetch(step2FormConfig.urls.deleteDoc, {
          method: 'POST',
          body: formData,
          credentials: 'same-origin',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });
        const result = await response.json();
        updateCsrfHash(result.csrfHash || '');
        if (!response.ok || !result.success) {
          throw new Error(buildErrorMessage(result, 'No se pudo eliminar el comprobante de derechos.'));
        }

        step2DocState.docs = step2DocState.docs.filter((doc) => String(doc.file || '') !== String(fileName));
        renderStep2DocUI();
        setStep2DocFeedback('Comprobante de derechos eliminado correctamente.', 'success');
      } catch (error) {
        setStep2DocFeedback(error.message || 'No se pudo eliminar el comprobante de derechos.', 'error');
      } finally {
        setStep2DocSaving(false);
      }
    };

    const uploadStep1Document = async () => {
      if (!step1DocsFormConfig.canUpload || !step1DocsFormConfig.urls || step1DocsFormConfig.urls.upload === '#') {
        return;
      }

      const documentoId = step1DocTypeSelect ? String(step1DocTypeSelect.value || '').trim() : '';
      const file = step1DocState.pendingFile || (step1DocFileInput && step1DocFileInput.files ? step1DocFileInput.files[0] : null);
      if (documentoId === '') {
        setStep1DocFeedback('Selecciona primero el documento del catálogo que vas a cargar.', 'error');
        return;
      }
      if (!file) {
        setStep1DocFeedback('Selecciona un archivo para cargarlo en el expediente.', 'error');
        return;
      }

      setStep1DocFeedback('', '');
      setStep1DocSaving(true);
      try {
        const formData = new FormData();
        formData.append(step1DocsFormConfig.csrfName, step1DocsFormConfig.csrfHash || '');
        formData.append('documento_id', documentoId);
        formData.append('file', file);

        const response = await fetch(step1DocsFormConfig.urls.upload, {
          method: 'POST',
          body: formData,
          credentials: 'same-origin',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });
        const result = await response.json();
        updateStep1DocsCsrfHash(result.csrfHash || '');
        if (!response.ok || !result.success) {
          throw new Error(buildErrorMessage(result, 'No se pudo subir el documento del expediente.'));
        }

        const numericDocumentoId = Number(documentoId || 0);
        let docEntry = step1DocState.docs.find((doc) => Number(doc.documento_id || 0) === numericDocumentoId);
        if (!docEntry) {
          const selectedDocMeta = getStep1SelectedDocMeta();
          docEntry = {
            documento_id: numericDocumentoId,
            documento_nombre: selectedDocMeta && selectedDocMeta.documentoNombre ? selectedDocMeta.documentoNombre : ('Documento #' + documentoId),
            is_required: selectedDocMeta ? selectedDocMeta.isRequired : true,
            is_configured: selectedDocMeta ? selectedDocMeta.isConfigured : false,
            source_badge: selectedDocMeta ? selectedDocMeta.sourceBadge : 'Catálogo general',
            source_tone: selectedDocMeta ? selectedDocMeta.sourceTone : 'neutral',
            source_types_label: '',
            has_file: false,
            file: '',
            file_url: '',
            status_label: 'Pendiente',
            comentario: '',
          };
          step1DocState.docs.unshift(docEntry);
        }

        docEntry.file = String(result.fileName || file.name || '');
        docEntry.file_url = String(result.filePath || buildStep1FileUrl(docEntry.file));
        docEntry.has_file = true;
        docEntry.status_label = 'Cargado';
        if (step1DocFileInput) {
          step1DocFileInput.value = '';
        }
        setStep1PendingDocFile(null);
        renderStep1DocUI();
        setStep1DocFeedback('Documento del expediente subido correctamente.', 'success');
      } catch (error) {
        setStep1DocFeedback(error.message || 'No se pudo subir el documento del expediente.', 'error');
      } finally {
        setStep1DocSaving(false);
      }
    };

    const deleteStep1Document = async (fileName, documentoId) => {
      if (!step1DocsFormConfig.canDelete || !fileName || !documentoId || !step1DocsFormConfig.urls || step1DocsFormConfig.urls.delete === '#') {
        return;
      }

      setStep1DocFeedback('', '');
      setStep1DocSaving(true);
      try {
        const formData = new FormData();
        formData.append(step1DocsFormConfig.csrfName, step1DocsFormConfig.csrfHash || '');
        formData.append('tramite_id', String(step1DocsFormConfig.tramiteId || '0'));
        formData.append('documento_id', String(documentoId));
        formData.append('file', String(fileName));

        const response = await fetch(step1DocsFormConfig.urls.delete, {
          method: 'POST',
          body: formData,
          credentials: 'same-origin',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });
        const result = await response.json();
        updateStep1DocsCsrfHash(result.csrfHash || '');
        if (!response.ok || !result.success) {
          throw new Error(buildErrorMessage(result, 'No se pudo eliminar el documento del expediente.'));
        }

        const docEntry = step1DocState.docs.find((doc) => Number(doc.documento_id || 0) === Number(documentoId || 0));
        if (docEntry) {
          docEntry.file = '';
          docEntry.file_url = '';
          docEntry.has_file = false;
          docEntry.status_label = 'Pendiente';
        }
        renderStep1DocUI();
        setStep1DocFeedback('Documento del expediente eliminado correctamente.', 'success');
      } catch (error) {
        setStep1DocFeedback(error.message || 'No se pudo eliminar el documento del expediente.', 'error');
      } finally {
        setStep1DocSaving(false);
      }
    };

    const deleteStep3Evidence = async (fileName) => {
      if (!step3FormConfig.canDelete || !fileName) {
        return;
      }

      setStep3Feedback('', '');
      setStep3Saving(true);
      try {
        const formData = new FormData();
        formData.append(step3FormConfig.csrfName, step3FormConfig.csrfHash || '');
        formData.append('tramite_id', String(step3FormConfig.tramiteId || '0'));
        formData.append('file', String(fileName));

        const response = await fetch(step3FormConfig.urls.delete, {
          method: 'POST',
          body: formData,
          credentials: 'same-origin',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });
        const result = await response.json();
        if (result && result.csrfHash) {
          step3FormConfig.csrfHash = result.csrfHash;
        }
        if (!response.ok || !result.success) {
          throw new Error(buildErrorMessage(result, 'No se pudo eliminar la evidencia final.'));
        }

        step3EvidenceState.docs = step3EvidenceState.docs.filter((doc) => String(doc.file || '') !== String(fileName));
        renderStep3EvidenceUI();
        setStep3Feedback('Evidencia final eliminada correctamente.', 'success');
      } catch (error) {
        setStep3Feedback(error.message || 'No se pudo eliminar la evidencia final.', 'error');
      } finally {
        setStep3Saving(false);
      }
    };

    const syncApprovalState = () => {
      const snapshot = getStep2ApprovalSnapshot();
      if (!snapshot.ready && getApprovalState()) {
        setApprovalState(false);
      }
      const isApproved = snapshot.ready && canApproveStep2 && getApprovalState();
      const approvalDisplay = getStep2ApprovalDisplay(snapshot, isApproved);
      root.dataset.operationalApprovedStep2 = isApproved ? '1' : '0';

      if (step2Panel) {
        step2Panel.classList.remove('is-ready', 'is-info', 'is-pending', 'is-approved-session');
        if (snapshot.ready && canApproveStep2 && !isApproved) {
          step2Panel.classList.add('is-ready');
        } else if (snapshot.ready) {
          step2Panel.classList.add('is-info');
        } else {
          step2Panel.classList.add('is-pending');
        }
        step2Panel.classList.toggle('is-approved-session', isApproved);
      }

      if (step2Title) {
        step2Title.textContent = approvalDisplay.title;
      }

      if (step2Copy) {
        step2Copy.textContent = approvalDisplay.copy;
      }

      renderStep2ApprovalActions(snapshot, isApproved);

      root.querySelectorAll('[data-operational-reset-row]').forEach((row) => {
        row.hidden = !isApproved;
      });

      if (step3Sequence) {
        step3Sequence.hidden = false;
        step3Sequence.classList.toggle('is-approved-handoff', isApproved);
      }

      if (step4InlinePanel) {
        const currentOperationalStep = Number(root.dataset.operationalActiveStep || state.activeStep || 1);
        step4InlinePanel.hidden = !isStep3FinancialGateReady() && currentOperationalStep < 4;
      }

      if (step3Tail) {
        step3Tail.textContent = isApproved ? 'Paso 2 aprobado en esta sesion' : 'Esperando aprobacion del Paso 2';
      }

      if (step3Note) {
        step3Note.textContent = isApproved
          ? 'Paso 2 ya quedo aprobado en esta sesion del prototipo. Aqui solo queda documentar el cierre que destraba a la par Pago a gestor y Cobro a cliente.'
          : 'Si Paso 2 ya fue aprobado, aqui ya no se valida derechos: aqui se documenta el cierre que destraba a la par Pago a gestor y Cobro a cliente.';
        step3Note.classList.toggle('is-approved', isApproved);
      }

      syncOperationalChecklistState();
    };

    const updateBaseTabHref = (step) => {
      const baseTab = document.querySelector('[data-phase-tab="base"]');
      const stepState = state.steps[String(step)] || state.steps[step];
      if (baseTab && stepState && stepState.url) {
        baseTab.setAttribute('href', stepState.url);
      }
    };

    const escapeHtml = (value) => String(value ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');

    const renderMiniItems = (items) => items.map((item) => {
      const status = normalizeChecklistStatus(item && item.status);
      if (status) {
        return '<div class="tp-mini-item tp-mini-item-status is-' + status + '">'
          + '<span class="tp-mini-status-square" aria-hidden="true"></span>'
          + '<div class="tp-mini-item-copy">'
            + '<strong>' + escapeHtml(item.label) + '</strong>'
            + '<span>' + escapeHtml(item.value) + '</span>'
          + '</div>'
        + '</div>';
      }
      return '<div class="tp-mini-item"><strong>' + escapeHtml(item.label) + '</strong><span>' + escapeHtml(item.value) + '</span></div>';
    }).join('');

    const renderSaveItems = (items) => items.map((item) => (
      '<div class="tp-save-item">'
        + '<span class="tp-save-kicker">' + escapeHtml(item.label) + '</span>'
        + '<strong class="tp-save-endpoint">' + escapeHtml(item.endpoint) + '</strong>'
        + '<p>' + escapeHtml(item.note) + '</p>'
      + '</div>'
    )).join('');

    const renderHeroCards = (items) => items.map((item) => (
      '<div class="tp-hero-card">'
        + '<span class="tp-hero-label">' + escapeHtml(item.label) + '</span>'
        + '<span class="tp-hero-value">' + escapeHtml(item.value) + '</span>'
        + (item.note ? '<small class="tp-hero-note">' + escapeHtml(item.note) + '</small>' : '')
      + '</div>'
    )).join('');

    const setText = (token, value) => {
      const node = root.querySelector('[data-operational-text="' + token + '"]');
      if (node) {
        node.textContent = value;
      }
    };

    const applyStep = (step, options = {}) => {
      if (!canAccessOperationalStep3() && Number(step) >= 3) {
        step = 2;
      }

      syncOperationalChecklistState();

      const stepState = state.steps[String(step)] || state.steps[step];
      if (!stepState) {
        return;
      }

      try {
        window.sessionStorage.setItem(storageKey, String(step));
      } catch (error) {
      }

      updateBaseTabHref(step);

      root.dataset.operationalActiveStep = String(step);

      setText('hero-chip', stepState.displayLabel);
      setText('hero-copy', stepState.heroCopy || '');
      setText('hero-meta', stepState.heroMeta);
      setText('progress-value', String(stepState.progress) + '%');
      setText('signal-step', stepState.displayLabel);
      setText('signal-next-action', stepState.nextAction);
      setText('signal-doc-count', stepState.docCount);
      setText('signal-risk', stepState.risk);
      setText('summary-title', stepState.summaryTitle);
      setText('checklist-title', stepState.checklistTitle);
      setText('save-title', stepState.saveTitle);

      const progressFill = root.querySelector('[data-operational-progress-fill]');
      if (progressFill) {
        progressFill.style.width = String(stepState.progress) + '%';
      }

      const summaryList = root.querySelector('[data-operational-summary-list]');
      if (summaryList) {
        summaryList.innerHTML = renderMiniItems(stepState.summaryItems || []);
      }

      const checklistList = root.querySelector('[data-operational-checklist-list]');
      if (checklistList) {
        checklistList.innerHTML = renderMiniItems(stepState.checklistItems || []);
      }

      const saveList = root.querySelector('[data-operational-save-list]');
      if (saveList) {
        saveList.innerHTML = renderSaveItems(stepState.saveContracts || []);
      }

      const heroGrid = root.querySelector('[data-operational-hero-grid]');
      if (heroGrid) {
        heroGrid.innerHTML = renderHeroCards(stepState.heroCards || []);
      }

      root.querySelectorAll('[data-operational-step-link]').forEach((link) => {
        const linkStep = Number(link.getAttribute('data-operational-step-link'));
        const isActive = linkStep === Number(step);
        link.classList.toggle('is-active', isActive);
        link.setAttribute('aria-current', isActive ? 'step' : 'false');
      });

      root.querySelectorAll('[data-operational-focus]').forEach((node) => {
        node.classList.toggle('is-focused', Number(node.getAttribute('data-operational-focus')) === Number(step));
      });

      root.querySelectorAll('[data-operational-doc-block]').forEach((node) => {
        node.classList.toggle('is-focused', Number(node.getAttribute('data-operational-doc-block')) === Number(step));
      });

      if (options.pushState !== false && stepState.url) {
        const currentUrl = window.location.pathname + window.location.search;
        const targetUrl = new URL(stepState.url, window.location.origin);
        if (currentUrl !== targetUrl.pathname + targetUrl.search) {
          window.history.pushState({ prototypeOperationalStep: step }, '', targetUrl.pathname + targetUrl.search);
        }
      }

      if (options.scroll !== false) {
        const anchor = root.querySelector('[data-operational-anchor="' + String(step) + '"]');
        if (anchor) {
          anchor.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
      }
    };

    if (step2Inputs.empresa_gestora_id) {
      step2Inputs.empresa_gestora_id.addEventListener('change', async () => {
        await loadGestores(getInputValue('empresa_gestora_id'));
        syncStep2CardsFromInputs();
        syncApprovalState();
      });
    }

    Object.values(step2Inputs).forEach((input) => {
      if (!input) {
        return;
      }
      input.addEventListener('input', () => {
        syncStep2CardsFromInputs();
        syncApprovalState();
      });
      input.addEventListener('change', () => {
        syncStep2CardsFromInputs();
        syncApprovalState();
      });
    });

    if (step1Inputs.cli_directo_id) {
      step1Inputs.cli_directo_id.addEventListener('change', (event) => {
        const nextClienteId = String(event.target.value || '').trim();
        loadEjecutivos(nextClienteId);
        syncStep1Diagnostics();
      });
    }

    Object.entries(step1Inputs).forEach(([name, input]) => {
      if (!input || name === 'cli_directo_id') {
        return;
      }
      input.addEventListener('input', syncStep1Diagnostics);
      input.addEventListener('change', syncStep1Diagnostics);
    });

    Object.values(step1ServiceInputs).forEach((input) => {
      if (!input) {
        return;
      }
      input.addEventListener('change', syncStep1Diagnostics);
    });

    if (step1SaveButton) {
      step1SaveButton.addEventListener('click', () => {
        saveStep1RealForm();
      });
    }

    if (step1PrincipalSaveButton) {
      step1PrincipalSaveButton.addEventListener('click', () => {
        saveStep1PrincipalType();
      });
    }

    if (step1AssociatedAddButton) {
      step1AssociatedAddButton.addEventListener('click', () => {
        console.log('[SGL Prototype] Click en Agregar tipos ligados. canManageBase:', step1ServicesConfig.canManageBase);
        addStep1AssociatedType();
      });
    }

    if (step1DocUploadButton) {
      step1DocUploadButton.addEventListener('click', () => {
        uploadStep1Document();
      });
    }

    if (step1DocDropzone) {
      step1DocDropzone.addEventListener('click', () => {
        if (step1DocsFormConfig.canUpload && step1DocFileInput) {
          step1DocFileInput.click();
        }
      });

      ['dragenter', 'dragover'].forEach((eventName) => {
        step1DocDropzone.addEventListener(eventName, (event) => {
          event.preventDefault();
          if (!step1DocsFormConfig.canUpload) {
            return;
          }
          step1DocDropzone.classList.add('is-dragover');
        });
      });

      ['dragleave', 'dragend', 'drop'].forEach((eventName) => {
        step1DocDropzone.addEventListener(eventName, (event) => {
          event.preventDefault();
          step1DocDropzone.classList.remove('is-dragover');
        });
      });

      step1DocDropzone.addEventListener('drop', (event) => {
        if (!step1DocsFormConfig.canUpload) {
          return;
        }
        const files = event.dataTransfer && event.dataTransfer.files ? event.dataTransfer.files : null;
        if (!files || !files.length) {
          return;
        }
        setStep1PendingDocFile(files[0]);
      });
    }

    if (step1DocFileInput) {
      step1DocFileInput.addEventListener('change', () => {
        const nextFile = step1DocFileInput.files && step1DocFileInput.files.length ? step1DocFileInput.files[0] : null;
        setStep1PendingDocFile(nextFile);
      });
    }

    if (step3UploadButton) {
      step3UploadButton.addEventListener('click', () => {
        uploadStep3Evidence();
      });
    }

    if (evidenceSaveButton) {
      evidenceSaveButton.addEventListener('click', () => {
        saveEvidenceComment();
      });
    }

    if (evidenceInput) {
      evidenceInput.addEventListener('keydown', (event) => {
        if ((event.metaKey || event.ctrlKey) && event.key === 'Enter') {
          event.preventDefault();
          saveEvidenceComment();
        }
      });
    }

    if (step2DocUploadButton) {
      step2DocUploadButton.addEventListener('click', () => {
        uploadStep2Document();
      });
    }

    if (step2DocDropzone) {
      step2DocDropzone.addEventListener('click', () => {
        if (step2FormConfig.canUploadDocs && step2DocFileInput) {
          step2DocFileInput.click();
        }
      });

      ['dragenter', 'dragover'].forEach((eventName) => {
        step2DocDropzone.addEventListener(eventName, (event) => {
          event.preventDefault();
          if (!step2FormConfig.canUploadDocs) {
            return;
          }
          step2DocDropzone.classList.add('is-dragover');
        });
      });

      ['dragleave', 'dragend', 'drop'].forEach((eventName) => {
        step2DocDropzone.addEventListener(eventName, (event) => {
          event.preventDefault();
          step2DocDropzone.classList.remove('is-dragover');
        });
      });

      step2DocDropzone.addEventListener('drop', (event) => {
        if (!step2FormConfig.canUploadDocs) {
          return;
        }
        const files = event.dataTransfer && event.dataTransfer.files ? event.dataTransfer.files : null;
        if (!files || !files.length) {
          return;
        }
        setStep2PendingFile(files[0]);
      });
    }

    if (step2DocFileInput) {
      step2DocFileInput.addEventListener('change', () => {
        const nextFile = step2DocFileInput.files && step2DocFileInput.files.length ? step2DocFileInput.files[0] : null;
        setStep2PendingFile(nextFile);
      });
    }

    if (step3Dropzone) {
      step3Dropzone.addEventListener('click', () => {
        if (step3FormConfig.canUpload && step3Inputs.file) {
          step3Inputs.file.click();
        }
      });

      ['dragenter', 'dragover'].forEach((eventName) => {
        step3Dropzone.addEventListener(eventName, (event) => {
          event.preventDefault();
          if (!step3FormConfig.canUpload) {
            return;
          }
          step3Dropzone.classList.add('is-dragover');
        });
      });

      ['dragleave', 'dragend', 'drop'].forEach((eventName) => {
        step3Dropzone.addEventListener(eventName, (event) => {
          event.preventDefault();
          step3Dropzone.classList.remove('is-dragover');
        });
      });

      step3Dropzone.addEventListener('drop', (event) => {
        if (!step3FormConfig.canUpload) {
          return;
        }
        const files = event.dataTransfer && event.dataTransfer.files ? event.dataTransfer.files : null;
        if (!files || !files.length) {
          return;
        }
        const nextFile = files[0];
        if (step3Inputs.file && typeof DataTransfer !== 'undefined') {
          const dataTransfer = new DataTransfer();
          dataTransfer.items.add(nextFile);
          step3Inputs.file.files = dataTransfer.files;
        }
        setStep3PendingFile(nextFile);
      });
    }

    if (step3Inputs.file) {
      step3Inputs.file.addEventListener('change', () => {
        const nextFile = step3Inputs.file.files && step3Inputs.file.files.length ? step3Inputs.file.files[0] : null;
        setStep3PendingFile(nextFile);
      });
    }

    root.addEventListener('click', (event) => {
      const stepLink = event.target instanceof Element ? event.target.closest('[data-operational-step-link]') : null;
      if (stepLink) {
        if (event.defaultPrevented || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || event.button !== 0) {
          return;
        }
        event.preventDefault();
        if (stepLink.getAttribute('data-operational-approve') === '1') {
          openStep2ApproveModal();
          return;
        }
        const nextStep = Number(stepLink.getAttribute('data-operational-step-link'));
        if (nextStep >= 1 && nextStep <= 3) {
          applyStep(nextStep);
        }
        return;
      }

      const resetApprovalLink = event.target instanceof Element ? event.target.closest('[data-operational-reset-approval="1"]') : null;
      if (resetApprovalLink) {
        event.preventDefault();
        setApprovalState(false);
        syncApprovalState();
        return;
      }

      const saveButton = event.target instanceof Element ? event.target.closest('[data-step1-associated-save]') : null;
      if (saveButton) {
        updateStep1AssociatedType(saveButton.getAttribute('data-step1-associated-save'));
        return;
      }

      const deleteButton = event.target instanceof Element ? event.target.closest('[data-step1-associated-delete]') : null;
      if (deleteButton) {
        deleteStep1AssociatedType(deleteButton.getAttribute('data-step1-associated-delete'));
        return;
      }

      const deleteStep1DocButton = event.target instanceof Element ? event.target.closest('[data-step1-doc-delete]') : null;
      if (deleteStep1DocButton) {
        deleteStep1Document(
          deleteStep1DocButton.getAttribute('data-step1-doc-delete'),
          deleteStep1DocButton.getAttribute('data-step1-doc-id')
        );
        return;
      }

      const previewButton = event.target instanceof Element ? event.target.closest('[data-doc-preview-url]') : null;
      if (previewButton) {
        event.preventDefault();
        openDocPreviewModal(
          previewButton.getAttribute('data-doc-preview-name'),
          previewButton.getAttribute('data-doc-preview-url'),
          previewButton.getAttribute('data-doc-preview-meta')
        );
        return;
      }

      const deleteStep2DocButton = event.target instanceof Element ? event.target.closest('[data-step2-doc-delete]') : null;
      if (deleteStep2DocButton) {
        deleteStep2Document(deleteStep2DocButton.getAttribute('data-step2-doc-delete'));
        return;
      }

      const deleteStep3DocButton = event.target instanceof Element ? event.target.closest('[data-step3-doc-delete]') : null;
      if (deleteStep3DocButton) {
        deleteStep3Evidence(deleteStep3DocButton.getAttribute('data-step3-doc-delete'));
      }
    });

    if (step2SaveButton) {
      step2SaveButton.addEventListener('click', () => {
        saveStep2RealForm();
      });
    }

    if (step2ApproveConfirmButton) {
      step2ApproveConfirmButton.addEventListener('click', () => {
        approveStep2Real();
      });
    }

    if (step2ApproveCancelButton) {
      step2ApproveCancelButton.addEventListener('click', () => {
        closeStep2ApproveModal();
      });
    }

    if (step2ApproveCloseButton) {
      step2ApproveCloseButton.addEventListener('click', () => {
        closeStep2ApproveModal();
      });
    }

    if (step2ApproveModal) {
      step2ApproveModal.addEventListener('cancel', (event) => {
        event.preventDefault();
        closeStep2ApproveModal();
      });
      step2ApproveModal.addEventListener('click', (event) => {
        if (event.target === step2ApproveModal) {
          closeStep2ApproveModal();
        }
      });
    }

    if (docPreviewCloseButton) {
      docPreviewCloseButton.addEventListener('click', () => {
        closeDocPreviewModal();
      });
    }

    if (docPreviewCancelButton) {
      docPreviewCancelButton.addEventListener('click', () => {
        closeDocPreviewModal();
      });
    }

    if (docPreviewModal) {
      docPreviewModal.addEventListener('cancel', (event) => {
        event.preventDefault();
        closeDocPreviewModal();
      });
      docPreviewModal.addEventListener('click', (event) => {
        if (event.target === docPreviewModal) {
          closeDocPreviewModal();
        }
      });
    }

    window.addEventListener('popstate', () => {
      const match = window.location.pathname.match(/paso-(\d+)/);
      const nextStep = match ? Number(match[1]) : Number(state.activeStep || 1);
      if (nextStep >= 1 && nextStep <= 3) {
        applyStep(nextStep, { pushState: false, scroll: false });
      }
    });

    syncApprovalState();
    refreshStep1ServicesUI();
    syncStep1Diagnostics();
    renderStep1DocUI();
    setStep1PendingDocFile(null);
    setStep1DocSaving(false);
    syncStep2CardsFromInputs();
    renderStep2DocUI();
    setStep2PendingFile(null);
    setStep2DocSaving(false);
    renderEvidenceList();
    setEvidenceSaving(false);
    renderStep3EvidenceUI();
    setStep3Saving(false);
    setStep3PendingFile(null);
    syncOperationalChecklistState();
    applyStep(Number(state.activeStep || 1), { pushState: false, scroll: false });
  })();
</script>
<?php endif; ?>

<script>
  (function() {
    const baseTab = document.querySelector('[data-phase-tab="base"]');
    if (!baseTab) {
      return;
    }

    const storageKey = 'sglPrototypeOperationalStep';
    const defaultUrl = baseTab.getAttribute('data-phase-base-default-url') || baseTab.getAttribute('href');
    const resolveUrl = (step) => {
      if (!(step >= 1 && step <= 3)) {
        return defaultUrl;
      }
      return defaultUrl.replace(/paso-\d+$/, 'paso-' + String(step));
    };

    try {
      const storedStep = Number(window.sessionStorage.getItem(storageKey) || '1');
      baseTab.setAttribute('href', resolveUrl(storedStep));
    } catch (error) {
      baseTab.setAttribute('href', defaultUrl);
    }
  })();
</script>

<script>
  (function() {
    const currentStep = <?= (int) $activeStep ?>;
    const isServerApprovedStep2 = <?= $prototypeStep2PostApprovalStage ? 'true' : 'false' ?>;
    const readApprovalState = () => {
      return isServerApprovedStep2;
    };

    const step4Panel = document.querySelector('[data-operational-step4-handoff-panel]');
    const step4Title = document.querySelector('[data-operational-step4-title]');
    const step4Copy = document.querySelector('[data-operational-step4-copy]');
    const step4Note = document.querySelector('[data-operational-step4-note]');
    const heroCopy = document.querySelector('[data-operational-text="hero-copy"]');
    const heroMeta = document.querySelector('[data-operational-text="hero-meta"]');
    const nextAction = document.querySelector('[data-operational-text="signal-next-action"]');

    const defaults = {
      heroCopy: heroCopy ? heroCopy.textContent : '',
      heroMeta: heroMeta ? heroMeta.textContent : '',
      nextAction: nextAction ? nextAction.textContent : '',
    };

    const isApproved = readApprovalState();
    if (!step4Panel) {
      return;
    }

    step4Panel.classList.remove('is-info', 'is-pending', 'is-ready', 'is-approved-session');
    if (isApproved) {
      step4Panel.classList.add('is-approved-session');
      if (step4Title) {
        step4Title.textContent = 'Fase financiera ya habilitada';
      }
      if (step4Copy) {
        step4Copy.textContent = 'El tramite ya paso a Pago a gestor o a una etapa posterior. Aqui la lectura debe cambiar a pago, comprobantes y conciliacion con gestor, sin mezclar otra vez la fase operativa.';
      }
      if (step4Note) {
        step4Note.textContent = 'Estado real detectado desde el status del tramite.';
        step4Note.classList.add('is-approved');
      }
      if (currentStep === 4 && heroCopy) {
        heroCopy.textContent = 'La fase financiera ya quedo habilitada por el status real del tramite. Este frente ahora debe orientar sobre pago, comprobantes y conciliacion con gestor.';
      }
      if (currentStep === 4 && heroMeta) {
        heroMeta.textContent = 'Handoff detectado desde el flujo real';
      }
      if (currentStep === 4 && nextAction) {
        nextAction.textContent = 'Capturar pago y comprobantes del gestor';
      }
      return;
    }

    step4Panel.classList.add('is-info');
    if (step4Title) {
      step4Title.textContent = 'Fase financiera esperando handoff operativo';
    }
    if (step4Copy) {
      step4Copy.textContent = 'Pago a gestor debe leer el cierre de la fase base antes de sentirse como frente habilitado. Esta franja ahora se basa en el status real del tramite.';
    }
    if (step4Note) {
      step4Note.textContent = 'El tramite aun no ha llegado a Pago a gestor.';
      step4Note.classList.remove('is-approved');
    }
    if (currentStep === 4 && heroCopy) {
      heroCopy.textContent = defaults.heroCopy;
    }
    if (currentStep === 4 && heroMeta) {
      heroMeta.textContent = defaults.heroMeta;
    }
    if (currentStep === 4 && nextAction) {
      nextAction.textContent = defaults.nextAction;
    }
  })();
</script>

<script>
  (function() {
    const step4FormConfig = <?= json_encode($prototypeStep4Form, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    const findVisibleStep4Node = (selector) => {
      const nodes = Array.from(document.querySelectorAll(selector));
      return nodes.find((node) => !node.closest('[hidden]') && node.offsetParent !== null) || nodes[0] || null;
    };
    const step4FormNode = findVisibleStep4Node('[data-step4-live-form]');
    if (!step4FormNode) {
      return;
    }

    const step4SaveButton = step4FormNode.querySelector('[data-step4-save]');
    const step4Feedback = step4FormNode.querySelector('[data-step4-feedback]');
    const step4Inputs = Array.from(step4FormNode.querySelectorAll('[data-step4-input]'));
    const step4CostList = step4FormNode.querySelector('[data-step4-cost-list]');
    const step4CostTotal = step4FormNode.querySelector('[data-step4-cost-total]');
    const step4CostStatus = step4FormNode.querySelector('[data-step4-cost-status]');
    const step4SaldoInfo = step4FormNode.querySelector('[data-step4-saldo-info]');
    const step4TotalText = step4FormNode.querySelector('[data-step4-total-text]');
    const step4TotalBreakdown = step4FormNode.querySelector('[data-step4-total-breakdown]');
    const step4DocPanel = findVisibleStep4Node('[data-step4-doc-panel]');
    const step4DocTypeSelect = step4DocPanel ? step4DocPanel.querySelector('[data-step4-doc-type]') : null;
    const step4DocDropzone = step4DocPanel ? step4DocPanel.querySelector('[data-step4-doc-dropzone]') : null;
    const step4DocFileInput = step4DocPanel ? step4DocPanel.querySelector('[data-step4-doc-file]') : null;
    const step4DocUploadButton = step4DocPanel ? step4DocPanel.querySelector('[data-step4-doc-upload]') : null;
    const step4DocFeedback = step4DocPanel ? step4DocPanel.querySelector('[data-step4-doc-feedback]') : null;
    const step4DocSelected = step4DocPanel ? step4DocPanel.querySelector('[data-step4-doc-selected]') : null;
    const step4DocGallery = step4DocPanel ? step4DocPanel.querySelector('[data-step4-doc-gallery]') : null;
    const step4DocCounts = Array.from(document.querySelectorAll('[data-step4-doc-count]'));
    const step4DocDeleteModal = document.querySelector('[data-step4-doc-delete-modal]');
    const step4DocDeleteName = step4DocDeleteModal ? step4DocDeleteModal.querySelector('[data-step4-doc-delete-name]') : null;
    const step4DocDeleteConfirmButton = step4DocDeleteModal ? step4DocDeleteModal.querySelector('[data-step4-doc-delete-confirm]') : null;
    const step4DocDeleteCancelButton = step4DocDeleteModal ? step4DocDeleteModal.querySelector('[data-step4-doc-delete-cancel]') : null;
    const step4DocDeleteCloseButton = step4DocDeleteModal ? step4DocDeleteModal.querySelector('[data-step4-doc-delete-close]') : null;
    const docPreviewModal = document.querySelector('[data-doc-preview-modal]');
    const docPreviewTitle = docPreviewModal ? docPreviewModal.querySelector('[data-doc-preview-title]') : null;
    const docPreviewMeta = docPreviewModal ? docPreviewModal.querySelector('[data-doc-preview-meta]') : null;
    const docPreviewImage = docPreviewModal ? docPreviewModal.querySelector('[data-doc-preview-image]') : null;
    const docPreviewLink = docPreviewModal ? docPreviewModal.querySelector('[data-doc-preview-link]') : null;
    const docPreviewCloseButton = docPreviewModal ? docPreviewModal.querySelector('[data-doc-preview-close]') : null;
    const docPreviewCancelButton = docPreviewModal ? docPreviewModal.querySelector('[data-doc-preview-cancel]') : null;

    const getStep4Input = (name) => step4FormNode.querySelector('[data-step4-input="' + name + '"]');
    const formatMoneyNumber = (value) => {
      const parsedValue = parseFloat(value);
      return (Number.isFinite(parsedValue) ? parsedValue : 0).toFixed(2);
    };
    const escapeHtml = (value) => String(value || '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');

    const step4DocState = {
      docs: Array.isArray(step4FormConfig.docs) ? step4FormConfig.docs.map((doc) => ({
        file: String(doc.file || ''),
        comprobante_final: String(doc.comprobante_final || ''),
      })) : [],
      pendingFile: null,
      hasFacturaGestor: false,
      hasComprobantePago: false,
    };
    let step4DocUploadInFlight = false;
    let step4PendingDeleteFile = '';
    let step4ServiceCostsReady = !step4CostList
      || !step4FormConfig.canEdit
      || !step4FormConfig.urls
      || !step4FormConfig.urls.getServiceCosts
      || step4FormConfig.urls.getServiceCosts === '#';

    const syncStep4SaveAvailability = () => {
      if (!step4SaveButton) {
        return;
      }
      step4SaveButton.disabled = !step4FormConfig.canEdit || !step4ServiceCostsReady;
    };

    const setFeedback = (message, tone) => {
      if (!step4Feedback) {
        return;
      }
      if (!message) {
        step4Feedback.hidden = true;
        step4Feedback.textContent = '';
        step4Feedback.classList.remove('is-success', 'is-error');
        return;
      }
      step4Feedback.hidden = false;
      step4Feedback.textContent = message;
      step4Feedback.classList.toggle('is-success', tone === 'success');
      step4Feedback.classList.toggle('is-error', tone === 'error');
    };

    const step4SuccessFlashKey = 'prototype-step4-success-flash-' + String(step4FormConfig.tramiteId || '0');
    const persistStep4SuccessFlash = (message) => {
      try {
        window.sessionStorage.setItem(step4SuccessFlashKey, String(message || ''));
      } catch (error) {
      }
    };
    const consumeStep4SuccessFlash = () => {
      try {
        const message = window.sessionStorage.getItem(step4SuccessFlashKey) || '';
        if (message !== '') {
          window.sessionStorage.removeItem(step4SuccessFlashKey);
          setFeedback(message, 'success');
        }
      } catch (error) {
      }
    };

    const setStep4DocFeedback = (message, tone) => {
      if (!step4DocFeedback) {
        return;
      }
      if (!message) {
        step4DocFeedback.hidden = true;
        step4DocFeedback.textContent = '';
        step4DocFeedback.classList.remove('is-success', 'is-error');
        return;
      }
      step4DocFeedback.hidden = false;
      step4DocFeedback.textContent = message;
      step4DocFeedback.classList.toggle('is-success', tone === 'success');
      step4DocFeedback.classList.toggle('is-error', tone === 'error');
    };

    const setSaving = (isSaving) => {
      if (step4SaveButton) {
        step4SaveButton.disabled = isSaving || !step4FormConfig.canEdit || !step4ServiceCostsReady;
        step4SaveButton.textContent = isSaving ? 'Guardando...' : 'Guardar Pago a gestor';
      }
      step4Inputs.forEach((input) => {
        input.disabled = isSaving || !step4FormConfig.canEdit;
      });
      if (step4CostList) {
        step4CostList.querySelectorAll('input, button').forEach((control) => {
          control.disabled = isSaving || !step4FormConfig.canEdit;
        });
      }
      if (step4DocTypeSelect) {
        step4DocTypeSelect.disabled = isSaving || !step4FormConfig.canUploadDocs;
      }
      if (step4DocFileInput) {
        step4DocFileInput.disabled = isSaving || !step4FormConfig.canUploadDocs;
      }
      if (step4DocUploadButton) {
        step4DocUploadButton.disabled = isSaving || !step4FormConfig.canUploadDocs;
      }
      if (step4DocDropzone) {
        step4DocDropzone.classList.toggle('is-disabled', isSaving || !step4FormConfig.canUploadDocs);
      }
    };

    const setStep4DocSaving = (isSaving) => {
      if (step4DocUploadButton) {
        step4DocUploadButton.disabled = isSaving || !step4FormConfig.canUploadDocs;
        step4DocUploadButton.textContent = isSaving ? 'Subiendo...' : 'Subir documento';
      }
      if (step4DocTypeSelect) {
        step4DocTypeSelect.disabled = isSaving || !step4FormConfig.canUploadDocs;
      }
      if (step4DocFileInput) {
        step4DocFileInput.disabled = isSaving || !step4FormConfig.canUploadDocs;
      }
      if (step4DocDropzone) {
        step4DocDropzone.classList.toggle('is-disabled', isSaving || !step4FormConfig.canUploadDocs);
      }
    };

    const updateCsrfHash = (hash) => {
      if (hash) {
        step4FormConfig.csrfHash = hash;
      }
    };

    const getInputValue = (name) => {
      const input = getStep4Input(name);
      return input ? String(input.value || '').trim() : '';
    };

    const buildErrorMessage = (result, fallback) => {
      if (result && result.errors) {
        return Object.values(result.errors).join(' | ');
      }
      return (result && result.message) ? result.message : fallback;
    };

    const getStep4DocLabel = (docType) => {
      const options = step4FormConfig.options && step4FormConfig.options.comprobanteFinal ? step4FormConfig.options.comprobanteFinal : {};
      return options[String(docType || '')] || String(docType || 'Documento de pago');
    };

    const buildStep4FileUrl = (fileName) => {
      const base = String(step4FormConfig.fileBaseUrl || '');
      if (base === '') {
        return '#';
      }
      const normalizedBase = base.endsWith('/') ? base : base + '/';
      return normalizedBase + encodeURIComponent(String(fileName || ''));
    };

    const isPreviewableImage = (fileName) => /\.(png|jpe?g|gif|webp|bmp|svg)$/i.test(String(fileName || ''));

    const closeDocPreviewModal = () => {
      if (!docPreviewModal) {
        return;
      }
      if (typeof docPreviewModal.close === 'function') {
        docPreviewModal.close();
      }
    };

    const openDocPreviewModal = (fileName, fileUrl, metaLabel) => {
      const normalizedUrl = String(fileUrl || '').trim();
      if (!normalizedUrl || normalizedUrl === '#') {
        return;
      }

      if (!docPreviewModal || typeof docPreviewModal.showModal !== 'function' || !docPreviewImage || !docPreviewLink) {
        window.open(normalizedUrl, '_blank', 'noopener');
        return;
      }

      if (docPreviewTitle) {
        docPreviewTitle.textContent = String(fileName || 'Vista previa del documento');
      }
      if (docPreviewMeta) {
        docPreviewMeta.textContent = String(metaLabel || 'Revision visual del archivo cargado en el expediente.');
      }
      docPreviewImage.src = normalizedUrl;
      docPreviewImage.alt = String(fileName || 'Vista previa del documento');
      docPreviewLink.href = normalizedUrl;
      docPreviewLink.textContent = 'Abrir ' + String(fileName || 'archivo completo') + ' en una pestaña nueva';
      docPreviewModal.showModal();
    };

    const buildPreviewTriggerHtml = (fileName, fileUrl, metaLabel) => {
      if (!isPreviewableImage(fileName)) {
        return '';
      }

      return ''
        + '<button type="button" class="tp-gallery-preview-trigger"'
        + ' data-doc-preview-url="' + escapeHtml(fileUrl) + '"'
        + ' data-doc-preview-name="' + escapeHtml(fileName) + '"'
        + ' data-doc-preview-meta="' + escapeHtml(metaLabel) + '">'
        + '<img class="tp-gallery-preview-image" src="' + escapeHtml(fileUrl) + '" alt="' + escapeHtml(fileName) + '" loading="lazy">'
        + '</button>';
    };

    const setStep4PendingFile = (file) => {
      step4DocState.pendingFile = file || null;
      if (step4DocSelected) {
        step4DocSelected.textContent = file ? String(file.name || 'Archivo listo') : 'Sin archivo seleccionado.';
      }
    };

    const normalizeStep4DocState = () => {
      step4DocState.docs = step4DocState.docs.filter((doc) => String(doc.file || '').trim() !== '');
      step4DocState.hasFacturaGestor = step4DocState.docs.some((doc) => String(doc.comprobante_final || '') === 'factura_gestor');
      step4DocState.hasComprobantePago = step4DocState.docs.some((doc) => String(doc.comprobante_final || '') === 'comprobante_pago');
    };

    const renderStep4DocUI = () => {
      normalizeStep4DocState();

      document.querySelectorAll('[data-step4-chip]').forEach((chip) => {
        const chipType = chip.getAttribute('data-step4-chip');
        const isSuccess = (chipType === 'factura_gestor' && step4DocState.hasFacturaGestor)
          || (chipType === 'comprobante_pago' && step4DocState.hasComprobantePago);
        chip.classList.toggle('is-success', isSuccess);
      });

      step4DocCounts.forEach((node) => {
        node.textContent = String(step4DocState.docs.length) + '/2';
        node.classList.toggle('is-success', step4DocState.docs.length >= 2);
      });

      if (!step4DocGallery) {
        return;
      }

      if (step4DocState.docs.length === 0) {
        step4DocGallery.innerHTML = '<div class="tp-gallery-item">Sin documentos de pago a gestor registrados</div>';
        return;
      }

      step4DocGallery.innerHTML = step4DocState.docs.map((doc) => (
        '<div class="tp-gallery-item">'
          + buildPreviewTriggerHtml(doc.file, buildStep4FileUrl(doc.file), getStep4DocLabel(doc.comprobante_final))
          + '<div class="tp-gallery-item-head">'
            + '<div>'
              + '<a class="tp-gallery-item-link" href="' + escapeHtml(buildStep4FileUrl(doc.file)) + '" target="_blank" rel="noreferrer">' + escapeHtml(doc.file) + '</a>'
              + '<span class="tp-gallery-item-meta">' + escapeHtml(getStep4DocLabel(doc.comprobante_final)) + '</span>'
            + '</div>'
            + '<div class="tp-gallery-item-actions">'
              + (isPreviewableImage(doc.file)
                ? '<button type="button" class="tp-btn ghost small" data-doc-preview-url="' + escapeHtml(buildStep4FileUrl(doc.file)) + '" data-doc-preview-name="' + escapeHtml(doc.file) + '" data-doc-preview-meta="' + escapeHtml(getStep4DocLabel(doc.comprobante_final)) + '">Ver imagen</button>'
                : '')
              + (step4FormConfig.canDeleteDocs
                ? '<button type="button" class="tp-btn secondary small" data-step4-doc-delete="' + escapeHtml(doc.file) + '">Eliminar</button>'
                : '')
            + '</div>'
          + '</div>'
        + '</div>'
      )).join('');
    };

    const setStep4DeleteModalBusy = (isBusy) => {
      if (step4DocDeleteConfirmButton) {
        step4DocDeleteConfirmButton.disabled = isBusy;
        step4DocDeleteConfirmButton.textContent = isBusy ? 'Eliminando...' : 'Si, eliminar documento';
      }
      if (step4DocDeleteCancelButton) {
        step4DocDeleteCancelButton.disabled = isBusy;
      }
      if (step4DocDeleteCloseButton) {
        step4DocDeleteCloseButton.disabled = isBusy;
      }
    };

    const closeStep4DeleteModal = () => {
      if (!step4DocDeleteModal) {
        return;
      }
      if (typeof step4DocDeleteModal.close === 'function') {
        step4DocDeleteModal.close();
      }
    };

    const openStep4DeleteModal = (fileName) => {
      if (!step4FormConfig.canDeleteDocs || !fileName) {
        return;
      }

      step4PendingDeleteFile = String(fileName);
      if (step4DocDeleteName) {
        step4DocDeleteName.textContent = step4PendingDeleteFile;
      }

      if (!step4DocDeleteModal || typeof step4DocDeleteModal.showModal !== 'function') {
        if (window.confirm('Estas seguro de eliminar el documento ' + step4PendingDeleteFile + '? Esta accion borrara el archivo y su registro.')) {
          deleteStep4Document(step4PendingDeleteFile);
        }
        return;
      }

      setStep4DeleteModalBusy(false);
      step4DocDeleteModal.showModal();
    };

    const deleteStep4Document = async (fileName) => {
      if (!step4FormConfig.canDeleteDocs || !fileName || !step4FormConfig.urls || !step4FormConfig.urls.delete || step4FormConfig.urls.delete === '#') {
        return;
      }

      setStep4DocFeedback('', '');
      setStep4DeleteModalBusy(true);
      setStep4DocSaving(true);
      try {
        const formData = new FormData();
        formData.append(step4FormConfig.csrfName, step4FormConfig.csrfHash || '');
        formData.append('tramite_id', String(step4FormConfig.tramiteId || '0'));
        formData.append('file', String(fileName));

        const response = await fetch(step4FormConfig.urls.delete, {
          method: 'POST',
          body: formData,
          credentials: 'same-origin',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });
        const result = await response.json();
        updateCsrfHash(result.csrfHash || '');
        if (!response.ok || !result.success) {
          throw new Error(buildErrorMessage(result, 'No se pudo eliminar el documento de pago a gestor.'));
        }

        step4DocState.docs = step4DocState.docs.filter((doc) => String(doc.file || '') !== String(fileName));
        step4PendingDeleteFile = '';
        closeStep4DeleteModal();
        renderStep4DocUI();
        setStep4DocFeedback('Documento de pago a gestor eliminado correctamente.', 'success');
      } catch (error) {
        closeStep4DeleteModal();
        setStep4DocFeedback(error.message || 'No se pudo eliminar el documento de pago a gestor.', 'error');
      } finally {
        setStep4DeleteModalBusy(false);
        setStep4DocSaving(false);
      }
    };

    const updateTopCostStatus = (label) => {
      if (step4CostStatus && label) {
        step4CostStatus.textContent = label;
      }
    };

    const clearCostRowState = (row) => {
      if (!row) {
        return;
      }
      row.classList.remove('is-saved', 'is-error');
      const icon = row.querySelector('[data-step4-cost-icon]');
      const status = row.querySelector('[data-step4-cost-row-status]');
      if (icon) {
        icon.textContent = '';
      }
      if (status) {
        status.textContent = '';
      }
    };

    const setCostRowSaving = (row, isSaving) => {
      if (!row) {
        return;
      }
      const input = row.querySelector('[data-step4-cost-id]');
      const button = row.querySelector('[data-step4-cost-save]');
      if (input) {
        input.disabled = isSaving || !step4FormConfig.canEdit;
      }
      if (button) {
        button.disabled = isSaving || !step4FormConfig.canEdit;
        button.textContent = isSaving ? 'Guardando...' : 'Guardar';
      }
      if (isSaving) {
        const status = row.querySelector('[data-step4-cost-row-status]');
        const icon = row.querySelector('[data-step4-cost-icon]');
        row.classList.remove('is-saved', 'is-error');
        if (icon) {
          icon.textContent = '';
        }
        if (status) {
          status.textContent = 'Guardando...';
        }
      }
    };

    const markCostRowState = (row, tone, label) => {
      if (!row) {
        return;
      }
      clearCostRowState(row);
      row.classList.add(tone === 'success' ? 'is-saved' : 'is-error');
      const icon = row.querySelector('[data-step4-cost-icon]');
      const status = row.querySelector('[data-step4-cost-row-status]');
      if (icon) {
        icon.innerHTML = tone === 'success' ? '&#10003;' : '&#10005;';
      }
      if (status) {
        status.textContent = label;
      }
      if (tone !== 'success') {
        window.setTimeout(() => {
          clearCostRowState(row);
        }, 2200);
      }
    };

    const updateStep4CostTotals = () => {
      if (!step4CostList || !step4CostTotal) {
        return;
      }

      let totalCost = 0;
      step4CostList.querySelectorAll('[data-step4-cost-id]').forEach((input) => {
        totalCost += parseFloat(input.value || '0') || 0;
      });

      const totalCostText = formatMoneyNumber(totalCost);
      step4CostTotal.textContent = '$' + totalCostText;

      const costoInput = getStep4Input('costo_tramite');
      const depositoInput = getStep4Input('deposito_gestor');
      const saldoInput = getStep4Input('col_a_favor');
      const impuestoInput = getStep4Input('impuesto_gestoria');
      const comisionInput = getStep4Input('gestoria_comision');
      const paqueteriaInput = getStep4Input('costo_paqueteria');
      const totalPagoInput = getStep4Input('gestor_total_pago');
      const reembolsoInput = getStep4Input('reembolso_status_id');

      const deposito = parseFloat(depositoInput ? depositoInput.value : '0') || 0;
      const impuesto = parseFloat(impuestoInput ? impuestoInput.value : '0') || 0;
      const comision = parseFloat(comisionInput ? comisionInput.value : '0') || 0;
      const paqueteria = parseFloat(paqueteriaInput ? paqueteriaInput.value : '0') || 0;
      const totalPago = totalCost + impuesto + comision + paqueteria;
      const saldo = totalPago - deposito;
      const saldoAbs = Math.abs(saldo);

      if (costoInput) {
        costoInput.value = totalCostText;
      }
      if (saldoInput) {
        saldoInput.value = formatMoneyNumber(saldo);
      }
      if (totalPagoInput) {
        totalPagoInput.value = formatMoneyNumber(totalPago);
      }

      if (step4TotalText) {
        step4TotalText.textContent = '$' + formatMoneyNumber(totalPago);
      }
      if (step4TotalBreakdown) {
        step4TotalBreakdown.textContent =
          'Costos: $' + totalCostText
          + ' + Honorarios: $' + formatMoneyNumber(impuesto)
          + ' + Gratificacion: $' + formatMoneyNumber(comision)
          + ' + Paqueteria: $' + formatMoneyNumber(paqueteria)
          + ' | Saldo = Pago total - Deposito';
      }

      if (reembolsoInput) {
        const targetStatus = Math.abs(saldo) > 0.0001 ? '22' : '24';
        if (String(reembolsoInput.value || '') !== targetStatus) {
          reembolsoInput.value = targetStatus;
        }
      }

      if (step4SaldoInfo) {
        step4SaldoInfo.classList.remove('is-sgl', 'is-gestor', 'is-even');
        if (saldo > 0.0001) {
          step4SaldoInfo.classList.add('is-gestor');
          step4SaldoInfo.textContent = 'Saldo a favor del Gestor (SGL debe pagar): $' + formatMoneyNumber(saldoAbs);
        } else if (saldo < -0.0001) {
          step4SaldoInfo.classList.add('is-sgl');
          step4SaldoInfo.textContent = 'Saldo a favor de la empresa (Gestor debe devolver): $' + formatMoneyNumber(saldoAbs);
        } else {
          step4SaldoInfo.classList.add('is-even');
          step4SaldoInfo.textContent = 'Sin saldo pendiente';
        }
      }
    };

    const bindStep4CostInputs = () => {
      if (!step4CostList) {
        return;
      }
      step4CostList.querySelectorAll('[data-step4-cost-id]').forEach((input) => {
        input.addEventListener('input', () => {
          clearCostRowState(input.closest('.tp-step4-cost-item'));
          updateTopCostStatus('Pendiente');
          updateStep4CostTotals();
        });
      });
    };

    const renderStep4CostRows = (rows) => {
      if (!step4CostList) {
        return;
      }

      if (!Array.isArray(rows) || rows.length === 0) {
        step4CostList.innerHTML = '<span class="tp-inline-note">No hay tramites asociados registrados para calcular costos.</span>';
        updateTopCostStatus('Sin datos');
        updateStep4CostTotals();
        return;
      }

      step4CostList.innerHTML = rows.map((row) => {
        const rowId = Number(row.id || 0);
        const rowLabel = String(row.tipo_tramite || ('Servicio #' + rowId));
        const rowValue = formatMoneyNumber(row.costo_tramite || 0);
        return ''
          + '<div class="tp-step4-cost-item">'
          + '  <div class="tp-step4-cost-name"><strong>' + rowLabel + '</strong><span>Monto editable por tramite</span></div>'
          + '  <input type="number" step="0.01" class="tp-step4-cost-input" data-step4-cost-id="' + rowId + '" value="' + rowValue + '" ' + (step4FormConfig.canEdit ? '' : 'disabled') + '>'
          + (step4FormConfig.canEdit
              ? '  <button type="button" class="tp-btn secondary tp-step4-cost-action" data-step4-cost-save="' + rowId + '">Guardar</button>'
              : '  <span></span>')
          + '  <span class="tp-step4-cost-icon" data-step4-cost-icon aria-hidden="true"></span>'
          + '  <span class="tp-step4-cost-row-status" data-step4-cost-row-status></span>'
          + '</div>';
      }).join('');

      updateTopCostStatus('Guardado');
      bindStep4CostInputs();
      updateStep4CostTotals();
    };

    const loadStep4ServiceCosts = async () => {
      if (!step4CostList || !step4FormConfig.urls || !step4FormConfig.urls.getServiceCosts || step4FormConfig.urls.getServiceCosts === '#') {
        step4ServiceCostsReady = true;
        syncStep4SaveAvailability();
        updateStep4CostTotals();
        return;
      }

      try {
        const response = await fetch(step4FormConfig.urls.getServiceCosts, {
          method: 'GET',
          credentials: 'same-origin',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });
        const result = await response.json();
        step4ServiceCostsReady = true;
        renderStep4CostRows(Array.isArray(result) ? result : []);
        syncStep4SaveAvailability();
      } catch (error) {
        step4CostList.innerHTML = '<span class="tp-inline-note">No se pudieron cargar los costos asociados.</span>';
        updateTopCostStatus('Error');
        step4ServiceCostsReady = false;
        syncStep4SaveAvailability();
        updateStep4CostTotals();
      }
    };

    const saveStep4ServiceCost = async (rowId, input, row) => {
      if (!step4FormConfig.canEdit || !step4FormConfig.urls || !step4FormConfig.urls.updateServiceCost || step4FormConfig.urls.updateServiceCost === '#') {
        return;
      }

      const formData = new FormData();
      formData.append('id', String(rowId));
      formData.append('costo_tramite', String(input.value || '0'));
      if (step4FormConfig.csrfName) {
        formData.append(step4FormConfig.csrfName, step4FormConfig.csrfHash || '');
      }

      try {
        updateTopCostStatus('Guardando...');
        setCostRowSaving(row, true);
        const response = await fetch(step4FormConfig.urls.updateServiceCost, {
          method: 'POST',
          body: formData,
          credentials: 'same-origin',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });
        const result = await response.json();
        if (!response.ok || !result || result.status !== 'success') {
          throw new Error((result && result.message) ? result.message : 'No se pudo guardar el costo del servicio.');
        }
        updateTopCostStatus('Guardado');
        markCostRowState(row, 'success', 'Guardado');
        updateStep4CostTotals();
      } catch (error) {
        updateTopCostStatus('Error');
        markCostRowState(row, 'error', 'Error al guardar');
      } finally {
        setCostRowSaving(row, false);
      }
    };

    const uploadStep4Document = async () => {
      if (!step4FormConfig.canUploadDocs || !step4FormConfig.urls || !step4FormConfig.urls.upload || step4FormConfig.urls.upload === '#') {
        return;
      }
      if (step4DocUploadInFlight) {
        return;
      }

      const file = step4DocState.pendingFile || (step4DocFileInput && step4DocFileInput.files ? step4DocFileInput.files[0] : null);
      const comprobanteFinal = step4DocTypeSelect ? String(step4DocTypeSelect.value || '').trim() : '';
      if (!comprobanteFinal) {
        setStep4DocFeedback('Selecciona el tipo de documento de pago.', 'error');
        return;
      }
      if (!file) {
        setStep4DocFeedback('Selecciona un archivo para subirlo como documento de pago a gestor.', 'error');
        return;
      }

      setStep4DocFeedback('', '');
      step4DocUploadInFlight = true;
      setStep4DocSaving(true);
      try {
        const formData = new FormData();
        formData.append(step4FormConfig.csrfName, step4FormConfig.csrfHash || '');
        formData.append('comprobante_final', comprobanteFinal);
        formData.append('file', file);

        const response = await fetch(step4FormConfig.urls.upload, {
          method: 'POST',
          body: formData,
          credentials: 'same-origin',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });
        const result = await response.json();
        updateCsrfHash(result.csrfHash || '');
        if (!response.ok || !result.success) {
          throw new Error(buildErrorMessage(result, 'No se pudo subir el documento de pago a gestor.'));
        }

        step4DocState.docs.unshift({
          file: String(result.fileName || file.name || ''),
          comprobante_final: String(result.comprobanteFinal || comprobanteFinal),
        });
        if (step4DocFileInput) {
          step4DocFileInput.value = '';
        }
        setStep4PendingFile(null);
        renderStep4DocUI();
        setStep4DocFeedback('Documento de pago a gestor subido correctamente.', 'success');
      } catch (error) {
        setStep4DocFeedback(error.message || 'No se pudo subir el documento de pago a gestor.', 'error');
      } finally {
        step4DocUploadInFlight = false;
        setStep4DocSaving(false);
      }
    };

    const saveStep4RealForm = async () => {
      if (!step4FormConfig.canEdit) {
        return;
      }

      if (!step4ServiceCostsReady) {
        setFeedback('Espera a que carguen los costos del expediente antes de guardar Pago a gestor.', 'error');
        return;
      }

      updateStep4CostTotals();

      if (getInputValue('reembolso_status_id') === '' || getInputValue('status_doctos_gestor') === '') {
        setFeedback('Estatus del reembolso y estatus de documentos son obligatorios.', 'error');
        return;
      }

      const currentTotalCost = parseFloat(getInputValue('costo_tramite') || '0') || 0;
      if (Math.abs(currentTotalCost) < 0.0001 && !window.confirm('La sumatoria de costos esta en $0.00. Falta guardar un monto. ¿Deseas continuar?')) {
        return;
      }

      setFeedback('', '');
      setSaving(true);

      const formData = new FormData();
      formData.append(step4FormConfig.csrfName, step4FormConfig.csrfHash || '');
      [
        'num_factura_gestor',
        'costo_tramite',
        'deposito_gestor',
        'col_a_favor',
        'impuesto_gestoria',
        'gestoria_comision',
        'costo_paqueteria',
        'gestor_total_pago',
        'pago_gestor_st_id',
        'status_doctos_gestor',
        'reembolso_status_id'
      ].forEach((fieldName) => {
        formData.append(fieldName, getInputValue(fieldName));
      });

      try {
        const response = await fetch(step4FormConfig.url, {
          method: 'POST',
          body: formData,
          credentials: 'same-origin',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });
        const result = await response.json();
        updateCsrfHash(result.csrfHash || '');
        if (!response.ok || !result.success) {
          throw new Error(buildErrorMessage(result, 'No se pudo guardar Pago a gestor.'));
        }

        persistStep4SuccessFlash('Pago a gestor guardado correctamente.');
        setFeedback('Pago a gestor guardado. Rehidratando el paso con el estado actual del servidor...', 'success');
        window.setTimeout(() => {
          window.location.reload();
        }, 600);
      } catch (error) {
        setFeedback(error.message || 'No se pudo guardar Pago a gestor.', 'error');
      } finally {
        setSaving(false);
      }
    };

    if (step4CostList) {
      step4CostList.addEventListener('click', (event) => {
        const button = event.target.closest('[data-step4-cost-save]');
        if (!button) {
          return;
        }
        const rowId = button.getAttribute('data-step4-cost-save');
        const row = button.closest('.tp-step4-cost-item');
        const input = row ? row.querySelector('[data-step4-cost-id]') : null;
        if (!rowId || !row || !input) {
          return;
        }
        saveStep4ServiceCost(rowId, input, row);
      });
    }

    ['deposito_gestor', 'impuesto_gestoria', 'gestoria_comision', 'costo_paqueteria'].forEach((fieldName) => {
      const input = getStep4Input(fieldName);
      if (!input) {
        return;
      }
      input.addEventListener('input', () => {
        updateStep4CostTotals();
      });
    });

    if (step4DocUploadButton) {
      step4DocUploadButton.addEventListener('click', () => {
        uploadStep4Document();
      });
    }

    if (step4DocDropzone) {
      step4DocDropzone.addEventListener('click', () => {
        if (step4FormConfig.canUploadDocs && step4DocFileInput) {
          step4DocFileInput.click();
        }
      });

      ['dragenter', 'dragover'].forEach((eventName) => {
        step4DocDropzone.addEventListener(eventName, (event) => {
          event.preventDefault();
          if (!step4FormConfig.canUploadDocs) {
            return;
          }
          step4DocDropzone.classList.add('is-dragover');
        });
      });

      ['dragleave', 'dragend', 'drop'].forEach((eventName) => {
        step4DocDropzone.addEventListener(eventName, (event) => {
          event.preventDefault();
          step4DocDropzone.classList.remove('is-dragover');
        });
      });

      step4DocDropzone.addEventListener('drop', (event) => {
        if (!step4FormConfig.canUploadDocs) {
          return;
        }
        const files = event.dataTransfer && event.dataTransfer.files ? event.dataTransfer.files : null;
        if (!files || !files.length) {
          return;
        }
        setStep4PendingFile(files[0]);
      });
    }

    if (step4DocFileInput) {
      step4DocFileInput.addEventListener('change', () => {
        const nextFile = step4DocFileInput.files && step4DocFileInput.files.length ? step4DocFileInput.files[0] : null;
        setStep4PendingFile(nextFile);
      });
    }

    if (step4DocGallery) {
      step4DocGallery.addEventListener('click', (event) => {
        const previewButton = event.target instanceof Element ? event.target.closest('[data-doc-preview-url]') : null;
        if (previewButton) {
          event.preventDefault();
          openDocPreviewModal(
            previewButton.getAttribute('data-doc-preview-name'),
            previewButton.getAttribute('data-doc-preview-url'),
            previewButton.getAttribute('data-doc-preview-meta')
          );
          return;
        }

        const deleteButton = event.target instanceof Element ? event.target.closest('[data-step4-doc-delete]') : null;
        if (!deleteButton) {
          return;
        }
        openStep4DeleteModal(deleteButton.getAttribute('data-step4-doc-delete'));
      });
    }

    if (step4DocDeleteConfirmButton) {
      step4DocDeleteConfirmButton.addEventListener('click', () => {
        if (!step4PendingDeleteFile) {
          closeStep4DeleteModal();
          return;
        }
        deleteStep4Document(step4PendingDeleteFile);
      });
    }

    if (step4DocDeleteCancelButton) {
      step4DocDeleteCancelButton.addEventListener('click', () => {
        closeStep4DeleteModal();
      });
    }

    if (step4DocDeleteCloseButton) {
      step4DocDeleteCloseButton.addEventListener('click', () => {
        closeStep4DeleteModal();
      });
    }

    if (step4DocDeleteModal) {
      step4DocDeleteModal.addEventListener('cancel', (event) => {
        event.preventDefault();
        closeStep4DeleteModal();
      });
      step4DocDeleteModal.addEventListener('click', (event) => {
        if (event.target === step4DocDeleteModal) {
          closeStep4DeleteModal();
        }
      });
    }

    if (docPreviewCloseButton) {
      docPreviewCloseButton.addEventListener('click', () => {
        closeDocPreviewModal();
      });
    }

    if (docPreviewCancelButton) {
      docPreviewCancelButton.addEventListener('click', () => {
        closeDocPreviewModal();
      });
    }

    if (docPreviewModal) {
      docPreviewModal.addEventListener('cancel', (event) => {
        event.preventDefault();
        closeDocPreviewModal();
      });
      docPreviewModal.addEventListener('click', (event) => {
        if (event.target === docPreviewModal) {
          closeDocPreviewModal();
        }
      });
    }

    if (!step4ServiceCostsReady) {
      updateTopCostStatus('Cargando...');
    }
    consumeStep4SuccessFlash();
    syncStep4SaveAvailability();
    loadStep4ServiceCosts();
    renderStep4DocUI();
    updateStep4CostTotals();

    if (step4SaveButton) {
      step4SaveButton.addEventListener('click', () => {
        saveStep4RealForm();
      });
    }
  })();
</script>

<script>
  (function() {
    const step4NotesFormConfig = <?= json_encode($prototypeStep4NotesForm, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    const findVisibleStep4NoteNode = (selector) => {
      const nodes = Array.from(document.querySelectorAll(selector));
      return nodes.find((node) => !node.closest('[hidden]') && node.offsetParent !== null) || nodes[0] || null;
    };
    const step4NotesInput = findVisibleStep4NoteNode('[data-step4-note-input]');
    const step4NotesSaveButton = findVisibleStep4NoteNode('[data-step4-note-save]');
    const step4NotesFeedback = findVisibleStep4NoteNode('[data-step4-note-feedback]');
    const step4NotesList = findVisibleStep4NoteNode('[data-step4-note-list]');
    const step4NotesEmpty = findVisibleStep4NoteNode('[data-step4-note-empty]');

    if (!step4NotesInput || !step4NotesSaveButton || !step4NotesList || !step4NotesEmpty) {
      return;
    }

    if (step4NotesSaveButton.dataset.step4NotesBound === '1') {
      return;
    }
    step4NotesSaveButton.dataset.step4NotesBound = '1';

    const step4NotesState = {
      items: Array.isArray(step4NotesFormConfig.items) ? step4NotesFormConfig.items.map((item) => ({
        id: Number(item.id || 0),
        comment: String(item.comment || ''),
        author: String(item.author || 'Sistema'),
        createdAt: String(item.createdAt || ''),
        createdAtLabel: String(item.createdAtLabel || 'Sin fecha'),
      })) : [],
    };

    const escapeHtml = (value) => String(value ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');

    const buildErrorMessage = (result, fallback) => {
      if (result && result.errors) {
        return Object.values(result.errors).join(' | ');
      }
      return (result && result.message) ? result.message : fallback;
    };

    const setStep4NotesFeedback = (message, tone) => {
      if (!message) {
        step4NotesFeedback.hidden = true;
        step4NotesFeedback.textContent = '';
        step4NotesFeedback.classList.remove('is-success', 'is-error');
        return;
      }
      step4NotesFeedback.hidden = false;
      step4NotesFeedback.textContent = message;
      step4NotesFeedback.classList.toggle('is-success', tone === 'success');
      step4NotesFeedback.classList.toggle('is-error', tone === 'error');
    };

    const setStep4NotesSaving = (isSaving) => {
      step4NotesSaveButton.disabled = isSaving || !step4NotesFormConfig.canAdd;
      step4NotesSaveButton.textContent = isSaving ? 'Guardando...' : 'Guardar nota interna';
      step4NotesInput.disabled = isSaving || !step4NotesFormConfig.canAdd;
    };

    const renderStep4NotesList = () => {
      const items = step4NotesState.items.filter((item) => String(item.comment || '').trim() !== '');
      step4NotesList.innerHTML = items.map((item) => (
        '<div class="tp-note-item tone-info">'
          + '<span class="tp-note-meta">' + escapeHtml(String(item.createdAtLabel || 'Sin fecha') + ' · ' + String(item.author || 'Sistema')) + '</span>'
          + '<span class="tp-note-body">' + escapeHtml(item.comment || '') + '</span>'
        + '</div>'
      )).join('');
      step4NotesList.hidden = items.length === 0;
      step4NotesEmpty.hidden = items.length !== 0;
    };

    const saveStep4InternalNote = async () => {
      if (!step4NotesFormConfig.canAdd || !step4NotesFormConfig.urls || !step4NotesFormConfig.urls.create || step4NotesFormConfig.urls.create === '#') {
        setStep4NotesFeedback('Notas internas no disponibles para este tramite o este perfil.', 'error');
        return;
      }

      const comment = String(step4NotesInput.value || '').trim();
      if (comment.length < 3) {
        setStep4NotesFeedback('Escribe una nota de al menos 3 caracteres.', 'error');
        return;
      }

      setStep4NotesFeedback('', '');
      setStep4NotesSaving(true);
      try {
        const formData = new FormData();
        formData.append(step4NotesFormConfig.csrfName, step4NotesFormConfig.csrfHash || '');
        formData.append('comentario', comment);

        const response = await fetch(step4NotesFormConfig.urls.create, {
          method: 'POST',
          body: formData,
          credentials: 'same-origin',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });
        const result = await response.json();
        if (result && result.csrfHash) {
          step4NotesFormConfig.csrfHash = result.csrfHash;
        }
        if (!response.ok || !result.success) {
          throw new Error(buildErrorMessage(result, 'No se pudo guardar la nota interna.'));
        }

        step4NotesState.items.unshift({
          id: Number(result.item && result.item.id ? result.item.id : 0),
          comment: String(result.item && result.item.comment ? result.item.comment : comment),
          author: String(result.item && result.item.author ? result.item.author : 'Sistema'),
          createdAt: String(result.item && result.item.createdAt ? result.item.createdAt : ''),
          createdAtLabel: String(result.item && result.item.createdAtLabel ? result.item.createdAtLabel : 'Sin fecha'),
        });
        step4NotesInput.value = '';
        renderStep4NotesList();
        setStep4NotesFeedback('Nota interna guardada correctamente.', 'success');
      } catch (error) {
        setStep4NotesFeedback(error.message || 'No se pudo guardar la nota interna.', 'error');
      } finally {
        setStep4NotesSaving(false);
      }
    };

    renderStep4NotesList();
    setStep4NotesSaving(false);
    step4NotesSaveButton.addEventListener('click', () => {
      saveStep4InternalNote();
    });
  })();
</script>

<?php if (!$isOperationalBasePhase && $activeStep === 4): ?>
<script>
  (function() {
    const step4NotesFormConfig = <?= json_encode($prototypeStep4NotesForm, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    const step4NotesInput = document.querySelector('[data-step4-note-input]');
    const step4NotesSaveButton = document.querySelector('[data-step4-note-save]');
    const step4NotesFeedback = document.querySelector('[data-step4-note-feedback]');
    const step4NotesList = document.querySelector('[data-step4-note-list]');
    const step4NotesEmpty = document.querySelector('[data-step4-note-empty]');
    const step4NotesState = {
      items: Array.isArray(step4NotesFormConfig.items) ? step4NotesFormConfig.items.map((item) => ({
        id: Number(item.id || 0),
        comment: String(item.comment || ''),
        author: String(item.author || 'Sistema'),
        createdAt: String(item.createdAt || ''),
        createdAtLabel: String(item.createdAtLabel || 'Sin fecha'),
      })) : [],
    };
    const evidenceFormConfig = <?= json_encode($prototypeEvidenceForm, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    const evidenceInput = document.querySelector('[data-prototype-evidence-input]');
    const evidenceSaveButton = document.querySelector('[data-prototype-evidence-save]');
    const evidenceFeedback = document.querySelector('[data-prototype-evidence-feedback]');
    const evidenceList = document.querySelector('[data-prototype-evidence-list]');
    const evidenceEmpty = document.querySelector('[data-prototype-evidence-empty]');
    const evidenceState = {
      items: Array.isArray(evidenceFormConfig.items) ? evidenceFormConfig.items.map((item) => ({
        id: Number(item.id || 0),
        comment: String(item.comment || ''),
        author: String(item.author || 'Sistema'),
        createdAt: String(item.createdAt || ''),
        createdAtLabel: String(item.createdAtLabel || 'Sin fecha'),
      })) : [],
    };

    if (!evidenceList || !evidenceEmpty) {
      return;
    }

    const escapeHtml = (value) => String(value ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');

    const setStep4NotesFeedback = (message, tone) => {
      if (!step4NotesFeedback) {
        return;
      }
      if (!message) {
        step4NotesFeedback.hidden = true;
        step4NotesFeedback.textContent = '';
        step4NotesFeedback.classList.remove('is-success', 'is-error');
        return;
      }
      step4NotesFeedback.hidden = false;
      step4NotesFeedback.textContent = message;
      step4NotesFeedback.classList.toggle('is-success', tone === 'success');
      step4NotesFeedback.classList.toggle('is-error', tone === 'error');
    };

    const buildErrorMessage = (result, fallback) => {
      if (result && result.errors) {
        return Object.values(result.errors).join(' | ');
      }
      return (result && result.message) ? result.message : fallback;
    };

    const setEvidenceFeedback = (message, tone) => {
      if (!evidenceFeedback) {
        return;
      }
      if (!message) {
        evidenceFeedback.hidden = true;
        evidenceFeedback.textContent = '';
        evidenceFeedback.classList.remove('is-success', 'is-error');
        return;
      }
      evidenceFeedback.hidden = false;
      evidenceFeedback.textContent = message;
      evidenceFeedback.classList.toggle('is-success', tone === 'success');
      evidenceFeedback.classList.toggle('is-error', tone === 'error');
    };

    const setEvidenceSaving = (isSaving) => {
      if (evidenceSaveButton) {
        evidenceSaveButton.disabled = isSaving || !evidenceFormConfig.canAdd;
        evidenceSaveButton.textContent = isSaving ? 'Guardando...' : 'Guardar comentario';
      }
      if (evidenceInput) {
        evidenceInput.disabled = isSaving || !evidenceFormConfig.canAdd;
      }
    };

    const setStep4NotesSaving = (isSaving) => {
      if (step4NotesSaveButton) {
        step4NotesSaveButton.disabled = isSaving || !step4NotesFormConfig.canAdd;
        step4NotesSaveButton.textContent = isSaving ? 'Guardando...' : 'Guardar nota interna';
      }
      if (step4NotesInput) {
        step4NotesInput.disabled = isSaving || !step4NotesFormConfig.canAdd;
      }
    };

    const renderEvidenceItem = (item) => {
      return '<div class="tp-note-item tone-info">'
        + '<span class="tp-note-meta">' + escapeHtml(String(item.createdAtLabel || 'Sin fecha') + ' · ' + String(item.author || 'Sistema')) + '</span>'
        + '<span class="tp-note-body">' + escapeHtml(item.comment || '') + '</span>'
      + '</div>';
    };

    const renderStep4NotesList = () => {
      if (!step4NotesList || !step4NotesEmpty) {
        return;
      }
      const items = step4NotesState.items.filter((item) => String(item.comment || '').trim() !== '');
      step4NotesList.innerHTML = items.map((item) => renderEvidenceItem(item)).join('');
      step4NotesList.hidden = items.length === 0;
      step4NotesEmpty.hidden = items.length !== 0;
    };

    const renderEvidenceList = () => {
      const items = evidenceState.items.filter((item) => String(item.comment || '').trim() !== '');
      evidenceList.innerHTML = items.map((item) => renderEvidenceItem(item)).join('');
      evidenceList.hidden = items.length === 0;
      evidenceEmpty.hidden = items.length !== 0;
    };

    const saveEvidenceComment = async () => {
      if (!evidenceFormConfig.canAdd || !evidenceInput) {
        return;
      }

      const comment = String(evidenceInput.value || '').trim();
      if (comment.length < 3) {
        setEvidenceFeedback('Escribe un comentario de al menos 3 caracteres.', 'error');
        return;
      }

      setEvidenceFeedback('', '');
      setEvidenceSaving(true);
      try {
        const formData = new FormData();
        formData.append(evidenceFormConfig.csrfName, evidenceFormConfig.csrfHash || '');
        formData.append('comentario', comment);

        const response = await fetch(evidenceFormConfig.urls.create, {
          method: 'POST',
          body: formData,
          credentials: 'same-origin',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });
        const result = await response.json();
        if (result && result.csrfHash) {
          evidenceFormConfig.csrfHash = result.csrfHash;
        }
        if (!response.ok || !result.success) {
          throw new Error(buildErrorMessage(result, 'No se pudo guardar el comentario.'));
        }

        evidenceState.items.unshift({
          id: Number(result.item && result.item.id ? result.item.id : 0),
          comment: String(result.item && result.item.comment ? result.item.comment : comment),
          author: String(result.item && result.item.author ? result.item.author : 'Sistema'),
          createdAt: String(result.item && result.item.createdAt ? result.item.createdAt : ''),
          createdAtLabel: String(result.item && result.item.createdAtLabel ? result.item.createdAtLabel : 'Sin fecha'),
        });
        evidenceInput.value = '';
        renderEvidenceList();
        setEvidenceFeedback('Comentario guardado correctamente.', 'success');
      } catch (error) {
        setEvidenceFeedback(error.message || 'No se pudo guardar el comentario.', 'error');
      } finally {
        setEvidenceSaving(false);
      }
    };

    const saveStep4InternalNote = async () => {
      if (!step4NotesFormConfig.canAdd || !step4NotesInput) {
        return;
      }

      const comment = String(step4NotesInput.value || '').trim();
      if (comment.length < 3) {
        setStep4NotesFeedback('Escribe una nota de al menos 3 caracteres.', 'error');
        return;
      }

      setStep4NotesFeedback('', '');
      setStep4NotesSaving(true);
      try {
        const formData = new FormData();
        formData.append(step4NotesFormConfig.csrfName, step4NotesFormConfig.csrfHash || '');
        formData.append('comentario', comment);

        const response = await fetch(step4NotesFormConfig.urls.create, {
          method: 'POST',
          body: formData,
          credentials: 'same-origin',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });
        const result = await response.json();
        if (result && result.csrfHash) {
          step4NotesFormConfig.csrfHash = result.csrfHash;
          evidenceFormConfig.csrfHash = result.csrfHash;
        }
        if (!response.ok || !result.success) {
          throw new Error(buildErrorMessage(result, 'No se pudo guardar la nota interna.'));
        }

        step4NotesState.items.unshift({
          id: Number(result.item && result.item.id ? result.item.id : 0),
          comment: String(result.item && result.item.comment ? result.item.comment : comment),
          author: String(result.item && result.item.author ? result.item.author : 'Sistema'),
          createdAt: String(result.item && result.item.createdAt ? result.item.createdAt : ''),
          createdAtLabel: String(result.item && result.item.createdAtLabel ? result.item.createdAtLabel : 'Sin fecha'),
        });
        step4NotesInput.value = '';
        renderStep4NotesList();
        setStep4NotesFeedback('Nota interna guardada correctamente.', 'success');
      } catch (error) {
        setStep4NotesFeedback(error.message || 'No se pudo guardar la nota interna.', 'error');
      } finally {
        setStep4NotesSaving(false);
      }
    };

    renderStep4NotesList();
    setStep4NotesSaving(false);
    renderEvidenceList();
    setEvidenceSaving(false);

    if (step4NotesSaveButton) {
      step4NotesSaveButton.addEventListener('click', () => {
        saveStep4InternalNote();
      });
    }

    if (evidenceSaveButton) {
      evidenceSaveButton.addEventListener('click', () => {
        saveEvidenceComment();
      });
    }
  })();
</script>
<?php endif; ?>

<script>
  (function() {
    const step5NotesFormConfig = <?= json_encode($prototypeStep5NotesForm, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    const findVisibleStep5NoteNode = (selector) => {
      const nodes = Array.from(document.querySelectorAll(selector));
      return nodes.find((node) => !node.closest('[hidden]') && node.offsetParent !== null) || nodes[0] || null;
    };
    const step5NotesInput = findVisibleStep5NoteNode('[data-step5-note-input]');
    const step5NotesSaveButton = findVisibleStep5NoteNode('[data-step5-note-save]');
    const step5NotesFeedback = findVisibleStep5NoteNode('[data-step5-note-feedback]');
    const step5NotesList = findVisibleStep5NoteNode('[data-step5-note-list]');
    const step5NotesEmpty = findVisibleStep5NoteNode('[data-step5-note-empty]');

    if (!step5NotesInput || !step5NotesSaveButton || !step5NotesList || !step5NotesEmpty) {
      return;
    }

    if (step5NotesSaveButton.dataset.step5NotesBound === '1') {
      return;
    }
    step5NotesSaveButton.dataset.step5NotesBound = '1';

    const step5NotesState = {
      items: Array.isArray(step5NotesFormConfig.items) ? step5NotesFormConfig.items.map((item) => ({
        id: Number(item.id || 0),
        comment: String(item.comment || ''),
        author: String(item.author || 'Sistema'),
        createdAt: String(item.createdAt || ''),
        createdAtLabel: String(item.createdAtLabel || 'Sin fecha'),
      })) : [],
    };

    const escapeHtml = (value) => String(value ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');

    const buildErrorMessage = (result, fallback) => {
      if (result && result.errors) {
        return Object.values(result.errors).join(' | ');
      }
      return (result && result.message) ? result.message : fallback;
    };

    const setStep5NotesFeedback = (message, tone) => {
      if (!message) {
        step5NotesFeedback.hidden = true;
        step5NotesFeedback.textContent = '';
        step5NotesFeedback.classList.remove('is-success', 'is-error');
        return;
      }
      step5NotesFeedback.hidden = false;
      step5NotesFeedback.textContent = message;
      step5NotesFeedback.classList.toggle('is-success', tone === 'success');
      step5NotesFeedback.classList.toggle('is-error', tone === 'error');
    };

    const setStep5NotesSaving = (isSaving) => {
      step5NotesSaveButton.disabled = isSaving || !step5NotesFormConfig.canAdd;
      step5NotesSaveButton.textContent = isSaving ? 'Guardando...' : 'Guardar nota interna';
      step5NotesInput.disabled = isSaving || !step5NotesFormConfig.canAdd;
    };

    const renderStep5NotesList = () => {
      const items = step5NotesState.items.filter((item) => String(item.comment || '').trim() !== '');
      step5NotesList.innerHTML = items.map((item) => (
        '<div class="tp-note-item tone-info">'
          + '<span class="tp-note-meta">' + escapeHtml(String(item.createdAtLabel || 'Sin fecha') + ' · ' + String(item.author || 'Sistema')) + '</span>'
          + '<span class="tp-note-body">' + escapeHtml(item.comment || '') + '</span>'
        + '</div>'
      )).join('');
      step5NotesList.hidden = items.length === 0;
      step5NotesEmpty.hidden = items.length !== 0;
    };

    const saveStep5InternalNote = async () => {
      if (!step5NotesFormConfig.canAdd || !step5NotesFormConfig.urls || !step5NotesFormConfig.urls.create || step5NotesFormConfig.urls.create === '#') {
        setStep5NotesFeedback('Notas internas no disponibles para este tramite o este perfil.', 'error');
        return;
      }

      const comment = String(step5NotesInput.value || '').trim();
      if (comment.length < 3) {
        setStep5NotesFeedback('Escribe una nota de al menos 3 caracteres.', 'error');
        return;
      }

      setStep5NotesFeedback('', '');
      setStep5NotesSaving(true);
      try {
        const formData = new FormData();
        formData.append(step5NotesFormConfig.csrfName, step5NotesFormConfig.csrfHash || '');
        formData.append('comentario', comment);

        const response = await fetch(step5NotesFormConfig.urls.create, {
          method: 'POST',
          body: formData,
          credentials: 'same-origin',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });
        const result = await response.json();
        if (result && result.csrfHash) {
          step5NotesFormConfig.csrfHash = result.csrfHash;
        }
        if (!response.ok || !result.success) {
          throw new Error(buildErrorMessage(result, 'No se pudo guardar la nota interna.'));
        }

        step5NotesState.items.unshift({
          id: Number(result.item && result.item.id ? result.item.id : 0),
          comment: String(result.item && result.item.comment ? result.item.comment : comment),
          author: String(result.item && result.item.author ? result.item.author : 'Sistema'),
          createdAt: String(result.item && result.item.createdAt ? result.item.createdAt : ''),
          createdAtLabel: String(result.item && result.item.createdAtLabel ? result.item.createdAtLabel : 'Sin fecha'),
        });
        step5NotesInput.value = '';
        renderStep5NotesList();
        setStep5NotesFeedback('Nota interna guardada correctamente.', 'success');
      } catch (error) {
        setStep5NotesFeedback(error.message || 'No se pudo guardar la nota interna.', 'error');
      } finally {
        setStep5NotesSaving(false);
      }
    };

    renderStep5NotesList();
    setStep5NotesSaving(false);
    step5NotesSaveButton.addEventListener('click', () => {
      saveStep5InternalNote();
    });
  })();
</script>

<script>
  (function() {
    const step5FormConfig = <?= json_encode($prototypeStep5Form, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    const findVisibleStep5Node = (selector) => {
      const nodes = Array.from(document.querySelectorAll(selector));
      return nodes.find((node) => !node.closest('[hidden]') && node.offsetParent !== null) || nodes[0] || null;
    };
    const step5FormNode = findVisibleStep5Node('[data-step5-live-form]');
    if (!step5FormNode) {
      return;
    }

    const step5SaveButton = step5FormNode.querySelector('[data-step5-save]');
    const step5Feedback = step5FormNode.querySelector('[data-step5-feedback]');
    const step5Inputs = Array.from(step5FormNode.querySelectorAll('[data-step5-input]'));
    const step5DocPanel = findVisibleStep5Node('[data-step5-doc-panel]');
    const step5DocTypeSelect = step5DocPanel ? step5DocPanel.querySelector('[data-step5-doc-type]') : null;
    const step5DocDropzone = step5DocPanel ? step5DocPanel.querySelector('[data-step5-doc-dropzone]') : null;
    const step5DocFileInput = step5DocPanel ? step5DocPanel.querySelector('[data-step5-doc-file]') : null;
    const step5DocUploadButton = step5DocPanel ? step5DocPanel.querySelector('[data-step5-doc-upload]') : null;
    const step5DocFeedback = step5DocPanel ? step5DocPanel.querySelector('[data-step5-doc-feedback]') : null;
    const step5DocSelected = step5DocPanel ? step5DocPanel.querySelector('[data-step5-doc-selected]') : null;
    const step5DocGallery = step5DocPanel ? step5DocPanel.querySelector('[data-step5-doc-gallery]') : null;
    const step5DocCounts = Array.from(document.querySelectorAll('[data-step5-doc-count]'));
    const step5DocDeleteModal = document.querySelector('[data-step5-doc-delete-modal]');
    const step5DocDeleteName = step5DocDeleteModal ? step5DocDeleteModal.querySelector('[data-step5-doc-delete-name]') : null;
    const step5DocDeleteConfirmButton = step5DocDeleteModal ? step5DocDeleteModal.querySelector('[data-step5-doc-delete-confirm]') : null;
    const step5DocDeleteCancelButton = step5DocDeleteModal ? step5DocDeleteModal.querySelector('[data-step5-doc-delete-cancel]') : null;
    const step5DocDeleteCloseButton = step5DocDeleteModal ? step5DocDeleteModal.querySelector('[data-step5-doc-delete-close]') : null;
    const docPreviewModal = document.querySelector('[data-doc-preview-modal]');
    const docPreviewTitle = docPreviewModal ? docPreviewModal.querySelector('[data-doc-preview-title]') : null;
    const docPreviewMeta = docPreviewModal ? docPreviewModal.querySelector('[data-doc-preview-meta]') : null;
    const docPreviewImage = docPreviewModal ? docPreviewModal.querySelector('[data-doc-preview-image]') : null;
    const docPreviewLink = docPreviewModal ? docPreviewModal.querySelector('[data-doc-preview-link]') : null;
    const docPreviewCloseButton = docPreviewModal ? docPreviewModal.querySelector('[data-doc-preview-close]') : null;
    const docPreviewCancelButton = docPreviewModal ? docPreviewModal.querySelector('[data-doc-preview-cancel]') : null;

    const getStep5Input = (name) => step5FormNode.querySelector('[data-step5-input="' + name + '"]');
    const formatMoneyNumber = (value) => {
      const parsedValue = parseFloat(value);
      return (Number.isFinite(parsedValue) ? parsedValue : 0).toFixed(2);
    };
    const escapeHtml = (value) => String(value || '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
    const buildErrorMessage = (result, fallback) => {
      if (result && result.errors) {
        return Object.values(result.errors).join(' | ');
      }
      return (result && result.message) ? result.message : fallback;
    };
    const updateCsrfHash = (hash) => {
      if (hash) {
        step5FormConfig.csrfHash = hash;
      }
    };
    const getInputValue = (name) => {
      const input = getStep5Input(name);
      return input ? String(input.value || '').trim() : '';
    };
    const isPreviewableImage = (fileName) => /\.(png|jpe?g|gif|webp|bmp|svg)$/i.test(String(fileName || ''));
    const getStep5DocLabel = (docType) => {
      const options = step5FormConfig.options && step5FormConfig.options.cobroCorrecto ? step5FormConfig.options.cobroCorrecto : {};
      return options[String(docType || '')] || String(docType || 'Soporte de cobro');
    };
    const buildStep5FileUrl = (doc) => {
      if (doc && doc.url) {
        return String(doc.url);
      }
      const base = String(step5FormConfig.fileBaseUrl || '');
      if (base === '') {
        return '#';
      }
      const normalizedBase = base.endsWith('/') ? base : base + '/';
      return normalizedBase + encodeURIComponent(String(doc && doc.file ? doc.file : ''));
    };

    const step5DocState = {
      docs: Array.isArray(step5FormConfig.docs) ? step5FormConfig.docs.map((doc) => ({
        id: Number(doc.id || 0),
        file: String(doc.file || ''),
        cobro_correcto: String(doc.cobro_correcto || 'otro'),
        url: String(doc.url || ''),
      })) : [],
      pendingFile: null,
    };
    let step5DocUploadInFlight = false;
    let step5PendingDeleteFile = '';

    const setFeedback = (message, tone) => {
      if (!step5Feedback) {
        return;
      }
      if (!message) {
        step5Feedback.hidden = true;
        step5Feedback.textContent = '';
        step5Feedback.classList.remove('is-success', 'is-error');
        return;
      }
      step5Feedback.hidden = false;
      step5Feedback.textContent = message;
      step5Feedback.classList.toggle('is-success', tone === 'success');
      step5Feedback.classList.toggle('is-error', tone === 'error');
    };

    const step5SuccessFlashKey = 'prototype-step5-success-flash-' + String(step5FormConfig.tramiteId || '0');
    const persistStep5SuccessFlash = (message) => {
      try {
        window.sessionStorage.setItem(step5SuccessFlashKey, String(message || ''));
      } catch (error) {
      }
    };
    const consumeStep5SuccessFlash = () => {
      try {
        const message = window.sessionStorage.getItem(step5SuccessFlashKey) || '';
        if (message !== '') {
          window.sessionStorage.removeItem(step5SuccessFlashKey);
          setFeedback(message, 'success');
        }
      } catch (error) {
      }
    };

    const setDocFeedback = (message, tone) => {
      if (!step5DocFeedback) {
        return;
      }
      if (!message) {
        step5DocFeedback.hidden = true;
        step5DocFeedback.textContent = '';
        step5DocFeedback.classList.remove('is-success', 'is-error');
        return;
      }
      step5DocFeedback.hidden = false;
      step5DocFeedback.textContent = message;
      step5DocFeedback.classList.toggle('is-success', tone === 'success');
      step5DocFeedback.classList.toggle('is-error', tone === 'error');
    };

    const setSaving = (isSaving) => {
      if (step5SaveButton) {
        step5SaveButton.disabled = isSaving || !step5FormConfig.canEdit;
        step5SaveButton.textContent = isSaving ? 'Guardando...' : 'Guardar Cobro a cliente';
      }
      step5Inputs.forEach((input) => {
        if (input.getAttribute('data-step5-input') === 'costo_gestoria' || input.getAttribute('data-step5-input') === 'costo_total') {
          input.disabled = true;
          return;
        }
        input.disabled = isSaving || !step5FormConfig.canEdit;
      });
    };

    const setDocSaving = (isSaving) => {
      if (step5DocUploadButton) {
        step5DocUploadButton.disabled = isSaving || !step5FormConfig.canUploadDocs;
        step5DocUploadButton.textContent = isSaving ? 'Subiendo...' : 'Subir evidencia';
      }
      if (step5DocTypeSelect) {
        step5DocTypeSelect.disabled = isSaving || !step5FormConfig.canUploadDocs;
      }
      if (step5DocFileInput) {
        step5DocFileInput.disabled = isSaving || !step5FormConfig.canUploadDocs;
      }
      if (step5DocDropzone) {
        step5DocDropzone.classList.toggle('is-disabled', isSaving || !step5FormConfig.canUploadDocs);
      }
    };

    const syncStep5Totals = () => {
      const costoGestoria = parseFloat(getInputValue('costo_gestoria') || '0') || 0;
      const costoPagoCliente = parseFloat(getInputValue('costo_pago_cliente') || '0') || 0;
      const comisionDerechos = parseFloat(getInputValue('comision_derechos') || '0') || 0;
      const iva = parseFloat(getInputValue('iva') || '0') || 0;
      const total = costoGestoria + costoPagoCliente + comisionDerechos + iva;
      const totalInput = getStep5Input('costo_total');
      if (totalInput) {
        totalInput.value = formatMoneyNumber(total);
      }
    };

    const closeDocPreviewModal = () => {
      if (docPreviewModal && typeof docPreviewModal.close === 'function') {
        docPreviewModal.close();
      }
    };

    const openDocPreviewModal = (fileName, fileUrl, metaLabel) => {
      const normalizedUrl = String(fileUrl || '').trim();
      if (!normalizedUrl || normalizedUrl === '#') {
        return;
      }
      if (!docPreviewModal || typeof docPreviewModal.showModal !== 'function' || !docPreviewImage || !docPreviewLink) {
        window.open(normalizedUrl, '_blank', 'noopener');
        return;
      }
      if (docPreviewTitle) {
        docPreviewTitle.textContent = String(fileName || 'Vista previa del documento');
      }
      if (docPreviewMeta) {
        docPreviewMeta.textContent = String(metaLabel || 'Revision visual del archivo cargado en el expediente.');
      }
      docPreviewImage.src = normalizedUrl;
      docPreviewImage.alt = String(fileName || 'Vista previa del documento');
      docPreviewLink.href = normalizedUrl;
      docPreviewLink.textContent = 'Abrir ' + String(fileName || 'archivo completo') + ' en una pestaña nueva';
      docPreviewModal.showModal();
    };

    const setPendingDocFile = (file) => {
      step5DocState.pendingFile = file || null;
      if (step5DocSelected) {
        step5DocSelected.textContent = file ? String(file.name || 'Archivo listo') : 'Sin archivo seleccionado.';
      }
    };

    const renderStep5DocUI = () => {
      step5DocState.docs = step5DocState.docs.filter((doc) => String(doc.file || '').trim() !== '');
      step5DocCounts.forEach((node) => {
        node.textContent = String(step5DocState.docs.length) + ' soporte(s)';
        node.classList.toggle('is-success', step5DocState.docs.length > 0);
      });

      if (!step5DocGallery) {
        return;
      }

      if (step5DocState.docs.length === 0) {
        step5DocGallery.innerHTML = '<div class="tp-gallery-item">Sin evidencias de cobro registradas</div>';
        return;
      }

      step5DocGallery.innerHTML = step5DocState.docs.map((doc) => {
        const fileUrl = buildStep5FileUrl(doc);
        const docLabel = getStep5DocLabel(doc.cobro_correcto);
        return ''
          + '<div class="tp-gallery-item">'
          + (isPreviewableImage(doc.file)
            ? '<button type="button" class="tp-gallery-preview-trigger" data-doc-preview-url="' + escapeHtml(fileUrl) + '" data-doc-preview-name="' + escapeHtml(doc.file) + '" data-doc-preview-meta="' + escapeHtml(docLabel) + '"><img class="tp-gallery-preview-image" src="' + escapeHtml(fileUrl) + '" alt="' + escapeHtml(doc.file) + '" loading="lazy"></button>'
            : '')
          + '<div class="tp-gallery-item-head">'
          + '<div>'
          + '<a class="tp-gallery-item-link" href="' + escapeHtml(fileUrl) + '" target="_blank" rel="noreferrer">' + escapeHtml(doc.file) + '</a>'
          + '<span class="tp-gallery-item-meta">' + escapeHtml(docLabel) + '</span>'
          + '</div>'
          + '<div class="tp-gallery-item-actions">'
          + (isPreviewableImage(doc.file)
            ? '<button type="button" class="tp-btn ghost small" data-doc-preview-url="' + escapeHtml(fileUrl) + '" data-doc-preview-name="' + escapeHtml(doc.file) + '" data-doc-preview-meta="' + escapeHtml(docLabel) + '">Ver imagen</button>'
            : '')
          + (step5FormConfig.canDeleteDocs
            ? '<button type="button" class="tp-btn secondary small" data-step5-doc-delete="' + escapeHtml(doc.file) + '">Eliminar</button>'
            : '')
          + '</div>'
          + '</div>'
          + '</div>';
      }).join('');
    };

    const reloadStep5Documents = async () => {
      if (!step5FormConfig.urls || !step5FormConfig.urls.getFiles || step5FormConfig.urls.getFiles === '#') {
        renderStep5DocUI();
        return;
      }
      const response = await fetch(step5FormConfig.urls.getFiles, {
        method: 'GET',
        credentials: 'same-origin',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
      });
      const result = await response.json();
      if (!response.ok || !Array.isArray(result)) {
        throw new Error('No se pudo refrescar la galeria de evidencias.');
      }
      step5DocState.docs = result.map((doc) => ({
        id: Number(doc.id || 0),
        file: String(doc.name || doc.file || ''),
        cobro_correcto: String(doc.cobro_correcto || 'otro'),
        url: String(doc.existing_path || ''),
      }));
      renderStep5DocUI();
    };

    const setDeleteModalBusy = (isBusy) => {
      if (step5DocDeleteConfirmButton) {
        step5DocDeleteConfirmButton.disabled = isBusy;
        step5DocDeleteConfirmButton.textContent = isBusy ? 'Eliminando...' : 'Si, eliminar evidencia';
      }
      if (step5DocDeleteCancelButton) {
        step5DocDeleteCancelButton.disabled = isBusy;
      }
      if (step5DocDeleteCloseButton) {
        step5DocDeleteCloseButton.disabled = isBusy;
      }
    };

    const closeDeleteModal = () => {
      if (step5DocDeleteModal && typeof step5DocDeleteModal.close === 'function') {
        step5DocDeleteModal.close();
      }
    };

    const openDeleteModal = (fileName) => {
      if (!step5FormConfig.canDeleteDocs || !fileName) {
        return;
      }
      step5PendingDeleteFile = String(fileName);
      if (step5DocDeleteName) {
        step5DocDeleteName.textContent = step5PendingDeleteFile;
      }
      if (!step5DocDeleteModal || typeof step5DocDeleteModal.showModal !== 'function') {
        if (window.confirm('Estas seguro de eliminar la evidencia ' + step5PendingDeleteFile + '? Esta accion borrara el archivo y su registro.')) {
          deleteStep5Document(step5PendingDeleteFile);
        }
        return;
      }
      setDeleteModalBusy(false);
      step5DocDeleteModal.showModal();
    };

    const deleteStep5Document = async (fileName) => {
      if (!step5FormConfig.canDeleteDocs || !fileName || !step5FormConfig.urls || !step5FormConfig.urls.delete || step5FormConfig.urls.delete === '#') {
        return;
      }
      setDocFeedback('', '');
      setDeleteModalBusy(true);
      setDocSaving(true);
      try {
        const formData = new FormData();
        formData.append(step5FormConfig.csrfName, step5FormConfig.csrfHash || '');
        formData.append('tramite_id', String(step5FormConfig.tramiteId || '0'));
        formData.append('file', String(fileName));
        const response = await fetch(step5FormConfig.urls.delete, {
          method: 'POST',
          body: formData,
          credentials: 'same-origin',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });
        const result = await response.json();
        updateCsrfHash(result.csrfHash || '');
        if (!response.ok || !result.success) {
          throw new Error(buildErrorMessage(result, 'No se pudo eliminar la evidencia de cobro.'));
        }
        closeDeleteModal();
        await reloadStep5Documents();
        setDocFeedback('Evidencia de cobro eliminada correctamente.', 'success');
      } catch (error) {
        closeDeleteModal();
        setDocFeedback(error.message || 'No se pudo eliminar la evidencia de cobro.', 'error');
      } finally {
        setDeleteModalBusy(false);
        setDocSaving(false);
      }
    };

    const uploadStep5Document = async () => {
      if (!step5FormConfig.canUploadDocs || !step5FormConfig.urls || !step5FormConfig.urls.upload || step5FormConfig.urls.upload === '#') {
        return;
      }
      if (step5DocUploadInFlight) {
        return;
      }
      const file = step5DocState.pendingFile || (step5DocFileInput && step5DocFileInput.files ? step5DocFileInput.files[0] : null);
      const cobroCorrecto = step5DocTypeSelect ? String(step5DocTypeSelect.value || '').trim() : '';
      if (!cobroCorrecto) {
        setDocFeedback('Selecciona el tipo de soporte de cobro.', 'error');
        return;
      }
      if (!file) {
        setDocFeedback('Selecciona un archivo para subirlo como evidencia de cobro.', 'error');
        return;
      }

      setDocFeedback('', '');
      step5DocUploadInFlight = true;
      setDocSaving(true);
      try {
        const formData = new FormData();
        formData.append(step5FormConfig.csrfName, step5FormConfig.csrfHash || '');
        formData.append('cobro_correcto', cobroCorrecto);
        formData.append('file', file);
        const response = await fetch(step5FormConfig.urls.upload, {
          method: 'POST',
          body: formData,
          credentials: 'same-origin',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });
        const result = await response.json();
        updateCsrfHash(result.csrfHash || '');
        if (!response.ok || !result.success) {
          throw new Error(buildErrorMessage(result, 'No se pudo subir la evidencia de cobro.'));
        }
        if (step5DocFileInput) {
          step5DocFileInput.value = '';
        }
        setPendingDocFile(null);
        await reloadStep5Documents();
        setDocFeedback('Evidencia de cobro subida correctamente.', 'success');
      } catch (error) {
        setDocFeedback(error.message || 'No se pudo subir la evidencia de cobro.', 'error');
      } finally {
        step5DocUploadInFlight = false;
        setDocSaving(false);
      }
    };

    const saveStep5RealForm = async () => {
      if (!step5FormConfig.canEdit) {
        return;
      }
      syncStep5Totals();
      if (getInputValue('id_give_cliente') === '' || getInputValue('numero_factura') === '' || getInputValue('cobro_status_id') === '') {
        setFeedback('ID del cliente, numero de factura y estatus del cobro son obligatorios.', 'error');
        return;
      }
      if (getInputValue('costo_pago_cliente') === '' || getInputValue('comision_derechos') === '') {
        setFeedback('Honorarios del tramite y comision de derechos son obligatorios.', 'error');
        return;
      }

      setFeedback('', '');
      setSaving(true);
      try {
        const formData = new FormData();
        formData.append(step5FormConfig.csrfName, step5FormConfig.csrfHash || '');
        ['id_give_cliente', 'numero_factura', 'numero_refactura', 'cobro_status_id', 'evidencia_cobro_txt', 'costo_pago_cliente', 'comision_derechos', 'iva', 'costo_total'].forEach((fieldName) => {
          formData.append(fieldName, getInputValue(fieldName));
        });
        formData.append('costo_gestoria', getInputValue('costo_gestoria'));
        formData.append('costo_gestoria_hidden', getInputValue('costo_gestoria'));

        const response = await fetch(step5FormConfig.url, {
          method: 'POST',
          body: formData,
          credentials: 'same-origin',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });
        const result = await response.json();
        updateCsrfHash(result.csrfHash || '');
        if (!response.ok || !result.success) {
          throw new Error(buildErrorMessage(result, 'No se pudo guardar Cobro a cliente.'));
        }
        persistStep5SuccessFlash('Cobro a cliente guardado correctamente.');
        setFeedback('Cobro a cliente guardado. Rehidratando el paso con el estado actual del servidor...', 'success');
        window.setTimeout(() => {
          window.location.reload();
        }, 600);
      } catch (error) {
        setFeedback(error.message || 'No se pudo guardar Cobro a cliente.', 'error');
      } finally {
        setSaving(false);
      }
    };

    ['costo_pago_cliente', 'comision_derechos', 'iva'].forEach((fieldName) => {
      const input = getStep5Input(fieldName);
      if (!input) {
        return;
      }
      input.addEventListener('input', () => {
        syncStep5Totals();
      });
    });

    if (step5SaveButton) {
      step5SaveButton.addEventListener('click', () => {
        saveStep5RealForm();
      });
    }

    if (step5DocUploadButton) {
      step5DocUploadButton.addEventListener('click', () => {
        uploadStep5Document();
      });
    }

    if (step5DocDropzone) {
      step5DocDropzone.addEventListener('click', () => {
        if (step5FormConfig.canUploadDocs && step5DocFileInput) {
          step5DocFileInput.click();
        }
      });

      ['dragenter', 'dragover'].forEach((eventName) => {
        step5DocDropzone.addEventListener(eventName, (event) => {
          event.preventDefault();
          if (!step5FormConfig.canUploadDocs) {
            return;
          }
          step5DocDropzone.classList.add('is-dragover');
        });
      });

      ['dragleave', 'dragend', 'drop'].forEach((eventName) => {
        step5DocDropzone.addEventListener(eventName, (event) => {
          event.preventDefault();
          step5DocDropzone.classList.remove('is-dragover');
        });
      });

      step5DocDropzone.addEventListener('drop', (event) => {
        if (!step5FormConfig.canUploadDocs) {
          return;
        }
        const files = event.dataTransfer && event.dataTransfer.files ? event.dataTransfer.files : null;
        if (!files || !files.length) {
          return;
        }
        setPendingDocFile(files[0]);
      });
    }

    if (step5DocFileInput) {
      step5DocFileInput.addEventListener('change', () => {
        const nextFile = step5DocFileInput.files && step5DocFileInput.files.length ? step5DocFileInput.files[0] : null;
        setPendingDocFile(nextFile);
      });
    }

    if (step5DocGallery) {
      step5DocGallery.addEventListener('click', (event) => {
        const previewButton = event.target instanceof Element ? event.target.closest('[data-doc-preview-url]') : null;
        if (previewButton) {
          event.preventDefault();
          openDocPreviewModal(
            previewButton.getAttribute('data-doc-preview-name'),
            previewButton.getAttribute('data-doc-preview-url'),
            previewButton.getAttribute('data-doc-preview-meta')
          );
          return;
        }

        const deleteButton = event.target instanceof Element ? event.target.closest('[data-step5-doc-delete]') : null;
        if (!deleteButton) {
          return;
        }
        openDeleteModal(deleteButton.getAttribute('data-step5-doc-delete'));
      });
    }

    if (step5DocDeleteConfirmButton) {
      step5DocDeleteConfirmButton.addEventListener('click', () => {
        if (!step5PendingDeleteFile) {
          closeDeleteModal();
          return;
        }
        deleteStep5Document(step5PendingDeleteFile);
      });
    }

    if (step5DocDeleteCancelButton) {
      step5DocDeleteCancelButton.addEventListener('click', () => {
        closeDeleteModal();
      });
    }

    if (step5DocDeleteCloseButton) {
      step5DocDeleteCloseButton.addEventListener('click', () => {
        closeDeleteModal();
      });
    }

    if (step5DocDeleteModal) {
      step5DocDeleteModal.addEventListener('cancel', (event) => {
        event.preventDefault();
        closeDeleteModal();
      });
      step5DocDeleteModal.addEventListener('click', (event) => {
        if (event.target === step5DocDeleteModal) {
          closeDeleteModal();
        }
      });
    }

    if (docPreviewCloseButton) {
      docPreviewCloseButton.addEventListener('click', () => {
        closeDocPreviewModal();
      });
    }

    if (docPreviewCancelButton) {
      docPreviewCancelButton.addEventListener('click', () => {
        closeDocPreviewModal();
      });
    }

    if (docPreviewModal) {
      docPreviewModal.addEventListener('cancel', (event) => {
        event.preventDefault();
        closeDocPreviewModal();
      });
      docPreviewModal.addEventListener('click', (event) => {
        if (event.target === docPreviewModal) {
          closeDocPreviewModal();
        }
      });
    }

    consumeStep5SuccessFlash();
    syncStep5Totals();
    renderStep5DocUI();
    setPendingDocFile(null);
    setSaving(false);
    setDocSaving(false);
  })();
</script>

<?php if (!$isOperationalBasePhase && $activeStep === 5): ?>
<script>
  (function() {
    const step5NotesFormConfig = <?= json_encode($prototypeStep5NotesForm, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    const step5NotesInput = document.querySelector('[data-step5-note-input]');
    const step5NotesSaveButton = document.querySelector('[data-step5-note-save]');
    const step5NotesFeedback = document.querySelector('[data-step5-note-feedback]');
    const step5NotesList = document.querySelector('[data-step5-note-list]');
    const step5NotesEmpty = document.querySelector('[data-step5-note-empty]');
    const step5NotesState = {
      items: Array.isArray(step5NotesFormConfig.items) ? step5NotesFormConfig.items.map((item) => ({
        id: Number(item.id || 0),
        comment: String(item.comment || ''),
        author: String(item.author || 'Sistema'),
        createdAt: String(item.createdAt || ''),
        createdAtLabel: String(item.createdAtLabel || 'Sin fecha'),
      })) : [],
    };

    if (!step5NotesList || !step5NotesEmpty) {
      return;
    }

    const escapeHtml = (value) => String(value ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');

    const buildErrorMessage = (result, fallback) => {
      if (result && result.errors) {
        return Object.values(result.errors).join(' | ');
      }
      return (result && result.message) ? result.message : fallback;
    };

    const setStep5NotesFeedback = (message, tone) => {
      if (!step5NotesFeedback) {
        return;
      }
      if (!message) {
        step5NotesFeedback.hidden = true;
        step5NotesFeedback.textContent = '';
        step5NotesFeedback.classList.remove('is-success', 'is-error');
        return;
      }
      step5NotesFeedback.hidden = false;
      step5NotesFeedback.textContent = message;
      step5NotesFeedback.classList.toggle('is-success', tone === 'success');
      step5NotesFeedback.classList.toggle('is-error', tone === 'error');
    };

    const setStep5NotesSaving = (isSaving) => {
      if (step5NotesSaveButton) {
        step5NotesSaveButton.disabled = isSaving || !step5NotesFormConfig.canAdd;
        step5NotesSaveButton.textContent = isSaving ? 'Guardando...' : 'Guardar nota interna';
      }
      if (step5NotesInput) {
        step5NotesInput.disabled = isSaving || !step5NotesFormConfig.canAdd;
      }
    };

    const renderStep5NoteItem = (item) => {
      return '<div class="tp-note-item tone-info">'
        + '<span class="tp-note-meta">' + escapeHtml(String(item.createdAtLabel || 'Sin fecha') + ' · ' + String(item.author || 'Sistema')) + '</span>'
        + '<span class="tp-note-body">' + escapeHtml(item.comment || '') + '</span>'
      + '</div>';
    };

    const renderStep5NotesList = () => {
      const items = step5NotesState.items.filter((item) => String(item.comment || '').trim() !== '');
      step5NotesList.innerHTML = items.map((item) => renderStep5NoteItem(item)).join('');
      step5NotesList.hidden = items.length === 0;
      step5NotesEmpty.hidden = items.length !== 0;
    };

    const saveStep5InternalNote = async () => {
      if (!step5NotesFormConfig.canAdd || !step5NotesInput) {
        return;
      }

      const comment = String(step5NotesInput.value || '').trim();
      if (comment.length < 3) {
        setStep5NotesFeedback('Escribe una nota de al menos 3 caracteres.', 'error');
        return;
      }

      setStep5NotesFeedback('', '');
      setStep5NotesSaving(true);
      try {
        const formData = new FormData();
        formData.append(step5NotesFormConfig.csrfName, step5NotesFormConfig.csrfHash || '');
        formData.append('comentario', comment);

        const response = await fetch(step5NotesFormConfig.urls.create, {
          method: 'POST',
          body: formData,
          credentials: 'same-origin',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });
        const result = await response.json();
        if (result && result.csrfHash) {
          step5NotesFormConfig.csrfHash = result.csrfHash;
        }
        if (!response.ok || !result.success) {
          throw new Error(buildErrorMessage(result, 'No se pudo guardar la nota interna.'));
        }

        step5NotesState.items.unshift({
          id: Number(result.item && result.item.id ? result.item.id : 0),
          comment: String(result.item && result.item.comment ? result.item.comment : comment),
          author: String(result.item && result.item.author ? result.item.author : 'Sistema'),
          createdAt: String(result.item && result.item.createdAt ? result.item.createdAt : ''),
          createdAtLabel: String(result.item && result.item.createdAtLabel ? result.item.createdAtLabel : 'Sin fecha'),
        });
        step5NotesInput.value = '';
        renderStep5NotesList();
        setStep5NotesFeedback('Nota interna guardada correctamente.', 'success');
      } catch (error) {
        setStep5NotesFeedback(error.message || 'No se pudo guardar la nota interna.', 'error');
      } finally {
        setStep5NotesSaving(false);
      }
    };

    renderStep5NotesList();
    setStep5NotesSaving(false);

    if (step5NotesSaveButton) {
      step5NotesSaveButton.addEventListener('click', () => {
        saveStep5InternalNote();
      });
    }
  })();
</script>
<?php endif; ?>

</div>

<?php if (!$isEmbeddedPrototypeBody): ?>
<?= $this->endSection() ?>
<?php endif; ?>