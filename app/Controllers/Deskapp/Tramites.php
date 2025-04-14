<?php
// namespace App\Controllers;
namespace App\Controllers\Deskapp;
use App\Controllers\BaseController;


use Config\Database as ConfigDatabase;
use Config\GroceryCrud as ConfigGroceryCrud;
use GroceryCrud\Core\GroceryCrud;

use Config\Database;

use App\Models\TraDocStatusModel;
use App\Models\TramiteAfterInsert;
use App\Models\BitacoraModel;
use App\Models\TraTiposModel;
use App\Models\EntMunicipioModel;
use App\Models\EntidadesModel;
use App\Models\ClienteModel;
use App\Models\ClienteDirectoModel;
use App\Models\ClienteDirectoEjecutivoModel;
use App\Models\EmpresaGestoraModel;
use App\Models\GestorModel;
use App\Models\TraStatusModel;
use App\Models\TramitesModel;
use App\Models\CobroStatusesModel;
use App\Models\TraUserLogModel;
use App\Models\ReembolsoStatusModel;
use App\Models\CobroStatusModel;
use App\Models\PagoDerechosModel;
use App\Models\PagoGestorStatusModel;
use App\Models\TraTramiteAsociadoModel;

class Tramites extends BaseController
{
    public function __construct() {
        // parent::__construct();
        helper(['form', 'url']);
    }

    public function index()
    {
        $output = (object)[
            'js_files' => [],
            'output' => ''
        ];
        
        return $this->_example_output($output);
    }

    public function tramite()
    {
        try {
            # Manejo de session de action
            $self = $this;
            $session = session();
            $data['session'] = \Config\Services::session();
            $data['username'] = $session->get('user_name');
            $myid = $session->get('id');
            # fin del manejo de session

            $tramite_crud = $this->_getGroceryCrudEnterprise();
            //$tramite_crud->where('tra_status_id NOT IN (20, 21)');
            
            $tramite_crud->unsetAdd();
            $tramite_crud->unsetEdit();
            $tramite_crud->unsetRead();
            // $tramite_crud->setTheme('bootstrap-v5');
            $tramite_crud->unsetDeleteMultiple();
            if (has_permission('editar_tramite', esc($session->get('user_permissions')),esc($session->get('user_roles')))){
                $tramite_crud->setActionButton('Editar', 'fas fa-pencil-alt', function ($row) {
                    return '/deskapp/tramites/update/' . $row->id;
                }, false);
            }

            if (!has_permission('delete_tramite', esc($session->get('user_permissions')),esc($session->get('user_roles')))){
                $tramite_crud->unsetDelete();
            }

            if (!has_permission('export_tramite', esc($session->get('user_permissions')),esc($session->get('user_roles')))){
                $tramite_crud->unsetExport();
            }

            if (!has_permission('print_tramite', esc($session->get('user_permissions')),esc($session->get('user_roles')))){
                $tramite_crud->unsetPrint();
            }

            if (has_permission('read_tramite', esc($session->get('user_permissions')),esc($session->get('user_roles')))){
                $tramite_crud->setActionButton('Ver', 'fas fa-eye', function ($row) {
                    return '/deskapp/tramites/update/' . $row->id;
                }, false);
            }

            if (!has_permission('clone_tramite', esc($session->get('user_permissions')),esc($session->get('user_roles')))){
                $tramite_crud->unsetClone();
            }

            $tramite_crud->setCsrfTokenName(csrf_token());
            $tramite_crud->setCsrfTokenValue(csrf_hash());

            //lista todos los unset de grocery crud
            
            $tramite_crud->setTable('tramite');
            $tramite_crud->setSubject('tramite', 'Tramites');
            $tramite_crud->defaultOrdering('tramite.id', 'desc');
            
            $tramite_crud->where([
                'tramite.created_at >= ?' => ['2025-01-01 00:00:00']
            ]);

            $tramite_crud->columns([
                'id', 'created_at', 'started_at', 'tra_status_id', 'folio', 'contrato', 'unidad', 'serie', 
                'placas', 'tra_tipos_id', 'entidad_id', 'ent_municipio_id', 'cli_directo_id',
                'cli_directo_ejecutivo_id', 'empresa_gestora_id', 'gestor_id',
                'cobro_status_id', 'user_id',
                'observaciones'
            ]);

            $tramite_crud->displayAs("started_at", "Desde Asignación");


            $tramite_crud->setRelation('user_id', 'users', '{firstname} {midname} {lastname}');
            $tramite_crud->displayAs("user_id", "Ejecutivo");

            $tramite_crud->callbackColumn('started_at', function ($value, $row) {
                $fechaAsignacion = new \DateTime($row->started_at); 
                $fechaActual = new \DateTime();
                $diasDiferencia = $fechaAsignacion->diff($fechaActual)->days;
            
                // Definir clases CSS según los días
                $claseVerde = 'background-verde';
                $claseAmarillo = 'background-amarillo';
                $claseRojo = 'background-rojo';
                $claseVioleta = 'background-violeta';
                $claseGris = 'background-gris';  // Clase CSS para gris claro
                $claseAzulClaro = 'background-azul-claro';  // Clase CSS para azul claro
                $claseAzul = 'background-azul';  // Clase CSS para azul
            
                // Verificar tra_status_id para colores especiales
                if ($row->tra_status_id == 23) {
                    $clase = $claseAzulClaro;
                } elseif ($row->tra_status_id == 21) {
                    $clase = $claseGris;
                } elseif ($row->tra_status_id == 20) {
                    $clase = $claseAzul;
                } else {
                    // Determinar si es Local o Foráneo
                    $local = ($row->ent_municipio_id >= 266 && $row->ent_municipio_id <= 281) || 
                             ($row->ent_municipio_id >= 657 && $row->ent_municipio_id <= 781);
                    
                    // Determinar la clase CSS basada en los días de diferencia y si es Local o Foráneo
                    if ($local) {
                        if ($diasDiferencia < 5) {
                            $clase = $claseVerde;
                        } elseif ($diasDiferencia < 8) {
                            $clase = $claseAmarillo;
                        } elseif ($diasDiferencia < 12) {
                            $clase = $claseRojo;
                        } else {
                            $clase = $claseVioleta;
                        }
                    } else {
                        if ($diasDiferencia < 10) {
                            $clase = $claseVerde;
                        } elseif ($diasDiferencia < 13) {
                            $clase = $claseAmarillo;
                        } elseif ($diasDiferencia < 16) {
                            $clase = $claseRojo;
                        } else {
                            $clase = $claseVioleta;
                        }
                    }
                }
                $arrFilter = [20, 21, 23];
                if (!in_array($row->tra_status_id, $arrFilter)) {
                    return '<span class="' . $clase . '">' . $diasDiferencia . ' días</span>';
                }
            
                return '<span class="' . $clase . '"></span>';
            });

            $tramite_crud->fields([
                'folio','contrato','unidad','serie', 
                'placas','tra_tipos_id','ent_municipio_id','cli_directo_id',
                'cli_directo_ejecutivo_id','empresa_gestora_id','gestor_id',
                'tra_status_id','cobro_status_id',
                'observaciones', 'user_id'
            ]); 

            $tramite_crud->displayAs("created_at", "Creación");
            /* SELECT Se configura el tipo de tramite */
            $tramite_crud->setRelation('tra_tipos_id', 'tra_tipos', 'tipo_tramite');
            $tramite_crud->displayAs('tra_tipos_id','Tipo de Tramite');

            /* SELECT Se configura el estatus del tramite */
            $tramite_crud->setRelation('tra_status_id', 'tra_status', 'tra_status');
            $tramite_crud->displayAs('tra_status_id','Estatus del Tramite');

            /* SELECT Se configura el cliente final o cliente directo */
            $tramite_crud->setRelation('cli_directo_id', 'cli_directo', 'razon_social');
            $tramite_crud->displayAs('cli_directo_id','Cliente Directo');
            
            /* SELECT Se configura el ejecutivo del cliente */
            $tramite_crud->setRelation('cli_directo_ejecutivo_id', 'cli_directo_ejecutivo', 'nombre');
            $tramite_crud->displayAs('cli_directo_ejecutivo_id','Ejecutivo del Cliente');

            $tramite_crud->setDependentRelation('cli_directo_ejecutivo_id','cli_directo_id','cli_directo_id');

            /* SELECT Se configura el la entidad */
            $tramite_crud->setRelation('entidad_id', 'entidad', 'entidad');
            $tramite_crud->displayAs('entidad_id','Entidad');

            /* SELECT Se configura el municipio */
            $tramite_crud->setRelation('ent_municipio_id', 'rel_ent_municipio', 'ent_municipality');
            $tramite_crud->displayAs('ent_municipio_id','Municipio');

            /* SELECT Se configura la empresa gestora */
            $tramite_crud->setRelation('empresa_gestora_id', 'ges_empresa_gestora', 'razon_social');
            $tramite_crud->displayAs('empresa_gestora_id','Empresa Gestora');

            /* SELECT Se configura el gestor*/
            $tramite_crud->setRelation('gestor_id', 'ges_gestor', 'nombre');
            $tramite_crud->displayAs('gestor_id','Gestor');

            $tramite_crud->setDependentRelation('gestor_id','empresa_gestora_id','empresa_gestora_id');

            $tramite_salida = $tramite_crud->render();
            
            $salida_total = array_merge((array)$tramite_salida, $data);
            $salida_total['insert_button_url'] = '/public/deskapp/tramites/add';

            echo $this->_example_output($salida_total);

        } catch (\Exception $e) {
            exit($e->getMessage());
        }
    }
    public function tramite_2024()
    {
        try {
            # Manejo de session de action
            $self = $this;
            $session = session();
            $data['session'] = \Config\Services::session();
            $data['username'] = $session->get('user_name');
            $myid = $session->get('id');
            # fin del manejo de session

            $tramite_crud = $this->_getGroceryCrudEnterprise();
            //$tramite_crud->where('tra_status_id NOT IN (20, 21)');
            
            $tramite_crud->unsetAdd();
            $tramite_crud->unsetEdit();
            $tramite_crud->unsetRead();
            // $tramite_crud->setTheme('bootstrap-v5');
            $tramite_crud->unsetDeleteMultiple();
            if (has_permission('editar_tramite', esc($session->get('user_permissions')),esc($session->get('user_roles')))){
                $tramite_crud->setActionButton('Editar', 'fas fa-pencil-alt', function ($row) {
                    return '/deskapp/tramites/update/' . $row->id;
                }, false);
            }

            if (!has_permission('delete_tramite', esc($session->get('user_permissions')),esc($session->get('user_roles')))){
                $tramite_crud->unsetDelete();
            }

            if (!has_permission('export_tramite', esc($session->get('user_permissions')),esc($session->get('user_roles')))){
                $tramite_crud->unsetExport();
            }

            if (!has_permission('print_tramite', esc($session->get('user_permissions')),esc($session->get('user_roles')))){
                $tramite_crud->unsetPrint();
            }

            if (has_permission('read_tramite', esc($session->get('user_permissions')),esc($session->get('user_roles')))){
                $tramite_crud->setActionButton('Ver', 'fas fa-eye', function ($row) {
                    return '/deskapp/tramites/update/' . $row->id;
                }, false);
            }

            if (!has_permission('clone_tramite', esc($session->get('user_permissions')),esc($session->get('user_roles')))){
                $tramite_crud->unsetClone();
            }

            $tramite_crud->setCsrfTokenName(csrf_token());
            $tramite_crud->setCsrfTokenValue(csrf_hash());

            //lista todos los unset de grocery crud
            
            $tramite_crud->setTable('tramite');
            $tramite_crud->setSubject('tramite', 'Tramites');
            $tramite_crud->defaultOrdering('tramite.id', 'desc');
            $tramite_crud->where([
                'tramite.created_at < ?' => ['2025-01-01']
            ]);
            $tramite_crud->columns([
                'id', 'created_at', 'started_at', 'tra_status_id', 'folio', 'contrato', 'unidad', 'serie', 
                'placas', 'tra_tipos_id', 'entidad_id', 'ent_municipio_id', 'cli_directo_id',
                'cli_directo_ejecutivo_id', 'empresa_gestora_id', 'gestor_id',
                'cobro_status_id', 'user_id',
                'observaciones'
            ]);

            $tramite_crud->displayAs("started_at", "Desde Asignación");


            $tramite_crud->setRelation('user_id', 'users', '{firstname} {midname} {lastname}');
            $tramite_crud->displayAs("user_id", "Ejecutivo");

            $tramite_crud->callbackColumn('started_at', function ($value, $row) {
                $fechaAsignacion = new \DateTime($row->started_at); 
                $fechaActual = new \DateTime();
                $diasDiferencia = $fechaAsignacion->diff($fechaActual)->days;
            
                // Definir clases CSS según los días
                $claseVerde = 'background-verde';
                $claseAmarillo = 'background-amarillo';
                $claseRojo = 'background-rojo';
                $claseVioleta = 'background-violeta';
                $claseGris = 'background-gris';  // Clase CSS para gris claro
                $claseAzulClaro = 'background-azul-claro';  // Clase CSS para azul claro
                $claseAzul = 'background-azul';  // Clase CSS para azul
            
                // Verificar tra_status_id para colores especiales
                if ($row->tra_status_id == 23) {
                    $clase = $claseAzulClaro;
                } elseif ($row->tra_status_id == 21) {
                    $clase = $claseGris;
                } elseif ($row->tra_status_id == 20) {
                    $clase = $claseAzul;
                } else {
                    // Determinar si es Local o Foráneo
                    $local = ($row->ent_municipio_id >= 266 && $row->ent_municipio_id <= 281) || 
                             ($row->ent_municipio_id >= 657 && $row->ent_municipio_id <= 781);
                    
                    // Determinar la clase CSS basada en los días de diferencia y si es Local o Foráneo
                    if ($local) {
                        if ($diasDiferencia < 5) {
                            $clase = $claseVerde;
                        } elseif ($diasDiferencia < 8) {
                            $clase = $claseAmarillo;
                        } elseif ($diasDiferencia < 12) {
                            $clase = $claseRojo;
                        } else {
                            $clase = $claseVioleta;
                        }
                    } else {
                        if ($diasDiferencia < 10) {
                            $clase = $claseVerde;
                        } elseif ($diasDiferencia < 13) {
                            $clase = $claseAmarillo;
                        } elseif ($diasDiferencia < 16) {
                            $clase = $claseRojo;
                        } else {
                            $clase = $claseVioleta;
                        }
                    }
                }
                $arrFilter = [20, 21, 23];
                if (!in_array($row->tra_status_id, $arrFilter)) {
                    return '<span class="' . $clase . '">' . $diasDiferencia . ' días</span>';
                }
            
                return '<span class="' . $clase . '"></span>';
            });

            $tramite_crud->fields([
                'folio','contrato','unidad','serie', 
                'placas','tra_tipos_id','ent_municipio_id','cli_directo_id',
                'cli_directo_ejecutivo_id','empresa_gestora_id','gestor_id',
                'tra_status_id','cobro_status_id',
                'observaciones', 'user_id'
            ]); 

            $tramite_crud->displayAs("created_at", "Creación");
            /* SELECT Se configura el tipo de tramite */
            $tramite_crud->setRelation('tra_tipos_id', 'tra_tipos', 'tipo_tramite');
            $tramite_crud->displayAs('tra_tipos_id','Tipo de Tramite');

            /* SELECT Se configura el estatus del tramite */
            $tramite_crud->setRelation('tra_status_id', 'tra_status', 'tra_status');
            $tramite_crud->displayAs('tra_status_id','Estatus del Tramite');

            /* SELECT Se configura el cliente final o cliente directo */
            $tramite_crud->setRelation('cli_directo_id', 'cli_directo', 'razon_social');
            $tramite_crud->displayAs('cli_directo_id','Cliente Directo');
            
            /* SELECT Se configura el ejecutivo del cliente */
            $tramite_crud->setRelation('cli_directo_ejecutivo_id', 'cli_directo_ejecutivo', 'nombre');
            $tramite_crud->displayAs('cli_directo_ejecutivo_id','Ejecutivo del Cliente');

            $tramite_crud->setDependentRelation('cli_directo_ejecutivo_id','cli_directo_id','cli_directo_id');

            /* SELECT Se configura el la entidad */
            $tramite_crud->setRelation('entidad_id', 'entidad', 'entidad');
            $tramite_crud->displayAs('entidad_id','Entidad');

            /* SELECT Se configura el municipio */
            $tramite_crud->setRelation('ent_municipio_id', 'rel_ent_municipio', 'ent_municipality');
            $tramite_crud->displayAs('ent_municipio_id','Municipio');

            /* SELECT Se configura la empresa gestora */
            $tramite_crud->setRelation('empresa_gestora_id', 'ges_empresa_gestora', 'razon_social');
            $tramite_crud->displayAs('empresa_gestora_id','Empresa Gestora');

            /* SELECT Se configura el gestor*/
            $tramite_crud->setRelation('gestor_id', 'ges_gestor', 'nombre');
            $tramite_crud->displayAs('gestor_id','Gestor');

            $tramite_crud->setDependentRelation('gestor_id','empresa_gestora_id','empresa_gestora_id');

            $tramite_salida = $tramite_crud->render();
            
            $salida_total = array_merge((array)$tramite_salida, $data);
            $salida_total['insert_button_url'] = '/public/deskapp/tramites/add';

            echo $this->_example_output($salida_total);

        } catch (\Exception $e) {
            exit($e->getMessage());
        }
    }
    public function finalizados()
    {
        try {
            # Manejo de session de action
            $self = $this;
            $session = session();
            $data['session'] = \Config\Services::session();
            $data['username'] = $session->get('user_name');
            $myid = $session->get('id');
            # fin del manejo de session

            $tramite_crud = $this->_getGroceryCrudEnterprise();
            
            $tramite_crud->unsetAdd();
            $tramite_crud->unsetEdit();
            $tramite_crud->unsetRead();
            // $tramite_crud->setTheme('bootstrap-v5');
            $tramite_crud->unsetDeleteMultiple();
            if (has_permission('editar_tramite', esc($session->get('user_permissions')),esc($session->get('user_roles')))){
                $tramite_crud->setActionButton('Editar', 'fas fa-pencil-alt', function ($row) {
                    return '/deskapp/tramites/update/' . $row->id;
                }, false);
            }

            if (!has_permission('delete_tramite', esc($session->get('user_permissions')),esc($session->get('user_roles')))){
                $tramite_crud->unsetDelete();
            }

            if (!has_permission('export_tramite', esc($session->get('user_permissions')),esc($session->get('user_roles')))){
                $tramite_crud->unsetExport();
            }

            if (!has_permission('print_tramite', esc($session->get('user_permissions')),esc($session->get('user_roles')))){
                $tramite_crud->unsetPrint();
            }

            if (has_permission('read_tramite', esc($session->get('user_permissions')),esc($session->get('user_roles')))){
                $tramite_crud->setActionButton('Ver', 'fas fa-eye', function ($row) {
                    return '/deskapp/tramites/update/' . $row->id;
                }, false);
            }

            if (!has_permission('clone_tramite', esc($session->get('user_permissions')),esc($session->get('user_roles')))){
                $tramite_crud->unsetClone();
            }

            $tramite_crud->setCsrfTokenName(csrf_token());
            $tramite_crud->setCsrfTokenValue(csrf_hash());

            //lista todos los unset de grocery crud
            
            $tramite_crud->setTable('tramite');
            $tramite_crud->setSubject('tramite', 'Tramites');
            $tramite_crud->defaultOrdering('tramite.id', 'desc');

            $tramite_crud->where([
                'tramite.finished_at >= ?' => ['2025-01-01'],
                 'tramite.tra_status_id IN (20, 21)'
            ]);

            
            $tramite_crud->columns([
                'id', 'created_at', 'started_at', 'tra_status_id', 'folio', 'contrato', 'unidad', 'serie', 
                'placas', 'tra_tipos_id', 'entidad_id', 'ent_municipio_id', 'cli_directo_id',
                'cli_directo_ejecutivo_id', 'empresa_gestora_id', 'gestor_id',
                'cobro_status_id', 'user_id',
                'observaciones'
            ]);

            $tramite_crud->displayAs("started_at", "Desde Asignación");


            $tramite_crud->setRelation('user_id', 'users', '{firstname} {midname} {lastname}');
            $tramite_crud->displayAs("user_id", "Ejecutivo");

            $tramite_crud->callbackColumn('started_at', function ($value, $row) {
                $fechaAsignacion = new \DateTime($row->started_at); 
                $fechaActual = new \DateTime();
                $diasDiferencia = $fechaAsignacion->diff($fechaActual)->days;
            
                // Definir clases CSS según los días
                $claseVerde = 'background-verde';
                $claseAmarillo = 'background-amarillo';
                $claseRojo = 'background-rojo';
                $claseVioleta = 'background-violeta';
                $claseGris = 'background-gris';  // Clase CSS para gris claro
                $claseAzulClaro = 'background-azul-claro';  // Clase CSS para azul claro
                $claseAzul = 'background-azul';  // Clase CSS para azul
            
                // Verificar tra_status_id para colores especiales
                if ($row->tra_status_id == 23) {
                    $clase = $claseAzulClaro;
                } elseif ($row->tra_status_id == 21) {
                    $clase = $claseGris;
                } elseif ($row->tra_status_id == 20) {
                    $clase = $claseAzul;
                } else {
                    // Determinar si es Local o Foráneo
                    $local = ($row->ent_municipio_id >= 266 && $row->ent_municipio_id <= 281) || 
                             ($row->ent_municipio_id >= 657 && $row->ent_municipio_id <= 781);
                    
                    // Determinar la clase CSS basada en los días de diferencia y si es Local o Foráneo
                    if ($local) {
                        if ($diasDiferencia < 5) {
                            $clase = $claseVerde;
                        } elseif ($diasDiferencia < 8) {
                            $clase = $claseAmarillo;
                        } elseif ($diasDiferencia < 12) {
                            $clase = $claseRojo;
                        } else {
                            $clase = $claseVioleta;
                        }
                    } else {
                        if ($diasDiferencia < 10) {
                            $clase = $claseVerde;
                        } elseif ($diasDiferencia < 13) {
                            $clase = $claseAmarillo;
                        } elseif ($diasDiferencia < 16) {
                            $clase = $claseRojo;
                        } else {
                            $clase = $claseVioleta;
                        }
                    }
                }
                $arrFilter = [20, 21, 23];
                if (!in_array($row->tra_status_id, $arrFilter)) {
                    return '<span class="' . $clase . '">' . $diasDiferencia . ' días</span>';
                }
            
                return '<span class="' . $clase . '"></span>';
            });

            $tramite_crud->fields([
                'folio','contrato','unidad','serie', 
                'placas','tra_tipos_id','ent_municipio_id','cli_directo_id',
                'cli_directo_ejecutivo_id','empresa_gestora_id','gestor_id',
                'tra_status_id','cobro_status_id',
                'observaciones', 'user_id'
            ]); 

            $tramite_crud->displayAs("created_at", "Creación");
            /* SELECT Se configura el tipo de tramite */
            $tramite_crud->setRelation('tra_tipos_id', 'tra_tipos', 'tipo_tramite');
            $tramite_crud->displayAs('tra_tipos_id','Tipo de Tramite');

            /* SELECT Se configura el estatus del tramite */
            $tramite_crud->setRelation('tra_status_id', 'tra_status', 'tra_status');
            $tramite_crud->displayAs('tra_status_id','Estatus del Tramite');

            /* SELECT Se configura el cliente final o cliente directo */
            $tramite_crud->setRelation('cli_directo_id', 'cli_directo', 'razon_social');
            $tramite_crud->displayAs('cli_directo_id','Cliente Directo');
            
            /* SELECT Se configura el ejecutivo del cliente */
            $tramite_crud->setRelation('cli_directo_ejecutivo_id', 'cli_directo_ejecutivo', 'nombre');
            $tramite_crud->displayAs('cli_directo_ejecutivo_id','Ejecutivo del Cliente');

            $tramite_crud->setDependentRelation('cli_directo_ejecutivo_id','cli_directo_id','cli_directo_id');

            /* SELECT Se configura el la entidad */
            $tramite_crud->setRelation('entidad_id', 'entidad', 'entidad');
            $tramite_crud->displayAs('entidad_id','Entidad');

            /* SELECT Se configura el municipio */
            $tramite_crud->setRelation('ent_municipio_id', 'rel_ent_municipio', 'ent_municipality');
            $tramite_crud->displayAs('ent_municipio_id','Municipio');

            /* SELECT Se configura la empresa gestora */
            $tramite_crud->setRelation('empresa_gestora_id', 'ges_empresa_gestora', 'razon_social');
            $tramite_crud->displayAs('empresa_gestora_id','Empresa Gestora');

            /* SELECT Se configura el gestor*/
            $tramite_crud->setRelation('gestor_id', 'ges_gestor', 'nombre');
            $tramite_crud->displayAs('gestor_id','Gestor');

            $tramite_crud->setDependentRelation('gestor_id','empresa_gestora_id','empresa_gestora_id');

            $tramite_salida = $tramite_crud->render();
            
            $salida_total = array_merge((array)$tramite_salida, $data);
            $salida_total['insert_button_url'] = '/public/deskapp/tramites/add';

            echo $this->_example_output($salida_total);

        } catch (\Exception $e) {
            exit($e->getMessage());
        }
    }

    public function tenencias()
    {
        try {
            # Manejo de session de action
            $self = $this;
            $session = session();
            $data['session'] = \Config\Services::session();
            $data['username'] = $session->get('user_name');
            $myid = $session->get('id');
            # fin del manejo de session

            $tramite_crud = $this->_getGroceryCrudEnterprise();
            
            $tramite_crud->unsetAdd();
            $tramite_crud->unsetEdit();
            $tramite_crud->unsetRead();
            // $tramite_crud->setTheme('bootstrap-v5');
            $tramite_crud->unsetDeleteMultiple();
            if (has_permission('editar_tramite', esc($session->get('user_permissions')),esc($session->get('user_roles')))){
                $tramite_crud->setActionButton('Editar', 'fas fa-pencil-alt', function ($row) {
                    return '/deskapp/tramites/update/' . $row->id;
                }, false);
            }

            if (!has_permission('delete_tramite', esc($session->get('user_permissions')),esc($session->get('user_roles')))){
                $tramite_crud->unsetDelete();
            }

            if (!has_permission('export_tramite', esc($session->get('user_permissions')),esc($session->get('user_roles')))){
                $tramite_crud->unsetExport();
            }

            if (!has_permission('print_tramite', esc($session->get('user_permissions')),esc($session->get('user_roles')))){
                $tramite_crud->unsetPrint();
            }

            if (has_permission('read_tramite', esc($session->get('user_permissions')),esc($session->get('user_roles')))){
                $tramite_crud->setActionButton('Ver', 'fas fa-eye', function ($row) {
                    return '/deskapp/tramites/update/' . $row->id;
                }, false);
            }

            if (!has_permission('clone_tramite', esc($session->get('user_permissions')),esc($session->get('user_roles')))){
                $tramite_crud->unsetClone();
            }

            $tramite_crud->setCsrfTokenName(csrf_token());
            $tramite_crud->setCsrfTokenValue(csrf_hash());

            //lista todos los unset de grocery crud
            
            $tramite_crud->setTable('tramite');
            $tramite_crud->setSubject('tramite', 'Tramites');
            $tramite_crud->defaultOrdering('tramite.id', 'desc');

            $tramite_crud->where([
                'tramite.created_at >= ?' => ['2025-01-01'],
                'tra_tipos_id IN (31, 32, 33)'
            ]);

            $tramite_crud->columns([
                'id', 'created_at', 'started_at', 'tra_status_id', 'folio', 'contrato', 'unidad', 'serie', 
                'placas', 'tra_tipos_id', 'entidad_id', 'ent_municipio_id', 'cli_directo_id',
                'cli_directo_ejecutivo_id', 'empresa_gestora_id', 'gestor_id',
                'cobro_status_id', 'user_id',
                'observaciones'
            ]);

            $tramite_crud->displayAs("started_at", "Desde Asignación");


            $tramite_crud->setRelation('user_id', 'users', '{firstname} {midname} {lastname}');
            $tramite_crud->displayAs("user_id", "Ejecutivo");

            $tramite_crud->callbackColumn('started_at', function ($value, $row) {
                $fechaAsignacion = new \DateTime($row->started_at); 
                $fechaActual = new \DateTime();
                $diasDiferencia = $fechaAsignacion->diff($fechaActual)->days;
            
                // Definir clases CSS según los días
                $claseVerde = 'background-verde';
                $claseAmarillo = 'background-amarillo';
                $claseRojo = 'background-rojo';
                $claseVioleta = 'background-violeta';
                $claseGris = 'background-gris';  // Clase CSS para gris claro
                $claseAzulClaro = 'background-azul-claro';  // Clase CSS para azul claro
                $claseAzul = 'background-azul';  // Clase CSS para azul
            
                // Verificar tra_status_id para colores especiales
                if ($row->tra_status_id == 23) {
                    $clase = $claseAzulClaro;
                } elseif ($row->tra_status_id == 21) {
                    $clase = $claseGris;
                } elseif ($row->tra_status_id == 20) {
                    $clase = $claseAzul;
                } else {
                    // Determinar si es Local o Foráneo
                    $local = ($row->ent_municipio_id >= 266 && $row->ent_municipio_id <= 281) || 
                             ($row->ent_municipio_id >= 657 && $row->ent_municipio_id <= 781);
                    
                    // Determinar la clase CSS basada en los días de diferencia y si es Local o Foráneo
                    if ($local) {
                        if ($diasDiferencia < 5) {
                            $clase = $claseVerde;
                        } elseif ($diasDiferencia < 8) {
                            $clase = $claseAmarillo;
                        } elseif ($diasDiferencia < 12) {
                            $clase = $claseRojo;
                        } else {
                            $clase = $claseVioleta;
                        }
                    } else {
                        if ($diasDiferencia < 10) {
                            $clase = $claseVerde;
                        } elseif ($diasDiferencia < 13) {
                            $clase = $claseAmarillo;
                        } elseif ($diasDiferencia < 16) {
                            $clase = $claseRojo;
                        } else {
                            $clase = $claseVioleta;
                        }
                    }
                }
                $arrFilter = [20, 21, 23];
                if (!in_array($row->tra_status_id, $arrFilter)) {
                    return '<span class="' . $clase . '">' . $diasDiferencia . ' días</span>';
                }
            
                return '<span class="' . $clase . '"></span>';
            });

            $tramite_crud->fields([
                'folio','contrato','unidad','serie', 
                'placas','tra_tipos_id','ent_municipio_id','cli_directo_id',
                'cli_directo_ejecutivo_id','empresa_gestora_id','gestor_id',
                'tra_status_id','cobro_status_id',
                'observaciones', 'user_id'
            ]); 

            $tramite_crud->displayAs("created_at", "Creación");
            /* SELECT Se configura el tipo de tramite */
            $tramite_crud->setRelation('tra_tipos_id', 'tra_tipos', 'tipo_tramite');
            $tramite_crud->displayAs('tra_tipos_id','Tipo de Tramite');

            /* SELECT Se configura el estatus del tramite */
            $tramite_crud->setRelation('tra_status_id', 'tra_status', 'tra_status');
            $tramite_crud->displayAs('tra_status_id','Estatus del Tramite');

            /* SELECT Se configura el cliente final o cliente directo */
            $tramite_crud->setRelation('cli_directo_id', 'cli_directo', 'razon_social');
            $tramite_crud->displayAs('cli_directo_id','Cliente Directo');
            
            /* SELECT Se configura el ejecutivo del cliente */
            $tramite_crud->setRelation('cli_directo_ejecutivo_id', 'cli_directo_ejecutivo', 'nombre');
            $tramite_crud->displayAs('cli_directo_ejecutivo_id','Ejecutivo del Cliente');

            $tramite_crud->setDependentRelation('cli_directo_ejecutivo_id','cli_directo_id','cli_directo_id');

            /* SELECT Se configura el la entidad */
            $tramite_crud->setRelation('entidad_id', 'entidad', 'entidad');
            $tramite_crud->displayAs('entidad_id','Entidad');

            /* SELECT Se configura el municipio */
            $tramite_crud->setRelation('ent_municipio_id', 'rel_ent_municipio', 'ent_municipality');
            $tramite_crud->displayAs('ent_municipio_id','Municipio');

            /* SELECT Se configura la empresa gestora */
            $tramite_crud->setRelation('empresa_gestora_id', 'ges_empresa_gestora', 'razon_social');
            $tramite_crud->displayAs('empresa_gestora_id','Empresa Gestora');

            /* SELECT Se configura el gestor*/
            $tramite_crud->setRelation('gestor_id', 'ges_gestor', 'nombre');
            $tramite_crud->displayAs('gestor_id','Gestor');

            $tramite_crud->setDependentRelation('gestor_id','empresa_gestora_id','empresa_gestora_id');

            $tramite_salida = $tramite_crud->render();
            
            $salida_total = array_merge((array)$tramite_salida, $data);
            $salida_total['insert_button_url'] = '/public/deskapp/tramites/add';

            echo $this->_example_output($salida_total);

        } catch (\Exception $e) {
            exit($e->getMessage());
        }
    }

    public function cotizaciones()
    {
        try {
            # Manejo de session de action
            $self = $this;
            $session = session();
            $data['session'] = \Config\Services::session();
            $data['username'] = $session->get('user_name');
            $myid = $session->get('id');
            # fin del manejo de session

            $tramite_crud = $this->_getGroceryCrudEnterprise();
            
            $tramite_crud->unsetAdd();
            $tramite_crud->unsetEdit();
            $tramite_crud->unsetRead();
            // $tramite_crud->setTheme('bootstrap-v5');
            $tramite_crud->unsetDeleteMultiple();
            if (has_permission('editar_tramite', esc($session->get('user_permissions')),esc($session->get('user_roles')))){
                $tramite_crud->setActionButton('Editar', 'fas fa-pencil-alt', function ($row) {
                    return '/deskapp/tramites/update_cotizacion/' . $row->id;
                }, false);
            }

            if (!has_permission('delete_tramite', esc($session->get('user_permissions')),esc($session->get('user_roles')))){
                $tramite_crud->unsetDelete();
            }

            if (!has_permission('export_tramite', esc($session->get('user_permissions')),esc($session->get('user_roles')))){
                $tramite_crud->unsetExport();
            }

            if (!has_permission('print_tramite', esc($session->get('user_permissions')),esc($session->get('user_roles')))){
                $tramite_crud->unsetPrint();
            }

            if (!has_permission('clone_tramite', esc($session->get('user_permissions')),esc($session->get('user_roles')))){
                $tramite_crud->unsetClone();
            }

            $tramite_crud->setCsrfTokenName(csrf_token());
            $tramite_crud->setCsrfTokenValue(csrf_hash());

            //lista todos los unset de grocery crud
            
            $tramite_crud->setTable('tramite');
            $tramite_crud->setSubject('tramite', 'Tramites');
            $tramite_crud->defaultOrdering('tramite.id', 'desc');

            $tramite_crud->where([
                 'tramite.tra_status_id IN (29)'
            ]);
            
            $tramite_crud->columns([
                'id', 'created_at', 'tra_status_id', 'folio', 'contrato', 'unidad', 'serie', 
                'placas', 'tra_tipos_id', 'entidad_id', 'ent_municipio_id', 'cli_directo_id',
                'cli_directo_ejecutivo_id', 'empresa_gestora_id', 'gestor_id',
                'cobro_status_id', 'user_id',
                'observaciones'
            ]);

            $tramite_crud->setRelation('user_id', 'users', '{firstname} {midname} {lastname}');
            $tramite_crud->displayAs("user_id", "Ejecutivo");

            $tramite_crud->fields([
                'folio','contrato','unidad','serie', 
                'placas','tra_tipos_id','ent_municipio_id','cli_directo_id',
                'cli_directo_ejecutivo_id','empresa_gestora_id','gestor_id',
                'tra_status_id','cobro_status_id',
                'observaciones', 'user_id'
            ]); 

            $tramite_crud->displayAs("created_at", "Creación");
            /* SELECT Se configura el tipo de tramite */
            $tramite_crud->setRelation('tra_tipos_id', 'tra_tipos', 'tipo_tramite');
            $tramite_crud->displayAs('tra_tipos_id','Tipo de Tramite');

            /* SELECT Se configura el estatus del tramite */
            $tramite_crud->setRelation('tra_status_id', 'tra_status', 'tra_status');
            $tramite_crud->displayAs('tra_status_id','Estatus del Tramite');

            /* SELECT Se configura el cliente final o cliente directo */
            $tramite_crud->setRelation('cli_directo_id', 'cli_directo', 'razon_social');
            $tramite_crud->displayAs('cli_directo_id','Cliente Directo');
            
            /* SELECT Se configura el ejecutivo del cliente */
            $tramite_crud->setRelation('cli_directo_ejecutivo_id', 'cli_directo_ejecutivo', 'nombre');
            $tramite_crud->displayAs('cli_directo_ejecutivo_id','Ejecutivo del Cliente');

            $tramite_crud->setDependentRelation('cli_directo_ejecutivo_id','cli_directo_id','cli_directo_id');

            /* SELECT Se configura el la entidad */
            $tramite_crud->setRelation('entidad_id', 'entidad', 'entidad');
            $tramite_crud->displayAs('entidad_id','Entidad');

            /* SELECT Se configura el municipio */
            $tramite_crud->setRelation('ent_municipio_id', 'rel_ent_municipio', 'ent_municipality');
            $tramite_crud->displayAs('ent_municipio_id','Municipio');

            /* SELECT Se configura la empresa gestora */
            $tramite_crud->setRelation('empresa_gestora_id', 'ges_empresa_gestora', 'razon_social');
            $tramite_crud->displayAs('empresa_gestora_id','Empresa Gestora');

            /* SELECT Se configura el gestor*/
            $tramite_crud->setRelation('gestor_id', 'ges_gestor', 'nombre');
            $tramite_crud->displayAs('gestor_id','Gestor');

            $tramite_crud->setDependentRelation('gestor_id','empresa_gestora_id','empresa_gestora_id');

            $tramite_salida = $tramite_crud->render();
            
            $salida_total = array_merge((array)$tramite_salida, $data);
            $salida_total['insert_button_url'] = '/public/deskapp/tramites/add';

            echo $this->_example_output($salida_total);

        } catch (\Exception $e) {
            exit($e->getMessage());
        }
    }

    private function _example_output($salida = null) {
        $salida = (object)esc($salida, 'raw');
        if ($salida->isJSONResponse) {
            header('Content-Type: application/json; charset=utf-8');
            echo $salida->output;
            exit;
        }
        return view('/deskapp/extra-pages/grocery_page', (array)$salida);
    }

    private function _simple_output($salida = null) {
        $salida = (object)esc($salida, 'raw');
        if ($salida->isJSONResponse) {
            header('Content-Type: application/json; charset=utf-8');
            echo $salida->output;
            exit;
        }
    }

    // Function to handle adding a new product
    public function add() {
        $session = session();
        $data['session'] = \Config\Services::session();
        $data['username'] = $session->get('user_name');
        $myid = $session->get('id');
        $db2 = $this->_getDbData();

        $TraTiposModel = new TraTiposModel($db2);
        $tra_tipos_options = $TraTiposModel->getTraTiposOptions();
        // $entMunicipios = new EntMunicipioModel($db2);
        // $ent_municipio_options = $entMunicipios->getEntMunicipios();

        $entidades = new EntidadesModel($db2);
        $entidad_options = $entidades->getEntidades();

        $clienteDirecto = new ClienteDirectoModel($db2);
        $cli_directo_options = $clienteDirecto->getClientesDirectosOptions();
        $empGestora = new EmpresaGestoraModel($db2);
        $empresa_gestora_options = $empGestora->getEmpresasGestorasOptions();

        // $tra_status_options = array_slice($tra_status_options, 0, 1, true); // Se deja unicamente la opción en proceso
        $cobroStatuses = new CobroStatusesModel($db2);
        $cobro_status_options = $cobroStatuses->getCobroStatusesOptions();

        $gestor_options = [];
        $output = new \stdClass();
        
        // Fields to be displayed in the add form
        $output->fields = [
            "folio" => ["label" => "Folio", "type" => "hidden", "readonly"=>"readonly"],
            "contrato" => ["label" => "Contrato", "type" => "text", "required"=>"required"],
            "unidad" => ["label" => "Unidad", "type" => "text"],
            "serie" => ["label" => "Serie", "type" => "text", "required"=>"required"],
            "placas" => ["label" => "Placas", "type" => "text"],
            "tra_tipos_id" => ["label" => "Tipo de Trámite", "type" => "select", "options" => $tra_tipos_options, "required"=>"required"], // Asumiendo que tienes un array $tra_tipos_options
            "cli_directo_id" => ["label" => "Cliente", "type" => "select", "options" => $cli_directo_options, "required"=>"required"], // Asumiendo que tienes un array $cli_directo_options
            "cli_directo_ejecutivo_id" => ["label" => "Ejecutivo de Cliente", "type" => "select", "options" => [], "required"=>"required"], // Asumiendo que tienes un array $cli_directo_ejecutivo_options
            "entidad_id" => ["label" => "Entidad", "type" => "select", "options" => $entidad_options, "required"=>"required"],
            "observaciones" => ["label" => "Observaciones", "type" => "textarea"],
            "user_id" => ["label" => "User Id", "type" => "hidden", "value" => "$myid"]
        ];
        $output->gestor_campos = [];
        $output->derechos_campos = [];
        $output->bancario_campos = [];
        $output->final_campos = [];

        $crud = $this->_getGroceryCrudEnterprise();
        $crudOutput = $crud->render();

        $output->css_files = $crudOutput->css_files;
        $output->js_files = $crudOutput->js_files;
        // Load the add form view
        $output = array_merge((array)$output, $data);

        return $this->_example_output_2($output, 'add');
    }

    public function getEjecutivosByClienteId($clienteDirectoId) {
        $db2 = $this->_getDbData();
        $ejecutivoModel = new ClienteDirectoEjecutivoModel($db2);
        $options = $ejecutivoModel->getEjecutivosOptions($clienteDirectoId);
        
        return $this->response->setJSON($options);
    }

    // Function to handle inserting a new product into the database
    public function insert() {
        $validation = \Config\Services::validation();

        // Set validation rules
        $validation->setRules([
            "contrato" => "required",
            // Añade más reglas de validación según sea necesario
        ]);
    
        if ($validation->withRequest($this->request)->run() === FALSE) {
            // Si la solicitud es AJAX, devuelve los errores en formato JSON
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'errors' => $validation->getErrors()
                ]);
            } else {
                // Si no es una solicitud AJAX, carga la vista del formulario nuevamente con los errores de validación
                return $this->add();
            }
        } else {
            try {
                // Insertar los datos en la base de datos
                $data = $this->request->getPost();
                $db = \Config\Database::connect();
                $builder = $db->table('tramite');
                $button_action = $this->request->getPost('accion');
                
                $clienteModel = new ClienteModel($this->_getDbData());
                $newFolio = $clienteModel->getPrefijoConUltimosSeisDigitos($data["cli_directo_id"]);
                $data["folio"] = $newFolio;
                
                if($button_action == 'quotation'){
                    $data["tra_status_id"] = 29;
                    $data["quoted_at"] = date('Y-m-d H:i:s');
                } else { // tramite
                    $data["tra_status_id"] = 11;
                }

                unset($data["accion"]);
                $builder->insert($data);
                // Get the last insert ID
                $lastInsertID = $db->insertID();

                # Insertar relación en tra_tramite_asociado
                $traTramiteAsociadoModel = new TraTramiteAsociadoModel();
                $tra_tipos_id = $data["tra_tipos_id"];
                $insert_tramite_asociado = [
                "tramite_id" => (int)$lastInsertID,
                "tra_tipos_id" => (int)$tra_tipos_id,
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s')
                ];
                $traTramiteAsociadoModel->insert($insert_tramite_asociado, 'tra_tramite_asociado');

                #Espacio para guardar la relación DosStatus 

                // $db = Database::connect();
                $db2 = $this->_getDbData();
                $tra_tipos_id = $data["tra_tipos_id"];
                $condition = ['tra_tipos_id' => $tra_tipos_id];
                $query = $db->table('tra_tipo_documentos')->where($condition)->get();
                $resultados = $query->getResultArray();

                $session = session();
                $myid = $session->get('id');
                
                $traDocStatusModel = new TraDocStatusModel($db2);

                foreach ($resultados as $elemento) {
                    // Inserta cada elemento en la tabla tra_doc_status
                    $insert_data = [
                        "id"=>null,
                        "folio_tramite" => $newFolio,
                        "tramite_id" => (int)$lastInsertID,
                        "documento_id" => (int)$elemento['documento_id'],
                        "status_documento_id" => 11,
                        "file" => null,
                        "comentario" => null,
                        "user_id" => (int)$myid
                    ];
                    // Inserta los datos en la base de datos utilizando el modelo apropiado (ejemplo: usando CodeIgniter Model)

                    $result = $traDocStatusModel->insert($insert_data, 'tra_doc_status');
                }

                $bitacoraModel = new BitacoraModel($db2);
                $data_bitacora = $data;
                $diferencias = $this->encontrarDiferencias($data_bitacora, []);
                $insert_bitacora = [
                    "id"=>null,
                    "tipo"=>"insert",
                    "origen"=>"tramite",
                    "folio_tramite" => $newFolio,
                    "tramite_id" => (int)$lastInsertID,
                    "cambios" => json_encode($diferencias),
                    "user_id" => (int)$myid
                ];
                $result = $bitacoraModel->insert($insert_bitacora, 'bitacora');

                $tra_user_log = new TraUserLogModel($db2);
                $log = [
                    "tramite_id"    => $lastInsertID,
                    "user_id"       => $myid,
                    "tra_status_id" => 11
                ];
                $tra_user_log->insert($log, 'tra_user_log');

                // Si la solicitud es AJAX, devuelve una respuesta JSON indicando éxito
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'from' => 'insert',
                        'success' => true,
                        'redirect' => '/deskapp/tramites/update/'.$lastInsertID
                    ]);
                } else {
                    // Si no es una solicitud AJAX, redirige a la página de lista
                    return redirect()->to('/deskapp/tramites/update/'.$lastInsertID);
                }
            } catch (\Exception $e) {
                // Manejo de excepciones de la base de datos
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Ocurrió un error al guardar el trámite: ' . $e->getMessage()
                    ]);
                } else {
                    // Si no es una solicitud AJAX, muestra el error de alguna otra forma
                    return redirect()->back()->withInput()->with('error', 'Ocurrió un error al guardar el trámite: ' . $e->getMessage());
                }
            }
        }
    }

    public function update($id) {
        $session = session();
        $data['session'] = \Config\Services::session();
        $data['username'] = $session->get('user_name');
        $myid = $session->get('id');
        $db = \Config\Database::connect();
        $builder = $db->table('tramite');
        $db2 = $this->_getDbData();


        // 🔹 1️⃣ Verificar si el trámite tiene relación en `tra_tramite_asociado`
        $tramiteAsociadoModel = new TraTramiteAsociadoModel($db2);
        $asociadoExists = $tramiteAsociadoModel->where('tramite_id', $id)->countAllResults();

        if ($asociadoExists == 0) {
            // 🔹 2️⃣ Obtener `tra_tipos_id` del trámite
            $tramite = $builder->getWhere(['id' => $id])->getRowArray();

            if (!empty($tramite['tra_tipos_id'])) {
                // 🔹 3️⃣ Crear la relación en `tra_tramite_asociado`
                $tramiteAsociadoModel->saveService($id, $tramite['tra_tipos_id']);
            }
        }

        // Retrieve the record
        $tramite = $builder->getWhere(['id' => $id])->getRowArray();

        $entidades = new EntidadesModel($db2);
        $entidad_options = $entidades->getEntidades();
        $clienteDirecto = new ClienteDirectoModel($db2);
        $cli_directo_options = $clienteDirecto->getClientesDirectosOptions();
        $empGestora = new EmpresaGestoraModel($db2);
        $empresa_gestora_options = $empGestora->getEmpresasGestorasOptions();

        $traStatus = new TraStatusModel($db2);
        $tra_status_obj = $traStatus->getTraStatusOptions();
        $tra_status_options = $tra_status_obj["tra_status"];
        $tra_status_steps = $tra_status_obj["steps"];

        $reembolso_status = new ReembolsoStatusModel($db2);
        $reembolso_status_options = $reembolso_status->getReembolsoStatusOptions();

        $cobro_status = new CobroStatusModel($db2);
        $cobro_status_options = $cobro_status->getCobroStatusOptions();

        $pago_derechos = new PagoDerechosModel($db2);
        $pago_derechos_db = $pago_derechos->getImgDerechosByTramiteId($id);

        $pago_gestor_st = new PagoGestorStatusModel($db2);
        $pago_gestor_st_opciones = $pago_gestor_st->getPagoGestorStatusOptions();

        $form = new \stdClass();
        
        // Fields to be displayed in the add form

        $form->fields = [
            "folio" => array_merge(["label" => "Folio", "type" => "hidden", "value" => $tramite['folio']]),
            "contrato" => array_merge(["label" => "Contrato", "type" => "text", "value" => $tramite['contrato'], "required" => "required"]),
            "unidad" => array_merge(["label" => "Unidad", "type" => "text", "value" => $tramite['unidad']]),
            "serie" => array_merge(["label" => "Serie", "type" => "text", "value" => $tramite['serie']]),
            "placas" => array_merge(["label" => "Placas", "type" => "text", "value" => $tramite['placas']]),
            "cli_directo_id" => array_merge(["label" => "Cliente", "type" => "select", "options" => $cli_directo_options, "value" => $tramite['cli_directo_id']]),
            "cli_directo_ejecutivo_id" => array_merge(["label" => "Ejecutivo de Cliente", "type" => "select", "options" => [], "value" => $tramite['cli_directo_ejecutivo_id']]),
            "entidad_id" => array_merge(["label" => "Entidad", "type" => "select", "options" => $entidad_options, "value" => $tramite['entidad_id'], "required"=>"required"]),
            "observaciones" => array_merge(["label" => "Observaciones", "type" => "textarea", "value" => $tramite['observaciones']])
        ];
        

        $form->gestor_campos = [
            "empresa_gestora_id" => ["label" => "Empresa Gestora", "type" => "select", "options" => $empresa_gestora_options, "value" => $tramite['empresa_gestora_id'], "required" => "required"],
            "gestor_id" => ["label" => "Gestor", "type" => "select", "options" => [], "value" => $tramite['gestor_id'], "required" => "required"]
        ];

        
        $form->derechos_campos = [
            "derechos_tramite" => ["label" => "Monto pago de derechos", "type" => "number", "value" => $tramite['derechos_tramite'], "required" => "required"],
            "derechos_pago_sitio" => ["label" => "Pago", "type" => "select", "options" => ["online"=>"En Linea", "ventanilla"=>"En Ventanilla"], "value" => $tramite['derechos_pago_sitio']],
            "derechos_vigencia" => ["label" => "Fecha Vigencia", "type" => "datetime", "value" => $tramite['derechos_vigencia']],
            "separador_1" => ["type" => "hr"],
            "separador_2" => ["type" => "hr"],
            "titulo_seccion" => ["type" => "divider", "id" => "seccion_datos_generales"],
            "derechos_revol_cliente" => ["label" => "Forma de Pago", "type" => "select", "options" => ["revolvente"=>"Fondo Revolvente", "cliente"=>"Pago Cliente"], "value" => $tramite['derechos_revol_cliente'], "required" => "required"],
            "derechos_refer_banc" => ["label" => "Referencia Bancaria", "type" => "text", "value" => $tramite['derechos_refer_banc'], "required" => "required"],
        ];
        
        $form->bancario_campos = [
            // "derechos_revol_cliente" => ["label" => "Forma de Pago", "type" => "select", "options" => ["revolvente"=>"Fondo Revolvente", "cliente"=>"Pago Cliente"], "value" => $tramite['derechos_revol_cliente'], "required" => "required"],
            // "derechos_refer_banc" => ["label" => "Referencia Bancaria", "type" => "text", "value" => $tramite['derechos_refer_banc'], "required" => "required"],
        ];

        $gestor_model = new GestorModel($db2);
        $gestor_nombre = $gestor_model->getGestorNameById($tramite['gestor_id']);

        $form->pago_gestor = [
            // nombre del gestor
            "gestor_id" => ["label" => "Gestor", "type" => "text", "value" => $gestor_nombre, "disabled"=>"disabled"],
            "separador_gestor" => ["type" => "hr"],
            "costo_tramite" => ["label" => "Costos de los Trámites", "type" => "number", "value" => $tramite['costo_tramite']],
            "deposito_gestor" => ["label" => "Deposito a Gestor", "type" => "number", "value" => $tramite['deposito_gestor'], "required" => "required"],
            "col_a_favor" => ["label" => "Saldo a Favor SGL", "type" => "number", "value" => $tramite['col_a_favor'], "required" => "required"], 
            "col_a_favor_gestor" => ["label" => "Saldo a Favor del Gestor", "type" => "number", "value" => $tramite['col_a_favor_gestor'], "required" => "required"],
            "separador_gestor2" => ["type" => "hr"],
            "num_factura_gestor" => ["label" => "Número de Factura", "type" => "text", "value" => $tramite['num_factura_gestor']],    
            "impuesto_gestoria" => ["label" => "Honorarios de Gestoría", "type" => "number", "value" => $tramite['impuesto_gestoria'], "required" => "required"],
            "impuesto_gestoria_hidden" => ["label" => "", "type" => "hidden", "value" => $tramite['impuesto_gestoria']],
            "gestoria_comision" => ["label" => "Gratificación", "type" => "number", "value" => $tramite['gestoria_comision']],
            "gestoria_comision_hidden" => ["label" => "", "type" => "hidden", "value" => $tramite['gestoria_comision']],
            "costo_paqueteria" => ["label" => "Costo de Paquetería", "type" => "number", "value" => $tramite['costo_paqueteria']],
            "gestor_total_pago" => ["label" => "Gasto Total", "type" => "number", "value" => $tramite['gestor_total_pago'], "disabled"=>"disabled"],
            "gestor_total_pago_hidden" => ["label" => "Pago Total", "type" => "hidden", "value" => $tramite['gestor_total_pago']],
            "reembolso_status_id" => ["label" => "Estatus del Reembolso", "type" => "select", "options" => $reembolso_status_options, "value" => $tramite['reembolso_status_id']],
            "reembolso_status_id_hidden" => ["label" => "Estatus del Reembolso", "type" => "hidden", "options" => $reembolso_status_options, "value" => $tramite['reembolso_status_id']]
        ];

        $form->final_campos = [
            "id_give_cliente" => ["label" => "ID que da el cliente", "type" => "text", "value" => $tramite['id_give_cliente'], "required" => "required"],
            "separador_costos" => ["type" => "hr"],
            "numero_factura" => ["label" => "Número de Factura", "type" => "text", "value" => $tramite['numero_factura'], "required" => "required"],
            "numero_refactura" => ["label" => "Número de Refactura", "type" => "text", "value" => $tramite['numero_refactura']],
            "cobro_status_id" => ["label" => "Estatus del Cobro", "type" => "select", "options" => $cobro_status_options, "value" => $tramite['cobro_status_id']],
            "separador_costos2" => ["type" => "hr"],
            "costo_gestoria" => ["label" => "Sumatoria de Derechos", "type" => "number", "value" => $tramite['costo_gestoria'], "disabled"=>"disabled"],
            "costo_gestoria_hidden" => ["label" => "Sumatoria de Derechos", "type" => "hidden", "value" => $tramite['costo_gestoria']],
            "costo_pago_cliente"=> ["label" => "Honorarios del Trámite", "type" => "number", "value" => $tramite['costo_pago_cliente'], "required" => "required"],
            "comision_derechos" => ["label" => "Comisión de Derechos", "type" => "number", "value" => $tramite['comision_derechos'], "required" => "required"],
            "iva" => ["label" => "IVA ($)", "type" => "number", "value" => $tramite['iva']],
            "costo_total" => ["label" => "Costo Total", "type" => "number", "value" => $tramite['costo_total'], "disabled"=>"disabled"],
        ];
        
        $data['id'] = $id;
        $data['folio'] = $tramite[ 'folio'];
        $data['tra_status'] = $tra_status_options[$tramite['tra_status_id']];
        $data['tra_status_id'] = $tramite['tra_status_id'];
        $data['created_at'] = $tramite['created_at'];

        $data['step'] = $tra_status_steps[$tramite['tra_status_id']];
        $data['started_at'] = $tramite['started_at'];
        $data['derechos_comprobante'] = $tramite['derechos_comprobante'];
        $data['reembolso_status_id'] = $tramite['reembolso_status_id'];
        $data['cobro_status_id'] = $tramite['cobro_status_id'];

        $form->id = $id;

        $crud = $this->_getGroceryCrudEnterprise();
        $crudOutput = $crud->render();
        
        $form->css_files = $crudOutput->css_files;
        $form->js_files = $crudOutput->js_files;
        
        // Load the view with the fields and current data
        // if (!is_read_only(esc($session->get('user_roles')))){
            $cruddocstatus = $this->_getGroceryCrudEnterprise();
            $cruddocstatus->setApiUrlPath('/deskapp/tramites/single_documentostatus/'.$id);
            $output_docs = $cruddocstatus->render();            
            
            $crudevidencias = $this->_getGroceryCrudEnterprise();
            $crudevidencias->setApiUrlPath('/deskapp/tramites/single_evidencias/'.$id);
            $outputevidencias = $crudevidencias->render();

            $crud_derechos = $this->_getGroceryCrudEnterprise();
            $crud_derechos->setApiUrlPath('/deskapp/tramites/single_pago_derechos/' . $id);
            $output_derechos = $crud_derechos->render();

            $crud_pago_gestor = $this->_getGroceryCrudEnterprise();
            if(puede_editar_modulo($session->get('user_roles'), $tramite['tra_status_id'], 'evidencias_finales_gestor', $tramite['reembolso_status_id'], $tramite['cobro_status_id'], $tramite['tra_status_id'])){
                $crud_pago_gestor->setApiUrlPath('/deskapp/tramites/single_pago_gestor/' . $id);
            } else {
                $crud_pago_gestor->setApiUrlPath('/deskapp/concluido/single_pago_gestor/' . $id);
            
            }
            $output_pago_gestor = $crud_pago_gestor->render();

            $crud_cobro_cliente = $this->_getGroceryCrudEnterprise();
            if(puede_editar_modulo($session->get('user_roles'), $tramite['tra_status_id'], 'evidencias_finales_cliente', $tramite['reembolso_status_id'], $tramite['cobro_status_id'], $tramite['tra_status_id'])){
                $crud_cobro_cliente->setApiUrlPath('/deskapp/tramites/single_cobro_cliente/' . $id);
            } else {
                $crud_cobro_cliente->setApiUrlPath('/deskapp/concluido/single_cobro_cliente/' . $id);
            }
            $output_cobro_cliente = $crud_cobro_cliente->render();

            $crudevidencias_finales = $this->_getGroceryCrudEnterprise();
            if(puede_editar_modulo($session->get('user_roles'), $tramite['tra_status_id'], 'evidencias_finales_cliente', $tramite['reembolso_status_id'], $tramite['cobro_status_id'], $tramite['tra_status_id'])){
                $crudevidencias_finales->setApiUrlPath('/deskapp/tramites/single_evidencias_finales/' . $id);
            } else {
                $crudevidencias_finales->setApiUrlPath('/deskapp/concluido/single_evidencias_finales/' . $id);
            }
            $outputevidencias_finales = $crudevidencias_finales->render();
            
            $form->output_docs = $output_docs->output;
            $form->output_bitacora = $outputevidencias->output;
            $form->outputevidencias_finales = $outputevidencias_finales->output;
            $form->output_derechos = $output_derechos->output;
            $form->output_pago_gestor = $output_pago_gestor->output;
            $form->output_cobro_cliente = $output_cobro_cliente->output;
            
            // $form->output = $output_docs->output;
        // }

        $form = array_merge((array)$form, $data);
        return $this->_example_output_2($form, 'update');
    }

    public function update_cotizacion($id) {
        $session = session();
        $data['session'] = \Config\Services::session();
        $data['username'] = $session->get('user_name');
        $myid = $session->get('id');
        $db = \Config\Database::connect();
        $builder = $db->table('tramite');
        $db2 = $this->_getDbData();
        // Retrieve the record
        $tramite = $builder->getWhere(['id' => $id])->getRowArray();

        $TraTiposModel = new TraTiposModel($db2);
        $tra_tipos_options = $TraTiposModel->getTraTiposOptions();

        // $entMunicipios = new EntMunicipioModel($db2);
        // $ent_municipio_options = $entMunicipios->getEntMunicipios();

        $entidades = new EntidadesModel($db2);
        $entidad_options = $entidades->getEntidades();
        $clienteDirecto = new ClienteDirectoModel($db2);
        $cli_directo_options = $clienteDirecto->getClientesDirectosOptions();
        $empGestora = new EmpresaGestoraModel($db2);
        $empresa_gestora_options = $empGestora->getEmpresasGestorasOptions();

        $traStatus = new TraStatusModel($db2);
        $tra_status_obj = $traStatus->getTraStatusOptions();
        $tra_status_options = $tra_status_obj["tra_status"];
        $tra_status_steps = $tra_status_obj["steps"];

        $reembolso_status = new ReembolsoStatusModel($db2);
        $reembolso_status_options = $reembolso_status->getReembolsoStatusOptions();

        $cobro_status = new CobroStatusModel($db2);
        $cobro_status_options = $cobro_status->getCobroStatusOptions();

        $pago_derechos = new PagoDerechosModel($db2);
        $pago_derechos_db = $pago_derechos->getImgDerechosByTramiteId($id);

        $pago_gestor_st = new PagoGestorStatusModel($db2);
        $pago_gestor_st_opciones = $pago_gestor_st->getPagoGestorStatusOptions();

        // $cobroStatuses = new CobroStatusesModel($db2);
        // $cobro_status_options = $cobroStatuses->getCobroStatusesOptions();
        $form = new \stdClass();
        
        // Fields to be displayed in the add form

        $puede_modificar = ["disabled"=>"disabled"];
        if(is_admin($session->get('user_roles'))){
            $puede_modificar = [];
        }
        $form->fields = [
            "folio" => array_merge(["label" => "Folio", "type" => "hidden", "value" => $tramite['folio']], $puede_modificar),
            "contrato" => array_merge(["label" => "Contrato", "type" => "text", "value" => $tramite['contrato'], "required" => "required"], $puede_modificar),
            "unidad" => array_merge(["label" => "Unidad", "type" => "text", "value" => $tramite['unidad']], $puede_modificar),
            "serie" => array_merge(["label" => "Serie", "type" => "text", "value" => $tramite['serie']], $puede_modificar),
            "placas" => array_merge(["label" => "Placas", "type" => "text", "value" => $tramite['placas']], $puede_modificar),
            "tra_tipos_id" => array_merge(["label" => "Tipo de Trámite", "type" => "select", "options" => $tra_tipos_options, "value" => $tramite['tra_tipos_id']], $puede_modificar),
            "cli_directo_id" => array_merge(["label" => "Cliente", "type" => "select", "options" => $cli_directo_options, "value" => $tramite['cli_directo_id']], $puede_modificar),
            "cli_directo_ejecutivo_id" => array_merge(["label" => "Ejecutivo de Cliente", "type" => "select", "options" => [], "value" => $tramite['cli_directo_ejecutivo_id']], $puede_modificar),
            "entidad_id" => array_merge(["label" => "Entidad", "type" => "select", "options" => $entidad_options, "value" => $tramite['entidad_id'], "required"=>"required"], $puede_modificar),
            "observaciones" => array_merge(["label" => "Observaciones", "type" => "textarea", "value" => $tramite['observaciones']], $puede_modificar)
        ];
        

        $form->gestor_campos = [
            "empresa_gestora_id" => ["label" => "Empresa Gestora", "type" => "select", "options" => $empresa_gestora_options, "value" => $tramite['empresa_gestora_id'], "required" => "required"],
            "gestor_id" => ["label" => "Gestor", "type" => "select", "options" => [], "value" => $tramite['gestor_id'], "required" => "required"]
        ];
        // }
        
        $form->derechos_campos = [
            "derechos_tramite" => ["label" => "Monto pago de derechos", "type" => "number", "value" => $tramite['derechos_tramite'], "required" => "required"],
            "derechos_pago_sitio" => ["label" => "Pago", "type" => "select", "options" => ["online"=>"En Linea", "ventanilla"=>"En Ventanilla"], "value" => $tramite['derechos_pago_sitio']],
            "derechos_vigencia" => ["label" => "Fecha Vigencia", "type" => "datetime", "value" => $tramite['derechos_vigencia']]
        ];
        
        $form->bancario_campos = [
            "derechos_revol_cliente" => ["label" => "Forma de Pago", "type" => "select", "options" => ["revolvente"=>"Fondo Revolvente", "cliente"=>"Pago Cliente"], "value" => $tramite['derechos_revol_cliente'], "required" => "required"],
            "derechos_refer_banc" => ["label" => "Referencia Bancaria", "type" => "text", "value" => $tramite['derechos_refer_banc'], "required" => "required"],
        ];

        $gestor_model = new GestorModel($db2);
        $gestor_nombre = $gestor_model->getGestorNameById($tramite['gestor_id']);

        $form->pago_gestor = [
            // nombre del gestor
            "gestor_id" => ["label" => "Gestor", "type" => "text", "value" => $gestor_nombre, "disabled"=>"disabled"],
            "costo_tramite" => ["label" => "Costo del Trámite", "type" => "number", "value" => $tramite['costo_tramite'], "required" => "required"],
            "deposito_gestor" => ["label" => "Deposito a Gestor", "type" => "number", "value" => $tramite['deposito_gestor'], "required" => "required"],
            "col_a_favor" => ["label" => "Saldo Pendiente", "type" => "number", "value" => $tramite['col_a_favor'], "required" => "required"], 
            "num_factura_gestor" => ["label" => "Número de Factura", "type" => "text", "value" => $tramite['num_factura_gestor']],    
            "pago_gestor_st_id" => ["label" => "Estatus del Pago", "type" => "select", "options" => $pago_gestor_st_opciones, "value" => $tramite['pago_gestor_st_id']],
            "impuesto_gestoria" => ["label" => "Honorarios de Gestoría", "type" => "number", "value" => $tramite['impuesto_gestoria'], "required" => "required"],
            "gestoria_comision" => ["label" => "Gratificación", "type" => "number", "value" => $tramite['gestoria_comision'], "required" => "required"],
            "gestor_total_pago" => ["label" => "Pago Total", "type" => "number", "value" => $tramite['gestor_total_pago'], "required" => "required"],
            "reembolso_status_id" => ["label" => "Estatus del Reembolso", "type" => "select", "options" => $reembolso_status_options, "value" => $tramite['reembolso_status_id']]
        ];

        $form->final_campos = [
            "id_give_cliente" => ["label" => "ID del cliente", "type" => "text", "value" => $tramite['id_give_cliente'], "required" => "required"],
            "numero_factura" => ["label" => "Número de Factura", "type" => "text", "value" => $tramite['numero_factura'], "required" => "required"],
            "numero_refactura" => ["label" => "Número de Refactura", "type" => "text", "value" => $tramite['numero_refactura']],
            "cobro_status_id" => ["label" => "Estatus del Cobro", "type" => "select", "options" => $cobro_status_options, "value" => $tramite['cobro_status_id']],
            "costo_gestoria" => ["label" => "Costo de Gestoría", "type" => "number", "value" => $tramite['costo_gestoria'], "required" => "required"],
            "costo_pago_cliente"=> ["label" => "Honorarios del Trámite", "type" => "number", "value" => $tramite['costo_pago_cliente'], "required" => "required"],
            "comision_derechos" => ["label" => "Comisión de Derechos", "type" => "number", "value" => $tramite['comision_derechos'], "required" => "required"],
            "costo_total" => ["label" => "Costo Total", "type" => "number", "value" => $tramite['costo_total'], "disabled"=>"disabled"],
        ];
        
        $data['id'] = $id;
        $data['folio'] = $tramite[      'folio'];
        $data['tra_tipo'] = $tra_tipos_options[$tramite['tra_tipos_id']];
        $data['tra_status'] = $tra_status_options[$tramite['tra_status_id']];
        $data['tra_status_id'] = $tramite['tra_status_id'];
        $data['created_at'] = $tramite['created_at'];

        $data['step'] = $tra_status_steps[$tramite['tra_status_id']];
        $data['started_at'] = $tramite['started_at'];
        $data['derechos_comprobante'] = $tramite['derechos_comprobante'];
        // $data['images_derechos_comprobante'] = $images_derechos;
        // $data['images_pago_gestor'] = $images_gestor;
        // $data['images_cobro_cliente'] = $images_cobro_cliente;
        $form->id = $id;

        $crud = $this->_getGroceryCrudEnterprise();
        $crudOutput = $crud->render();
        
        $form->css_files = $crudOutput->css_files;
        $form->js_files = $crudOutput->js_files;
        
        // Load the view with the fields and current data
        // if (!is_read_only(esc($session->get('user_roles')))){
            $cruddocstatus = $this->_getGroceryCrudEnterprise();
            $cruddocstatus->setApiUrlPath('/deskapp/tramites/single_documentostatus/'.$id);
            $output_docs = $cruddocstatus->render();            
            
            $crudevidencias = $this->_getGroceryCrudEnterprise();
            $crudevidencias->setApiUrlPath('/deskapp/tramites/single_evidencias/'.$id);
            $outputevidencias = $crudevidencias->render();

            $crudevidencias_finales = $this->_getGroceryCrudEnterprise();
            $crudevidencias_finales->setApiUrlPath('/deskapp/tramites/single_evidencias_finales/' . $id);
            $outputevidencias_finales = $crudevidencias_finales->render();

            $crud_derechos = $this->_getGroceryCrudEnterprise();
            $crud_derechos->setApiUrlPath('/deskapp/tramites/single_pago_derechos/' . $id);
            $output_derechos = $crud_derechos->render();

            $crud_pago_gestor = $this->_getGroceryCrudEnterprise();
            $crud_pago_gestor->setApiUrlPath('/deskapp/tramites/single_pago_gestor/' . $id);
            $output_pago_gestor = $crud_pago_gestor->render();

            $crud_cobro_cliente = $this->_getGroceryCrudEnterprise();
            $crud_cobro_cliente->setApiUrlPath('/deskapp/tramites/single_cobro_cliente/' . $id);
            $output_cobro_cliente = $crud_cobro_cliente->render();

            // $output_docs->output .= "<hr>".$outputevidencias->output;
            // $form->output_docs = $output->output;
            
            $form->output_docs = $output_docs->output;
            $form->output_bitacora = $outputevidencias->output;
            $form->outputevidencias_finales = $outputevidencias_finales->output;
            $form->output_derechos = $output_derechos->output;
            $form->output_pago_gestor = $output_pago_gestor->output;
            $form->output_cobro_cliente = $output_cobro_cliente->output;
            
            // $form->output = $output_docs->output;
        // }

        $form = array_merge((array)$form, $data);
        return $this->_example_output_2($form, 'cotizacion');
    }

    public function getPagoGestorFiles($id)
    {
        $db = \Config\Database::connect(); // Conexión a la base de datos
        $result = [];
        $ds = DIRECTORY_SEPARATOR;

        // Ruta de la carpeta donde se almacenan los archivos
        $storeFolderSpecific = 'assets/uploads/pago_gestor/' . $id . $ds;

        // Consulta a la base de datos
        $cobro_cliente_db = $db->table('tra_pago_gestor')
                                ->select('id, file')
                                ->where('tramite_id', $id)
                                ->get()
                                ->getResultObject();

        // Función para asignar íconos personalizados según el tipo de archivo
        $assignIcon = function ($extension, $filePath) {
            $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];

            // Si el archivo es una imagen, devolver la ruta del archivo
            if (in_array(strtolower($extension), $imageExtensions)) {
                return base_url($filePath);
            }

            // Si no es una imagen, usar un ícono específico por extensión
            $icons = [
                'xml'  => '/public/assets/src/images/xml-icon.png',
                'pdf'  => '/public/assets/src/images/pdf-icon.png',
                'doc'  => '/public/assets/src/images/doc-icon.png',
                'docx' => '/public/assets/src/images/docx-icon.png',
                'xls'  => '/public/assets/src/images/xls-icon.png',
                'xlsx' => '/public/assets/src/images/xlsx-icon.png',
                'txt'  => '/public/assets/src/images/txt-icon.png',
                'zip'  => '/public/assets/src/images/zip-icon.png',
                'rar'  => '/public/assets/src/images/rar-icon.png',
            ];

            return $icons[strtolower($extension)] ?? '/public/assets/src/images/file-icon.png';
        };

        // Validar archivos de la base de datos
        foreach ($cobro_cliente_db as $dbFile) {
            $filePath = $storeFolderSpecific . $dbFile->file;
            $absoluteFilePath = FCPATH . $filePath;

            // Verificar que el archivo exista físicamente
            if (file_exists($absoluteFilePath)) {
                $extension = strtolower(pathinfo($dbFile->file, PATHINFO_EXTENSION));
                $obj['id'] = $dbFile->id; // ID del archivo en la base de datos
                $obj['name'] = $dbFile->file; // Nombre del archivo
                $obj['size'] = filesize($absoluteFilePath); // Tamaño del archivo físico
                $obj['existing_path'] = base_url($filePath); // Ruta para la vista
                $obj['icon'] = $assignIcon($extension, $filePath); // Ícono o imagen real según el tipo de archivo
                $result[] = $obj;
            }
        }

        // Devolver los resultados en formato JSON
        return $this->response->setJSON($result);
    }
    public function getPagoDerechosFiles($id)
    {
        $db = \Config\Database::connect(); // Conexión a la base de datos
        $result = [];
        $ds = DIRECTORY_SEPARATOR;

        // Ruta de la carpeta donde se almacenan los archivos
        $storeFolderSpecific = 'assets/uploads/pago_derechos/' . $id . $ds;

        // Consulta a la base de datos
        $cobro_cliente_db = $db->table('tra_pago_derechos')
                                ->select('id, file')
                                ->where('tramite_id', $id)
                                ->get()
                                ->getResultObject();

        // Función para asignar íconos personalizados según el tipo de archivo
        $assignIcon = function ($extension, $filePath) {
            $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];

            // Si el archivo es una imagen, devolver la ruta del archivo
            if (in_array(strtolower($extension), $imageExtensions)) {
                return base_url($filePath);
            }

            // Si no es una imagen, usar un ícono específico por extensión
            $icons = [
                'xml'  => '/public/assets/src/images/xml-icon.png',
                'pdf'  => '/public/assets/src/images/pdf-icon.png',
                'doc'  => '/public/assets/src/images/doc-icon.png',
                'docx' => '/public/assets/src/images/docx-icon.png',
                'xls'  => '/public/assets/src/images/xls-icon.png',
                'xlsx' => '/public/assets/src/images/xlsx-icon.png',
                'txt'  => '/public/assets/src/images/txt-icon.png',
                'zip'  => '/public/assets/src/images/zip-icon.png',
                'rar'  => '/public/assets/src/images/rar-icon.png',
            ];

            return $icons[strtolower($extension)] ?? '/public/assets/src/images/file-icon.png';
        };

        // Validar archivos de la base de datos
        foreach ($cobro_cliente_db as $dbFile) {
            $filePath = $storeFolderSpecific . $dbFile->file;
            $absoluteFilePath = FCPATH . $filePath;

            // Verificar que el archivo exista físicamente
            if (file_exists($absoluteFilePath)) {
                $extension = strtolower(pathinfo($dbFile->file, PATHINFO_EXTENSION));
                $obj['id'] = $dbFile->id; // ID del archivo en la base de datos
                $obj['name'] = $dbFile->file; // Nombre del archivo
                $obj['size'] = filesize($absoluteFilePath); // Tamaño del archivo físico
                $obj['existing_path'] = base_url($filePath); // Ruta para la vista
                $obj['icon'] = $assignIcon($extension, $filePath); // Ícono o imagen real según el tipo de archivo
                $result[] = $obj;
            }
        }

        // Devolver los resultados en formato JSON
        return $this->response->setJSON($result);
    }

    public function getCobroClienteFiles($id)
    {
        $db = \Config\Database::connect(); // Conexión a la base de datos
        $result = [];
        $ds = DIRECTORY_SEPARATOR;

        // Ruta de la carpeta donde se almacenan los archivos
        $storeFolderSpecific = 'assets/uploads/cobro_cliente/' . $id . $ds;

        // Consulta a la base de datos
        $cobro_cliente_db = $db->table('tra_cobro_cliente')
                                ->select('id, file')
                                ->where('tramite_id', $id)
                                ->get()
                                ->getResultObject();

        // Función para asignar íconos personalizados según el tipo de archivo
        $assignIcon = function ($extension, $filePath) {
            $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];

            // Si el archivo es una imagen, devolver la ruta del archivo
            if (in_array(strtolower($extension), $imageExtensions)) {
                return base_url($filePath);
            }

            // Si no es una imagen, usar un ícono específico por extensión
            $icons = [
                'xml'  => '/public/assets/src/images/xml-icon.png',
                'pdf'  => '/public/assets/src/images/pdf-icon.png',
                'doc'  => '/public/assets/src/images/doc-icon.png',
                'docx' => '/public/assets/src/images/docx-icon.png',
                'xls'  => '/public/assets/src/images/xls-icon.png',
                'xlsx' => '/public/assets/src/images/xlsx-icon.png',
                'txt'  => '/public/assets/src/images/txt-icon.png',
                'zip'  => '/public/assets/src/images/zip-icon.png',
                'rar'  => '/public/assets/src/images/rar-icon.png',
            ];

            return $icons[strtolower($extension)] ?? '/public/assets/src/images/file-icon.png';
        };

        // Validar archivos de la base de datos
        foreach ($cobro_cliente_db as $dbFile) {
            $filePath = $storeFolderSpecific . $dbFile->file;
            $absoluteFilePath = FCPATH . $filePath;

            // Verificar que el archivo exista físicamente
            if (file_exists($absoluteFilePath)) {
                $extension = strtolower(pathinfo($dbFile->file, PATHINFO_EXTENSION));
                $obj['id'] = $dbFile->id; // ID del archivo en la base de datos
                $obj['name'] = $dbFile->file; // Nombre del archivo
                $obj['size'] = filesize($absoluteFilePath); // Tamaño del archivo físico
                $obj['existing_path'] = base_url($filePath); // Ruta para la vista
                $obj['icon'] = $assignIcon($extension, $filePath); // Ícono o imagen real según el tipo de archivo
                $result[] = $obj;
            }
        }

        // Devolver los resultados en formato JSON
        return $this->response->setJSON($result);
    }




    public function update_solicitud($id) {
        $session = session();
        $data['session'] = \Config\Services::session();
        $data['username'] = $session->get('user_name');
        $myid = $session->get('id');
        $db = \Config\Database::connect();
        $builder = $db->table('tramite');
        $db2 = $this->_getDbData();
        // Retrieve the record
        $tramite = $builder->getWhere(['id' => $id])->getRowArray();
        if($tramite['tra_status_id'] == 23){
            return redirect()->to('/deskapp/proceso/update_final/'. $id);
        }
        
        $TraTiposModel = new TraTiposModel($db2);
        $tra_tipos_options = $TraTiposModel->getTraTiposOptions();
        $entMunicipios = new EntMunicipioModel($db2);
        $ent_municipio_options = $entMunicipios->getEntMunicipios();
        $clienteDirecto = new ClienteDirectoModel($db2);
        $cli_directo_options = $clienteDirecto->getClientesDirectosOptions();
        $empGestora = new EmpresaGestoraModel($db2);
        $empresa_gestora_options = $empGestora->getEmpresasGestorasOptions();

        $traStatus = new TraStatusModel($db2);
        $tra_status_obj = $traStatus->getTraStatusOptions();
        $tra_status_options = $tra_status_obj["tra_status"];
        // $tra_status_steps = $tra_status_obj["steps"];

        $cobroStatuses = new CobroStatusesModel($db2);
        // $cobro_status_options = $cobroStatuses->getCobroStatusesOptions();
        $form = new \stdClass();
        
        // Fields to be displayed in the add form

        if (is_read_only(esc($session->get('user_roles')))){
            $form->fields = [
                "folio" => ["label" => "Folio", "type" => "text", "value" => $tramite['folio'], "disabled"=>"disabled"],
                "contrato" => ["label" => "Contrato", "type" => "text", "value" => $tramite['contrato'], "required" => "required", "disabled"=>"disabled"],
                "unidad" => ["label" => "Unidad", "type" => "text", "value" => $tramite['unidad'], "disabled"=>"disabled"],
                "serie" => ["label" => "Serie", "type" => "text", "value" => $tramite['serie'], "disabled"=>"disabled"],
                "placas" => ["label" => "Placas", "type" => "text", "value" => $tramite['placas'], "disabled"=>"disabled"],
                "tra_tipos_id" => ["label" => "Tipo de Trámite", "type" => "select", "options" => $tra_tipos_options, "value" => $tramite['tra_tipos_id'], "disabled"=>"disabled"],
                "cli_directo_id" => ["label" => "Cliente", "type" => "select", "options" => $cli_directo_options, "value" => $tramite['cli_directo_id'], "disabled"=>"disabled"],
                "cli_directo_ejecutivo_id" => ["label" => "Ejecutivo de Cliente", "type" => "select", "options" => [], "value" => $tramite['cli_directo_ejecutivo_id'], "disabled"=>"disabled"],
                "empresa_gestora_id" => ["label" => "Empresa Gestora", "type" => "select", "options" => $empresa_gestora_options, "value" => $tramite['empresa_gestora_id'], "disabled"=>"disabled"],
                "gestor_id" => ["label" => "Gestor", "type" => "select", "options" => [], "value" => $tramite['gestor_id'], "disabled"=>"disabled"],
                "ent_municipio_id" => ["label" => "Municipio", "type" => "select", "options" => $ent_municipio_options, "value" => $tramite['ent_municipio_id'], "disabled"=>"disabled"],
                "tra_status_id" => ["label" => "Estatus", "type" => "select", "options" => $tra_status_options, "value" => $tramite['tra_status_id'], "disabled"=>"disabled"],
                "observaciones" => ["label" => "Observaciones", "type" => "textarea", "value" => $tramite['observaciones'], "disabled"=>"disabled"]
            ];
        }else{
            $form->fields = [
                "folio" => ["label" => "Folio", "type" => "text", "value" => $tramite['folio'], "readonly"=>"readonly"],
                "contrato" => ["label" => "Contrato", "type" => "text", "value" => $tramite['contrato'], "required" => "required"],
                "unidad" => ["label" => "Unidad", "type" => "text", "value" => $tramite['unidad']],
                "serie" => ["label" => "Serie", "type" => "text", "value" => $tramite['serie']],
                "placas" => ["label" => "Placas", "type" => "text", "value" => $tramite['placas']],
                "tra_tipos_id" => ["label" => "Tipo de Trámite", "type" => "select", "options" => $tra_tipos_options, "value" => $tramite['tra_tipos_id']],
                "cli_directo_id" => ["label" => "Cliente", "type" => "select", "options" => $cli_directo_options, "value" => $tramite['cli_directo_id']],
                "cli_directo_ejecutivo_id" => ["label" => "Ejecutivo de Cliente", "type" => "select", "options" => [], "value" => $tramite['cli_directo_ejecutivo_id']],
                "empresa_gestora_id" => ["label" => "Empresa Gestora", "type" => "select", "options" => $empresa_gestora_options, "value" => $tramite['empresa_gestora_id']],
                "gestor_id" => ["label" => "Gestor", "type" => "select", "options" => [], "value" => $tramite['gestor_id']],
                "ent_municipio_id" => ["label" => "Municipio", "type" => "select", "options" => $ent_municipio_options, "value" => $tramite['ent_municipio_id']],
                "tra_status_id" => ["label" => "Estatus", "type" => "select", "options" => $tra_status_options, "value" => $tramite['tra_status_id']],
                "observaciones" => ["label" => "Observaciones", "type" => "textarea", "value" => $tramite['observaciones']]
            ];
        }

        if (!has_permission('tramite_view_gestor', esc($session->get('user_permissions')),esc($session->get('user_roles')))){
            unset($output->fields['empresa_gestora_id']);
            unset($output->fields['gestor_id']);
        }

        $data['id'] = $id;
        $form->id = $id;

        
        $crud = $this->_getGroceryCrudEnterprise();
        $crudOutput = $crud->render();

        $form->css_files = $crudOutput->css_files;
        $form->js_files = $crudOutput->js_files;
        
        // Load the view with the fields and current data
        if (!is_read_only(esc($session->get('user_roles')))){
            $cruddocstatus = $this->_getGroceryCrudEnterprise();
            $cruddocstatus->setApiUrlPath('/deskapp/tramites/single_documentostatus/'.$id);
            $output = $cruddocstatus->render();

            $crudevidencias = $this->_getGroceryCrudEnterprise();
            $crudevidencias->setApiUrlPath('/deskapp/tramites/single_evidencias/'.$id);
            $outputevidencias = $crudevidencias->render();
            
            $output->output .= "<hr>".$outputevidencias->output;

            $form->output = $output->output;
        }
        
        $data['target_title'] = 'Asignarme este trámite';
        $data['target_id'] = 11;
        $form = array_merge((array)$form, $data);
        return $this->_example_output_2($form, 'add');
    }

    public function update_recoleccion($id) {
        $session = session();
        $data['session'] = \Config\Services::session();
        $data['username'] = $session->get('user_name');
        $myid = $session->get('id');
        $db = \Config\Database::connect();
        $builder = $db->table('tramite');
        $db2 = $this->_getDbData();
        // Retrieve the record
        $tramite = $builder->getWhere(['id' => $id])->getRowArray();
        if($tramite['tra_status_id'] == 23){
            return redirect()->to('/deskapp/proceso/update_final/'. $id);
        }
        
        $TraTiposModel = new TraTiposModel($db2);
        $tra_tipos_options = $TraTiposModel->getTraTiposOptions();
        $entMunicipios = new EntMunicipioModel($db2);
        $ent_municipio_options = $entMunicipios->getEntMunicipios();
        $clienteDirecto = new ClienteDirectoModel($db2);
        $cli_directo_options = $clienteDirecto->getClientesDirectosOptions();
        $empGestora = new EmpresaGestoraModel($db2);
        $empresa_gestora_options = $empGestora->getEmpresasGestorasOptions();

        $traStatus = new TraStatusModel($db2);
        $tra_status_obj = $traStatus->getTraStatusOptions();
        $tra_status_options = $tra_status_obj["tra_status"];
        // $tra_status_steps = $tra_status_obj["steps"];

        $cobroStatuses = new CobroStatusesModel($db2);
        // $cobro_status_options = $cobroStatuses->getCobroStatusesOptions();
        $form = new \stdClass();
        
        // Fields to be displayed in the add form

        if (is_read_only(esc($session->get('user_roles')))){
            $form->fields = [
                "folio" => ["label" => "Folio", "type" => "text", "value" => $tramite['folio'], "disabled"=>"disabled"],
                "contrato" => ["label" => "Contrato", "type" => "text", "value" => $tramite['contrato'], "required" => "required", "disabled"=>"disabled"],
                "unidad" => ["label" => "Unidad", "type" => "text", "value" => $tramite['unidad'], "disabled"=>"disabled"],
                "serie" => ["label" => "Serie", "type" => "text", "value" => $tramite['serie'], "disabled"=>"disabled"],
                "placas" => ["label" => "Placas", "type" => "text", "value" => $tramite['placas'], "disabled"=>"disabled"],
                "tra_tipos_id" => ["label" => "Tipo de Trámite", "type" => "select", "options" => $tra_tipos_options, "value" => $tramite['tra_tipos_id'], "disabled"=>"disabled"],
                "cli_directo_id" => ["label" => "Cliente", "type" => "select", "options" => $cli_directo_options, "value" => $tramite['cli_directo_id'], "disabled"=>"disabled"],
                "cli_directo_ejecutivo_id" => ["label" => "Ejecutivo de Cliente", "type" => "select", "options" => [], "value" => $tramite['cli_directo_ejecutivo_id'], "disabled"=>"disabled"],
                "empresa_gestora_id" => ["label" => "Empresa Gestora", "type" => "select", "options" => $empresa_gestora_options, "value" => $tramite['empresa_gestora_id'], "disabled"=>"disabled"],
                "gestor_id" => ["label" => "Gestor", "type" => "select", "options" => [], "value" => $tramite['gestor_id'], "disabled"=>"disabled"],
                "ent_municipio_id" => ["label" => "Municipio", "type" => "select", "options" => $ent_municipio_options, "value" => $tramite['ent_municipio_id'], "disabled"=>"disabled"],
                "tra_status_id" => ["label" => "Estatus", "type" => "select", "options" => $tra_status_options, "value" => $tramite['tra_status_id'], "disabled"=>"disabled"],
                "observaciones" => ["label" => "Observaciones", "type" => "textarea", "value" => $tramite['observaciones'], "disabled"=>"disabled"]
            ];
        }else{
            $form->fields = [
                "folio" => ["label" => "Folio", "type" => "text", "value" => $tramite['folio'], "readonly"=>"readonly"],
                "contrato" => ["label" => "Contrato", "type" => "text", "value" => $tramite['contrato'], "required" => "required"],
                "unidad" => ["label" => "Unidad", "type" => "text", "value" => $tramite['unidad']],
                "serie" => ["label" => "Serie", "type" => "text", "value" => $tramite['serie']],
                "placas" => ["label" => "Placas", "type" => "text", "value" => $tramite['placas']],
                "tra_tipos_id" => ["label" => "Tipo de Trámite", "type" => "select", "options" => $tra_tipos_options, "value" => $tramite['tra_tipos_id']],
                "cli_directo_id" => ["label" => "Cliente", "type" => "select", "options" => $cli_directo_options, "value" => $tramite['cli_directo_id']],
                "cli_directo_ejecutivo_id" => ["label" => "Ejecutivo de Cliente", "type" => "select", "options" => [], "value" => $tramite['cli_directo_ejecutivo_id']],
                "empresa_gestora_id" => ["label" => "Empresa Gestora", "type" => "select", "options" => $empresa_gestora_options, "value" => $tramite['empresa_gestora_id']],
                "gestor_id" => ["label" => "Gestor", "type" => "select", "options" => [], "value" => $tramite['gestor_id']],
                "ent_municipio_id" => ["label" => "Municipio", "type" => "select", "options" => $ent_municipio_options, "value" => $tramite['ent_municipio_id']],
                "tra_status_id" => ["label" => "Estatus", "type" => "select", "options" => $tra_status_options, "value" => $tramite['tra_status_id']],
                "observaciones" => ["label" => "Observaciones", "type" => "textarea", "value" => $tramite['observaciones']]
            ];
        }

        if (!has_permission('tramite_view_gestor', esc($session->get('user_permissions')),esc($session->get('user_roles')))){
            unset($output->fields['empresa_gestora_id']);
            unset($output->fields['gestor_id']);
        }

        $data['id'] = $id;
        $form->id = $id;

        
        $crud = $this->_getGroceryCrudEnterprise();
        $crudOutput = $crud->render();

        $form->css_files = $crudOutput->css_files;
        $form->js_files = $crudOutput->js_files;
        
        // Load the view with the fields and current data
        if (!is_read_only(esc($session->get('user_roles')))){
            $cruddocstatus = $this->_getGroceryCrudEnterprise();
            $cruddocstatus->setApiUrlPath('/deskapp/tramites/single_documentostatus/'.$id);
            $output = $cruddocstatus->render();

            $crudevidencias = $this->_getGroceryCrudEnterprise();
            $crudevidencias->setApiUrlPath('/deskapp/tramites/single_evidencias/'.$id);
            $outputevidencias = $crudevidencias->render();
            
            $output->output .= "<hr>".$outputevidencias->output;

            $form->output = $output->output;
        }
        $data['target_title'] = 'Documentos Completos';
        $data['target_id'] = 22;
        $form = array_merge((array)$form, $data);
        return $this->_example_output_2($form, 'add');
    }

    public function update_en_tramite($id) {
        $session = session();
        $data['session'] = \Config\Services::session();
        $data['username'] = $session->get('user_name');
        $myid = $session->get('id');
        $db = \Config\Database::connect();
        $db2 = $this->_getDbData();
        $builder = $db->table('tramite');
        // Retrieve the record
        $tramite = $builder->getWhere(['id' => $id])->getRowArray();
        if($tramite['tra_status_id'] == 23){
            return redirect()->to('/deskapp/proceso/update_final/'. $id);
        }
        
        $TraTiposModel = new TraTiposModel($db2);
        $tra_tipos_options = $TraTiposModel->getTraTiposOptions();
        $entMunicipios = new EntMunicipioModel($db2);
        $ent_municipio_options = $entMunicipios->getEntMunicipios();
        $clienteDirecto = new ClienteDirectoModel($db2);
        $cli_directo_options = $clienteDirecto->getClientesDirectosOptions();
        $empGestora = new EmpresaGestoraModel($db2);
        $empresa_gestora_options = $empGestora->getEmpresasGestorasOptions();

        $traStatus = new TraStatusModel($db2);
        $tra_status_obj = $traStatus->getTraStatusOptions();
        $tra_status_options = $tra_status_obj["tra_status"];
        // $tra_status_steps = $tra_status_obj["steps"];
        $cobroStatuses = new CobroStatusesModel($db2);
        // $cobro_status_options = $cobroStatuses->getCobroStatusesOptions();
        $form = new \stdClass();
        
        // Fields to be displayed in the add form

        if (is_read_only(esc($session->get('user_roles')))){
            $form->fields = [
                "folio" => ["label" => "Folio", "type" => "text", "value" => $tramite['folio'], "disabled"=>"disabled"],
                "contrato" => ["label" => "Contrato", "type" => "text", "value" => $tramite['contrato'], "required" => "required", "disabled"=>"disabled"],
                "unidad" => ["label" => "Unidad", "type" => "text", "value" => $tramite['unidad'], "disabled"=>"disabled"],
                "serie" => ["label" => "Serie", "type" => "text", "value" => $tramite['serie'], "disabled"=>"disabled"],
                "placas" => ["label" => "Placas", "type" => "text", "value" => $tramite['placas'], "disabled"=>"disabled"],
                "tra_tipos_id" => ["label" => "Tipo de Trámite", "type" => "select", "options" => $tra_tipos_options, "value" => $tramite['tra_tipos_id'], "disabled"=>"disabled"],
                "cli_directo_id" => ["label" => "Cliente", "type" => "select", "options" => $cli_directo_options, "value" => $tramite['cli_directo_id'], "disabled"=>"disabled"],
                "cli_directo_ejecutivo_id" => ["label" => "Ejecutivo de Cliente", "type" => "select", "options" => [], "value" => $tramite['cli_directo_ejecutivo_id'], "disabled"=>"disabled"],
                "empresa_gestora_id" => ["label" => "Empresa Gestora", "type" => "select", "options" => $empresa_gestora_options, "value" => $tramite['empresa_gestora_id'], "disabled"=>"disabled"],
                "gestor_id" => ["label" => "Gestor", "type" => "select", "options" => [], "value" => $tramite['gestor_id'], "disabled"=>"disabled"],
                "ent_municipio_id" => ["label" => "Municipio", "type" => "select", "options" => $ent_municipio_options, "value" => $tramite['ent_municipio_id'], "disabled"=>"disabled"],
                "tra_status_id" => ["label" => "Estatus", "type" => "select", "options" => $tra_status_options, "value" => $tramite['tra_status_id'], "disabled"=>"disabled"],
                "observaciones" => ["label" => "Observaciones", "type" => "textarea", "value" => $tramite['observaciones'], "disabled"=>"disabled"]
            ];
        }else{
            $form->fields = [
                "folio" => ["label" => "Folio", "type" => "text", "value" => $tramite['folio'], "readonly"=>"readonly"],
                "contrato" => ["label" => "Contrato", "type" => "text", "value" => $tramite['contrato'], "required" => "required"],
                "unidad" => ["label" => "Unidad", "type" => "text", "value" => $tramite['unidad']],
                "serie" => ["label" => "Serie", "type" => "text", "value" => $tramite['serie']],
                "placas" => ["label" => "Placas", "type" => "text", "value" => $tramite['placas']],
                "tra_tipos_id" => ["label" => "Tipo de Trámite", "type" => "select", "options" => $tra_tipos_options, "value" => $tramite['tra_tipos_id']],
                "cli_directo_id" => ["label" => "Cliente", "type" => "select", "options" => $cli_directo_options, "value" => $tramite['cli_directo_id']],
                "cli_directo_ejecutivo_id" => ["label" => "Ejecutivo de Cliente", "type" => "select", "options" => [], "value" => $tramite['cli_directo_ejecutivo_id']],
                "empresa_gestora_id" => ["label" => "Empresa Gestora", "type" => "select", "options" => $empresa_gestora_options, "value" => $tramite['empresa_gestora_id']],
                "gestor_id" => ["label" => "Gestor", "type" => "select", "options" => [], "value" => $tramite['gestor_id']],
                "ent_municipio_id" => ["label" => "Municipio", "type" => "select", "options" => $ent_municipio_options, "value" => $tramite['ent_municipio_id']],
                "tra_status_id" => ["label" => "Estatus", "type" => "select", "options" => $tra_status_options, "value" => $tramite['tra_status_id']],
                "observaciones" => ["label" => "Observaciones", "type" => "textarea", "value" => $tramite['observaciones']]
            ];
        }

        if (!has_permission('tramite_view_gestor', esc($session->get('user_permissions')),esc($session->get('user_roles')))){
            unset($output->fields['empresa_gestora_id']);
            unset($output->fields['gestor_id']);
        }

        $data['id'] = $id;
        $form->id = $id;

        
        $crud = $this->_getGroceryCrudEnterprise();
        $crudOutput = $crud->render();

        $form->css_files = $crudOutput->css_files;
        $form->js_files = $crudOutput->js_files;
        
        // Load the view with the fields and current data
        if (!is_read_only(esc($session->get('user_roles')))){
            $cruddocstatus = $this->_getGroceryCrudEnterprise();
            $cruddocstatus->setApiUrlPath('/deskapp/tramites/single_documentostatus/'.$id);
            $output = $cruddocstatus->render();

            $crudevidencias = $this->_getGroceryCrudEnterprise();
            $crudevidencias->setApiUrlPath('/deskapp/tramites/single_evidencias/'.$id);
            $outputevidencias = $crudevidencias->render();
            
            $output->output .= "<hr>".$outputevidencias->output;

            $form->output = $output->output;
        }
        $data['target_title'] = 'Enviar a Fase Final';
        $data['target_id'] = 23;
        $form = array_merge((array)$form, $data);
        return $this->_example_output_2($form, 'add');
    }

    public function delete_comprobante()
    {
        $request = \Config\Services::request();

        // Obtener el ID del trámite desde la URI
        $uri = $request->getUri();
        $tramiteId = $request->getPost('tramite_id');

        // Validar que se haya proporcionado el ID del trámite
        if ($tramiteId === null) {
            return $this->response->setJSON(['success' => false, 'message' => 'ID de trámite no proporcionado']);
        }

        // Obtener el nombre del archivo desde la solicitud POST
        $fileName = $request->getPost('file');
        if (empty($fileName)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Nombre del archivo no proporcionado']);
        }

        // Ruta base del directorio de los archivos
        $ds = DIRECTORY_SEPARATOR;
        $storeFolder = 'assets/uploads/pago_derechos/' . $tramiteId;
        $filePath = FCPATH . $storeFolder . $ds . $fileName;
        // echo "<br>" . $filePath; die();
        // Eliminar archivo de la carpeta si existe
        if (file_exists($filePath)) {
            if (!unlink($filePath)) {
                return $this->response->setJSON(['success' => false, 'message' => 'No se pudo eliminar el archivo del servidor']);
            }
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'El archivo no existe en el servidor']);
        }

        // Conectar a la base de datos y eliminar el registro
        $db = \Config\Database::connect();
        $builder = $db->table('tra_pago_derechos');
        $builder->where('tramite_id', $tramiteId);
        $builder->where('file', $fileName);

        if (!$builder->delete()) {
            return $this->response->setJSON(['success' => false, 'message' => 'No se pudo eliminar el registro en la base de datos']);
        }

        return $this->response->setJSON(['success' => true, 'message' => 'Archivo eliminado correctamente']);
    }

    public function upload_comprobante()
    {
        $request = \Config\Services::request();

        $uri = $request->getUri();
        $tramiteId = (int) $uri->getSegment(4);

        if ($tramiteId === null) {
            return $this->response->setJSON(['success' => false, 'message' => 'ID de trámite no proporcionado']);
        }

        $ds = DIRECTORY_SEPARATOR; 
        $storeFolder = 'assets/uploads/pago_derechos/' . $tramiteId; // Carpeta destino para los archivos
        $targetPath = FCPATH . $storeFolder . $ds;

        if (!is_dir($targetPath)) {
            mkdir($targetPath, 0777, true); // Crear carpeta si no existe
        }

        if (!empty($_FILES['file'])) {
            // Subir archivo
            $tempFile = $_FILES['file']['tmp_name'];         
            $originalFileName = pathinfo($_FILES['file']['name'], PATHINFO_FILENAME); // Nombre del archivo sin extensión
            $extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION); // Extensión del archivo

            // Concatenar la cadena aleatoria al nombre del archivo
            $fileName = $originalFileName .'.' . $extension;         
            $targetFile = $targetPath . $fileName;

            if (move_uploaded_file($tempFile, $targetFile)) {
                // Guardar el registro en la tabla tra_pago_derechos
                $db = \Config\Database::connect();
                $builder = $db->table('tra_pago_derechos');
                $data = [
                    'tramite_id' => $tramiteId,
                    'file' => $fileName,
                    'user_id' => session()->get('user_id'), // Asume que el ID del usuario está en la sesión
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                    'status' => 1
                ];
                $builder->insert($data);
                $filePath = $ds . $storeFolder . $ds . $fileName;
                return $this->response->setJSON(['success' => true, 'message' => 'Archivo subido y registro creado correctamente', 'filePath'=>$filePath]);
            } else {
                return $this->response->setJSON(['success' => false, 'message' => 'No se pudo mover el archivo']);
            }
        }

        return $this->response->setJSON(['success' => false, 'message' => 'No se recibió ningún archivo']);
    }

    public function delete_pago_gestor()
    {
        $request = \Config\Services::request();

        // Obtener el ID del trámite desde la URI
        $uri = $request->getUri();
        $tramiteId = $request->getPost('tramite_id');

        // Validar que se haya proporcionado el ID del trámite
        if ($tramiteId === null) {
            return $this->response->setJSON(['success' => false, 'message' => 'ID de trámite no proporcionado']);
        }

        // Obtener el nombre del archivo desde la solicitud POST
        $fileName = $request->getPost('file');
        if (empty($fileName)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Nombre del archivo no proporcionado']);
        }

        // Ruta base del directorio de los archivos
        $ds = DIRECTORY_SEPARATOR;
        $storeFolder = 'assets/uploads/pago_gestor/' . $tramiteId;
        $filePath = FCPATH . $storeFolder . $ds . $fileName;

        // Eliminar archivo de la carpeta si existe
        if (file_exists($filePath)) {
            if (!unlink($filePath)) {
                return $this->response->setJSON(['success' => false, 'message' => 'No se pudo eliminar el archivo del servidor']);
            }
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'El archivo no existe en el servidor']);
        }

        // Conectar a la base de datos y eliminar el registro
        $db = \Config\Database::connect();
        $builder = $db->table('tra_pago_gestor'); // Cambiado a la tabla 'tra_pago_gestor'
        $builder->where('tramite_id', $tramiteId);
        $builder->where('file', $fileName);

        if (!$builder->delete()) {
            return $this->response->setJSON(['success' => false, 'message' => 'No se pudo eliminar el registro en la base de datos']);
        }

        return $this->response->setJSON(['success' => true, 'message' => 'Archivo eliminado correctamente']);
    }

    public function upload_pago_gestor()
    {
        $request = \Config\Services::request();

        $uri = $request->getUri();
        $tramiteId = (int) $uri->getSegment(4);

        if ($tramiteId === null) {
            return $this->response->setJSON(['success' => false, 'message' => 'ID de trámite no proporcionado']);
        }

        $ds = DIRECTORY_SEPARATOR;
        $storeFolder = 'assets/uploads/pago_gestor/' . $tramiteId; // Carpeta destino para los archivos
        $targetPath = FCPATH . $storeFolder . $ds;

        if (!is_dir($targetPath)) {
            mkdir($targetPath, 0777, true); // Crear carpeta si no existe
        }

        if (!empty($_FILES['file'])) {
            // Subir archivo
            $tempFile = $_FILES['file']['tmp_name'];         
            $originalFileName = pathinfo($_FILES['file']['name'], PATHINFO_FILENAME); // Nombre del archivo sin extensión
            $extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION); // Extensión del archivo

            // Concatenar la cadena aleatoria al nombre del archivo
            $fileName = $originalFileName . '.' . $extension;         
            $targetFile = $targetPath . $fileName;

            if (move_uploaded_file($tempFile, $targetFile)) {
                // Guardar el registro en la tabla tra_pago_gestor
                $db = \Config\Database::connect();
                $builder = $db->table('tra_pago_gestor'); // Cambiado a la tabla 'tra_pago_gestor'
                $data = [
                    'tramite_id' => $tramiteId,
                    'file' => $fileName,
                    'user_id' => session()->get('user_id'), // Asume que el ID del usuario está en la sesión
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                    'status' => 1
                ];
                $builder->insert($data);

                return $this->response->setJSON(['success' => true, 'message' => 'Archivo subido y registro creado correctamente']);
            } else {
                return $this->response->setJSON(['success' => false, 'message' => 'No se pudo mover el archivo']);
            }
        }

        return $this->response->setJSON(['success' => false, 'message' => 'No se recibió ningún archivo']);
    }

    public function delete_cobro_cliente(): ResponseInterface
    {
        $request = \Config\Services::request();

        // Obtener el ID del trámite desde la URI
        $uri = $request->getUri();
        $tramiteId = $request->getPost('tramite_id');

        // Validar que se haya proporcionado el ID del trámite
        if ($tramiteId === null) {
            return $this->response->setJSON(['success' => false, 'message' => 'ID de trámite no proporcionado']);
        }

        // Obtener el nombre del archivo desde la solicitud POST
        $fileName = $request->getPost('file');
        if (empty($fileName)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Nombre del archivo no proporcionado']);
        }

        // Ruta base del directorio de los archivos
        $ds = DIRECTORY_SEPARATOR;
        $storeFolder = 'assets/uploads/cobro_cliente/' . $tramiteId;
        $filePath = FCPATH . $storeFolder . $ds . $fileName;

        // Eliminar archivo de la carpeta si existe
        if (file_exists($filePath)) {
            if (!unlink($filePath)) {
                return $this->response->setJSON(['success' => false, 'message' => 'No se pudo eliminar el archivo del servidor']);
            }
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'El archivo no existe en el servidor']);
        }

        // Conectar a la base de datos y eliminar el registro
        $db = \Config\Database::connect();
        $builder = $db->table('tra_cobro_cliente'); // Cambiado a la tabla 'tra_cobro_cliente'
        $builder->where('tramite_id', $tramiteId);
        $builder->where('file', $fileName);

        if (!$builder->delete()) {
            return $this->response->setJSON(['success' => false, 'message' => 'No se pudo eliminar el registro en la base de datos']);
        }

        return $this->response->setJSON(['success' => true, 'message' => 'Archivo eliminado correctamente']);
    }

    public function upload_cobro_cliente()
    {
        $request = \Config\Services::request();
    
        $uri = $request->getUri();
        $tramiteId = (int) $uri->getSegment(4);
    
        if ($tramiteId === null) {
            return $this->response->setJSON(['success' => false, 'message' => 'ID de trámite no proporcionado']);
        }
    
        $ds = DIRECTORY_SEPARATOR;
        $storeFolder = 'assets/uploads/cobro_cliente/' . $tramiteId; // Carpeta destino para los archivos
        $targetPath = FCPATH . $storeFolder . $ds;
    
        if (!is_dir($targetPath)) {
            mkdir($targetPath, 0777, true); // Crear carpeta si no existe
        }
    
        if (!empty($_FILES['file'])) {
            // Subir archivo
            $tempFile = $_FILES['file']['tmp_name'];         
            $originalFileName = pathinfo($_FILES['file']['name'], PATHINFO_FILENAME); // Nombre del archivo sin extensión
            $extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION); // Extensión del archivo
    
            // Concatenar la cadena aleatoria al nombre del archivo
            $fileName = $originalFileName . '.' . $extension;         
            $targetFile = $targetPath . $fileName;
    
            if (move_uploaded_file($tempFile, $targetFile)) {
                // Guardar el registro en la tabla tra_cobro_cliente
                $db = \Config\Database::connect();
                $builder = $db->table('tra_cobro_cliente'); // Cambiado a la tabla 'tra_cobro_cliente'
                $data = [
                    'tramite_id' => $tramiteId,
                    'file' => $fileName,
                    'user_id' => session()->get('user_id'), // Asume que el ID del usuario está en la sesión
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                    'status' => 1
                ];
                $builder->insert($data);
    
                return $this->response->setJSON(['success' => true, 'message' => 'Archivo subido y registro creado correctamente']);
            } else {
                return $this->response->setJSON(['success' => false, 'message' => 'No se pudo mover el archivo']);
            }
        }
    
        return $this->response->setJSON(['success' => false, 'message' => 'No se recibió ningún archivo']);
    }
    

    public function getDependentData($type, $parentId) {
        $db = \Config\Database::connect();
    
        switch ($type) {
            case 'gestor':
                $builder = $db->table('ges_gestor');
                $builder->where('empresa_gestora_id', $parentId);
                $result = $builder->get()->getResultArray();
                break;
            case 'ejecutivo':
                $builder = $db->table('cli_directo_ejecutivo');
                $builder->where('cli_directo_id', $parentId);
                $result = $builder->get()->getResultArray();
                break;
            default:
                $result = [];
                break;
        }
    
        return $this->response->setJSON($result);
    }
    
    public function update_save() {
        $session = session();
        $data['session'] = \Config\Services::session();
        $data['username'] = $session->get('user_name');
        $myid = $session->get('id');
        $id = $this->request->uri->getSegment(4);
        $validation = \Config\Services::validation();   
        $db2 = $this->_getDbData();    
        // Set validation rules
        $validation->setRules([
            "folio" => "required",
            "contrato" => "required"
        ]);
    
        if ($validation->withRequest($this->request)->run() === FALSE) {
            // Validation failed, return errors as JSON
            return $this->response->setJSON([
                'success' => false,
                'errors' => $validation->getErrors()
            ]);
        } else {
            // Update the data in the database
            $data = $this->request->getPost();
            $db = \Config\Database::connect();
            $builder = $db->table('tramite');
            $data["user_id"] = $myid;
            $builder->where('id', $id);

            $builder->update($data);
            $folio = $data["folio"];
            #adding bitacora
            $bitacoraModel = new BitacoraModel($db2);
            $data_bitacora = $data;
            $diferencias = $this->encontrarDiferencias($data_bitacora, []);
            $insert_bitacora = [
                "id"=>null,
                "tipo"=>"update",
                "origen"=>"tramite",
                "folio_tramite" => $folio,
                "tramite_id" => (int)$id,
                "cambios" => json_encode($diferencias),
                "user_id" => (int)$myid
            ];
            $bitacoraModel->insert($insert_bitacora, 'bitacora');

            $tra_user_log = new TraUserLogModel($db2);
            $log = [
                "tramite_id"    => (int)$id,
                "user_id"       => (int)$myid,
                "tra_status_id" => 11
            ];
            $tra_user_log->insert($log, 'tra_user_log');

            // Return success message as JSON
            return $this->response->setJSON([
                'success' => true,
                'message' => 'El trámite se guardó correctamente.',
                'redirect' => '/deskapp/tramites/update/'.$id
            ]);
        }
    }

    public function update_gestor_save() {
        $session = session();
        $data['session'] = \Config\Services::session();
        $data['username'] = $session->get('user_name');
        $myid = $session->get('id');
        $id = $this->request->uri->getSegment(4);
        $validation = \Config\Services::validation();   
        $db2 = $this->_getDbData();    
        $validation->setRules([
            "empresa_gestora_id" => "required",
            "gestor_id" => "required"
        ]);
        if ($validation->withRequest($this->request)->run() === FALSE) {
            // Validation failed, return errors as JSON
            return $this->response->setJSON([
                'success' => false,
                'errors' => $validation->getErrors()
            ]);
        } else {
            // Update the data in the database
            $data = $this->request->getPost();
            $db = \Config\Database::connect();
            $builder = $db->table('tramite');
            $builder->where('id', $id);

            $this->updateTramiteStatus($id, 25);

            if (empty($tramite_base['started_at'])) {
                $data["started_at"] = date('Y-m-d H:i:s');
            }

            $builder->where('id', $id);
            $builder->update($data);

            $bitacoraModel = new BitacoraModel($db2);
            $data_bitacora = $data;
            $diferencias = $this->encontrarDiferencias($data_bitacora, []);
            $insert_bitacora = [
                "id"=>null,
                "tipo"=>"update",
                "origen"=>"tramite",
                "tramite_id" => (int)$id,
                "cambios" => json_encode($diferencias),
                "user_id" => (int)$myid
            ];
            $bitacoraModel->insert($insert_bitacora, 'bitacora');

            $tra_user_log = new TraUserLogModel($db2);
            $log = [
                "tramite_id"    => (int)$id,
                "user_id"       => (int)$myid,
                "tra_status_id" => 22
            ];
            $tra_user_log->insert($log, 'tra_user_log');

            // Return success message as JSON
            return $this->response->setJSON([
                'success' => true,
                'message' => 'El Gestor se asignó correctamente.',
                'redirect' => '/deskapp/tramites/update/'.$id
            ]);
        }
    }

    public function update_gestor_costos() {
        $session = session();
        $data['session'] = \Config\Services::session();
        $data['username'] = $session->get('user_name');
        $myid = $session->get('id');
        $id = $this->request->uri->getSegment(4);  // Obtener el ID del trámite desde la URL
    
        // Validación de los campos
        $validation = \Config\Services::validation();
        $validation->setRules([
            "costo_tramite" => "required|numeric",
            "deposito_gestor" => "required|numeric",
            "reembolso_status_id" => "required|integer"
        ]);
    
        // Validación fallida
        if ($validation->withRequest($this->request)->run() === FALSE) {
            return $this->response->setJSON([
                'success' => false,
                'errors' => $validation->getErrors()
            ]);
        }
    
        // Obtener los datos enviados
        $data = $this->request->getPost();
    
        try {
            // Conexión a la base de datos y actualización del trámite
            $db = \Config\Database::connect();
            $builder = $db->table('tramite');
    
            // Actualizar los campos en la tabla tramite
            $builder->where('id', $id);
            $builder->update([
                'costo_tramite' => $data['costo_tramite'],
                'deposito_gestor' => $data['deposito_gestor'],
                'reembolso_status_id' => $data['reembolso_status_id']
            ]);
    
            // Registro en la bitácora
            $db2 = $this->_getDbData();
            $bitacoraModel = new BitacoraModel($db2);
            $diferencias = $this->encontrarDiferencias($data, []);
            $insert_bitacora = [
                "id" => null,
                "tipo" => "update",
                "origen" => "tramite",
                "tramite_id" => (int)$id,
                "cambios" => json_encode($diferencias),
                "user_id" => (int)$myid
            ];
            $bitacoraModel->insert($insert_bitacora, 'bitacora');
    
            // Registro en tra_user_log
            $tra_user_log = new TraUserLogModel($db2);
            $log = [
                "tramite_id" => (int)$id,
                "user_id" => (int)$myid,
                "tra_status_id" => 22  // Estado ejemplo
            ];
            $tra_user_log->insert($log, 'tra_user_log');
    
            // Retornar éxito como respuesta JSON
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Los costos del trámite se actualizaron correctamente.',
                'redirect' => '/deskapp/tramites/update/' . $id
            ]);
        } catch (\Exception $e) {
            // Manejo de errores
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Ocurrió un error al actualizar los costos: ' . $e->getMessage()
            ]);
        }
    }

    public function update_derechos_save() {
        $session = session();
        $data['session'] = \Config\Services::session();
        $data['username'] = $session->get('user_name');
        $myid = $session->get('id');
        $id = $this->request->uri->getSegment(4);
        $validation = \Config\Services::validation();   
        $db2 = $this->_getDbData();    
        $validation->setRules([
            "derechos_tramite"=> "required",
            "derechos_pago_sitio" => "required",
            "derechos_vigencia" => "required"
        ]);
        if ($validation->withRequest($this->request)->run() === FALSE) {
            // Validation failed, return errors as JSON
            return $this->response->setJSON([
                'success' => false,
                'errors' => $validation->getErrors()
            ]);
        } else {
            // Update the data in the database
            $data = $this->request->getPost();
            $db = \Config\Database::connect();
            $builder = $db->table('tramite');
            $builder->where('id', $id);

            $this->updateTramiteStatus($id, 26);

            $builder->where('id', $id);
            $builder->update($data);
            #adding bitacora
            $bitacoraModel = new BitacoraModel($db2);
            $data_bitacora = $data;
            $diferencias = $this->encontrarDiferencias($data_bitacora, []);
            $insert_bitacora = [
                "id"=>null,
                "tipo"=>"update",
                "origen"=>"tramite",
                "tramite_id" => (int)$id,
                "cambios" => json_encode($diferencias),
                "user_id" => (int)$myid
            ];
            $bitacoraModel->insert($insert_bitacora, 'bitacora');

            $tra_user_log = new TraUserLogModel($db2);
            $log = [
                "tramite_id"    => (int)$id,
                "user_id"       => (int)$myid,
                "tra_status_id" => 22
            ];
            $tra_user_log->insert($log, 'tra_user_log');

            // Return success message as JSON
            return $this->response->setJSON([
                'success' => true,
                'message' => 'El trámite se guardó correctamente.',
                'redirect' => '/deskapp/tramites/update/'.$id
            ]);
        }
    }

    public function update_bancario_save() {
        $session = session();
        $data['session'] = \Config\Services::session();
        $data['username'] = $session->get('user_name');
        $myid = $session->get('id');
        $id = $this->request->uri->getSegment(4);
        $validation = \Config\Services::validation();   
        $db2 = $this->_getDbData();    
        $validation->setRules([
            "derechos_revol_cliente"=> "required",
            "derechos_refer_banc" => "required"
        ]);
        if ($validation->withRequest($this->request)->run() === FALSE) {
            // Validation failed, return errors as JSON
            return $this->response->setJSON([
                'success' => false,
                'errors' => $validation->getErrors()
            ]);
        } else {
            // Update the data in the database
            $data = $this->request->getPost();
            $db = \Config\Database::connect();
            $builder = $db->table('tramite');
            $builder->where('id', $id);

            $this->updateTramiteStatus($id, 27);

            $builder->where('id', $id);
            $builder->update($data);
            #adding bitacora
            $bitacoraModel = new BitacoraModel($db2);
            $data_bitacora = $data;
            $diferencias = $this->encontrarDiferencias($data_bitacora, []);
            $insert_bitacora = [
                "id"=>null,
                "tipo"=>"update",
                "origen"=>"tramite",
                "tramite_id" => (int)$id,
                "cambios" => json_encode($diferencias),
                "user_id" => (int)$myid
            ];
            $bitacoraModel->insert($insert_bitacora, 'bitacora');

            $tra_user_log = new TraUserLogModel($db2);
            $log = [
                "tramite_id"    => (int)$id,
                "user_id"       => (int)$myid,
                "tra_status_id" => 22
            ];
            $tra_user_log->insert($log, 'tra_user_log');

            // Return success message as JSON
            return $this->response->setJSON([
                'success' => true,
                'message' => 'El trámite se guardó correctamente.',
                'redirect' => '/deskapp/tramites/update/'.$id
            ]);
        }
    }

    public function update_pago_gestor() {
        $session = session();
        $data['session'] = \Config\Services::session();
        $data['username'] = $session->get('user_name');
        $myid = $session->get('id');
        $id = $this->request->uri->getSegment(4);
        $validation = \Config\Services::validation();   
        $db2 = $this->_getDbData();
    
        $db = \Config\Database::connect();
        $builder = $db->table('tramite');
        $builder->where('id', $id);
        $existingData = $builder->get()->getRowArray();
    
        // Reglas de validación
        $validation->setRules([
            // "costo_gestoria" => "required|decimal",
            // "impuesto_gestoria" => "required|decimal",
            // "gestoria_comision" => "required|decimal",
            // "pago_gestor_st_id" => "required|integer",
            "reembolso_status_id" => "required|integer",
        ], [
            // "costo_gestoria" => ["required" => "El costo de gestoría es obligatorio.", "decimal" => "Debe ser un número decimal válido."],
            // "impuesto_gestoria" => ["required" => "El impuesto de gestoría es obligatorio.", "decimal" => "Debe ser un número decimal válido."],
            // "gestoria_comision" => ["required" => "La Gratificación es obligatoria.", "decimal" => "Debe ser un número decimal válido."],
            // "pago_gestor_st_id" => ["required" => "El estatus del pago es obligatorio.", "integer" => "Debe ser un número entero válido."],
            "reembolso_status_id" => ["required" => "El estatus del reembolso es obligatorio.", "integer" => "Debe ser un número entero válido."]
        ]);
    
        if ($validation->withRequest($this->request)->run() === FALSE) {
            // Validación fallida
            return $this->response->setJSON([
                'success' => false,
                'errors' => $validation->getErrors()
            ]);
        } else {
            // Obtener datos del formulario
            $data = $this->request->getPost();
            $data["user_id"] = $myid;
    
            // Cálculo de campos adicionales
            $data["gestor_total_pago"] = $data["gestor_total_pago_hidden"];
            unset($data["gestor_total_pago_hidden"]);
            $data["reembolso_status_id"] = $data["reembolso_status_id_hidden"];
            unset($data["reembolso_status_id_hidden"]);
            $data["impuesto_gestoria"] = $data["impuesto_gestoria_hidden"];
            unset($data["impuesto_gestoria_hidden"]);
            $data["gestoria_comision"] = $data["gestoria_comision_hidden"];
            unset($data["gestoria_comision_hidden"]);
            
            
            $this->updateTramiteStatus($id, 28);
            // Actualizar en la base de datos
            $builder->where('id', $id);
            $builder->update($data);
    
            // Bitácora
            $bitacoraModel = new BitacoraModel($db2);
            $diferencias = $this->encontrarDiferencias($data, $existingData);
            $bitacoraModel->insert([
                "id" => null,
                "tipo" => "update",
                "origen" => "tramite",
                "tramite_id" => (int)$id,
                "cambios" => json_encode($diferencias),
                "user_id" => (int)$myid
            ], 'bitacora');
    
            // Registrar log
            $tra_user_log = new TraUserLogModel($db2);
            $tra_user_log->insert([
                "tramite_id"    => (int)$id,
                "user_id"       => (int)$myid,
                "tra_status_id" => 22
            ], 'tra_user_log');
    
            // Respuesta de éxito
            return $this->response->setJSON([
                'success' => true,
                'message' => 'El trámite se guardó correctamente.',
                'redirect' => '/deskapp/tramites/update/' . $id
            ]);
        }
    }    

    public function update_final_save()
    {
        $session = session();
        $data['session'] = \Config\Services::session();
        $data['username'] = $session->get('user_name');
        $myid = $session->get('id');
        $id = $this->request->uri->getSegment(4);
        $validation = \Config\Services::validation();
        $db2 = $this->_getDbData();

        // Reglas de validación para los campos definidos en el fragmento
        $validation->setRules([
            "id_give_cliente" => "required",
            "numero_factura" => "required",
            "numero_refactura" => "permit_empty",
            "cobro_status_id" => "required|integer",
            "costo_pago_cliente" => "required|decimal",
            "comision_derechos" => "required|decimal",
            "costo_total" => "permit_empty|decimal"
        ]);

        if ($validation->withRequest($this->request)->run() === FALSE) {
            // Validación fallida, retornar errores como JSON
            return $this->response->setJSON([
                'success' => false,
                'errors' => $validation->getErrors()
            ]);
        } else {
            // Obtener los datos permitidos del formulario
            $data = $this->request->getPost([
                "id_give_cliente",
                "numero_factura",
                "numero_refactura",
                "cobro_status_id",
                "costo_pago_cliente",
                "comision_derechos",
                "costo_gestoria",
                "costo_gestoria_hidden",
                "iva"
            ]);
            $data["user_id"] = $myid;
            $data["costo_gestoria"] = $data["costo_gestoria_hidden"];
            unset($data["costo_gestoria_hidden"]);
            // Calcular el costo total
            $data["costo_total"] = $data["costo_gestoria"] + $data["costo_pago_cliente"] + $data["comision_derechos"] + $data["iva"] ;
            #costo_gestoria, #costo_pago_cliente, #comision_derechos
            // Actualizar los datos en la tabla 'tramite'

            $db = \Config\Database::connect();
            
            $builder = $db->table('tramite');
            $builder->where('id', $id);
            $builder->update($data);

            $this->updateTramiteStatus($id, 28);

            // Agregar registro en bitácora
            $bitacoraModel = new BitacoraModel($db2);
            $data_bitacora = $data;
            $diferencias = $this->encontrarDiferencias($data_bitacora, []);
            $insert_bitacora = [
                "id" => null,
                "tipo" => "update",
                "origen" => "tramite",
                "tramite_id" => (int)$id,
                "cambios" => json_encode($diferencias),
                "user_id" => (int)$myid
            ];
            $bitacoraModel->insert($insert_bitacora, 'bitacora');
    
            // Registrar en la tabla tra_user_log
            $tra_user_log = new TraUserLogModel($db2);
            $log = [
                "tramite_id"    => (int)$id,
                "user_id"       => (int)$myid,
                "tra_status_id" => 22
            ];
            $tra_user_log->insert($log, 'tra_user_log');
    
            // Retornar mensaje de éxito como JSON
            return $this->response->setJSON([
                'success' => true,
                'message' => 'El trámite se guardó correctamente.',
                'redirect' => '/deskapp/tramites/update/' . $id
            ]);
        }
    }

    private function _example_output_2($output = null, $page = 'index') {
        return view('/deskapp/extra-pages/tramite_' . $page . '_view', (array)$output);
    }

    public function updateTramiteStatus($id, $newStatus){
        $db = \Config\Database::connect();
        $builder = $db->table('tramite');
        // $builder = $this->db->table('tramite'); // Cambia 'tramite' por el nombre real de tu tabla
        $tramite_base = $builder->getWhere(['id' => $id])->getRowArray();

        if (!$tramite_base) {
            return ['success' => false, 'message' => 'Trámite no encontrado'];
        }

        // Define el flujo de estados válidos
        $arr_status = [22, 25, 26, 27, 23, 28, 20, 21];

        // Obtener la posición del estado actual y el nuevo estado en el flujo
        $currentStatusIndex = array_search($tramite_base['tra_status_id'], $arr_status);
        $newStatusIndex = array_search($newStatus, $arr_status);

        if ($newStatusIndex !== false && $newStatusIndex >= $currentStatusIndex) {
            $data = ['tra_status_id' => $newStatus];
            $builder->where('id', $id);
            $builder->update($data);

            return ['success' => true, 'message' => 'Estado actualizado correctamente'];
        }

        // Si el nuevo estado es anterior, no hacer nada
        return null; // Opcionalmente puedes omitir esta línea si no necesitas retorno
    }


    public function demo_multigrid() {
        $crud = $this->_getGroceryCrudEnterprise();
        #ancla
        $crud->setApiUrlPath('/tramites/mios');
        $output = $crud->render();

        $crud2 = $this->_getGroceryCrudEnterprise();
        $crud2->setApiUrlPath('/tramites/tipo');
        $output2 = $crud2->render();
        
        $output->output .= "<hr>".$output2->output;
        // echo "<br>". $output->output;

        return $this->_example_output_test($output);
    }

    public function _example_output_test($output = null) {
        // echo $version = GroceryCrud::VERSION;
        try{
            if (isset($output->isJSONResponse) && $output->isJSONResponse) {
                header('Content-Type: application/json; charset=utf-8');
                echo $output->output;
                exit;
            }
        }catch (\Exception $e) {
            var_dump($e->getMessage());
            // exit($e->getMessage());
            throw new \Exception('Wrong data');
        }
        return view('/deskapp/extra-pages/grocery_page', (array)$output);
        // $this->load->view('grocery_simple_page', $output); 
     }

    public function mios()
    {
        try {
            # Manejo de session de action
            $self = $this;
            $session = session();
            $data['session'] = \Config\Services::session();
            $data['username'] = $session->get('user_name');
            $myid = $session->get('id');
            # fin del manejo de session

            $tramite_crud = $this->_getGroceryCrudEnterprise();
            $tramite_crud->unsetAdd();    
            $tramite_crud->unsetEdit();
            $tramite_crud->unsetDelete();
            $tramite_crud->setCsrfTokenName(csrf_token());
            $tramite_crud->setCsrfTokenValue(csrf_hash());
            
            $tramite_crud->setTable('tramite');
            $tramite_crud->setSubject('tramite', 'Mis Tramites');

            if(is_starter($session->get('user_roles'))){
                $tramite_crud->where(['(tramite.user_id = ? AND tramite.tra_status_id = ?)' => [$myid, 11]])
                    ->where('tramite.tra_status_id NOT IN (20, 21)');
            }elseif(is_executer($session->get('user_roles'))){
                    $tramite_crud->where([
                        '(tramite.user_id = ? AND tramite.tra_status_id = ?)' => [$myid, 22]
                    ]);
            }else{
                $tramite_crud->where([
                    'tramite.user_id' => $myid
                ]); 
            }

            $tramite_crud->defaultOrdering('tramite.id', 'desc');
            
            $tramite_crud->columns([
                'created_at', 'started_at', 'id', 'folio','contrato','unidad','serie', 
                'placas','tra_tipos_id','ent_municipio_id','cli_directo_id',
                'cli_directo_ejecutivo_id','empresa_gestora_id','gestor_id',
                'tra_status_id','cobro_status_id', 'user_id',
                'observaciones'
            ]);
            $tramite_crud->displayAs('created_at','Creación');  
            $tramite_crud->displayAs("started_at", "Desde Asignacion");
            $tramite_crud->setRelation('user_id', 'users', '{firstname} {midname} {lastname}');
            $tramite_crud->displayAs("user_id", "Ejecutivo");

            $tramite_crud->callbackColumn('started_at', function ($value, $row) {
                $fechaAsignacion = new \DateTime($row->started_at);
                $fechaActual = new \DateTime();
                $diasDiferencia = $fechaAsignacion->diff($fechaActual)->days;
            
                // Definir clases CSS según los días
                $claseVerde = 'background-verde';  // Clase CSS para verde
                $claseAmarillo = 'background-amarillo';  // Clase CSS para amarillo
                $claseRojo = 'background-rojo';  // Clase CSS para rojo
                $claseVioleta = 'background-violeta';  // Clase CSS para violeta
            
                // Determinar si es Local o Foráneo
                $local = ($row->ent_municipio_id >= 266 && $row->ent_municipio_id <= 281) || 
                ($row->ent_municipio_id >= 657 && $row->ent_municipio_id <= 781);
                
                // Determinar la clase CSS basada en los días de diferencia y si es Local o Foráneo
                if ($local) {
                    if ($diasDiferencia < 5) {
                        $clase = $claseVerde;
                    } elseif ($diasDiferencia < 8) {
                        $clase = $claseAmarillo;
                    } elseif ($diasDiferencia < 12) {
                        $clase = $claseRojo;
                    } else {
                        $clase = $claseVioleta;
                    }
                } else {
                    if ($diasDiferencia < 10) {
                        $clase = $claseVerde;
                    } elseif ($diasDiferencia < 13) {
                        $clase = $claseAmarillo;
                    } elseif ($diasDiferencia < 16) {
                        $clase = $claseRojo;
                    } else {
                        $clase = $claseVioleta;
                    }
                }
            
                return '<span class="' . $clase . '">' . $diasDiferencia . ' días</span>';
            });

            $tramite_crud->fields([
                'folio','contrato','unidad','serie', 
                'placas','tra_tipos_id','ent_municipio_id','cli_directo_id',
                'cli_directo_ejecutivo_id','empresa_gestora_id','gestor_id',
                'tra_status_id','cobro_status_id',
                'observaciones', 'user_id'
            ]); 
            // $tramite_crud->readOnlyFields(["folio"]);
            $tramite_crud->unsetDeleteMultiple();
            
            /* SELECT Se configura el tipo de tramite */
            $tramite_crud->setRelation('tra_tipos_id', 'tra_tipos', 'tipo_tramite');
            $tramite_crud->displayAs('tra_tipos_id','Tipo de Tramite');

            /* SELECT Se configura el estatus del tramite */
            $tramite_crud->setRelation('tra_status_id', 'tra_status', 'tra_status');
            $tramite_crud->displayAs('tra_status_id','Estatus del Tramite');

            /* SELECT Se configura el cliente final o cliente directo */
            $tramite_crud->setRelation('cli_directo_id', 'cli_directo', 'razon_social');
            $tramite_crud->displayAs('cli_directo_id','Cliente Directo');
            
            /* SELECT Se configura el ejecutivo del cliente */
            $tramite_crud->setRelation('cli_directo_ejecutivo_id', 'cli_directo_ejecutivo', 'nombre');
            $tramite_crud->displayAs('cli_directo_ejecutivo_id','Ejecutivo del Cliente');

            $tramite_crud->setDependentRelation('cli_directo_ejecutivo_id','cli_directo_id','cli_directo_id');

            /* SELECT Se configura el municipio */
            $tramite_crud->setRelation('ent_municipio_id', 'rel_ent_municipio', 'ent_municipality');
            $tramite_crud->displayAs('ent_municipio_id','Municipio');

            /* SELECT Se configura la empresa gestora */
            $tramite_crud->setRelation('empresa_gestora_id', 'ges_empresa_gestora', 'razon_social');
            $tramite_crud->displayAs('empresa_gestora_id','Empresa Gestora');

            /* SELECT Se configura el gestor*/
            $tramite_crud->setRelation('gestor_id', 'ges_gestor', 'nombre');
            $tramite_crud->displayAs('gestor_id','Gestor');

            $tramite_crud->setDependentRelation('gestor_id','empresa_gestora_id','empresa_gestora_id');

            $tramite_crud->setActionButton('Editar', 'fas fa-pencil-alt', function ($row) {
                return '/deskapp/tramites/update/' . $row->id;
            }, false);

            $tramite_salida = $tramite_crud->render();
            
            $salida_total = array_merge((array)$tramite_salida, $data);
            $salida_total['insert_button_url'] = '/public/deskapp/tramites/add';

            echo $this->_example_output($salida_total);

        } catch (\Exception $e) {
            exit($e->getMessage());
        }
    }

    public function solicitudes()
    {
        try {
            # Manejo de session de action
            $self = $this;
            $session = session();
            $data['session'] = \Config\Services::session();
            $data['username'] = $session->get('user_name');
            $myid = $session->get('id');
            # fin del manejo de session

            $tramite_crud = $this->_getGroceryCrudEnterprise();
            $tramite_crud->unsetAdd();    
            $tramite_crud->unsetEdit();
            $tramite_crud->unsetDelete();
            $tramite_crud->setCsrfTokenName(csrf_token());
            $tramite_crud->setCsrfTokenValue(csrf_hash());
            
            $tramite_crud->setTable('tramite');
            $tramite_crud->setSubject('tramite', 'Mis Tramites');

            $tramite_crud->where([
                'tramite.tra_status_id = ?' => [24]
            ]);   

            $tramite_crud->defaultOrdering('tramite.id', 'desc');
            
            $tramite_crud->columns([
                'created_at', 'started_at', 'id', 'folio','contrato','unidad','serie', 
                'placas','tra_tipos_id','ent_municipio_id','cli_directo_id',
                'cli_directo_ejecutivo_id','empresa_gestora_id','gestor_id',
                'tra_status_id','cobro_status_id', 'user_id',
                'observaciones'
            ]);

            $tramite_crud->displayAs("started_at", "Desde Asignación");
            $tramite_crud->displayAs('created_at','Creación'); 
            $tramite_crud->setRelation('user_id', 'users', '{firstname} {midname} {lastname}');
            $tramite_crud->displayAs("user_id", "Ejecutivo");
            $tramite_crud->callbackColumn('started_at', function ($value, $row) {
                $fechaAsignacion = new \DateTime($row->started_at);
                $fechaActual = new \DateTime();
                $diasDiferencia = $fechaAsignacion->diff($fechaActual)->days;
            
                // Definir clases CSS según los días
                $claseVerde = 'background-verde';  // Clase CSS para verde
                $claseAmarillo = 'background-amarillo';  // Clase CSS para amarillo
                $claseRojo = 'background-rojo';  // Clase CSS para rojo
                $claseVioleta = 'background-violeta';  // Clase CSS para violeta
            
                // Determinar si es Local o Foráneo
                $local = ($row->ent_municipio_id >= 266 && $row->ent_municipio_id <= 281) || 
                ($row->ent_municipio_id >= 657 && $row->ent_municipio_id <= 781);
                
                // Determinar la clase CSS basada en los días de diferencia y si es Local o Foráneo
                if ($local) {
                    if ($diasDiferencia < 5) {
                        $clase = $claseVerde;
                    } elseif ($diasDiferencia < 8) {
                        $clase = $claseAmarillo;
                    } elseif ($diasDiferencia < 12) {
                        $clase = $claseRojo;
                    } else {
                        $clase = $claseVioleta;
                    }
                } else {
                    if ($diasDiferencia < 10) {
                        $clase = $claseVerde;
                    } elseif ($diasDiferencia < 13) {
                        $clase = $claseAmarillo;
                    } elseif ($diasDiferencia < 16) {
                        $clase = $claseRojo;
                    } else {
                        $clase = $claseVioleta;
                    }
                }
            
                return '<span class="' . $clase . '">' . $diasDiferencia . ' días</span>';
            });

            $tramite_crud->fields([
                'folio','contrato','unidad','serie', 
                'placas','tra_tipos_id','ent_municipio_id','cli_directo_id',
                'cli_directo_ejecutivo_id','empresa_gestora_id','gestor_id',
                'tra_status_id','cobro_status_id',
                'observaciones', 'user_id'
            ]); 
            // $tramite_crud->readOnlyFields(["folio"]);
            $tramite_crud->unsetDeleteMultiple();
            
            /* SELECT Se configura el tipo de tramite */
            $tramite_crud->setRelation('tra_tipos_id', 'tra_tipos', 'tipo_tramite');
            $tramite_crud->displayAs('tra_tipos_id','Tipo de Tramite');

            /* SELECT Se configura el estatus del tramite */
            $tramite_crud->setRelation('tra_status_id', 'tra_status', 'tra_status');
            $tramite_crud->displayAs('tra_status_id','Estatus del Tramite');

            /* SELECT Se configura el cliente final o cliente directo */
            $tramite_crud->setRelation('cli_directo_id', 'cli_directo', 'razon_social');
            $tramite_crud->displayAs('cli_directo_id','Cliente Directo');
            
            /* SELECT Se configura el ejecutivo del cliente */
            $tramite_crud->setRelation('cli_directo_ejecutivo_id', 'cli_directo_ejecutivo', 'nombre');
            $tramite_crud->displayAs('cli_directo_ejecutivo_id','Ejecutivo del Cliente');

            $tramite_crud->setDependentRelation('cli_directo_ejecutivo_id','cli_directo_id','cli_directo_id');

            /* SELECT Se configura el municipio */
            $tramite_crud->setRelation('ent_municipio_id', 'rel_ent_municipio', 'ent_municipality');
            $tramite_crud->displayAs('ent_municipio_id','Municipio');

            /* SELECT Se configura la empresa gestora */
            $tramite_crud->setRelation('empresa_gestora_id', 'ges_empresa_gestora', 'razon_social');
            $tramite_crud->displayAs('empresa_gestora_id','Empresa Gestora');

            /* SELECT Se configura el gestor*/
            $tramite_crud->setRelation('gestor_id', 'ges_gestor', 'nombre');
            $tramite_crud->displayAs('gestor_id','Gestor');

            $tramite_crud->setDependentRelation('gestor_id','empresa_gestora_id','empresa_gestora_id');

            $tramite_crud->setActionButton('Editar', 'fas fa-pencil-alt', function ($row) {
                return '/deskapp/tramites/update/' . $row->id;
            }, false);

            $tramite_salida = $tramite_crud->render();
            
            $salida_total = array_merge((array)$tramite_salida, $data);
            $salida_total['insert_button_url'] = '/public/deskapp/tramites/add';

            echo $this->_example_output($salida_total);

        } catch (\Exception $e) {
            exit($e->getMessage());
        }
    }

    public function recoleccion()
    {
        try {
            # Manejo de session de action
            $self = $this;
            $session = session();
            $data['session'] = \Config\Services::session();
            $data['username'] = $session->get('user_name');
            $myid = $session->get('id');
            # fin del manejo de session

            $tramite_crud = $this->_getGroceryCrudEnterprise();
            $tramite_crud->unsetAdd();    
            $tramite_crud->unsetEdit();
            $tramite_crud->unsetDelete();
            $tramite_crud->setCsrfTokenName(csrf_token());
            $tramite_crud->setCsrfTokenValue(csrf_hash());
            
            $tramite_crud->setTable('tramite');
            $tramite_crud->setSubject('tramite', 'Mis Tramites');

            $tramite_crud->where([
                'tramite.tra_status_id = ?' => [11]
            ]);   

            $tramite_crud->defaultOrdering('tramite.id', 'desc');
            
            $tramite_crud->columns([
                'created_at', 'started_at', 'id', 'folio','contrato','unidad','serie', 
                'placas','tra_tipos_id','ent_municipio_id','cli_directo_id',
                'cli_directo_ejecutivo_id','empresa_gestora_id','gestor_id',
                'tra_status_id','cobro_status_id', 'user_id',
                'observaciones'
            ]);

            $tramite_crud->displayAs("started_at", "Desde Asignación");
            $tramite_crud->displayAs('created_at','Creación'); 
            $tramite_crud->setRelation('user_id', 'users', '{firstname} {midname} {lastname}');
            $tramite_crud->displayAs("user_id", "Ejecutivo");
            $tramite_crud->callbackColumn('started_at', function ($value, $row) {
                $fechaAsignacion = new \DateTime($row->started_at);
                $fechaActual = new \DateTime();
                $diasDiferencia = $fechaAsignacion->diff($fechaActual)->days;
            
                // Definir clases CSS según los días
                $claseVerde = 'background-verde';  // Clase CSS para verde
                $claseAmarillo = 'background-amarillo';  // Clase CSS para amarillo
                $claseRojo = 'background-rojo';  // Clase CSS para rojo
                $claseVioleta = 'background-violeta';  // Clase CSS para violeta
            
                // Determinar si es Local o Foráneo
                $local = ($row->ent_municipio_id >= 266 && $row->ent_municipio_id <= 281) || 
                ($row->ent_municipio_id >= 657 && $row->ent_municipio_id <= 781);
                
                // Determinar la clase CSS basada en los días de diferencia y si es Local o Foráneo
                if ($local) {
                    if ($diasDiferencia < 5) {
                        $clase = $claseVerde;
                    } elseif ($diasDiferencia < 8) {
                        $clase = $claseAmarillo;
                    } elseif ($diasDiferencia < 12) {
                        $clase = $claseRojo;
                    } else {
                        $clase = $claseVioleta;
                    }
                } else {
                    if ($diasDiferencia < 10) {
                        $clase = $claseVerde;
                    } elseif ($diasDiferencia < 13) {
                        $clase = $claseAmarillo;
                    } elseif ($diasDiferencia < 16) {
                        $clase = $claseRojo;
                    } else {
                        $clase = $claseVioleta;
                    }
                }
            
                return '<span class="' . $clase . '">' . $diasDiferencia . ' días</span>';
            });

            $tramite_crud->fields([
                'folio','contrato','unidad','serie', 
                'placas','tra_tipos_id','ent_municipio_id','cli_directo_id',
                'cli_directo_ejecutivo_id','empresa_gestora_id','gestor_id',
                'tra_status_id','cobro_status_id',
                'observaciones', 'user_id'
            ]); 
            // $tramite_crud->readOnlyFields(["folio"]);
            $tramite_crud->unsetDeleteMultiple();
            
            /* SELECT Se configura el tipo de tramite */
            $tramite_crud->setRelation('tra_tipos_id', 'tra_tipos', 'tipo_tramite');
            $tramite_crud->displayAs('tra_tipos_id','Tipo de Tramite');

            /* SELECT Se configura el estatus del tramite */
            $tramite_crud->setRelation('tra_status_id', 'tra_status', 'tra_status');
            $tramite_crud->displayAs('tra_status_id','Estatus del Tramite');

            /* SELECT Se configura el cliente final o cliente directo */
            $tramite_crud->setRelation('cli_directo_id', 'cli_directo', 'razon_social');
            $tramite_crud->displayAs('cli_directo_id','Cliente Directo');
            
            /* SELECT Se configura el ejecutivo del cliente */
            $tramite_crud->setRelation('cli_directo_ejecutivo_id', 'cli_directo_ejecutivo', 'nombre');
            $tramite_crud->displayAs('cli_directo_ejecutivo_id','Ejecutivo del Cliente');

            $tramite_crud->setDependentRelation('cli_directo_ejecutivo_id','cli_directo_id','cli_directo_id');

            /* SELECT Se configura el municipio */
            $tramite_crud->setRelation('ent_municipio_id', 'rel_ent_municipio', 'ent_municipality');
            $tramite_crud->displayAs('ent_municipio_id','Municipio');

            /* SELECT Se configura la empresa gestora */
            $tramite_crud->setRelation('empresa_gestora_id', 'ges_empresa_gestora', 'razon_social');
            $tramite_crud->displayAs('empresa_gestora_id','Empresa Gestora');

            /* SELECT Se configura el gestor*/
            $tramite_crud->setRelation('gestor_id', 'ges_gestor', 'nombre');
            $tramite_crud->displayAs('gestor_id','Gestor');

            $tramite_crud->setDependentRelation('gestor_id','empresa_gestora_id','empresa_gestora_id');

            $tramite_crud->setActionButton('Editar', 'fas fa-pencil-alt', function ($row) {
                return '/deskapp/tramites/update/' . $row->id;
            }, false);

            $tramite_salida = $tramite_crud->render();
            
            $salida_total = array_merge((array)$tramite_salida, $data);
            //$salida_total['insert_button_url'] = '/public/deskapp/tramites/add';

            echo $this->_example_output($salida_total);

        } catch (\Exception $e) {
            exit($e->getMessage());
        }
    }

    public function en_tramite()
    {
        try {
            # Manejo de session de action
            $self = $this;
            $session = session();
            $data['session'] = \Config\Services::session();
            $data['username'] = $session->get('user_name');
            $myid = $session->get('id');
            # fin del manejo de session

            $tramite_crud = $this->_getGroceryCrudEnterprise();
            $tramite_crud->unsetAdd();    
            $tramite_crud->unsetEdit();
            $tramite_crud->unsetDelete();
            $tramite_crud->setCsrfTokenName(csrf_token());
            $tramite_crud->setCsrfTokenValue(csrf_hash());
            
            $tramite_crud->setTable('tramite');
            $tramite_crud->setSubject('tramite', 'Mis Tramites');

            $tramite_crud->where([
                'tramite.tra_status_id = ?' => [22]
            ]);   

            $tramite_crud->defaultOrdering('tramite.id', 'desc');
            
            $tramite_crud->columns([
                'created_at', 'started_at', 'id', 'folio','contrato','unidad','serie', 
                'placas','tra_tipos_id','ent_municipio_id','cli_directo_id',
                'cli_directo_ejecutivo_id','empresa_gestora_id','gestor_id',
                'tra_status_id','cobro_status_id', 'user_id',
                'observaciones'
            ]);

            $tramite_crud->displayAs("started_at", "Desde Asignación");
            $tramite_crud->displayAs('created_at','Creación'); 
            $tramite_crud->setRelation('user_id', 'users', '{firstname} {midname} {lastname}');
            $tramite_crud->displayAs("user_id", "Ejecutivo");
            $tramite_crud->callbackColumn('started_at', function ($value, $row) {
                $fechaAsignacion = new \DateTime($row->started_at);
                $fechaActual = new \DateTime();
                $diasDiferencia = $fechaAsignacion->diff($fechaActual)->days;
            
                // Definir clases CSS según los días
                $claseVerde = 'background-verde';  // Clase CSS para verde
                $claseAmarillo = 'background-amarillo';  // Clase CSS para amarillo
                $claseRojo = 'background-rojo';  // Clase CSS para rojo
                $claseVioleta = 'background-violeta';  // Clase CSS para violeta
            
                // Determinar si es Local o Foráneo
                $local = ($row->ent_municipio_id >= 266 && $row->ent_municipio_id <= 281) || 
                ($row->ent_municipio_id >= 657 && $row->ent_municipio_id <= 781);
                
                // Determinar la clase CSS basada en los días de diferencia y si es Local o Foráneo
                if ($local) {
                    if ($diasDiferencia < 5) {
                        $clase = $claseVerde;
                    } elseif ($diasDiferencia < 8) {
                        $clase = $claseAmarillo;
                    } elseif ($diasDiferencia < 12) {
                        $clase = $claseRojo;
                    } else {
                        $clase = $claseVioleta;
                    }
                } else {
                    if ($diasDiferencia < 10) {
                        $clase = $claseVerde;
                    } elseif ($diasDiferencia < 13) {
                        $clase = $claseAmarillo;
                    } elseif ($diasDiferencia < 16) {
                        $clase = $claseRojo;
                    } else {
                        $clase = $claseVioleta;
                    }
                }
            
                return '<span class="' . $clase . '">' . $diasDiferencia . ' días</span>';
            });

            $tramite_crud->fields([
                'folio','contrato','unidad','serie', 
                'placas','tra_tipos_id','ent_municipio_id','cli_directo_id',
                'cli_directo_ejecutivo_id','empresa_gestora_id','gestor_id',
                'tra_status_id','cobro_status_id',
                'observaciones', 'user_id'
            ]); 
            // $tramite_crud->readOnlyFields(["folio"]);
            $tramite_crud->unsetDeleteMultiple();
            
            /* SELECT Se configura el tipo de tramite */
            $tramite_crud->setRelation('tra_tipos_id', 'tra_tipos', 'tipo_tramite');
            $tramite_crud->displayAs('tra_tipos_id','Tipo de Tramite');

            /* SELECT Se configura el estatus del tramite */
            $tramite_crud->setRelation('tra_status_id', 'tra_status', 'tra_status');
            $tramite_crud->displayAs('tra_status_id','Estatus del Tramite');

            /* SELECT Se configura el cliente final o cliente directo */
            $tramite_crud->setRelation('cli_directo_id', 'cli_directo', 'razon_social');
            $tramite_crud->displayAs('cli_directo_id','Cliente Directo');
            
            /* SELECT Se configura el ejecutivo del cliente */
            $tramite_crud->setRelation('cli_directo_ejecutivo_id', 'cli_directo_ejecutivo', 'nombre');
            $tramite_crud->displayAs('cli_directo_ejecutivo_id','Ejecutivo del Cliente');

            $tramite_crud->setDependentRelation('cli_directo_ejecutivo_id','cli_directo_id','cli_directo_id');

            /* SELECT Se configura el municipio */
            $tramite_crud->setRelation('ent_municipio_id', 'rel_ent_municipio', 'ent_municipality');
            $tramite_crud->displayAs('ent_municipio_id','Municipio');

            /* SELECT Se configura la empresa gestora */
            $tramite_crud->setRelation('empresa_gestora_id', 'ges_empresa_gestora', 'razon_social');
            $tramite_crud->displayAs('empresa_gestora_id','Empresa Gestora');

            /* SELECT Se configura el gestor*/
            $tramite_crud->setRelation('gestor_id', 'ges_gestor', 'nombre');
            $tramite_crud->displayAs('gestor_id','Gestor');

            $tramite_crud->setDependentRelation('gestor_id','empresa_gestora_id','empresa_gestora_id');

            $tramite_crud->setActionButton('Editar', 'fas fa-pencil-alt', function ($row) {
                return '/deskapp/tramites/update/' . $row->id;
            }, false);

            $tramite_salida = $tramite_crud->render();
            
            $salida_total = array_merge((array)$tramite_salida, $data);

            echo $this->_example_output($salida_total);

        } catch (\Exception $e) {
            exit($e->getMessage());
        }
    }

    public function getGestoresByEmpresaId($empresaGestoraId)
    {
        try {
            $db2 = $this->_getDbData();
            $gestorModel = new GestorModel($db2);
            $options = $gestorModel->getGestoresOptions($empresaGestoraId);

            return $this->response->setJSON($options);
        } catch (\Exception $e) {
            // Logging the error for debugging purposes
            // log_message('error', $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['error' => 'An error occurred']);
        }
    }

    public function ultimos_seis_digitos() {
        $tiempo = time();
        $tiempo_str = (string) $tiempo;
        $ultimos_seis = substr($tiempo_str, -6);
        return $ultimos_seis;
    }

    public function encontrarDiferencias($datos1, $datos2) {
        $diferencias = [];
        foreach ($datos1 as $clave => $valor) {
            // Verificar si la clave existe en el segundo conjunto de datos y si los valores son diferentes
            if (array_key_exists($clave, $datos2) && $datos2[$clave] !== $valor) {
                $diferencias[$clave] = [
                    'valor_original' => $valor,
                    'valor_nuevo' => $datos2[$clave]
                ];
            } else {
                // Si la clave no existe en el segundo conjunto de datos, agregarla con valores vacíos
                $diferencias[$clave] = [
                    'valor_original' => $valor,
                    'valor_nuevo' => ''
                ];
            }
        }
        return $diferencias;
    }
    
    public function flattenObject($object, &$result = [], $prefix = '') {
        foreach ($object as $key => $value) {
            if (is_object($value)) {
                // $this->flattenObject($value, $result, $prefix . $key . '_');
            } else {
                $result[$key] = $value;
            }
        }
        return $result;
    }

    public function tipo()
    {
        $session = session();
        $data['session'] = \Config\Services::session();
        $data['username'] = $session->get('user_name');
    
        $crud = $this->_getGroceryCrudEnterprise();

        $crud->setCsrfTokenName(csrf_token());
        $crud->setCsrfTokenValue(csrf_hash());

        $crud->setTable('tra_tipos');
        $crud->setSubject('Tipo de Tramite', 'Tipos de Tramite');

        // $crud->columns(["tipo_tramite", "descripcion"]); 
        // $crud->fields(["tipo_tramite", "descripcion"]);

        $crud->setRelationNtoN(
            "Documentos",
            "tra_tipo_documentos",
            "documento",
            "tra_tipos_id",
            "documento_id", 
            "documento"
        );
        $crud->unsetFields(["created_at", "updated_at", "user_id"]);
        $crud->unsetColumns(["created_at", "updated_at", "user_id"]);

        $crud->callbackAddForm(function ($data) {
            $session = session();
            $myid = $session->get('id');
            $data['user_id'] = $myid;
            return $data;
        });
         // Callbacks para registrar el log
         $crud->callbackAfterInsert(function ($stateParameters) use ($crud) {
            $tableName = $crud->getTable();
            return logOperation($stateParameters, $tableName);
        });
        $crud->callbackAfterUpdate(function ($stateParameters) use ($crud) {
            $tableName = $crud->getTable();
            return logOperation($stateParameters, $tableName);
        });
        $crud->callbackAfterDelete(function ($stateParameters) use ($crud) {
            $tableName = $crud->getTable();
            return logOperation($stateParameters, $tableName);
        });
        $salida = $crud->render();
        $salida2 = array_merge((array)$salida, $data);
        return $this->_example_output($salida2);
    }

    public function status()
    {
        $session = session();
        $data['session'] = \Config\Services::session();
        $data['username'] = $session->get('user_name');
    
        $crud = $this->_getGroceryCrudEnterprise();

        $crud->setCsrfTokenName(csrf_token());
        $crud->setCsrfTokenValue(csrf_hash());

        $crud->setTable('tra_status');
        $crud->setSubject('Etatus de Tramite', 'Estatuses de Tramite');

        // Callbacks para registrar el log
        $crud->callbackAfterInsert(function ($stateParameters) use ($crud) {
            $tableName = $crud->getTable();
            return logOperation($stateParameters, $tableName);
        });
        $crud->callbackAfterUpdate(function ($stateParameters) use ($crud) {
            $tableName = $crud->getTable();
            return logOperation($stateParameters, $tableName);
        });
        $crud->callbackAfterDelete(function ($stateParameters) use ($crud) {
            $tableName = $crud->getTable();
            return logOperation($stateParameters, $tableName);
        });

        $salida = $crud->render();
        $salida2 = array_merge((array)$salida, $data);
        return $this->_example_output($salida2);
    }

    public function documentostatus()
    {
        $self = $this;
        $request = \Config\Services::request();
        $uri = $request->getUri();
        $folio_tramite = $uri->getSegment(4);
        $tramite_id = (int) $uri->getSegment(5);

        $db = Database::connect();
        $session = session();
        $data['session'] = \Config\Services::session();
        $data['username'] = $session->get('user_name');
    
        $crud = $this->_getGroceryCrudEnterprise();

        $crud->setCsrfTokenName(csrf_token());
        $crud->setCsrfTokenValue(csrf_hash());

        $crud->setTable('tra_doc_status');
        $crud->setSubject('Documento', 'Documentos');

        $crud->where([
            'folio_tramite' => $folio_tramite
        ]);        

        $crud->fieldType('folio_tramite','hidden');
        $crud->fieldType('tramite_id','hidden');
        
        /* SELECT Se configura el documento */
        $crud->setRelation('documento_id', 'documento', 'documento');
        $crud->displayAs('documento_id','Documento');

        /* SELECT Se configura el doc_status */
        $crud->setRelation('status_documento_id', 'doc_statuses', 'st_documento');
        $crud->displayAs('status_documento_id','Status del Documento');

        $crud->callbackEditForm(function ($data) use ($self){
            $session = session();
            $data2 = $data;
            $data3 = $data2->getArrayCopy();
            $flatArray = $self->flattenObject($data3);
            $session->set('data_documents_before_update',  $flatArray);
            $session->set('doc_tramite_id',  $flatArray["id"]);
            return $data;
        });

        $crud->callbackAfterUpdate(function ($stateParameters) use ($self){
            $db = Database::connect();
            $db2 = $this->_getDbData();
            $session = session();
            $data = $stateParameters->data;
            $myid = $session->get('id');

            $bitacoraModel = new BitacoraModel($db2);
            $data_bitacora = $data;            

            $data_prev = $session->get('data_documents_before_update');
            $tramite_id = $session->get('doc_tramite_id');
            $diferencias = $self->encontrarDiferencias($data_prev, $data_bitacora);
            $diferencias["documento_id"] = $data["documento_id"];
            $insert_bitacora = [
                "tipo" => "update",
                "origen"=>"documentos",
                "folio_tramite" => $data['folio_tramite'],
                "tramite_id" => (int)$tramite_id,
                "cambios" => json_encode($diferencias),
                "user_id" => (int)$myid
            ];
            $result = $bitacoraModel->insert($insert_bitacora, 'bitacora');

        });

        $uploadValidations = [
            'maxUploadSize' => '20M', // 20 Mega Bytes
            'minUploadSize' => '1K', // 1 Kilo Byte
            'allowedFileTypes' => [
                'gif', 'jpeg', 'jpg', 'png', 'tiff', 'pdf', 'xml'
            ]
        ];

        $crud->setFieldUploadMultiple(
            'file', 
            'assets/uploads/documentostatus/', 
            '/assets/uploads/documentostatus/', 
            $uploadValidations
        );

        $crud->fieldType('user_id','hidden');

        $crud->callbackAddForm(function ($data) {
            $request = \Config\Services::request();
            $uri = $request->getUri();
            $folio_tramite = $uri->getSegment(4);
            $tramite_id = (int) $uri->getSegment(5);

            $session = session();
            $myid = $session->get('id');
            $data['user_id'] = $myid;
            $data['folio_tramite'] = $folio_tramite;
            $data['tramite_id'] = $tramite_id;
            return $data;
        });

        $salida = $crud->render();
        $salida2 = array_merge((array)$salida, $data);
        return $this->_example_output($salida2);
    }

    public function single_documentostatus()
    {
        $session = session();
        $data['session'] = \Config\Services::session();
        $data['username'] = $session->get('user_name');
        $db = Database::connect();
        $db2 = $this->_getDbData();
        $self = $this;

        $request = \Config\Services::request();
        $uri = $request->getUri();
        $tramite_id = (int) $uri->getSegment(4);
        $tramiteModel = new TramitesModel($db2);
        $folio_tramite = $tramiteModel->getFolioById($tramite_id);
        $session->set('folio_tramite_id',  $folio_tramite);

        // Verificar si se encontró un folio
        if (!$folio_tramite) {
            throw new \Exception('No existe el folio');
        } 

        $crud = $this->_getGroceryCrudEnterprise();
        $crud->setCsrfTokenName(csrf_token());
        $crud->setCsrfTokenValue(csrf_hash());
        $crud->setTable('tra_doc_status');
        $crud->setSubject('Documento', 'Documentos');
        $crud->defaultOrdering('tra_doc_status.created_at', 'desc');

        $crud->fields([
            "tramite_id", "folio_tramite", "documento_id", "file", "comentario", "status_documento_id"
        ]); 

        $crud->columns([
            "created_at", "documento_id", "file", "comentario"
        ]);

        $crud->displayAs('created_at','Creación');

        $crud->fieldType('tramite_id','hidden');
        $crud->fieldType('status_documento_id','hidden');
        
        $crud->where([
            'folio_tramite' => $folio_tramite
        ]);        
        $crud->readOnlyFields(['folio_tramite']);
        
        $crud->fieldType('user_id','hidden');
        $crud->callbackBeforeInsert(function ($stateParameters) {
            $session = session();
            $folio_tramite = $session->get('folio_tramite_id');
            $stateParameters->data['folio_tramite'] = $folio_tramite;
            return $stateParameters;
        });    
        $crud->callbackBeforeUpdate(function ($stateParameters) {
            $session = session();
            $folio_tramite = $session->get('folio_tramite_id');
            $stateParameters->data['folio_tramite'] = $folio_tramite;
            return $stateParameters;
        });    
        /* SELECT Se configura el documento */
        $crud->setRelation('documento_id', 'documento', 'documento');
        $crud->displayAs('documento_id','Documento');

        /* SELECT Se configura el doc_status */
        $crud->setRelation('status_documento_id', 'doc_statuses', 'st_documento');
        $crud->displayAs('status_documento_id','Status del Documento');

        $crud->callbackEditForm(function ($data) use ($self){
            $session = session();
            $data2 = $data;
            $data3 = $data2->getArrayCopy();
            $flatArray = $self->flattenObject($data3);
            $session->set('data_documents_before_update',  $flatArray);
            $session->set('doc_tramite_id',  $flatArray["id"]);
            return $data;
        });

        $crud->callbackAfterUpdate(function ($stateParameters) use ($self){
            $db2 = $this->_getDbData();
            $session = session();
            $data = $stateParameters->data;
            $myid = $session->get('id');

            $bitacoraModel = new BitacoraModel($db2);
            $data_bitacora = $data;            

            $data_prev = $session->get('data_documents_before_update');
            $tramite_id = $session->get('doc_tramite_id');
            $diferencias = $self->encontrarDiferencias($data_prev, $data_bitacora);
            $diferencias["documento_id"] = $data["documento_id"];
            $insert_bitacora = [
                "tipo" => "update",
                "origen"=>"documentos",
                "folio_tramite" => $data['folio_tramite'],
                "tramite_id" => (int)$tramite_id,
                "cambios" => json_encode($diferencias),
                "user_id" => (int)$myid
            ];
            $result = $bitacoraModel->insert($insert_bitacora, 'bitacora');

        });

        $uploadValidations = [
            'maxUploadSize' => '20M', // 20 Mega Bytes
            'minUploadSize' => '1K', // 1 Kilo Byte
            'allowedFileTypes' => [
                'gif', 'jpeg', 'jpg', 'png', 'tiff', 'pdf', 'xml'
            ]
        ];

        $crud->setFieldUploadMultiple(
            'file', 
            'assets/uploads/documentostatus/', 
            '/assets/uploads/documentostatus/', 
            $uploadValidations
        );

        $crud->fieldType('user_id','hidden');

        $crud->callbackAddForm(function ($data) {
            $request = \Config\Services::request();
            $uri = $request->getUri();
            $tramite_id = $uri->getSegment(4);

            $session = session();
            $myid = $session->get('id');
            $folio_tramite = $session->get('folio_tramite_id');
            $data['user_id'] = $myid;
            $data['folio_tramite'] = $folio_tramite;
            $data['tramite_id'] = $tramite_id;
            $data['status_documento_id'] = 3;
            return $data;
        });

        // Callbacks para registrar el log
        $crud->callbackAfterInsert(function ($stateParameters) use ($crud) {
            $tableName = $crud->getTable();
            return logOperation($stateParameters, $tableName);
        });
        $crud->callbackAfterUpdate(function ($stateParameters) use ($crud) {
            $tableName = $crud->getTable();
            return logOperation($stateParameters, $tableName);
        });
        $crud->callbackAfterDelete(function ($stateParameters) use ($crud) {
            $tableName = $crud->getTable();
            return logOperation($stateParameters, $tableName);
        });

        $salida = $crud->render();
        $salida2 = array_merge((array)$salida, $data);
        return $this->_example_output($salida2);
    }

    public function evidencias()
    {
        $self = $this;
        $request = \Config\Services::request();
        $uri = $request->getUri();
        $folio_tramite = $uri->getSegment(4);
        $tramite_id = (int) $uri->getSegment(5);

        $db = Database::connect();
        $session = session();
        $data['session'] = \Config\Services::session();
        $data['username'] = $session->get('user_name');
    
        $crud = $this->_getGroceryCrudEnterprise();
        $crud->setCsrfTokenName(csrf_token());
        $crud->setCsrfTokenValue(csrf_hash());

        $crud->setTable('tra_evidencias');
        $crud->setSubject('Bitacora', 'Bitacora');

        $crud->where([
            'folio_tramite' => $folio_tramite
        ]);   

        $crud->callbackAfterInsert(function ($stateParameters)  use ($self) {
            if (is_object($stateParameters) && property_exists($stateParameters, 'insertId')) {
                $parameters = $stateParameters;
                $db = Database::connect();
                $db2 = $this->_getDbData();
                $data = $parameters->data;

                $request = \Config\Services::request();
                $uri = $request->getUri();
                $folio_tramite = $uri->getSegment(4);
                $tramite_id = (int) $uri->getSegment(5);

                $session = session();
                $myid = $session->get('id');
                                
                $bitacoraModel = new BitacoraModel($db2);
                $data_bitacora = $data;
                $diferencias = $self->encontrarDiferencias($data_bitacora, []);
                $insert_bitacora = [
                    "id"=>null,
                    "tipo"=>"insert",
                    "origen"=>"evidencia",
                    "folio_tramite" => $folio_tramite,
                    "tramite_id" => (int)$tramite_id,
                    "cambios" => json_encode($diferencias),
                    "user_id" => (int)$myid
                ];
                $result = $bitacoraModel->insert($insert_bitacora, 'bitacora');
            }
            return $stateParameters;
        });

        $crud->callbackColumn('comentario', function($value, $row) {
            return '<div style="white-space: normal;">' . htmlspecialchars($value, ENT_QUOTES) . '</div>';
        });

        $crud->callbackEditForm(function ($data) use ($self){
            $session = session();
            $data2 = $data;
            $data3 = $data2->getArrayCopy();
            $flatArray = $self->flattenObject($data3);
            $session->set('data_evidencias_before_update',  $flatArray);
            $session->set('doc_tramite_evidencia_id',  $flatArray["id"]);
            return $data;
        });

        $crud->callbackAfterUpdate(function ($stateParameters) use ($self){
            $db = Database::connect();
            $db2 = $this->_getDbData();
            $session = session();
            $data = $stateParameters->data;
            $myid = $session->get('id');

            $bitacoraModel = new BitacoraModel($db2);
            $data_bitacora = $data;            

            $data_prev = $session->get('data_evidencias_before_update');
            $tramite_id = $session->get('doc_tramite_evidencia_id');
            $diferencias = $self->encontrarDiferencias($data_prev, $data_bitacora);
            $insert_bitacora = [
                "tipo" => "update",
                "origen"=>"evidencia",
                "folio_tramite" => $data['folio_tramite'],
                "tramite_id" => (int)$tramite_id,
                "cambios" => json_encode($diferencias),
                "user_id" => (int)$myid
            ];
            $result = $bitacoraModel->insert($insert_bitacora, 'bitacora');

        });

        $uploadValidations = [
            'maxUploadSize' => '20M', // 20 Mega Bytes
            'minUploadSize' => '1K', // 1 Kilo Byte
            'allowedFileTypes' => [
                'gif', 'jpeg', 'jpg', 'png', 'tiff', 'pdf', 'xml'
            ]
        ];

        $crud->setFieldUploadMultiple(
            'file', 
            'assets/uploads/evidencias/', 
            '/assets/uploads/evidencias/', 
            $uploadValidations
        );

        $crud->fieldType('user_id','hidden');

        $crud->fieldType('folio_tramite','hidden');
        $crud->fieldType('tramite_id','hidden');

        $crud->callbackAddForm(function ($data) {
            $request = \Config\Services::request();
            $uri = $request->getUri();
            $folio_tramite = $uri->getSegment(4);
            $tramite_id = (int) $uri->getSegment(5);
            $session = session();
            $myid = $session->get('id');
            $data['user_id'] = $myid;
            $data['folio_tramite'] = $folio_tramite;
            $data['tramite_id'] = $tramite_id;
            return $data;
        });

        $salida = $crud->render();
        $salida2 = array_merge((array)$salida, $data);
        return $this->_example_output($salida2);
    }

    public function single_evidencias(){
        $session = session();
        $data['session'] = \Config\Services::session();
        $data['username'] = $session->get('user_name');
        $db2 = $this->_getDbData();
        $self = $this;
        $request = \Config\Services::request();

        $uri = $request->getUri();
        $tramite_id = (int) $uri->getSegment(4);
        $tramiteModel = new TramitesModel($db2);
        $folio_tramite = $tramiteModel->getFolioById($tramite_id);
        $session->set('folio_tramite_id',  $folio_tramite);

        // Verificar si se encontró un folio
        if (!$folio_tramite) {
            throw new \Exception('No existe el folio');
        } 

        $db = Database::connect();
    
        $crud = $this->_getGroceryCrudEnterprise();
        $crud->setCsrfTokenName(csrf_token());
        $crud->setCsrfTokenValue(csrf_hash());

        $crud->setTable('tra_evidencias');
        $crud->setSubject('Bitacora', 'Bitacora');
        $crud->defaultOrdering('tra_evidencias.created_at', 'desc');

        $crud->fields([
            "folio_tramite", "tramite_id", "comentario", "user_id"
        ]); 

        $crud->columns([
            "created_at", "id", "comentario", "user_id"
        ]);

        $crud->setRelation('user_id', 'users', '{firstname} {midname} {lastname}');

        $crud->where([
            'folio_tramite' => $folio_tramite
        ]);   
        $crud->callbackColumn('comentario', function($value, $row) {
            // Recortar el texto si es muy largo
            $shortened_value = strlen($value) > 50 ? substr($value, 0, 50) . '...' : $value;
        
            // Retornar el texto con un tooltip para ver el contenido completo
            return '<span title="' . htmlspecialchars($value, ENT_QUOTES) . '">' . $shortened_value . '</span>';
        });
        // $crud->callbackColumn('comentario', function($value, $row) {
        //     return '<div style="white-space: normal;">' . htmlspecialchars($value, ENT_QUOTES) . '</div>';
        // });


        $crud->callbackAfterInsert(function ($stateParameters)  use ($self) {
            if (is_object($stateParameters) && property_exists($stateParameters, 'insertId')) {
                $session = session();
                $parameters = $stateParameters;
                $db = Database::connect();
                $db2 = $this->_getDbData();
                $data = $parameters->data;
                $request = \Config\Services::request();
                $uri = $request->getUri();
                $tramite_id = (int) $uri->getSegment(4);
                $folio_tramite = $session->get('folio_tramite_id');

                $myid = $session->get('id');
                                
                $bitacoraModel = new BitacoraModel($db2);
                $data_bitacora = $data;
                $diferencias = $self->encontrarDiferencias($data_bitacora, []);
                $insert_bitacora = [
                    "id"=>null,
                    "tipo"=>"insert",
                    "origen"=>"evidencia",
                    "folio_tramite" => $folio_tramite,
                    "tramite_id" => (int)$tramite_id,
                    "cambios" => json_encode($diferencias),
                    "user_id" => (int)$myid
                ];
                $result = $bitacoraModel->insert($insert_bitacora, 'bitacora');
            }
            return $stateParameters;
        });

        $crud->callbackEditForm(function ($data) use ($self){
            $session = session();
            $data2 = $data;
            $data3 = $data2->getArrayCopy();
            $flatArray = $self->flattenObject($data3);
            $session->set('data_evidencias_before_update',  $flatArray);
            $session->set('doc_tramite_evidencia_id',  $flatArray["id"]);
            return $data;
        });

        $crud->callbackAfterUpdate(function ($stateParameters) use ($self){
            $db = Database::connect();
            $db2 = $this->_getDbData();
            $session = session();
            $data = $stateParameters->data;
            $myid = $session->get('id');
            
            $request = \Config\Services::request();
            $uri = $request->getUri();
            $tramite_id = (int) $uri->getSegment(4);
            $folio_tramite = $session->get('folio_tramite_id');

            $bitacoraModel = new BitacoraModel($db2);
            $data_bitacora = $data;
            $diferencias = $self->encontrarDiferencias($data_bitacora, []);
            $insert_bitacora = [
                "tipo"=>"update",
                "origen"=>"evidencia",
                "folio_tramite" => $folio_tramite,
                "tramite_id" => (int)$tramite_id,
                "cambios" => json_encode($diferencias),
                "user_id" => (int)$myid
            ];
            $result = $bitacoraModel->insert($insert_bitacora, 'bitacora');
        });

        $uploadValidations = [
            'maxUploadSize' => '20M', // 20 Mega Bytes
            'minUploadSize' => '1K', // 1 Kilo Byte
            'allowedFileTypes' => [
                'gif', 'jpeg', 'jpg', 'png', 'tiff', 'pdf', 'xml'
            ]
        ];

        $crud->setFieldUploadMultiple(
            'file', 
            'assets/uploads/evidencias/', 
            '/assets/uploads/evidencias/', 
            $uploadValidations
        );

        $crud->fieldType('user_id','hidden');
        $crud->fieldType('folio_tramite','hidden');
        $crud->fieldType('tramite_id','hidden');
        $crud->callbackBeforeInsert(function ($stateParameters) {
            $session = session();
            $folio_tramite = $session->get('folio_tramite_id');
            $stateParameters->data['folio_tramite'] = $folio_tramite;
            return $stateParameters;
        });    
        $crud->callbackBeforeUpdate(function ($stateParameters) {
            $session = session();
            $folio_tramite = $session->get('folio_tramite_id');
            $stateParameters->data['folio_tramite'] = $folio_tramite;
            return $stateParameters;
        });  
        $crud->callbackAddForm(function ($data) {
            $session = session();

            $request = \Config\Services::request();
            $uri = $request->getUri();

            $tramite_id = (int) $uri->getSegment(4);
            $folio_tramite = $session->get('folio_tramite_id');

            $myid = $session->get('id');
            $data['user_id'] = $myid;
            $data['folio_tramite'] = $folio_tramite;
            $data['tramite_id'] = $tramite_id;
            return $data;
        });
        // Callbacks para registrar el log
        $crud->callbackAfterInsert(function ($stateParameters) use ($crud) {
            $tableName = $crud->getTable();
            return logOperation($stateParameters, $tableName);
        });
        $crud->callbackAfterUpdate(function ($stateParameters) use ($crud) {
            $tableName = $crud->getTable();
            return logOperation($stateParameters, $tableName);
        });
        $crud->callbackAfterDelete(function ($stateParameters) use ($crud) {
            $tableName = $crud->getTable();
            return logOperation($stateParameters, $tableName);
        });

        $salida = $crud->render();
        $salida2 = array_merge((array)$salida, $data);
        return $this->_example_output($salida2);
    }

    public function single_pago_derechos(){
        $session = session();
        $data['session'] = \Config\Services::session();
        $data['username'] = $session->get('user_name');
        $db2 = $this->_getDbData();
        $self = $this;
        $request = \Config\Services::request();

        $uri = $request->getUri();
        $tramite_id = (int) $uri->getSegment(4);
    
        // Paso 1: Definir directorios
        $sourceDir = FCPATH . 'assets/uploads/pago_derechos/';
        $targetDir = FCPATH . 'assets/uploads/pago_derechos/' . $tramite_id . '/';

        // Verificar si el directorio de destino existe
        if (!is_dir($targetDir)) {
            // Intentar crear el directorio con permisos 777
            if (!mkdir($targetDir, 0777, true)) {
                return json_encode([
                    'status' => 'error',
                    'message' => 'No se pudo crear el directorio de destino. Verifica permisos.'
                ]);
            }
        }
        chmod($targetDir, 0777); // Asegurar permisos
        // Paso 2: Consultar las imágenes vinculadas en la tabla tra_pago_derechos
        $db = \Config\Database::connect();
        $builder = $db->table('tra_pago_derechos');
        $builder->select('file');
        $builder->where('tramite_id', $tramite_id);
        $query = $builder->get();
        $files = $query->getResultArray();

        foreach ($files as $file) {
            $fileName = $file['file'];
            $sourceFilePath = $sourceDir . $fileName;
            $targetFilePath = $targetDir . $fileName;

            // Verificar si el archivo existe en el directorio fuente
            if (file_exists($sourceFilePath)) {
                // Copiar archivo solo si no existe en el directorio destino
                if (!file_exists($targetFilePath)) {
                    if (!copy($sourceFilePath, $targetFilePath)) {
                        return json_encode([
                            'status' => 'error',
                            'message' => "Error al copiar el archivo $fileName a $targetDir."
                        ]);
                    }
                }
            } 

        }

        $crud = $this->_getGroceryCrudEnterprise();
        $crud->setSkin('bootstrap');
        $crud->setCsrfTokenName(csrf_token());
        $crud->setCsrfTokenValue(csrf_hash());
        $crud->unsetRead();

        // $tramite_crud->setTheme('bootstrap-v5');
        $crud->unsetDeleteMultiple();
        // $crud->unsetDelete();
        $crud->unsetAdd();
        $crud->unsetExport();
        $crud->unsetPrint();
        // $crud->unsetFilters();
        $crud->unsetClone();
        $crud->setTable('tra_pago_derechos');
        $crud->setSubject('Pago', 'Pagos de Derechos');
        $crud->defaultOrdering('tra_pago_derechos.created_at', 'desc');

        $crud->fields([
            "file", "costo", "comentario", "tramite_id", "user_id"
        ]); 

        $crud->columns([
            "id", "created_at", "file", "costo", "comentario", "user_id"
        ]);

        $crud->setRelation('user_id', 'users', '{firstname} {midname} {lastname}');

        $crud->where([
            'tramite_id' => $tramite_id
        ]);   

        $crud->callbackAfterInsert(function ($stateParameters)  use ($self) {
            if (is_object($stateParameters) && property_exists($stateParameters, 'insertId')) {
                $session = session();
                $parameters = $stateParameters;
                $db2 = $this->_getDbData();
                $data = $parameters->data;
                $request = \Config\Services::request();
                $uri = $request->getUri();
                $tramite_id = (int) $uri->getSegment(4);

                $myid = $session->get('id');
                                
                $bitacoraModel = new BitacoraModel($db2);
                $data_bitacora = $data;
                $diferencias = $self->encontrarDiferencias($data_bitacora, []);
                $insert_bitacora = [
                    "id"=>null,
                    "tipo"=>"insert",
                    "origen"=>"derechos",
                    "tramite_id" => (int)$tramite_id,
                    "cambios" => json_encode($diferencias),
                    "user_id" => (int)$myid
                ];
                $result = $bitacoraModel->insert($insert_bitacora, 'bitacora');
            }
            return $stateParameters;
        });

        $crud->callbackEditForm(function ($data) use ($self){
            $session = session();
            $data2 = $data;
            $data3 = $data2->getArrayCopy();
            $flatArray = $self->flattenObject($data3);
            $session->set('data_evidencias_before_update',  $flatArray);
            $session->set('doc_tramite_evidencia_id',  $flatArray["id"]);
            return $data;
        });

        $crud->callbackAfterUpdate(function ($stateParameters) use ($self) {
            $db = \Config\Database::connect();
            $session = session();
            $request = \Config\Services::request();
        
            // Datos generales
            $userId = $session->get('id'); // ID del usuario actual
            $uri = $request->getUri(); // URI completa
            $method = $request->getMethod(); // Método HTTP
            $endpoint = $uri->getPath(); // Ruta completa como endpoint
            $primaryKeyValue = $stateParameters->primaryKeyValue; // ID afectado
            $data = $stateParameters->data; // Datos enviados desde Grocery CRUD
        
            // Extraer controlador y acción de la URL
            $pathParts = explode('/', $endpoint);
            $controller = $pathParts[1] ?? null; // El segundo segmento como controlador
            $action = $pathParts[2] ?? null; // El tercer segmento como acción
        
            // Buscar números en la URL
            preg_match_all('/\d+/', $endpoint, $matches);
            $numbers = $matches[0] ?? [];
        
            // Obtener ID principal y números adicionales
            $sent_id = $numbers[0] ?? 0; // Primer número
            $additional_ids = array_slice($numbers, 1); // Números adicionales
        
            // Determinar las diferencias de datos
            $oldData = []; // Aquí puedes incluir lógica para obtener datos anteriores si es necesario
            $diferencias = $self->encontrarDiferencias($data, $oldData);
        
            // Obtener la respuesta del estado de Grocery CRUD
            $response = [
                'success' => $stateParameters->success,
                'message' => $stateParameters->message,
                'data'    => $stateParameters->data,
            ];
        
            // Registrar el log
            $logModel = new \App\Models\ApiLogModel($db);
            $logData = [
                'method'     => $method,
                'endpoint'   => $endpoint,
                'controller' => $controller,
                'action'     => $action,
                'sent_id'    => $sent_id,
                'vista'      => implode(',', $additional_ids), // Números adicionales concatenados
                'body'       => json_encode($data), // Datos enviados a Grocery CRUD
                'response'   => json_encode($response), // Respuesta generada por Grocery CRUD
                'user_id'    => $userId,
                'ip_address' => $request->getIPAddress(),
                'user_agent' => $request->getUserAgent()->getAgentString(),
            ];
        
            $logModel->insert($logData);
        
            return $stateParameters; // Continuar con el flujo normal
        });

        $uploadValidations = [
            'maxUploadSize' => '20M', // 20 Mega Bytes
            'minUploadSize' => '1K', // 1 Kilo Byte
            'allowedFileTypes' => [
                'gif', 'jpeg', 'jpg', 'png', 'tiff', 'pdf', 'xml'
            ]
        ];

        $crud->setFieldUpload(
            'file', 
            'assets/uploads/pago_derechos/'.$tramite_id.'/', 
            '/assets/uploads/pago_derechos/'.$tramite_id.'/', 
            $uploadValidations
        );

        $crud->callbackBeforeDelete(function ($stateParameters) {
            // Access the primary key value directly from the $stateParameters object
            // var_dump($stateParameters->primaryKeyValue);die();

            $primaryKeyValue = (int)$stateParameters->primaryKeyValue;
        
            // Log the primary key value for debugging purposes
            // log_message('debug', "Primary Key Value: var_dump($primaryKeyValue)");
        
            // Database connection
            $db = \Config\Database::connect();
        
            // Query to retrieve file and tramite_id using the primary key
            $builder = $db->table('tra_pago_derechos');
            $builder->select('file, tramite_id');
            $builder->where('id', $primaryKeyValue);
        
            $query = $builder->get();
            $row = $query->getRowArray();
        
            if ($row) {
                $tramite_id = $row['tramite_id'];
                $fileName = $row['file'];
        
                // Define the base image path
                $baseImagePath = FCPATH . 'assets/uploads/pago_derechos/';
        
                // Ensure tramite_id and fileName are available
                if ($tramite_id && $fileName) {
                    // Construct the full file path
                    $filePath = $baseImagePath . $tramite_id . '/' . $fileName;
        
                    // Check if the file exists
                    if (file_exists($filePath)) {
                        // Attempt to delete the file
                        if (unlink($filePath)) {
                            // log_message('info', "File successfully deleted: $filePath");
                        } else {
                            // log_message('error', "Failed to delete file: $filePath");
                        }
                    } else {
                        // log_message('warning', "File does not exist: $filePath");
                    }
                } else {
                    // log_message('error', "Incomplete data: Tramite ID: $tramite_id, File: $fileName");
                }
            } else {
                // log_message('error', "No record found for Primary Key: $primaryKeyValue");
            }
        
            // Continue with the delete operation
            return $stateParameters;
        });
        
        $crud->fieldType('user_id','hidden');
        $crud->fieldType('tramite_id','hidden');
     
        $crud->callbackAddForm(function ($data) {
            $session = session();
            $request = \Config\Services::request();
            $uri = $request->getUri();
            $tramite_id = (int) $uri->getSegment(4);
            $myid = $session->get('id');
            $data['user_id'] = $myid;
            $data['tramite_id'] = $tramite_id;
            return $data;
        });

        // Callbacks para registrar el log
        $crud->callbackAfterInsert(function ($stateParameters) use ($crud) {
            $tableName = $crud->getTable();
            return logOperation($stateParameters, $tableName);
        });
        $crud->callbackAfterUpdate(function ($stateParameters) use ($crud) {
            $tableName = $crud->getTable();
            return logOperation($stateParameters, $tableName);
        });
        $crud->callbackAfterDelete(function ($stateParameters) use ($crud) {
            $tableName = $crud->getTable();
            return logOperation($stateParameters, $tableName);
        });

        $salida = $crud->render();
        $salida2 = array_merge((array)$salida, $data);
        return $this->_example_output($salida2);
    }

    public function single_pago_gestor(){
        $session = session();
        $data['session'] = \Config\Services::session();
        $data['username'] = $session->get('user_name');
        $db2 = $this->_getDbData();
        $self = $this;
        $request = \Config\Services::request();
    
        $uri = $request->getUri();
        $tramite_id = (int) $uri->getSegment(4);
    
        // Paso 1: Definir directorios
        $sourceDir = FCPATH . 'assets/uploads/pago_gestor/';
        $targetDir = FCPATH . 'assets/uploads/pago_gestor/' . $tramite_id . '/';
    
        // Verificar si el directorio de destino existe
        if (!is_dir($targetDir)) {
            if (!mkdir($targetDir, 0777, true)) {
                return json_encode([
                    'status' => 'error',
                    'message' => 'No se pudo crear el directorio de destino. Verifica permisos.'
                ]);
            }
            chmod($targetDir, 0777); // Asegurar permisos
        }
    
        // Paso 2: Consultar las imágenes vinculadas en la tabla tra_pago_gestor
        $db = \Config\Database::connect();
        $builder = $db->table('tra_pago_gestor');
        $builder->select('file');
        $builder->where('tramite_id', $tramite_id);
        $query = $builder->get();
        $files = $query->getResultArray();
    
        foreach ($files as $file) {
            $fileName = $file['file'];
            $sourceFilePath = $sourceDir . $fileName;
            $targetFilePath = $targetDir . $fileName;
    
            // Verificar si el archivo existe en el directorio fuente
            if (file_exists($sourceFilePath)) {
                // Copiar archivo solo si no existe en el directorio destino
                if (!file_exists($targetFilePath)) {
                    if (!copy($sourceFilePath, $targetFilePath)) {
                        return json_encode([
                            'status' => 'error',
                            'message' => "Error al copiar el archivo $fileName a $targetDir."
                        ]);
                    }
                }
            }
        }
    
        $crud = $this->_getGroceryCrudEnterprise();
        $crud->setSkin('bootstrap');
        $crud->setCsrfTokenName(csrf_token());
        $crud->setCsrfTokenValue(csrf_hash());
        $crud->unsetRead();
    
        $crud->unsetDeleteMultiple();
        $crud->unsetAdd();
        $crud->unsetExport();
        $crud->unsetPrint();
        $crud->unsetClone();
        $crud->setTable('tra_pago_gestor');
        $crud->setSubject('Pago', 'Pagos de Gestor');
        $crud->defaultOrdering('tra_pago_gestor.created_at', 'desc');
    
        $crud->fields([
            "file", "costo", "comentario", "tramite_id", "user_id"
        ]);
    
        $crud->columns([
            "id", "created_at", "file", "costo", "comentario", "user_id"
        ]);
    
        $crud->setRelation('user_id', 'users', '{firstname} {midname} {lastname}');
    
        $crud->where([
            'tramite_id' => $tramite_id
        ]);
    
        $crud->callbackAfterInsert(function ($stateParameters)  use ($self) {
            if (is_object($stateParameters) && property_exists($stateParameters, 'insertId')) {
                $session = session();
                $parameters = $stateParameters;
                $data = $parameters->data;
                $request = \Config\Services::request();
                $uri = $request->getUri();
                $tramite_id = (int) $uri->getSegment(4);
    
                $myid = $session->get('id');
    
                $bitacoraModel = new BitacoraModel($self->_getDbData());
                $data_bitacora = $data;
                $diferencias = $self->encontrarDiferencias($data_bitacora, []);
                $insert_bitacora = [
                    "id"=>null,
                    "tipo"=>"insert",
                    "origen"=>"gestor",
                    "tramite_id" => (int)$tramite_id,
                    "cambios" => json_encode($diferencias),
                    "user_id" => (int)$myid
                ];
                $bitacoraModel->insert($insert_bitacora, 'bitacora');
            }
            return $stateParameters;
        });
    
        $crud->callbackAddForm(function ($data) {
            $session = session();
            $request = \Config\Services::request();
            $uri = $request->getUri();
            $tramite_id = (int) $uri->getSegment(4);
            $myid = $session->get('id');
            $data['user_id'] = $myid;
            $data['tramite_id'] = $tramite_id;
            return $data;
        });

        $uploadValidations = [
            'maxUploadSize' => '20M', // 20 Mega Bytes
            'minUploadSize' => '1K', // 1 Kilo Byte
            'allowedFileTypes' => [
                'gif', 'jpeg', 'jpg', 'png', 'tiff', 'pdf', 'xml'
            ]
        ];

        $crud->setFieldUpload(
            'file', 
            'assets/uploads/pago_gestor/'.$tramite_id.'/', 
            '/assets/uploads/pago_gestor/'.$tramite_id.'/', 
            $uploadValidations
        );

        $crud->callbackBeforeDelete(function ($stateParameters) {
            // Access the primary key value directly from the $stateParameters object
            // var_dump($stateParameters->primaryKeyValue);die();

            $primaryKeyValue = (int)$stateParameters->primaryKeyValue;
        
            // Log the primary key value for debugging purposes
            // log_message('debug', "Primary Key Value: var_dump($primaryKeyValue)");
        
            // Database connection
            $db = \Config\Database::connect();
        
            // Query to retrieve file and tramite_id using the primary key
            $builder = $db->table('tra_pago_gestor');
            $builder->select('file, tramite_id');
            $builder->where('id', $primaryKeyValue);
        
            $query = $builder->get();
            $row = $query->getRowArray();
        
            if ($row) {
                $tramite_id = $row['tramite_id'];
                $fileName = $row['file'];
        
                // Define the base image path
                $baseImagePath = FCPATH . 'assets/uploads/pago_gestor/';
        
                // Ensure tramite_id and fileName are available
                if ($tramite_id && $fileName) {
                    // Construct the full file path
                    $filePath = $baseImagePath . $tramite_id . '/' . $fileName;
        
                    // Check if the file exists
                    if (file_exists($filePath)) {
                        // Attempt to delete the file
                        if (unlink($filePath)) {
                            // log_message('info', "File successfully deleted: $filePath");
                        } else {
                            // log_message('error', "Failed to delete file: $filePath");
                        }
                    } else {
                        // log_message('warning', "File does not exist: $filePath");
                    }
                } else {
                    // log_message('error', "Incomplete data: Tramite ID: $tramite_id, File: $fileName");
                }
            } else {
                // log_message('error', "No record found for Primary Key: $primaryKeyValue");
            }
        
            // Continue with the delete operation
            return $stateParameters;
        });
    
        $salida = $crud->render();
        $salida2 = array_merge((array)$salida, $data);
        return $this->_example_output($salida2);
    }

    public function single_cobro_cliente()
    {
        $session = session();
        $data['session'] = \Config\Services::session();
        $data['username'] = $session->get('user_name');
        $db2 = $this->_getDbData();
        $self = $this;
        $request = \Config\Services::request();

        $uri = $request->getUri();
        $tramite_id = (int) $uri->getSegment(4);

        // Paso 1: Definir directorios
        $sourceDir = FCPATH . 'assets/uploads/cobro_cliente/';
        $targetDir = FCPATH . 'assets/uploads/cobro_cliente/' . $tramite_id . '/';

        // Verificar si el directorio de destino existe
        if (!is_dir($targetDir)) {
            if (!mkdir($targetDir, 0777, true)) {
                return json_encode([
                    'status' => 'error',
                    'message' => 'No se pudo crear el directorio de destino. Verifica permisos.'
                ]);
            }
            chmod($targetDir, 0777); // Asegurar permisos
        }

        // Paso 2: Consultar las imágenes vinculadas en la tabla tra_cobro_cliente
        $db = \Config\Database::connect();
        $builder = $db->table('tra_cobro_cliente');
        $builder->select('file');
        $builder->where('tramite_id', $tramite_id);
        $query = $builder->get();
        $files = $query->getResultArray();

        foreach ($files as $file) {
            $fileName = $file['file'];
            $sourceFilePath = $sourceDir . $fileName;
            $targetFilePath = $targetDir . $fileName;

            // Verificar si el archivo existe en el directorio fuente
            if (file_exists($sourceFilePath)) {
                // Copiar archivo solo si no existe en el directorio destino
                if (!file_exists($targetFilePath)) {
                    if (!copy($sourceFilePath, $targetFilePath)) {
                        return json_encode([
                            'status' => 'error',
                            'message' => "Error al copiar el archivo $fileName a $targetDir."
                        ]);
                    }
                }
            }
        }

        $crud = $this->_getGroceryCrudEnterprise();
        $crud->setSkin('bootstrap');
        $crud->setCsrfTokenName(csrf_token());
        $crud->setCsrfTokenValue(csrf_hash());
        $crud->unsetRead();

        $crud->unsetDeleteMultiple();
        $crud->unsetAdd();
        $crud->unsetExport();
        $crud->unsetPrint();
        $crud->unsetClone();
        $crud->setTable('tra_cobro_cliente');
        $crud->setSubject('Cobro', 'Cobros a Cliente');
        $crud->defaultOrdering('tra_cobro_cliente.created_at', 'desc');

        $crud->fields([
            "file", "costo", "comentario", "tramite_id", "user_id"
        ]);

        $crud->columns([
            "id", "created_at", "file", "costo", "comentario", "user_id"
        ]);

        $crud->setRelation('user_id', 'users', '{firstname} {midname} {lastname}');

        $crud->where([
            'tramite_id' => $tramite_id
        ]);

        $crud->callbackAfterInsert(function ($stateParameters)  use ($self) {
            if (is_object($stateParameters) && property_exists($stateParameters, 'insertId')) {
                $session = session();
                $parameters = $stateParameters;
                $data = $parameters->data;
                $request = \Config\Services::request();
                $uri = $request->getUri();
                $tramite_id = (int) $uri->getSegment(4);

                $myid = $session->get('id');

                $bitacoraModel = new BitacoraModel($self->_getDbData());
                $data_bitacora = $data;
                $diferencias = $self->encontrarDiferencias($data_bitacora, []);
                $insert_bitacora = [
                    "id"=>null,
                    "tipo"=>"insert",
                    "origen"=>"cliente",
                    "tramite_id" => (int)$tramite_id,
                    "cambios" => json_encode($diferencias),
                    "user_id" => (int)$myid
                ];
                $bitacoraModel->insert($insert_bitacora, 'bitacora');
            }
            return $stateParameters;
        });

        $crud->callbackAddForm(function ($data) {
            $session = session();
            $request = \Config\Services::request();
            $uri = $request->getUri();
            $tramite_id = (int) $uri->getSegment(4);
            $myid = $session->get('id');
            $data['user_id'] = $myid;
            $data['tramite_id'] = $tramite_id;
            return $data;
        });

        $uploadValidations = [
            'maxUploadSize' => '20M', 
            'minUploadSize' => '1K', 
            'allowedFileTypes' => [
                'gif', 'jpeg', 'jpg', 'png', 'tiff', 'pdf', 'xml'
            ]
        ];

        $crud->setFieldUploadMultiple(
            'file', 
            'assets/uploads/cobro_cliente/'.$tramite_id.'/', 
            '/assets/uploads/cobro_cliente/'.$tramite_id.'/', 
            $uploadValidations
        );

        $crud->callbackBeforeDelete(function ($stateParameters) {
            // Access the primary key value directly from the $stateParameters object
            // var_dump($stateParameters->primaryKeyValue);die();

            $primaryKeyValue = (int)$stateParameters->primaryKeyValue;
        
            // Database connection
            $db = \Config\Database::connect();
        
            // Query to retrieve file and tramite_id using the primary key
            $builder = $db->table('tra_cobro_cliente');
            $builder->select('file, tramite_id');
            $builder->where('id', $primaryKeyValue);
        
            $query = $builder->get();
            $row = $query->getRowArray();
        
            if ($row) {
                $tramite_id = $row['tramite_id'];
                $fileName = $row['file'];
        
                // Define the base image path
                $baseImagePath = FCPATH . 'assets/uploads/cobro_cliente/';
        
                // Ensure tramite_id and fileName are available
                if ($tramite_id && $fileName) {
                    // Construct the full file path
                    $filePath = $baseImagePath . $tramite_id . '/' . $fileName;
        
                    // Check if the file exists
                    if (file_exists($filePath)) {
                        // Attempt to delete the file
                        if (unlink($filePath)) {
                            // log_message('info', "File successfully deleted: $filePath");
                        } else {
                            // log_message('error', "Failed to delete file: $filePath");
                        }
                    } else {
                        // log_message('warning', "File does not exist: $filePath");
                    }
                } else {
                    // log_message('error', "Incomplete data: Tramite ID: $tramite_id, File: $fileName");
                }
            } else {
                // log_message('error', "No record found for Primary Key: $primaryKeyValue");
            }
        
            // Continue with the delete operation
            return $stateParameters;
        });

        $salida = $crud->render();
        $salida2 = array_merge((array)$salida, $data);
        return $this->_example_output($salida2);
    }


    private function logDeletedImage($primaryKey, $row)
    {
        // Ruta base de las imágenes
        $imageField = 'file'; // Cambia según el nombre de tu campo de imagen
        $imagePath = '/assets/uploads/pago_derechos/'.$primaryKey.'/'; // Cambia según tu configuración

        // Construir la ruta completa de la imagen
        $imageFile = isset($row[$imageField]) ? $imagePath . $row[$imageField] : 'No definido';

        // Registrar en el log el ID y la ruta de la imagen
        // log_message('info', "Registro eliminado: ID = $primaryKey, Imagen = $imageFile");
        // echo "Registro eliminado: ID = $primaryKey, Imagen = $imageFile";die();
        // Retornar true para continuar el flujo normal
        return true;
    }
    public function single_evidencias_finales(){
        $session = session();
        $data['session'] = \Config\Services::session();
        $data['username'] = $session->get('user_name');
        $self = $this;
        $request = \Config\Services::request();
        $uri = $request->getUri();
        $tramite_id = (int) $uri->getSegment(4);
    
        $crud = $this->_getGroceryCrudEnterprise();
        $crud->setCsrfTokenName(csrf_token());
        $crud->setCsrfTokenValue(csrf_hash());

        $crud->setTable('tra_evidencias_finales');
        $crud->setSubject('Evidencia Final', 'Evidencias Finales');

        $crud->where([
            'tramite_id' => $tramite_id
        ]);   

        $crud->fields([
            'file', 'tramite_id',
            'costo', 'comentario', 'user_id'
        ]); 

        $crud->columns([
            'id', 'tramite_id','file', 
            'costo', 'created_at'
        ]); 

        $crud->callbackAfterInsert(function ($stateParameters)  use ($self) {
            if (is_object($stateParameters) && property_exists($stateParameters, 'insertId')) {
                $session = session();
                $parameters = $stateParameters;
                $db2 = $this->_getDbData();
                $data = $parameters->data;
                $request = \Config\Services::request();
                $uri = $request->getUri();
                $tramite_id = (int) $uri->getSegment(4);

                $myid = $session->get('id');
                                
                $bitacoraModel = new BitacoraModel($db2);
                $data_bitacora = $data;
                $diferencias = $self->encontrarDiferencias($data_bitacora, []);
                $insert_bitacora = [
                    "id"=>null,
                    "tipo"=>"insert",
                    "origen"=>"final",
                    "tramite_id" => (int)$tramite_id,
                    "cambios" => json_encode($diferencias),
                    "user_id" => (int)$myid
                ];
                $bitacoraModel->insert($insert_bitacora, 'bitacora');
            }
            return $stateParameters;
        });

        $crud->callbackAfterUpdate(function ($stateParameters) use ($self){
            $db2 = $this->_getDbData();
            $session = session();
            $data = $stateParameters->data;
            $myid = $session->get('id');
            
            $request = \Config\Services::request();
            $uri = $request->getUri();
            $tramite_id = (int) $uri->getSegment(4);

            $bitacoraModel = new BitacoraModel($db2);
            $data_bitacora = $data;
            $diferencias = $self->encontrarDiferencias($data_bitacora, []);
            $insert_bitacora = [
                "tipo"=>"update",
                "origen"=>"final",
                "tramite_id" => (int)$tramite_id,
                "cambios" => json_encode($diferencias),
                "user_id" => (int)$myid
            ];
            $bitacoraModel->insert($insert_bitacora, 'bitacora');
        });

        $uploadValidations = [
            'maxUploadSize' => '20M', // 20 Mega Bytes
            'minUploadSize' => '1K', // 1 Kilo Byte
            'allowedFileTypes' => [
                'gif', 'jpeg', 'jpg', 'png', 'tiff', 'pdf', 'xml'
            ]
        ];

        $crud->setFieldUploadMultiple(
            'file', 
            'assets/uploads/evidencias/', 
            '/assets/uploads/evidencias/', 
            $uploadValidations
        );

        $crud->fieldType('user_id','hidden');
        $crud->fieldType('tramite_id','hidden');

        $crud->callbackAddForm(function ($data) {
            $session = session();

            $request = \Config\Services::request();
            $uri = $request->getUri();
            $tramite_id = (int) $uri->getSegment(4);

            $myid = $session->get('id');
            $data['user_id'] = $myid;
            $data['tramite_id'] = $tramite_id;

            return $data;
        });

        // Callbacks para registrar el log
        $crud->callbackAfterInsert(function ($stateParameters) use ($crud) {
            $tableName = $crud->getTable();
            return logOperation($stateParameters, $tableName);
        });
        $crud->callbackAfterUpdate(function ($stateParameters) use ($crud) {
            $tableName = $crud->getTable();
            return logOperation($stateParameters, $tableName);
        });
        $crud->callbackAfterDelete(function ($stateParameters) use ($crud) {
            $tableName = $crud->getTable();
            return logOperation($stateParameters, $tableName);
        });

        $salida = $crud->render();
        $salida2 = array_merge((array)$salida, $data);
        return $this->_example_output($salida2);
    }

    private function _getDbData() {
        $db = (new ConfigDatabase())->default;
        return [
            'adapter' => [
                'driver' => 'mysqli',
                'host'     => $db['hostname'],
                'database' => $db['database'],
                'username' => $db['username'],
                'password' => $db['password'],
                'charset' => 'utf8'
            ]
        ];
    }
    private function _getGroceryCrudEnterprise($bootstrap = true, $jquery = true) {
        $db = $this->_getDbData();
        $config = (new ConfigGroceryCrud())->getDefaultConfig();

        $groceryCrud = new GroceryCrud($config, $db);
        return $groceryCrud;
    }

    public function autorizar(){
        $tramiteId = $this->request->getPost('tramite_id');
        $statusId = $this->request->getPost('status_id');
        $db = \Config\Database::connect();
        $db2 = $this->_getDbData();
        $builder = $db->table('tramite');

        try {
            // Actualizar el estatus del trámite
            $builder->where('id', $tramiteId);
            $builder->update(['tra_status_id' => $statusId]);

            // Opcional: Insertar un registro en tra_user_log
            $session = session();
            $myid = $session->get('id');
            $tra_user_log = new TraUserLogModel($db2);
            $logData = [
                'tramite_id' => $tramiteId,
                'user_id' => $myid,
                'tra_status_id' => 23
            ];

            $tra_user_log->insert($logData, 'tra_user_log');

            return $this->response->setJSON(['success' => true]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function check_reembolso_status() {
        $tramiteId = $this->request->getPost('tramite_id');
        $db = \Config\Database::connect();
    
        try {
            // Consultar el reembolso_status_id del trámite
            $builder = $db->table('tramite');
            $builder->select('reembolso_status_id');
            $builder->where('id', $tramiteId);
            $tramite = $builder->get()->getRow();
    
            // Verificar si el reembolso está pendiente (21 o 22)
            $reembolsoPendiente = false;
            if ($tramite && in_array($tramite->reembolso_status_id, [21, 22])) {
                $reembolsoPendiente = true;
            }
    
            // Devolver la respuesta JSON
            return $this->response->setJSON(['reembolso_pendiente' => $reembolsoPendiente]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function change_status(){
        $tramiteId = $this->request->getPost('tramite_id');
        $statusId = $this->request->getPost('status_id');
        $db = \Config\Database::connect();
        $db2 = $this->_getDbData();
        $builder = $db->table('tramite');

        try {
            // Actualizar el estatus del trámite

            $builder->where('id', $tramiteId);
            if($statusId == 20){
                $builder->update([
                    'finished_at' => date('Y-m-d H:i:s'),
                    'tra_status_id' => $statusId
                ]);
            }else{
                $builder->update([
                    'tra_status_id' => $statusId
                ]);
            }
                

            
            // Opcional: Insertar un registro en tra_user_log
            $session = session();
            $myid = $session->get('id');
            $tra_user_log = new TraUserLogModel($db2);
            $logData = [
                'tramite_id' => $tramiteId,
                'user_id' => $myid,
                'tra_status_id' => $statusId
            ];

            $tra_user_log->insert($logData, 'tra_user_log');

            return $this->response->setJSON(['success' => true]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function cancelar_tramite(){
        $tramiteId = $this->request->getPost('tramite_id');
        $motivo = $this->request->getPost('motivo');
        $statusId = $this->request->getPost('status_id');
        $db = \Config\Database::connect();
        $db2 = $this->_getDbData();
        $builder = $db->table('tramite');

        try {
            // Actualizar el estatus del trámite

            $builder->where('id', $tramiteId);
            $builder->update([
                'tra_status_id' => $statusId,
                'cancelacion_motivo' => $motivo
            ]);
            
            // Opcional: Insertar un registro en tra_user_log
            $session = session();
            $myid = $session->get('id');
            $tra_user_log = new TraUserLogModel($db2);
            $logData = [
                'tramite_id' => $tramiteId,
                'user_id' => $myid,
                'tra_status_id' => $statusId
            ];

            $tra_user_log->insert($logData, 'tra_user_log');

            return $this->response->setJSON(['success' => true]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    public function get_service_types()
    {
        $db2 = $this->_getDbData();
        $TraTiposModel = new TraTiposModel($db2);
        $tra_tipos_options = $TraTiposModel->getTraTiposOptions();
        return $this->response->setJSON($tra_tipos_options);
    }

    public function get_services_by_tramite($tramiteId)
    {
        $model = new TraTramiteAsociadoModel();
        return $this->response->setJSON($model->getServicesByTramiteId($tramiteId));
    }
    public function save_services()
    {
        $tramiteId = $this->request->getPost('tramite_id');
        $services = $this->request->getPost('services');

        if (empty($tramiteId) || empty($services)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Datos insuficientes']);
        }

        $model = new TraTramiteAsociadoModel();
        foreach ($services as $serviceId) {
            $model->saveService($tramiteId, $serviceId);
        }

        return $this->response->setJSON(['status' => 'success', 'message' => 'Servicios guardados exitosamente']);
    }

    // Eliminar un servicio asociado al trámite
    public function delete_service()
    {
        $serviceId = $this->request->getPost('asociado_id');
        if (empty($serviceId)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Datos insuficientes']);
        }

        $model = new TraTramiteAsociadoModel();
        $model->deleteService( $serviceId);

        return $this->response->setJSON(['status' => 'deleted', 'message' => 'Servicio eliminado correctamente']);
    }

    public function get_service_costs_by_tramite($tramiteId)
    {
        $db = \Config\Database::connect();
        $query = $db->table('tra_tramite_asociado')
                    ->select('tra_tramite_asociado.id, tra_tramite_asociado.costo_tramite, tra_tipos.tipo_tramite')
                    ->join('tra_tipos', 'tra_tipos.id = tra_tramite_asociado.tra_tipos_id')
                    ->where('tra_tramite_asociado.tramite_id', $tramiteId)
                    ->get();

        return $this->response->setJSON($query->getResultArray());
    }

    public function update_service_cost(){
        $id = $this->request->getPost('id');
        $costo_tramite = $this->request->getPost('costo_tramite');

        if (empty($id) || !is_numeric($costo_tramite)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Datos inválidos para la actualización.'
            ]);
        }

        $db = \Config\Database::connect();
        $builder = $db->table('tra_tramite_asociado');
        $builder->where('id', $id);
        $builder->update(['costo_tramite' => $costo_tramite, 'updated_at' => date('Y-m-d H:i:s')]);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Costo actualizado correctamente.'
        ]);
    }
    public function sincronizarTramites()
    {
        $db = \Config\Database::connect();
        $traTramiteAsociadoModel = new TraTramiteAsociadoModel($db);
        $resultado = $traTramiteAsociadoModel->syncTramitesWithoutAsociados();

        return $this->response->setJSON(['message' => $resultado]);
    }

    

}