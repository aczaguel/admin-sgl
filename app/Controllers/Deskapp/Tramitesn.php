<?php

namespace App\Controllers\Deskapp;

use App\Controllers\BaseController;
use Config\Database as ConfigDatabase;
use App\Models\TraTiposModel;
use App\Models\EntidadesModel;
use App\Models\ClienteDirectoModel;
use App\Models\EmpresaGestoraModel;
use App\Models\TraStatusModel;
use App\Models\ReembolsoStatusModel;
use App\Models\CobroStatusModel;
use App\Models\PagoDerechosModel;
use App\Models\PagoGestorStatusModel;
use App\Models\GestorModel;
use App\Models\TraTramiteAsociadoModel;
use App\Models\TraCobroClienteModel;
use App\Models\TraEvidenciasFinalesModel;
use App\Models\ClienteDirectoEjecutivoModel;
use App\Models\BitacoraModel;
use App\Models\TraUserLogModel;
use App\Controllers\Deskapp\Tramites;

class Tramitesn extends Tramites
{
    private function isLockedStatusId(int $statusId): bool
    {
        return in_array($statusId, [20, 21], true);
    }

    private function isTramiteLocked(int $tramiteId): bool
    {
        $tramiteId = (int) $tramiteId;
        if ($tramiteId <= 0) {
            return false;
        }
        $db = \Config\Database::connect();
        $row = $db->table('tramite')->select('tra_status_id')->where('id', $tramiteId)->get()->getRowArray();
        $statusId = (int) ($row['tra_status_id'] ?? 0);
        return $this->isLockedStatusId($statusId);
    }

    private function normalizeRolesPermsFromSession(): array
    {
        $session = session();
        $roles = $session->get('user_roles') ?? [];
        if (!is_array($roles)) {
            $roles = [$roles];
        }
        $perms = $session->get('user_permissions') ?? [];
        if (!is_array($perms)) {
            $perms = [$perms];
        }
        return [$roles, $perms];
    }

    private function denyJson(int $statusCode, string $message)
    {
        return $this->response->setStatusCode($statusCode)->setJSON([
            'status' => 'error',
            'message' => $message,
            'csrfHash' => csrf_hash(),
        ]);
    }

    public function search()
    {
        helper(['permissions', 'cliente_filter']);

        $session = session();
        $userId = (int) ($session->get('id') ?? 0);
        if ($userId <= 0) {
            return redirect()->to('/')->with('error', 'Sesión expirada.');
        }

        [$roles, $perms] = $this->normalizeRolesPermsFromSession();
        $canRead = (is_super_admin($roles) || is_admin($roles) || has_permission('read_tramite', $perms, $roles) || has_permission('read_final_tramite', $perms, $roles));
        if (!$canRead) {
            return redirect()->to('/deskapp/dashboard')->with('error', 'No tienes permisos para buscar trámites.');
        }

        if (strtolower((string) $this->request->getMethod()) !== 'post') {
            return view('deskapp/tramitesn/search', [
                'session' => $session,
                'title' => 'Buscar Trámite',
            ]);
        }

        $tramiteId = (int) ($this->request->getPost('tramite_id') ?? 0);
        $folio = trim((string) ($this->request->getPost('folio') ?? ''));
        $folio = strtoupper($folio);

        if ($tramiteId <= 0 && $folio === '') {
            return redirect()->to('/deskapp/tramitesn/search')->with('error', 'Ingresa el ID del trámite o el folio.');
        }

        $db = \Config\Database::connect();
        $tramiteRow = null;

        if ($tramiteId > 0) {
            $tramiteRow = $db->table('tramite')->select('id, folio')->where('id', $tramiteId)->get()->getRowArray();
        } else {
            $tramiteRow = $db->table('tramite')->select('id, folio')->where('folio', $folio)->get()->getRowArray();
        }

        $resolvedId = (int) ($tramiteRow['id'] ?? 0);
        if ($resolvedId <= 0) {
            return redirect()->to('/deskapp/tramitesn/search')->with('error', 'El trámite no existe.');
        }

        $hasTenantAccess = (is_super_admin($roles) || is_admin($roles)) ? true : validate_tramite_access($resolvedId, $userId);
        if (!$hasTenantAccess) {
            log_unauthorized_access_attempt('tramite_search', $resolvedId);
            return redirect()->to('/deskapp/tramitesn/search')->with('error', 'El ejecutivo no tiene acceso a ese recurso.');
        }

        return redirect()->to('/deskapp/tramitesn/update/' . $resolvedId . '?from=search');
    }

    public function services($tramiteId)
    {
        helper(['permissions', 'cliente_filter']);

        $session = session();
        $userId = (int) $session->get('id');
        if ($userId <= 0) {
            return $this->response->setStatusCode(401)->setJSON([
                'status' => 'error',
                'message' => 'Sesión expirada.',
                'csrfHash' => csrf_hash(),
            ]);
        }
        [$roles, $perms] = $this->normalizeRolesPermsFromSession();

        $tramiteId = (int) $tramiteId;
        if ($tramiteId <= 0) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => 'ID inválido.',
                'csrfHash' => csrf_hash(),
            ]);
        }

        $hasTenantAccess = (is_super_admin($roles) || is_admin($roles)) ? true : validate_tramite_access($tramiteId, $userId);
        if (!$hasTenantAccess) {
            return $this->response->setStatusCode(403)->setJSON([
                'status' => 'error',
                'message' => 'Acceso denegado.',
                'csrfHash' => csrf_hash(),
            ]);
        }
        if (!has_permission('editar_tramite', $perms, $roles) && !has_permission('read_tramite', $perms, $roles)) {
            return $this->response->setStatusCode(403)->setJSON([
                'status' => 'error',
                'message' => 'Acceso denegado.',
                'csrfHash' => csrf_hash(),
            ]);
        }

        $db = \Config\Database::connect();
        $query = $db->table('tra_tramite_asociado')
            ->select('tra_tramite_asociado.id, tra_tramite_asociado.tra_tipos_id, tra_tipos.tipo_tramite')
            ->join('tra_tipos', 'tra_tipos.id = tra_tramite_asociado.tra_tipos_id')
            ->where('tra_tramite_asociado.tramite_id', $tramiteId)
            ->orderBy('tra_tramite_asociado.id', 'ASC')
            ->get();

        return $this->response->setJSON([
            'status' => 'success',
            'data' => $query->getResultArray(),
            'csrfHash' => csrf_hash(),
        ]);
    }

    public function services_add()
    {
        helper(['permissions', 'cliente_filter']);

        $session = session();
        $userId = (int) $session->get('id');
        if ($userId <= 0) {
            return $this->response->setStatusCode(401)->setJSON([
                'status' => 'error',
                'message' => 'Sesión expirada.',
                'csrfHash' => csrf_hash(),
            ]);
        }
        [$roles, $perms] = $this->normalizeRolesPermsFromSession();

        $tramiteId = (int) $this->request->getPost('tramite_id');
        $traTiposId = (int) $this->request->getPost('tra_tipos_id');
        if ($tramiteId <= 0 || $traTiposId <= 0) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => 'Datos insuficientes.',
                'csrfHash' => csrf_hash(),
            ]);
        }

        if (!has_permission('editar_tramite', $perms, $roles) && !(is_super_admin($roles) || is_admin($roles))) {
            return $this->response->setStatusCode(403)->setJSON([
                'status' => 'error',
                'message' => 'Acceso denegado.',
                'csrfHash' => csrf_hash(),
            ]);
        }

        $hasTenantAccess = (is_super_admin($roles) || is_admin($roles)) ? true : validate_tramite_access($tramiteId, $userId);
        if (!$hasTenantAccess) {
            return $this->response->setStatusCode(403)->setJSON([
                'status' => 'error',
                'message' => 'Acceso denegado.',
                'csrfHash' => csrf_hash(),
            ]);
        }

        // No permitir ligar el tipo principal como asociado
        $db = \Config\Database::connect();
        $tramiteRow = $db->table('tramite')->select('id, tra_tipos_id, tra_status_id')->where('id', $tramiteId)->get()->getRowArray();
        if (!$tramiteRow) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 'error',
                'message' => 'Trámite no encontrado.',
                'csrfHash' => csrf_hash(),
            ]);
        }

        if ($this->isLockedStatusId((int) ($tramiteRow['tra_status_id'] ?? 0))) {
            return $this->denyJson(409, 'El trámite está concluido o cancelado.');
        }
        if ((int) ($tramiteRow['tra_tipos_id'] ?? 0) === $traTiposId) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'No puedes ligar el tipo de trámite principal como asociado.',
                'csrfHash' => csrf_hash(),
            ]);
        }

        $model = new TraTramiteAsociadoModel();
        $insertId = $model->saveService($tramiteId, $traTiposId);
        if ($insertId === false) {
            return $this->response->setJSON([
                'status' => 'exists',
                'message' => 'Este tipo de trámite ya está asociado.',
                'csrfHash' => csrf_hash(),
            ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Tipo de trámite agregado correctamente.',
            'asociado_id' => $insertId,
            'tra_tipos_id' => $traTiposId,
            'csrfHash' => csrf_hash(),
        ]);
    }

    public function services_update()
    {
        helper(['permissions', 'cliente_filter']);
        $session = session();
        $userId = (int) $session->get('id');
        if ($userId <= 0) {
            return $this->denyJson(401, 'Sesión expirada.');
        }
        [$roles, $perms] = $this->normalizeRolesPermsFromSession();

        $tramiteId = (int) $this->request->getPost('tramite_id');
        $asociadoId = (int) $this->request->getPost('asociado_id');
        $nuevoTipoId = (int) $this->request->getPost('tra_tipos_id');
        if ($tramiteId <= 0 || $asociadoId <= 0 || $nuevoTipoId <= 0) {
            return $this->denyJson(400, 'Datos insuficientes.');
        }

        $hasTenantAccess = (is_super_admin($roles) || is_admin($roles)) ? true : validate_tramite_access($tramiteId, $userId);
        if (!$hasTenantAccess) {
            return $this->denyJson(403, 'Acceso denegado.');
        }
        if (!(is_super_admin($roles) || is_admin($roles)) && !has_permission('editar_tramite_asociado', $perms, $roles)) {
            return $this->denyJson(403, 'No tienes permisos para cambiar tipos asociados.');
        }

        $db = \Config\Database::connect();

        // No permitir cambiar un asociado al tipo principal actual
        $tramiteRow = $db->table('tramite')->select('id, tra_tipos_id, tra_status_id')->where('id', $tramiteId)->get()->getRowArray();
        if (!$tramiteRow) {
            return $this->denyJson(404, 'Trámite no encontrado.');
        }

        if ($this->isLockedStatusId((int) ($tramiteRow['tra_status_id'] ?? 0))) {
            return $this->denyJson(409, 'El trámite está concluido o cancelado.');
        }
        if ((int) ($tramiteRow['tra_tipos_id'] ?? 0) === $nuevoTipoId) {
            return $this->denyJson(400, 'No puedes asignar el tipo principal como tipo asociado.');
        }

        $row = $db->table('tra_tramite_asociado')
            ->select('id, tramite_id, tra_tipos_id')
            ->where('id', $asociadoId)
            ->get()
            ->getRowArray();
        if (!$row || (int) $row['tramite_id'] !== $tramiteId) {
            return $this->denyJson(404, 'Asociación no encontrada.');
        }

        $exists = $db->table('tra_tramite_asociado')
            ->where('tramite_id', $tramiteId)
            ->where('tra_tipos_id', $nuevoTipoId)
            ->countAllResults();
        if ($exists > 0) {
            return $this->response->setJSON([
                'status' => 'exists',
                'message' => 'Ese tipo ya está ligado al trámite.',
                'csrfHash' => csrf_hash(),
            ]);
        }

        $db->table('tra_tramite_asociado')->where('id', $asociadoId)->update([
            'tra_tipos_id' => $nuevoTipoId,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $tipoLabelRow = $db->table('tra_tipos')->select('tipo_tramite')->where('id', $nuevoTipoId)->get()->getRowArray();
        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Tipo asociado actualizado.',
            'asociado_id' => $asociadoId,
            'tra_tipos_id' => $nuevoTipoId,
            'label' => $tipoLabelRow['tipo_tramite'] ?? null,
            'csrfHash' => csrf_hash(),
        ]);
    }

    public function services_delete()
    {
        helper(['permissions', 'cliente_filter']);
        $session = session();
        $userId = (int) $session->get('id');
        if ($userId <= 0) {
            return $this->denyJson(401, 'Sesión expirada.');
        }
        [$roles, $perms] = $this->normalizeRolesPermsFromSession();

        $tramiteId = (int) $this->request->getPost('tramite_id');
        $asociadoId = (int) $this->request->getPost('asociado_id');
        if ($tramiteId <= 0 || $asociadoId <= 0) {
            return $this->denyJson(400, 'Datos insuficientes.');
        }

        $hasTenantAccess = (is_super_admin($roles) || is_admin($roles)) ? true : validate_tramite_access($tramiteId, $userId);
        if (!$hasTenantAccess) {
            return $this->denyJson(403, 'Acceso denegado.');
        }
        if (!(is_super_admin($roles) || is_admin($roles)) && !has_permission('delete_tramite_asociado', $perms, $roles)) {
            return $this->denyJson(403, 'No tienes permisos para eliminar tipos asociados.');
        }

        if ($this->isTramiteLocked($tramiteId)) {
            return $this->denyJson(409, 'El trámite está concluido o cancelado.');
        }

        $db = \Config\Database::connect();
        $row = $db->table('tra_tramite_asociado')
            ->select('id, tramite_id')
            ->where('id', $asociadoId)
            ->get()
            ->getRowArray();
        if (!$row || (int) $row['tramite_id'] !== $tramiteId) {
            return $this->denyJson(404, 'Asociación no encontrada.');
        }

        $db->table('tra_tramite_asociado')->where('id', $asociadoId)->delete();
        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Tipo asociado eliminado.',
            'asociado_id' => $asociadoId,
            'csrfHash' => csrf_hash(),
        ]);
    }

    public function principal_update_tipo()
    {
        helper(['permissions', 'cliente_filter']);
        $session = session();
        $userId = (int) $session->get('id');
        if ($userId <= 0) {
            return $this->denyJson(401, 'Sesión expirada.');
        }
        [$roles, $perms] = $this->normalizeRolesPermsFromSession();

        $tramiteId = (int) $this->request->getPost('tramite_id');
        $nuevoTipoId = (int) $this->request->getPost('tra_tipos_id');
        if ($tramiteId <= 0 || $nuevoTipoId <= 0) {
            return $this->denyJson(400, 'Datos insuficientes.');
        }

        $hasTenantAccess = (is_super_admin($roles) || is_admin($roles)) ? true : validate_tramite_access($tramiteId, $userId);
        if (!$hasTenantAccess) {
            return $this->denyJson(403, 'Acceso denegado.');
        }
        if (!(is_super_admin($roles) || is_admin($roles)) && !has_permission('editar_tramite_principal', $perms, $roles)) {
            return $this->denyJson(403, 'No tienes permisos para editar el trámite principal.');
        }

        $db = \Config\Database::connect();
        $tramiteRow = $db->table('tramite')->select('id, tra_tipos_id, tra_status_id')->where('id', $tramiteId)->get()->getRowArray();
        if (!$tramiteRow) {
            return $this->denyJson(404, 'Trámite no encontrado.');
        }

        if ($this->isLockedStatusId((int) ($tramiteRow['tra_status_id'] ?? 0))) {
            return $this->denyJson(409, 'El trámite está concluido o cancelado.');
        }

        $currentTipoId = (int) ($tramiteRow['tra_tipos_id'] ?? 0);
        if ($currentTipoId === $nuevoTipoId) {
            $tipoLabelRow = $db->table('tra_tipos')->select('tipo_tramite')->where('id', $nuevoTipoId)->get()->getRowArray();
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Sin cambios.',
                'tra_tipos_id' => $nuevoTipoId,
                'old_tipo_id' => $currentTipoId,
                'label' => $tipoLabelRow['tipo_tramite'] ?? null,
                'csrfHash' => csrf_hash(),
            ]);
        }

        $db->table('tramite')->where('id', $tramiteId)->update([
            'tra_tipos_id' => $nuevoTipoId,
        ]);

        $principalAssoc = null;
        if ($currentTipoId > 0) {
            $principalAssoc = $db->table('tra_tramite_asociado')
                ->select('id')
                ->where('tramite_id', $tramiteId)
                ->where('tra_tipos_id', $currentTipoId)
                ->get()
                ->getRowArray();
        }

        $nuevoAssoc = $db->table('tra_tramite_asociado')
            ->select('id')
            ->where('tramite_id', $tramiteId)
            ->where('tra_tipos_id', $nuevoTipoId)
            ->get()
            ->getRowArray();

        $assocAction = 'none';
        $principalAssocId = !empty($nuevoAssoc) ? (int) $nuevoAssoc['id'] : null;

        if (!empty($nuevoAssoc) && !empty($principalAssoc) && (int) $nuevoAssoc['id'] !== (int) $principalAssoc['id']) {
            $db->table('tra_tramite_asociado')->where('id', (int) $principalAssoc['id'])->delete();
            $assocAction = 'deleted_old';
        } elseif (!empty($principalAssoc) && empty($nuevoAssoc)) {
            $db->table('tra_tramite_asociado')->where('id', (int) $principalAssoc['id'])->update([
                'tra_tipos_id' => $nuevoTipoId,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            $principalAssocId = (int) $principalAssoc['id'];
            $assocAction = 'updated';
        } elseif (empty($principalAssoc) && empty($nuevoAssoc)) {
            $tramiteAsociadoModel = new TraTramiteAsociadoModel();
            $principalAssocId = $tramiteAsociadoModel->saveService($tramiteId, $nuevoTipoId);
            $assocAction = 'inserted';
        } else {
            $assocAction = 'kept_existing';
        }

        $tipoLabelRow = $db->table('tra_tipos')->select('tipo_tramite')->where('id', $nuevoTipoId)->get()->getRowArray();
        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Tipo principal actualizado.',
            'tra_tipos_id' => $nuevoTipoId,
            'old_tipo_id' => $currentTipoId,
            'asociado_id' => $principalAssocId,
            'assoc_action' => $assocAction,
            'label' => $tipoLabelRow['tipo_tramite'] ?? null,
            'csrfHash' => csrf_hash(),
        ]);
    }

    public function get_service_costs_by_tramite($tramiteId)
    {
        helper(['permissions', 'cliente_filter']);

        $session = session();
        $userId = (int) $session->get('id');
        $roles = $session->get('user_roles') ?? [];
        if (!is_array($roles)) {
            $roles = [$roles];
        }
        $perms = $session->get('user_permissions') ?? [];
        if (!is_array($perms)) {
            $perms = [$perms];
        }

        $tramiteId = (int) $tramiteId;
        if ($tramiteId <= 0) {
            return $this->response->setStatusCode(400)->setJSON([]);
        }

        $hasTenantAccess = (is_super_admin($roles) || is_admin($roles)) ? true : validate_tramite_access($tramiteId, $userId);
        if (!$hasTenantAccess) {
            return $this->response->setStatusCode(403)->setJSON([
                'status' => 'error',
                'message' => 'Acceso denegado.',
            ]);
        }
        if (!has_permission('section_pago_gestor', $perms, $roles)) {
            return $this->response->setStatusCode(403)->setJSON([
                'status' => 'error',
                'message' => 'Acceso denegado.',
            ]);
        }

        $db = \Config\Database::connect();
        $query = $db->table('tra_tramite_asociado')
            ->select('tra_tramite_asociado.id, tra_tramite_asociado.costo_tramite, tra_tipos.tipo_tramite')
            ->join('tra_tipos', 'tra_tipos.id = tra_tramite_asociado.tra_tipos_id')
            ->where('tra_tramite_asociado.tramite_id', $tramiteId)
            ->get();

        return $this->response->setJSON($query->getResultArray());
    }

    public function update_service_cost()
    {
        helper(['permissions', 'cliente_filter']);

        $session = session();
        $userId = (int) $session->get('id');
        $roles = $session->get('user_roles') ?? [];
        if (!is_array($roles)) {
            $roles = [$roles];
        }
        $perms = $session->get('user_permissions') ?? [];
        if (!is_array($perms)) {
            $perms = [$perms];
        }

        $id = $this->request->getPost('id');
        $costo_tramite = $this->request->getPost('costo_tramite');

        if (!$id || !is_numeric($id)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'ID de servicio inválido.'
            ]);
        }

        if ($costo_tramite !== '' && $costo_tramite !== null && !is_numeric($costo_tramite)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'El costo debe ser un valor numérico válido.'
            ]);
        }

        if ($costo_tramite === '' || $costo_tramite === null) {
            $costo_tramite = null;
        }

        try {
            $db = \Config\Database::connect();
            $builder = $db->table('tra_tramite_asociado');

            $existingRecord = $builder->where('id', $id)->get()->getRowArray();
            if (!$existingRecord) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'El servicio asociado no existe.'
                ]);
            }

            if (!has_permission('editar_pago_gestor', $perms, $roles)) {
                return $this->response->setStatusCode(403)->setJSON([
                    'status' => 'error',
                    'message' => 'Acceso denegado.'
                ]);
            }

            $tramiteId = (int) ($existingRecord['tramite_id'] ?? 0);
            if ($tramiteId <= 0) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'error',
                    'message' => 'Servicio inválido.'
                ]);
            }

            $hasTenantAccess = (is_super_admin($roles) || is_admin($roles)) ? true : validate_tramite_access($tramiteId, $userId);
            if (!$hasTenantAccess) {
                return $this->response->setStatusCode(403)->setJSON([
                    'status' => 'error',
                    'message' => 'Acceso denegado.'
                ]);
            }

            if ($this->isTramiteLocked($tramiteId)) {
                return $this->response->setStatusCode(409)->setJSON([
                    'status' => 'error',
                    'message' => 'El trámite está concluido o cancelado.'
                ]);
            }

            $data = [
                'costo_tramite' => $costo_tramite,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $builder->where('id', $id);
            $updateResult = $builder->update($data);
            if (!$updateResult) {
                throw new \Exception('No se pudo actualizar el costo del servicio.');
            }

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Costo actualizado correctamente.'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error en Tramitesn::update_service_cost: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Error al actualizar: ' . $e->getMessage()
            ]);
        }
    }

    public function update_save()
    {
        $session = session();
        $myid = $session->get('id');
        $id = $this->request->uri->getSegment(4);

        if (!$id || !is_numeric($id)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ID de trámite inválido.',
                'csrfHash' => csrf_hash(),
            ]);
        }

        $validation = \Config\Services::validation();
        $validation->setRules([
            'folio' => 'required',
            'contrato' => 'required',
        ]);

        if ($validation->withRequest($this->request)->run() === false) {
            return $this->response->setJSON([
                'success' => false,
                'errors' => $validation->getErrors(),
                'csrfHash' => csrf_hash(),
            ]);
        }

        try {
            $db = \Config\Database::connect();
            $builder = $db->table('tramite');

            $existingTramite = $builder->where('id', $id)->get()->getRowArray();
            if (!$existingTramite) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'El trámite no existe.',
                    'csrfHash' => csrf_hash(),
                ]);
            }

            if ($this->isLockedStatusId((int) ($existingTramite['tra_status_id'] ?? 0))) {
                return $this->response->setStatusCode(409)->setJSON([
                    'success' => false,
                    'message' => 'El trámite está concluido o cancelado.',
                    'csrfHash' => csrf_hash(),
                ]);
            }

            $data = $this->request->getPost();
            $csrfName = csrf_token();
            if (isset($data[$csrfName])) {
                unset($data[$csrfName]);
            }
            $data['user_id'] = $myid;

            $currentStep = isset($data['current_step']) ? (int) $data['current_step'] : 0;
            unset($data['current_step']);

            if (array_key_exists('gestor_id', $data) && ($data['gestor_id'] === '' || $data['gestor_id'] === 'null')) {
                unset($data['gestor_id']);
            }
            if (array_key_exists('empresa_gestora_id', $data) && ($data['empresa_gestora_id'] === '' || $data['empresa_gestora_id'] === 'null')) {
                unset($data['empresa_gestora_id']);
            }

            if ($currentStep > 0 && $currentStep < 3) {
                foreach (['derechos_tramite', 'derechos_pago_sitio', 'derechos_vigencia', 'derechos_revol_cliente', 'derechos_refer_banc'] as $field) {
                    if (array_key_exists($field, $data)) {
                        unset($data[$field]);
                    }
                }
            }

            $changes = [];
            try {
                $changes = compare_tramite_data($existingTramite, $data);
            } catch (\Throwable $e) {
                log_message('error', 'Error en compare_tramite_data (Tramitesn::update_save): ' . $e->getMessage());
            }

            $logFile = WRITEPATH . 'logs/audit_debug.log';
            $logData = [
                'timestamp' => date('Y-m-d H:i:s'),
                'tramite_id' => $id,
                'user_id' => $myid,
                'post_fields' => array_keys($data),
                'existing_fields' => array_keys($existingTramite),
                'changes_detected' => count($changes),
                'changes' => $changes,
            ];
            file_put_contents($logFile, json_encode($logData, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND);

            $builder->where('id', $id);
            $updateResult = $builder->update($data);
            if (!$updateResult) {
                throw new \Exception('No se pudo actualizar el trámite.');
            }

            $targetStatus = null;
            $hasGestor = !empty($data['empresa_gestora_id']) && !empty($data['gestor_id']);
            $hasDerechosBase = !empty($data['derechos_tramite'])
                && !empty($data['derechos_pago_sitio'])
                && !empty($data['derechos_vigencia']);
            $hasDerechosBanc = !empty($data['derechos_revol_cliente'])
                && !empty($data['derechos_refer_banc']);

            if ($hasGestor) {
                $targetStatus = 25;
            }
            if ($hasDerechosBase) {
                $targetStatus = 26;
            }
            if ($hasDerechosBanc) {
                $targetStatus = 27;
            }

            $statusUpdatedTo = (int) ($existingTramite['tra_status_id'] ?? 0);
            if ($targetStatus !== null) {
                $statusResult = $this->updateTramiteStatus($id, $targetStatus);
                if (!empty($statusResult['success'])) {
                    $statusUpdatedTo = $targetStatus;
                }
            }

            $principalTipoId = (int) ($existingTramite['tra_tipos_id'] ?? 0);
            $asociadoFields = [
                'derechos_tramite',
                'derechos_pago_sitio',
                'derechos_vigencia',
                'derechos_revol_cliente',
                'derechos_refer_banc',
            ];
            $asociadoData = [];
            foreach ($asociadoFields as $field) {
                if (array_key_exists($field, $data)) {
                    $asociadoData[$field] = $data[$field];
                }
            }
            if (!empty($asociadoData)) {
                $asociadoData['updated_at'] = date('Y-m-d H:i:s');
                $asociadoBuilder = $db->table('tra_tramite_asociado');
                $asociadoBuilder->where('tramite_id', (int) $id);
                if ($principalTipoId > 0) {
                    $asociadoBuilder->where('tra_tipos_id !=', $principalTipoId);
                }
                $asociadoBuilder->update($asociadoData);
            }

            $folio = $data['folio'] ?? null;
            $db2 = $this->_getDbData();

            $bitacoraModel = new BitacoraModel($db2);
            $diferencias = [];
            try {
                $diferencias = $this->buildBitacoraChanges($changes);
            } catch (\Throwable $e) {
                log_message('error', 'Error en buildBitacoraChanges (Tramitesn::update_save): ' . $e->getMessage());
            }
            $insert_bitacora = [
                'id' => null,
                'tipo' => 'update',
                'origen' => 'tramite',
                'folio_tramite' => $folio,
                'tramite_id' => (int) $id,
                'cambios' => json_encode($diferencias),
                'user_id' => (int) $myid,
            ];
            $bitacoraModel->insert($insert_bitacora, 'bitacora');

            $tra_user_log = new TraUserLogModel($db2);
            $log = [
                'tramite_id' => (int) $id,
                'user_id' => (int) $myid,
                'tra_status_id' => $statusUpdatedTo > 0 ? $statusUpdatedTo : 11,
            ];
            $tra_user_log->insert($log, 'tra_user_log');

            if (!empty($changes)) {
                try {
                    $changeCount = log_tramite_bulk_changes($id, $changes, 'tramite', [
                        'form_name' => 'Datos Generales',
                        'form_step' => 1,
                        'form_section' => 'update_save',
                    ]);
                    log_message('info', "[Tramitesn::update_save] Registrados {$changeCount} cambios para trámite ID: {$id}");
                } catch (\Throwable $e) {
                    log_message('error', 'Error en log_tramite_bulk_changes (Tramitesn::update_save): ' . $e->getMessage());
                }

                try {
                    $cambiosTexto = implode(', ', array_keys($changes));
                    notify_tramite_actualizado($id, $folio ?? "Trámite #{$id}", $cambiosTexto, $myid);
                } catch (\Throwable $e) {
                    log_message('error', 'Error en notify_tramite_actualizado (Tramitesn::update_save): ' . $e->getMessage());
                }
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'El trámite se guardó correctamente.',
                'redirect' => '/deskapp/tramites/update/' . $id,
                'csrfHash' => csrf_hash(),
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error en Tramitesn::update_save: ' . $e->getMessage());
            log_message('error', 'Trace Tramitesn::update_save: ' . $e->getTraceAsString());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al actualizar: ' . $e->getMessage(),
                'csrfHash' => csrf_hash(),
            ]);
        }
    }

    public function update_gestor_save()
    {
        $session = session();
        $myid = $session->get('id');
        $id = $this->request->uri->getSegment(4);

        if (!$id || !is_numeric($id)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ID de trámite inválido.',
                'csrfHash' => csrf_hash(),
            ]);
        }

        $validation = \Config\Services::validation();
        $validation->setRules([
            'empresa_gestora_id' => 'required',
            'gestor_id' => 'required',
        ]);

        if ($validation->withRequest($this->request)->run() === false) {
            return $this->response->setJSON([
                'success' => false,
                'errors' => $validation->getErrors(),
                'csrfHash' => csrf_hash(),
            ]);
        }

        try {
            $db = \Config\Database::connect();
            $builder = $db->table('tramite');

            $tramiteBase = $builder->where('id', $id)->get()->getRowArray();
            if (!$tramiteBase) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'El trámite no existe.',
                    'csrfHash' => csrf_hash(),
                ]);
            }

            if ($this->isLockedStatusId((int) ($tramiteBase['tra_status_id'] ?? 0))) {
                return $this->response->setStatusCode(409)->setJSON([
                    'success' => false,
                    'message' => 'El trámite está concluido o cancelado.',
                    'csrfHash' => csrf_hash(),
                ]);
            }

            $this->updateTramiteStatus($id, 25);

            $data = $this->request->getPost();
            $csrfName = csrf_token();
            if (isset($data[$csrfName])) {
                unset($data[$csrfName]);
            }
            if (isset($data['current_step'])) {
                unset($data['current_step']);
            }

            if (empty($tramiteBase['started_at'])) {
                $data['started_at'] = date('Y-m-d H:i:s');
            }

            if (isset($data['gestor_name'])) {
                unset($data['gestor_name']);
            }

            $changes = [];
            try {
                $changes = compare_tramite_data($tramiteBase, $data);
            } catch (\Throwable $e) {
                log_message('error', 'Error en compare_tramite_data (Tramitesn::update_gestor_save): ' . $e->getMessage());
            }

            $builder->where('id', $id);
            $updateResult = $builder->update($data);

            if (!$updateResult) {
                throw new \Exception('No se pudo asignar el gestor.');
            }

            $db2 = $this->_getDbData();
            $bitacoraModel = new BitacoraModel($db2);
            $diferencias = $this->buildBitacoraChanges($changes);
            $insert_bitacora = [
                'id' => null,
                'tipo' => 'update',
                'origen' => 'tramite',
                'tramite_id' => (int) $id,
                'cambios' => json_encode($diferencias),
                'user_id' => (int) $myid,
            ];
            $bitacoraModel->insert($insert_bitacora, 'bitacora');

            $tra_user_log = new TraUserLogModel($db2);
            $log = [
                'tramite_id' => (int) $id,
                'user_id' => (int) $myid,
                'tra_status_id' => 22,
            ];
            $tra_user_log->insert($log, 'tra_user_log');

            if (!empty($changes)) {
                log_tramite_bulk_changes($id, $changes, 'tramite', [
                    'form_name' => 'Asignacion de Gestor',
                    'form_step' => 2,
                    'form_section' => 'update_gestor_save',
                ]);

                if (isset($changes['gestor_id'])) {
                    $db = \Config\Database::connect();
                    $tramiteData = $db->table('tramite')->select('folio')->where('id', $id)->get()->getRowArray();
                    $gestor = $db->table('ges_gestor')->select('nombre')->where('id', $data['gestor_id'] ?? 0)->get()->getRowArray();

                    $folio = $tramiteData['folio'] ?? "Trámite #{$id}";
                    $gestorNombre = $gestor['nombre'] ?? 'Gestor';
                    notify_gestor_asignado($id, $folio, $gestorNombre, $myid);
                }
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'El Gestor se asigno correctamente.',
                'redirect' => '/deskapp/tramitesn/update/' . $id,
                'csrfHash' => csrf_hash(),
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error en Tramitesn::update_gestor_save: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al asignar gestor: ' . $e->getMessage(),
                'csrfHash' => csrf_hash(),
            ]);
        }
    }

    public function update_derechos_save()
    {
        $session = session();
        $myid = $session->get('id');
        $id = $this->request->uri->getSegment(4);

        if (!$id || !is_numeric($id)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ID de trámite inválido.',
                'csrfHash' => csrf_hash(),
            ]);
        }

        $validation = \Config\Services::validation();
        $validation->setRules([
            'derechos_tramite' => 'required',
            'derechos_pago_sitio' => 'required',
            'derechos_vigencia' => 'required',
        ]);

        if ($validation->withRequest($this->request)->run() === false) {
            return $this->response->setJSON([
                'success' => false,
                'errors' => $validation->getErrors(),
                'csrfHash' => csrf_hash(),
            ]);
        }

        try {
            $db = \Config\Database::connect();
            $builder = $db->table('tramite');

            $existingTramite = $builder->where('id', $id)->get()->getRowArray();
            if (!$existingTramite) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'El trámite no existe.',
                    'csrfHash' => csrf_hash(),
                ]);
            }

            if ($this->isLockedStatusId((int) ($existingTramite['tra_status_id'] ?? 0))) {
                return $this->response->setStatusCode(409)->setJSON([
                    'success' => false,
                    'message' => 'El trámite está concluido o cancelado.',
                    'csrfHash' => csrf_hash(),
                ]);
            }

            $data = $this->request->getPost();
            $csrfName = csrf_token();
            if (isset($data[$csrfName])) {
                unset($data[$csrfName]);
            }

            $changes = [];

            if (isset($data['current_step'])) {
                unset($data['current_step']);
            }

            try {
                $changes = compare_tramite_data($existingTramite, $data);
            } catch (\Throwable $e) {
                log_message('error', 'Error en compare_tramite_data (Tramitesn::update_derechos_save): ' . $e->getMessage());
            }

            $builder->where('id', $id);
            $updateResult = $builder->update($data);

            if (!$updateResult) {
                throw new \Exception('No se pudo guardar los derechos.');
            }

            $hasDerechosBase = !empty($data['derechos_tramite'])
                && !empty($data['derechos_pago_sitio'])
                && !empty($data['derechos_vigencia']);
            $hasDerechosBanc = !empty($data['derechos_revol_cliente'])
                && !empty($data['derechos_refer_banc']);

            if ($hasDerechosBase) {
                $this->updateTramiteStatus($id, 26);
            }
            if ($hasDerechosBanc) {
                $this->updateTramiteStatus($id, 27);
            }

            $principalTipoId = (int) ($existingTramite['tra_tipos_id'] ?? 0);
            $asociadoFields = [
                'derechos_tramite',
                'derechos_pago_sitio',
                'derechos_vigencia',
                'derechos_revol_cliente',
                'derechos_refer_banc',
            ];
            $asociadoData = [];
            foreach ($asociadoFields as $field) {
                if (array_key_exists($field, $data)) {
                    $asociadoData[$field] = $data[$field];
                }
            }
            if (!empty($asociadoData)) {
                $asociadoData['updated_at'] = date('Y-m-d H:i:s');
                $asociadoBuilder = $db->table('tra_tramite_asociado');
                $asociadoBuilder->where('tramite_id', (int) $id);
                if ($principalTipoId > 0) {
                    $asociadoBuilder->where('tra_tipos_id !=', $principalTipoId);
                }
                $asociadoBuilder->update($asociadoData);
            }

            $db2 = $this->_getDbData();
            $bitacoraModel = new BitacoraModel($db2);
            $diferencias = $this->buildBitacoraChanges($changes);
            $insert_bitacora = [
                'id' => null,
                'tipo' => 'update',
                'origen' => 'tramite',
                'tramite_id' => (int) $id,
                'cambios' => json_encode($diferencias),
                'user_id' => (int) $myid,
            ];
            $bitacoraModel->insert($insert_bitacora, 'bitacora');

            $tra_user_log = new TraUserLogModel($db2);
            $log = [
                'tramite_id' => (int) $id,
                'user_id' => (int) $myid,
                'tra_status_id' => 22,
            ];
            $tra_user_log->insert($log, 'tra_user_log');

            if (!empty($changes)) {
                log_tramite_bulk_changes($id, $changes, 'tramite', [
                    'form_name' => 'Pago de Derechos',
                    'form_step' => 3,
                    'form_section' => 'update_derechos_save',
                ]);

                $db = \Config\Database::connect();
                $tramiteData = $db->table('tramite')->select('folio')->where('id', $id)->get()->getRowArray();
                $folio = $tramiteData['folio'] ?? "Trámite #{$id}";
                notify_tramite_actualizado($id, $folio, 'Pago de Derechos actualizado', $myid);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'El trámite se guardo correctamente.',
                'redirect' => '/deskapp/tramitesn/update/' . $id,
                'csrfHash' => csrf_hash(),
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error en Tramitesn::update_derechos_save: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al guardar derechos: ' . $e->getMessage(),
                'csrfHash' => csrf_hash(),
            ]);
        }
    }
    public function index()
    {
        $output = (object)[
            'js_files' => [],
            'output' => ''
        ];
        
        return $this->_example_output($output);
    }
    /**
     * Listado de trámites usando el nuevo flujo, independiente del listado original.
     * Mantiene la misma lógica de filtros/seguridad que Tramites::tramite(),
     * pero los botones apuntan a /deskapp/tramitesn/update.
     */
    public function tramite()
    {
        try {
            $self = $this;
            $session = session();
            $data['session'] = \Config\Services::session();
            $data['username'] = $session->get('user_name');
            $myid = $session->get('id');
            $roles = $session->get('user_roles') ?? [];
            if (!is_array($roles)) {
                $roles = [$roles];
            }
            $perms = $session->get('user_permissions') ?? [];
            if (!is_array($perms)) {
                $perms = [$perms];
            }

            $tramite_crud = $this->_getGroceryCrudEnterprise();

            // Filtro multi-tenancy
            $filterSql = get_tramite_filter_sql($myid);
            $tramite_crud->where($filterSql);
            $data['audit_payload'] = [
                'source' => 'Tramitesn::tramite',
                'user_id' => (int) $myid,
                'filterSql' => $filterSql,
            ];

            // Mostrar todos los estatus en el listado

            $tramite_crud->unsetAdd();
            $tramite_crud->unsetEdit();
            $tramite_crud->unsetRead();
            $tramite_crud->unsetDeleteMultiple();

            // Botón Editar → nuevo flujo
            if (has_permission('editar_tramite', $perms, $roles)){
                $tramite_crud->setActionButton('Editar', 'fas fa-pencil-alt', function ($row) {
                    return '/deskapp/tramitesn/update/' . $row->id;
                }, false);
            }

            $tramite_crud->unsetDelete();

            if (!has_permission('export_tramite', $perms, $roles)){
                $tramite_crud->unsetExport();
            }

            if (!has_permission('print_tramite', $perms, $roles)){
                $tramite_crud->unsetPrint();
            }

            // Botón Ver → también al nuevo flujo
            if (has_permission('read_tramite', $perms, $roles)){
                $tramite_crud->setActionButton('Ver', 'fas fa-eye', function ($row) {
                    return '/deskapp/tramitesn/update/' . $row->id;
                }, false);
            }

            if (!has_permission('clone_tramite', $perms, $roles)){
                $tramite_crud->unsetClone();
            }

            $tramite_crud->setCsrfTokenName(csrf_token());
            $tramite_crud->setCsrfTokenValue(csrf_hash());

            $tramite_crud->setTable('tramite');
            $tramite_crud->setSubject('tramite', 'Tramites (Nuevo Flujo)');
            $tramite_crud->defaultOrdering('tramite.id', 'desc');

            // Mismo filtro de fecha que el listado principal 2026+
            $tramite_crud->where([
                'tramite.created_at >= ?' => ['2026-01-01 00:00:00']
            ]);

            $tramite_crud->columns([
                'id', 'created_at', 'started_at', 'tra_status_id', 'folio', 'contrato', 'unidad', 'serie',
                'placas', 'tra_tipos_id', 'entidad_id', 'ent_municipio_id', 'cli_directo_id',
                'cli_directo_ejecutivo_id', 'empresa_gestora_id', 'gestor_id',
                'cobro_status_id', 'user_id',
                'observaciones'
            ]);

            $tramite_crud->displayAs('started_at', 'Desde Asignación');
            $tramite_crud->setRelation('user_id', 'users', '{firstname} {midname} {lastname}');
            $tramite_crud->displayAs('user_id', 'Ejecutivo');

            // Callback de color por días / status (copiado del flujo original)
            $tramite_crud->callbackColumn('started_at', function ($value, $row) {
                $fechaAsignacion = new \DateTime($row->started_at);
                $fechaActual = new \DateTime();
                $diasDiferencia = $fechaAsignacion->diff($fechaActual)->days;

                $claseVerde = 'background-verde';
                $claseAmarillo = 'background-amarillo';
                $claseRojo = 'background-rojo';
                $claseVioleta = 'background-violeta';
                $claseGris = 'background-gris';
                $claseAzulClaro = 'background-azul-claro';
                $claseAzul = 'background-azul';
                $claseAzulCobroCliente = 'background-azul-cobro-cliente';

                if ($row->tra_status_id == 23 || $row->tra_status_id == 28) {
                    if($row->tra_status_id == 23){
                        $clase = $claseAzulClaro;
                    }
                    $txt_generar_factura = '';

                    $traCobroClienteModel = new TraCobroClienteModel();
                    $registrosCobroCliente = $traCobroClienteModel->getByTramiteId($row->id);

                    $traEvidenciasFinalesModel = new TraEvidenciasFinalesModel();
                    $registrosEvidenciasFinales = $traEvidenciasFinalesModel->getByTramiteId($row->id);

                    if (count($registrosCobroCliente) > 0 || count($registrosEvidenciasFinales) > 0) {
                        $txt_generar_factura = 'Facturar';
                    }

                    if($row->tra_status_id == 28){
                        $clase = $claseAzulCobroCliente;
                        return '<span class="' . $clase . '">' . $txt_generar_factura . '</span>';
                    }
                } elseif ($row->tra_status_id == 21) {
                    $clase = $claseGris;
                } elseif ($row->tra_status_id == 20) {
                    $clase = $claseAzul;
                } else {
                    $local = ($row->ent_municipio_id >= 266 && $row->ent_municipio_id <= 281) ||
                             ($row->ent_municipio_id >= 657 && $row->ent_municipio_id <= 781);

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

                $arrFilter = [20, 21, 23, 28];
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

            $tramite_crud->displayAs('created_at', 'Creación');

            $tramite_crud->setRelation('tra_tipos_id', 'tra_tipos', 'tipo_tramite');
            $tramite_crud->displayAs('tra_tipos_id','Tipo de Tramite');

            $tramite_crud->setRelation('tra_status_id', 'tra_status', 'tra_status');
            $tramite_crud->displayAs('tra_status_id','Estatus del Tramite');

            $clienteRelationFilter = get_cliente_relation_filter($myid);
            if ($clienteRelationFilter !== null) {
                $tramite_crud->setRelation('cli_directo_id', 'cli_directo', 'razon_social', $clienteRelationFilter);
            } else {
                $tramite_crud->setRelation('cli_directo_id', 'cli_directo', 'razon_social');
            }
            $tramite_crud->displayAs('cli_directo_id','Cliente Directo');

            $tramite_crud->setRelation('cli_directo_ejecutivo_id', 'cli_directo_ejecutivo', 'nombre');
            $tramite_crud->displayAs('cli_directo_ejecutivo_id','Ejecutivo del Cliente');
            $tramite_crud->setDependentRelation('cli_directo_ejecutivo_id','cli_directo_id','cli_directo_id');

            $tramite_crud->setRelation('entidad_id', 'entidad', 'entidad');
            $tramite_crud->displayAs('entidad_id','Entidad');

            $tramite_crud->setRelation('ent_municipio_id', 'rel_ent_municipio', 'ent_municipality');
            $tramite_crud->displayAs('ent_municipio_id','Municipio');

            $tramite_crud->setRelation('empresa_gestora_id', 'ges_empresa_gestora', 'razon_social');
            $tramite_crud->displayAs('empresa_gestora_id','Empresa Gestora');

            $tramite_crud->setRelation('gestor_id', 'ges_gestor', 'nombre');
            $tramite_crud->displayAs('gestor_id','Gestor');
            $tramite_crud->setDependentRelation('gestor_id','empresa_gestora_id','empresa_gestora_id');

            $tramite_salida = $tramite_crud->render();

            $salida_total = array_merge((array)$tramite_salida, $data);
            $salida_total['audit_payload'] = $data['audit_payload'] ?? null;
            // El botón de "Nuevo" seguirá usando el alta tradicional si se requiere
            $salida_total['insert_button_url'] = '/public/deskapp/tramites/add';

            echo $this->_example_output($salida_total);

        } catch (\Exception $e) {
            exit($e->getMessage());
        }
    }

    /**
     * Listado de trámites listos para Cobro a Cliente (Paso 5).
     * Filtra por evidencia en tra_pago_gestor (tramite_recibido + acuse_recibo_cliente).
     */
    public function cobro_cliente()
    {
        try {
            $session = session();
            $data['session'] = \Config\Services::session();
            $data['username'] = $session->get('user_name');
            $myid = $session->get('id');
            $roles = $session->get('user_roles') ?? [];
            if (!is_array($roles)) {
                $roles = [$roles];
            }
            $perms = $session->get('user_permissions') ?? [];
            if (!is_array($perms)) {
                $perms = [$perms];
            }

            $tramite_crud = $this->_getGroceryCrudEnterprise();

            // $filterSql = get_tramite_filter_sql($myid);
            // $tramite_crud->where($filterSql);
            $tramite_crud->where('tra_status_id = 28');

            // El listado muestra todos los tramites en estatus 28; la columna indica si ya puede cobrarse.

            $tramite_crud->unsetAdd();
            $tramite_crud->unsetEdit();
            $tramite_crud->unsetRead();
            $tramite_crud->unsetDeleteMultiple();

            if (has_permission('section_final_costos', $perms, $roles)) {
                $tramite_crud->setActionButton('Cobro a Cliente', 'fas fa-receipt', function ($row) {
                    return '/deskapp/tramitesn/cobro_cliente/' . $row->id;
                }, false);
            }

            $tramite_crud->unsetDelete();

            if (!has_permission('export_tramite', $perms, $roles)){
                $tramite_crud->unsetExport();
            }

            if (!has_permission('print_tramite', $perms, $roles)){
                $tramite_crud->unsetPrint();
            }

            if (!has_permission('clone_tramite', $perms, $roles)){
                $tramite_crud->unsetClone();
            }

            $tramite_crud->setCsrfTokenName(csrf_token());
            $tramite_crud->setCsrfTokenValue(csrf_hash());

            $tramite_crud->setTable('tramite');
            $tramite_crud->setSubject('tramite', 'Cobro a Cliente');
            $tramite_crud->defaultOrdering('tramite.id', 'desc');

            $tramite_crud->where([
                'tramite.created_at >= ?' => ['2026-01-01 00:00:00']
            ]);

            $tramite_crud->columns([
                'id', 'cobro_status_id', 'created_at', 'started_at', 'tra_status_id', 'folio', 'contrato', 'unidad', 'serie',
                'placas', 'tra_tipos_id', 'entidad_id', 'ent_municipio_id', 'cli_directo_id',
                'cli_directo_ejecutivo_id', 'empresa_gestora_id', 'gestor_id',
                'user_id',
                'observaciones'
            ]);

            $tramite_crud->displayAs('started_at', 'Desde Asignacion');
            $tramite_crud->setRelation('user_id', 'users', '{firstname} {midname} {lastname}');
            $tramite_crud->displayAs('user_id', 'Ejecutivo');
            $tramite_crud->displayAs('cobro_status_id', 'Tramite puede ser cobrado');

            $db = \Config\Database::connect();
            $tramite_crud->callbackColumn('cobro_status_id', function ($value, $row) use ($db) {
                if ((int) $value === 23) {
                    return '<span class="badge badge-primary">Cobrado</span>';
                }
                $rows = $db->table('tra_pago_gestor')
                    ->select('comprobante_final')
                    ->where('tramite_id', (int) $row->id)
                    ->where('status', 1)
                    ->get()
                    ->getResultArray();

                $hasTramite = false;
                $hasAcuse = false;
                foreach ($rows as $doc) {
                    $tipo = (string) ($doc['comprobante_final'] ?? '');
                    if ($tipo === 'tramite_recibido') {
                        $hasTramite = true;
                    } elseif ($tipo === 'acuse_recibo_cliente') {
                        $hasAcuse = true;
                    }
                }
                if ($hasTramite && $hasAcuse) {
                    return '<span class="badge badge-success">Listo para Cobrar</span>';
                }
                return '<span class="badge badge-secondary">Pendiente</span>';
            });

            $tramite_crud->fields([
                'folio','contrato','unidad','serie',
                'placas','tra_tipos_id','ent_municipio_id','cli_directo_id',
                'cli_directo_ejecutivo_id','empresa_gestora_id','gestor_id',
                'tra_status_id','cobro_status_id',
                'observaciones', 'user_id'
            ]);

            $tramite_crud->displayAs('created_at', 'Creacion');

            $tramite_crud->setRelation('tra_tipos_id', 'tra_tipos', 'tipo_tramite');
            $tramite_crud->displayAs('tra_tipos_id','Tipo de Tramite');

            $tramite_crud->setRelation('tra_status_id', 'tra_status', 'tra_status');
            $tramite_crud->displayAs('tra_status_id','Estatus del Tramite');

            $clienteRelationFilter = get_cliente_relation_filter($myid);
            if ($clienteRelationFilter !== null) {
                $tramite_crud->setRelation('cli_directo_id', 'cli_directo', 'razon_social', $clienteRelationFilter);
            } else {
                $tramite_crud->setRelation('cli_directo_id', 'cli_directo', 'razon_social');
            }
            $tramite_crud->displayAs('cli_directo_id','Cliente Directo');

            $tramite_crud->setRelation('cli_directo_ejecutivo_id', 'cli_directo_ejecutivo', 'nombre');
            $tramite_crud->displayAs('cli_directo_ejecutivo_id','Ejecutivo del Cliente');
            $tramite_crud->setDependentRelation('cli_directo_ejecutivo_id','cli_directo_id','cli_directo_id');

            $tramite_crud->setRelation('entidad_id', 'entidad', 'entidad');
            $tramite_crud->displayAs('entidad_id','Entidad');

            $tramite_crud->setRelation('ent_municipio_id', 'rel_ent_municipio', 'ent_municipality');
            $tramite_crud->displayAs('ent_municipio_id','Municipio');

            $tramite_crud->setRelation('empresa_gestora_id', 'ges_empresa_gestora', 'razon_social');
            $tramite_crud->displayAs('empresa_gestora_id','Empresa Gestora');

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

    public function cobro_cliente_ver($id)
    {
        if (!validate_tramite_access($id)) {
            log_unauthorized_access_attempt('tramite', $id);
            return redirect()->to('/deskapp/tramitesn/cobro_cliente')
                ->with('error', 'No tienes permiso para ver este tramite');
        }

        helper(['permissions']);
        [$roles, $perms] = $this->normalizeRolesPermsFromSession();
        $canSectionFinal = has_permission('section_final_costos', $perms, $roles);
        if (!(is_super_admin($roles) || is_admin($roles) || $canSectionFinal)) {
            return redirect()->to('/deskapp/tramitesn/cobro_cliente')
                ->with('error', 'No tienes permisos para acceder a Cobro a Cliente');
        }

        // Asegurar que solo se muestre esta vista cuando el trámite está en Cobro a Cliente (28)
        $db = \Config\Database::connect();
        $tramite = $db->table('tramite')->select('tra_status_id')->where('id', (int) $id)->get()->getRowArray();
        $statusId = (int) ($tramite['tra_status_id'] ?? 0);
        if ($statusId !== 28) {
            return redirect()->to('/deskapp/tramitesn/update/' . (int) $id);
        }

        return $this->update($id, 'deskapp/extra-pages/tramite_cobro_cliente_view');
    }

    /**
     * Versión nueva del update del trámite sin Grocery CRUD para el wizard.
     * Mantiene la misma lógica de negocio, pero la vista es 100% custom.
     */
    public function update($id, $viewName = null)
    {
        // ========================================================================
        // VALIDACIÓN DE ACCESO - MULTI-TENANCY
        // ========================================================================
        if (!validate_tramite_access($id)) {
            log_unauthorized_access_attempt('tramite', $id);
            $from = strtolower((string) $this->request->getGet('from'));
            if ($from === 'search') {
                return redirect()->to('/deskapp/tramitesn/search')
                    ->with('error', 'El ejecutivo no tiene acceso a ese recurso.');
            }

            return redirect()->to('/deskapp/tramitesn/tramite')
                ->with('error', '⛔ No tienes permiso para editar este trámite');
        }

        helper(['permissions']);
		$session = session();
        $data['session'] = \Config\Services::session();
        $data['username'] = $session->get('user_name');
        $myid = $session->get('id');
		[$roles, $perms] = $this->normalizeRolesPermsFromSession();
		$canEditPrincipal = (is_super_admin($roles) || is_admin($roles) || has_permission('editar_tramite_principal', $perms, $roles));
		$canEditAsociado = (is_super_admin($roles) || is_admin($roles) || has_permission('editar_tramite_asociado', $perms, $roles));
		$canDeleteAsociado = (is_super_admin($roles) || is_admin($roles) || has_permission('delete_tramite_asociado', $perms, $roles));
        $db = \Config\Database::connect();
        $builder = $db->table('tramite');
        $db2 = $this->_getDbData();

        // 1) Verificar/crear relación en tra_tramite_asociado (incluye tipo principal)
        $tramiteAsociadoModel = new TraTramiteAsociadoModel();
        $tramiteTmp = $builder->getWhere(['id' => $id])->getRowArray();
        $principalTipoId = (int) ($tramiteTmp['tra_tipos_id'] ?? 0);
        if ($principalTipoId > 0) {
            $principalExists = $tramiteAsociadoModel
                ->where('tramite_id', $id)
                ->where('tra_tipos_id', $principalTipoId)
                ->countAllResults();
            if ($principalExists == 0) {
                $tramiteAsociadoModel->saveService($id, $principalTipoId);
            }
        }

        // Recuperar el trámite
        $tramite = $builder->getWhere(['id' => $id])->getRowArray();
        if (!$tramite) {
            return redirect()->to('/deskapp/tramitesn/tramite')
                ->with('error', 'No se encontró el trámite solicitado');
        }

        // Si el trámite está en Cobro a Cliente (28), enviarlo al flujo dedicado.
        // Evita loop cuando ya se invoca update() desde cobro_cliente_ver().
        if (((int) ($tramite['tra_status_id'] ?? 0)) === 28 && $viewName !== 'deskapp/extra-pages/tramite_cobro_cliente_view') {
            return redirect()->to('/deskapp/tramitesn/cobro_cliente/' . $id);
        }

        // Sumatoria de derechos desde costos del tramite (principal + asociados)
        $sumDerechos = 0.0;

        // Catálogos
        $TraTiposModel = new TraTiposModel($db2);
        $tra_tipos_options = $TraTiposModel->getTraTiposOptions();

        // Servicios asociados (incluye tipo principal)
        $servicesRaw = $tramiteAsociadoModel->getServicesByTramiteId($id);
        if (!empty($principalTipoId) && !empty($servicesRaw)) {
            $principalRows = [];
            $otherRows = [];
            foreach ($servicesRaw as $srv) {
                if ((int) ($srv['tra_tipos_id'] ?? 0) === (int) $principalTipoId) {
                    $principalRows[] = $srv;
                } else {
                    $otherRows[] = $srv;
                }
            }
            $servicesRaw = array_merge($principalRows, $otherRows);
        }
        $servicios_asociados = [];
        $servicios_tipos_ids = [];
        foreach ($servicesRaw as $srv) {
            $tipoId = (int) ($srv['tra_tipos_id'] ?? 0);
            if ($tipoId <= 0) {
                continue;
            }
            $rawCosto = $srv['costo_tramite'] ?? 0;
            $costoNum = is_numeric($rawCosto) ? (float) $rawCosto : 0.0;
            $costo = number_format($costoNum, 2, '.', '');
            $servicios_tipos_ids[] = $tipoId;
            $servicios_asociados[] = [
                'asociado_id' => (int) ($srv['id'] ?? 0),
                'tra_tipos_id' => $tipoId,
                'label' => $tra_tipos_options[$tipoId] ?? ('Tipo #' . $tipoId),
                'costo_tramite' => $costo,
            ];
            $sumDerechos += $costoNum;
        }
        $tramite['costo_gestoria'] = number_format($sumDerechos, 2, '.', '');

        $entidades = new EntidadesModel($db2);
        $entidad_options = $entidades->getEntidades();

        $clienteDirecto = new ClienteDirectoModel($db2);
        $cli_directo_options = $clienteDirecto->getClientesDirectosOptions();

        // Opciones dependientes (para que el wizard cargue con valores existentes)
        $cliEjecutivoModel = new ClienteDirectoEjecutivoModel($db2);
        $cli_ejecutivo_options = [];
        if (!empty($tramite['cli_directo_id'])) {
            $cli_ejecutivo_options = $cliEjecutivoModel->getEjecutivosOptions($tramite['cli_directo_id']);
        }

        $empGestora = new EmpresaGestoraModel($db2);
        $empresa_gestora_options = $empGestora->getEmpresasGestorasOptions();

        $gestor_model = new GestorModel($db2);
        $gestor_options = [];
        if (!empty($tramite['empresa_gestora_id'])) {
            $gestor_options = $gestor_model->getGestoresOptions($tramite['empresa_gestora_id']);
        }
        $gestor_nombre = $gestor_model->getGestorNameById($tramite['gestor_id']);

        $traStatus = new TraStatusModel($db2);
        $tra_status_obj = $traStatus->getTraStatusOptions();
        $tra_status_options = $tra_status_obj['tra_status'];
        $tra_status_steps = $tra_status_obj['steps'];

        $reembolso_status = new ReembolsoStatusModel($db2);
        $reembolso_status_options = $reembolso_status->getReembolsoStatusOptions();

        $cobro_status = new CobroStatusModel($db2);
        $cobro_status_options = $cobro_status->getCobroStatusOptions();

        $pago_derechos = new PagoDerechosModel($db2);
        $pago_derechos_db = $pago_derechos->getImgDerechosByTramiteId($id);

        $pago_gestor_db = [];
        try {
            $pago_gestor_db = $db->table('tra_pago_gestor')
                ->select('file, comprobante_final')
                ->where('tramite_id', (int) $id)
                ->orderBy('id', 'DESC')
                ->get()
                ->getResultArray();
        } catch (\Exception $e) {
            $pago_gestor_db = [];
        }

        $hasComprobanteTramiteRecibido = false;
        $hasComprobanteAcuseRecibo = false;
        foreach ($pago_gestor_db as $rowDoc) {
            $tipo = (string) ($rowDoc['comprobante_final'] ?? '');
            if ($tipo === 'tramite_recibido') {
                $hasComprobanteTramiteRecibido = true;
            } elseif ($tipo === 'acuse_recibo_cliente') {
                $hasComprobanteAcuseRecibo = true;
            }
        }

        $final_docs_db = [
            16 => null,
            17 => null,
        ];
        try {
            $rowsFinalDocs = $db->table('tra_doc_status')
                ->select('id, documento_id, file')
                ->where('tramite_id', (int) $id)
                ->whereIn('documento_id', [16, 17])
                ->where('status', 1)
                ->orderBy('id', 'DESC')
                ->get()
                ->getResultArray();
            foreach ($rowsFinalDocs as $rowDoc) {
                $docId = (int) ($rowDoc['documento_id'] ?? 0);
                if ($docId > 0 && isset($final_docs_db[$docId]) && $final_docs_db[$docId] === null) {
                    $final_docs_db[$docId] = $rowDoc;
                }
            }
        } catch (\Exception $e) {
            $final_docs_db = [16 => null, 17 => null];
        }

        $pago_gestor_st = new PagoGestorStatusModel($db2);
        $pago_gestor_st_opciones = $pago_gestor_st->getPagoGestorStatusOptions();

        $form = new \stdClass();

        // Campos Paso 1: Datos generales
        $form->fields = [
            'folio' => [
                'label' => 'Folio',
                'type'  => 'hidden',
                'value' => $tramite['folio'],
            ],
            'contrato' => [
                'label'    => 'Contrato',
                'type'     => 'text',
                'value'    => $tramite['contrato'],
                'required' => true,
            ],
            'unidad' => [
                'label' => 'Unidad',
                'type'  => 'text',
                'value' => $tramite['unidad'],
            ],
            'serie' => [
                'label' => 'Serie',
                'type'  => 'text',
                'value' => $tramite['serie'],
            ],
            'placas' => [
                'label' => 'Placas',
                'type'  => 'text',
                'value' => $tramite['placas'],
            ],
            'cli_directo_id' => [
                'label'   => 'Cliente',
                'type'    => 'select',
                'options' => $cli_directo_options,
                'value'   => $tramite['cli_directo_id'],
            ],
            'cli_directo_ejecutivo_id' => [
                'label'   => 'Ejecutivo de Cliente',
                'type'    => 'select',
                'options' => $cli_ejecutivo_options,
                'value'   => $tramite['cli_directo_ejecutivo_id'],
            ],
            'entidad_id' => [
                'label'    => 'Entidad',
                'type'     => 'select',
                'options'  => $entidad_options,
                'value'    => $tramite['entidad_id'],
                'required' => true,
            ],
            'observaciones' => [
                'label' => 'Observaciones',
                'type'  => 'textarea',
                'value' => $tramite['observaciones'],
            ],
        ];

        // Campos Paso 2: Asignación gestor / empresa gestora
        $form->gestor_campos = [
            'empresa_gestora_id' => [
                'label'    => 'Empresa Gestora',
                'type'     => 'select',
                'options'  => $empresa_gestora_options,
                'value'    => $tramite['empresa_gestora_id'],
                'required' => true,
            ],
            'gestor_id' => [
                'label'    => 'Gestor',
                'type'     => 'select',
                'options'  => $gestor_options,
                'value'    => $tramite['gestor_id'],
                'required' => true,
            ],
        ];

        // Campos Paso 3: Derechos base
        $form->derechos_campos = [
            'derechos_tramite' => [
                'label'    => 'Monto pago de derechos',
                'type'     => 'number',
                'value'    => $tramite['derechos_tramite'],
                'required' => true,
            ],
            'derechos_pago_sitio' => [
                'label'   => 'Pago',
                'type'    => 'select',
                'options' => [
                    'online'    => 'En Línea',
                    'ventanilla'=> 'En Ventanilla',
                ],
                'value'   => $tramite['derechos_pago_sitio'],
            ],
            'derechos_vigencia' => [
                'label' => 'Fecha Vigencia',
                'type'  => 'datetime',
                'value' => $tramite['derechos_vigencia'],
            ],
            'derechos_revol_cliente' => [
                'label'    => 'Forma de Pago',
                'type'     => 'select',
                'options'  => [
                    'revolvente' => 'Fondo Revolvente',
                    'cliente'    => 'Pago Cliente',
                ],
                'value'    => $tramite['derechos_revol_cliente'],
                'required' => true,
            ],
            'derechos_refer_banc' => [
                'label'    => 'Referencia Bancaria',
                'type'     => 'text',
                'value'    => $tramite['derechos_refer_banc'],
                'required' => true,
            ],
        ];

        $labelOrId = static function ($options, $value) {
            if ($value === null || $value === '') {
                return '';
            }
            if (is_array($options) && array_key_exists($value, $options)) {
                return $options[$value];
            }
            return 'ID ' . $value;
        };
        $derechosPagoMap = [
            'online' => 'En Linea',
            'ventanilla' => 'En Ventanilla',
        ];
        $derechosFormaMap = [
            'revolvente' => 'Fondo Revolvente',
            'cliente' => 'Pago Cliente',
        ];

        $readonly_step1 = [
            ['label' => 'Contrato', 'value' => $tramite['contrato']],
            ['label' => 'Unidad', 'value' => $tramite['unidad']],
            ['label' => 'Serie', 'value' => $tramite['serie']],
            ['label' => 'Placas', 'value' => $tramite['placas']],
            ['label' => 'Cliente', 'value' => $labelOrId($cli_directo_options, $tramite['cli_directo_id'])],
            ['label' => 'Ejecutivo de Cliente', 'value' => $labelOrId($cli_ejecutivo_options, $tramite['cli_directo_ejecutivo_id'])],
            ['label' => 'Entidad', 'value' => $labelOrId($entidad_options, $tramite['entidad_id'])],
            ['label' => 'Observaciones', 'value' => $tramite['observaciones']],
        ];
        $readonly_step2 = [
            ['label' => 'Empresa Gestora', 'value' => $labelOrId($empresa_gestora_options, $tramite['empresa_gestora_id'])],
            ['label' => 'Gestor', 'value' => $gestor_nombre],
        ];
        $readonly_step3 = [
            ['label' => 'Monto pago de derechos', 'value' => $tramite['derechos_tramite']],
            ['label' => 'Pago', 'value' => $derechosPagoMap[$tramite['derechos_pago_sitio']] ?? ($tramite['derechos_pago_sitio'] ?? '')],
            ['label' => 'Fecha Vigencia', 'value' => $tramite['derechos_vigencia']],
            ['label' => 'Forma de Pago', 'value' => $derechosFormaMap[$tramite['derechos_revol_cliente']] ?? ($tramite['derechos_revol_cliente'] ?? '')],
            ['label' => 'Referencia Bancaria', 'value' => $tramite['derechos_refer_banc']],
        ];

        // Campos Paso 4: Pago a Gestor (custom, sin Grocery CRUD)
        $form->pago_gestor_campos = [
            'gestor_name' => [
                'label' => 'Gestor',
                'type' => 'text',
                'value' => $gestor_nombre,
                'readonly' => true,
            ],
            'costo_tramite' => [
                'label' => 'Costo del Tramite',
                'type' => 'number',
                'value' => $tramite['costo_tramite'],
            ],
            'deposito_gestor' => [
                'label' => 'Deposito a Gestor',
                'type' => 'number',
                'value' => $tramite['deposito_gestor'],
            ],
            'col_a_favor' => [
                'label' => 'Saldo Pendiente',
                'type' => 'number',
                'value' => $tramite['col_a_favor'],
            ],
            'num_factura_gestor' => [
                'label' => 'Numero de Factura',
                'type' => 'text',
                'value' => $tramite['num_factura_gestor'],
            ],
            'pago_gestor_st_id' => [
                'label' => 'Estatus del Pago',
                'type' => 'select',
                'options' => $pago_gestor_st_opciones,
                'value' => $tramite['pago_gestor_st_id'],
            ],
            'impuesto_gestoria' => [
                'label' => 'Honorarios de Gestoria',
                'type' => 'number',
                'value' => $tramite['impuesto_gestoria'],
            ],
            'gestoria_comision' => [
                'label' => 'Gratificacion',
                'type' => 'number',
                'value' => $tramite['gestoria_comision'],
            ],
            'costo_paqueteria' => [
                'label' => 'Costo Paqueteria',
                'type' => 'number',
                'value' => $tramite['costo_paqueteria'] ?? 0,
            ],
            'gestor_total_pago' => [
                'label' => 'Pago Total',
                'type' => 'number',
                'value' => $tramite['gestor_total_pago'],
            ],
            'reembolso_status_id' => [
                'label' => 'Estatus del Reembolso',
                'type' => 'select',
                'options' => $reembolso_status_options,
                'value' => $tramite['reembolso_status_id'],
                'required' => true,
            ],
        ];

        $baseIva = 0.0;
        $baseIva += is_numeric($tramite['costo_pago_cliente']) ? (float) $tramite['costo_pago_cliente'] : 0.0;
        $baseIva += is_numeric($tramite['comision_derechos']) ? (float) $tramite['comision_derechos'] : 0.0;
        $ivaCalc = round($baseIva * 0.16, 2);
        $costoTotalCalc = round($sumDerechos + $baseIva + $ivaCalc, 2);

        $tramite['iva'] = number_format($ivaCalc, 2, '.', '');
        $tramite['costo_total'] = number_format($costoTotalCalc, 2, '.', '');

        $form->final_campos = [
            'id_give_cliente' => [
                'label' => 'ID que da el cliente',
                'type' => 'text',
                'value' => $tramite['id_give_cliente'],
                'required' => 'required',
            ],
            'separador_costos' => [
                'type' => 'hr',
            ],
            'numero_factura' => [
                'label' => 'Numero de Factura',
                'type' => 'text',
                'value' => $tramite['numero_factura'],
                'required' => 'required',
            ],
            'numero_refactura' => [
                'label' => 'Numero de Refactura',
                'type' => 'text',
                'value' => $tramite['numero_refactura'],
            ],
            'cobro_status_id' => [
                'label' => 'Estatus del Cobro',
                'type' => 'select',
                'options' => $cobro_status_options,
                'value' => $tramite['cobro_status_id'],
            ],
            'evidencia_cobro_txt' => [
                'label' => 'Evidencia de cobro',
                'type' => 'textarea',
                'value' => $tramite['evidencia_cobro_txt'] ?? '',
                'maxlength' => 100,
            ],
            'separador_costos2' => [
                'type' => 'hr',
            ],
            'costo_gestoria' => [
                'label' => 'Sumatoria de Derechos',
                'type' => 'number',
                'value' => $tramite['costo_gestoria'],
                'disabled' => 'disabled',
            ],
            'costo_gestoria_hidden' => [
                'label' => 'Sumatoria de Derechos',
                'type' => 'hidden',
                'value' => $tramite['costo_gestoria'],
            ],
            'costo_pago_cliente' => [
                'label' => 'Honorarios del Tramite',
                'type' => 'number',
                'value' => $tramite['costo_pago_cliente'],
                'required' => 'required',
            ],
            'comision_derechos' => [
                'label' => 'Comision de Derechos',
                'type' => 'number',
                'value' => $tramite['comision_derechos'],
                'required' => 'required',
            ],
            'iva' => [
                'label' => 'IVA ($)',
                'type' => 'number',
                'value' => $tramite['iva'],
            ],
            'costo_total' => [
                'label' => 'Costo Total',
                'type' => 'number',
                'value' => $tramite['costo_total'],
                'disabled' => 'disabled',
            ],
        ];

        // Flags de completado por paso
        $step1Complete = !empty($tramite['contrato']) && !empty($tramite['entidad_id']);
        $step2Complete = !empty($tramite['empresa_gestora_id']) && !empty($tramite['gestor_id']);
        $step3Complete = !empty($tramite['derechos_tramite']) && !empty($tramite['derechos_revol_cliente']) && !empty($tramite['derechos_refer_banc']);

        $canUploadDerechos = puede_editar_modulo($roles, (int) $tramite['tra_status_id'], 'step3_upload', (int) $tramite['reembolso_status_id'], (int) $tramite['cobro_status_id'], 3);
        $canSectionPagoDerechos = has_permission('section_pago_derechos', $perms, $roles);
        $canSectionPagoGestor = has_permission('section_pago_gestor', $perms, $roles);
        $canSectionFinalCostos = has_permission('section_final_costos', $perms, $roles);
        $canEditPagoGestor = puede_editar_modulo($roles, (int) $tramite['tra_status_id'], 'editar_pago_gestor', (int) $tramite['reembolso_status_id'], (int) $tramite['cobro_status_id'], 4);
        $canUploadPagoGestor = puede_editar_modulo($roles, (int) $tramite['tra_status_id'], 'upload_pago_gestor', (int) $tramite['reembolso_status_id'], (int) $tramite['cobro_status_id'], 4);
        $canUploadFinalDocs = puede_editar_modulo($roles, (int) $tramite['tra_status_id'], 'upload_cobro_cliente', (int) $tramite['reembolso_status_id'], (int) $tramite['cobro_status_id'], 5);

        if (isset($tramite['costo_tramite']) && $tramite['costo_tramite'] > 0) {
            $tramite['costo_tramite'] = number_format($tramite['costo_tramite'], 2, '.', '');
        } else {
            $tramite['costo_tramite'] = 0;
        }

        $data['id'] = $id;
        $data['folio'] = $tramite['folio'];
        $data['tra_status'] = $tra_status_options[$tramite['tra_status_id']] ?? '';
        $data['tra_status_id'] = $tramite['tra_status_id'];
        $data['created_at'] = $tramite['created_at'];
        $data['step'] = $tra_status_steps[$tramite['tra_status_id']] ?? 1;
        $data['started_at'] = $tramite['started_at'];
        $data['derechos_comprobante'] = $tramite['derechos_comprobante'];
        $data['reembolso_status_id'] = $tramite['reembolso_status_id'];
        $data['cobro_status_id'] = $tramite['cobro_status_id'];
        $data['sumatoria_derechos'] = $sumDerechos;

        $data['tipo_tramite'] = $tra_tipos_options[$tramite['tra_tipos_id']] ?? 'N/A';
        $data['cliente'] = $cli_directo_options[$tramite['cli_directo_id']] ?? 'N/A';
        $data['gestor'] = $gestor_nombre ?? 'Sin asignar';
        $data['empresa_gestora'] = $empresa_gestora_options[$tramite['empresa_gestora_id']] ?? 'Sin asignar';

        $form->id = $id;

        $crud = $this->_getGroceryCrudEnterprise();
        $crudOutput = $crud->render();

        $form->css_files = $crudOutput->css_files;
        $form->js_files = $crudOutput->js_files;

        $isLocked = in_array((int) ($tramite['tra_status_id'] ?? 0), [20, 21], true);

        $cruddocstatus = $this->_getGroceryCrudEnterprise();
		$cruddocstatus->setApiUrlPath(($isLocked ? '/deskapp/concluido' : '/deskapp/tramites') . '/single_documentostatus/' . $id);
        $output_docs = $cruddocstatus->render();

        $crudevidencias = $this->_getGroceryCrudEnterprise();
		$crudevidencias->setApiUrlPath(($isLocked ? '/deskapp/concluido' : '/deskapp/tramites') . '/single_evidencias/' . $id);
        $outputevidencias = $crudevidencias->render();

        $crud_derechos = $this->_getGroceryCrudEnterprise();
		$crud_derechos->setApiUrlPath(($isLocked ? '/deskapp/concluido' : '/deskapp/tramites') . '/single_pago_derechos/' . $id);
        $output_derechos = $crud_derechos->render();

        $crud_pago_gestor = $this->_getGroceryCrudEnterprise();
		if (!$isLocked && puede_editar_modulo($session->get('user_roles'), $tramite['tra_status_id'], 'evidencias_finales_gestor', $tramite['reembolso_status_id'], $tramite['cobro_status_id'], $tramite['tra_status_id'])) {
            $crud_pago_gestor->setApiUrlPath('/deskapp/tramites/single_pago_gestor/' . $id);
        } else {
            $crud_pago_gestor->setApiUrlPath('/deskapp/concluido/single_pago_gestor/' . $id);
        }
        $output_pago_gestor = $crud_pago_gestor->render();

        $crud_cobro_cliente = $this->_getGroceryCrudEnterprise();
		if (!$isLocked && puede_editar_modulo($session->get('user_roles'), $tramite['tra_status_id'], 'evidencias_finales_cliente', $tramite['reembolso_status_id'], $tramite['cobro_status_id'], $tramite['tra_status_id'])) {
            $crud_cobro_cliente->setApiUrlPath('/deskapp/tramites/single_cobro_cliente/' . $id);
        } else {
            $crud_cobro_cliente->setApiUrlPath('/deskapp/concluido/single_cobro_cliente/' . $id);
        }
        $output_cobro_cliente = $crud_cobro_cliente->render();

        $crudevidencias_finales = $this->_getGroceryCrudEnterprise();
		if (!$isLocked && puede_editar_modulo($session->get('user_roles'), $tramite['tra_status_id'], 'evidencias_finales_cliente', $tramite['reembolso_status_id'], $tramite['cobro_status_id'], $tramite['tra_status_id'])) {
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

        // Fusionar datos para la vista nueva (sin Grocery CRUD)
        $viewData = array_merge((array) $form, $data);
        $viewData['tra_tipos_options'] = $tra_tipos_options;
        $viewData['principal_tipo_id'] = (int) ($tramite['tra_tipos_id'] ?? 0);
        $viewData['servicios_asociados'] = $servicios_asociados;
        $viewData['servicios_tipos_ids'] = array_values(array_unique($servicios_tipos_ids));
		$viewData['can_edit_principal'] = $canEditPrincipal;
		$viewData['can_edit_asociado'] = $canEditAsociado;
		$viewData['can_delete_asociado'] = $canDeleteAsociado;
        $viewData['user_roles'] = $roles;
        $viewData['user_permissions'] = $perms;
        $viewData['pago_derechos_db'] = $pago_derechos_db;
        $viewData['pago_gestor_db'] = $pago_gestor_db;
        $viewData['has_comprobante_tramite_recibido'] = $hasComprobanteTramiteRecibido;
        $viewData['has_comprobante_acuse_recibo'] = $hasComprobanteAcuseRecibo;
        $viewData['final_docs_db'] = $final_docs_db;
        $viewData['pago_gestor_campos'] = $form->pago_gestor_campos;
        $viewData['final_campos'] = $form->final_campos;
        $viewData['readonly_step1'] = $readonly_step1;
        $viewData['readonly_step2'] = $readonly_step2;
        $viewData['readonly_step3'] = $readonly_step3;
        $viewData['step1_complete'] = $step1Complete;
        $viewData['step2_complete'] = $step2Complete;
        $viewData['step3_complete'] = $step3Complete;
        $viewData['can_upload_derechos'] = $canUploadDerechos;
        $viewData['can_section_pago_derechos'] = $canSectionPagoDerechos;
        $viewData['can_section_pago_gestor'] = $canSectionPagoGestor;
        $viewData['can_section_final_costos'] = $canSectionFinalCostos;
        $viewData['can_edit_pago_gestor'] = $canEditPagoGestor;
        $viewData['can_upload_pago_gestor'] = $canUploadPagoGestor;
        $viewData['can_upload_final_docs'] = $canUploadFinalDocs;

        $targetView = $viewName ?: 'deskapp/extra-pages/tramite_update_view_nuevo';
        return view($targetView, $viewData);
    }

    public function upload_final_doc($tramiteId = null, $documentoId = null)
    {
        helper(['permissions', 'cliente_filter']);

        $session = session();
        $userId = (int) $session->get('id');
        if ($userId <= 0) {
            return $this->response->setStatusCode(401)->setJSON(['success' => false, 'message' => 'Sesión expirada.']);
        }

        $roles = $session->get('user_roles') ?? [];
        if (!is_array($roles)) {
            $roles = [$roles];
        }
        $perms = $session->get('user_permissions') ?? [];
        if (!is_array($perms)) {
            $perms = [$perms];
        }

        $tramiteId = (int) $tramiteId;
        $documentoId = (int) $documentoId;
        if ($tramiteId <= 0 || !in_array($documentoId, [16, 17], true)) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Parámetros inválidos.']);
        }

        $hasTenantAccess = (is_super_admin($roles) || is_admin($roles)) ? true : validate_tramite_access($tramiteId, $userId);
        if (!$hasTenantAccess) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Acceso denegado.']);
        }

        if (!(is_super_admin($roles) || is_admin($roles)) && !has_permission('section_final_costos', $perms, $roles)) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Acceso denegado.']);
        }

        $db = \Config\Database::connect();
        $tramiteRow = $db->table('tramite')
            ->select('id, folio, tra_status_id, reembolso_status_id, cobro_status_id')
            ->where('id', $tramiteId)
            ->get(1)
            ->getRowArray();
        if (empty($tramiteRow)) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Trámite no encontrado.']);
        }

        $traStatusId = (int) ($tramiteRow['tra_status_id'] ?? 0);
        $reembolsoStatusId = (int) ($tramiteRow['reembolso_status_id'] ?? 0);
        $cobroStatusId = (int) ($tramiteRow['cobro_status_id'] ?? 0);

        if ($this->isLockedStatusId($traStatusId)) {
            return $this->response->setStatusCode(409)->setJSON(['success' => false, 'message' => 'El trámite está concluido o cancelado.']);
        }
        if (!puede_editar_modulo($roles, $traStatusId, 'upload_cobro_cliente', $reembolsoStatusId, $cobroStatusId, 5)) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Acceso denegado.']);
        }

        if (empty($_FILES['file'])) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'No se recibió ningún archivo.']);
        }

        $ds = DIRECTORY_SEPARATOR;
        $storeFolder = 'assets/uploads/documentostatus';
        $targetPath = FCPATH . $storeFolder . $ds;
        if (!is_dir($targetPath)) {
            mkdir($targetPath, 0777, true);
        }

        $tempFile = $_FILES['file']['tmp_name'];
        $originalName = (string) ($_FILES['file']['name'] ?? '');
        $extension = strtolower((string) pathinfo($originalName, PATHINFO_EXTENSION));
        $baseName = (string) pathinfo($originalName, PATHINFO_FILENAME);
        $safeBase = preg_replace('/[^a-zA-Z0-9_-]+/', '_', $baseName);
        $safeBase = trim((string) $safeBase, '_');
        if ($safeBase === '') {
            $safeBase = 'documento';
        }
        try {
            $random = bin2hex(random_bytes(8));
        } catch (\Exception $e) {
            $random = uniqid();
        }
        $fileName = $safeBase . '_' . $tramiteId . '_' . $documentoId . '_' . $random . ($extension !== '' ? '.' . $extension : '');
        $targetFile = $targetPath . $fileName;

        if (!move_uploaded_file($tempFile, $targetFile)) {
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'No se pudo mover el archivo.']);
        }

        try {
            $existingRows = $db->table('tra_doc_status')
                ->select('id, file')
                ->where('tramite_id', $tramiteId)
                ->where('documento_id', $documentoId)
                ->where('status', 1)
                ->get()
                ->getResultArray();

            foreach ($existingRows as $existing) {
                $existingFile = trim((string) ($existing['file'] ?? ''));
                if ($existingFile !== '' && $existingFile === basename($existingFile) && strpos($existingFile, '..') === false) {
                    $existingPath = $targetPath . $existingFile;
                    if (is_file($existingPath)) {
                        @unlink($existingPath);
                    }
                }
            }

            $db->table('tra_doc_status')
                ->where('tramite_id', $tramiteId)
                ->where('documento_id', $documentoId)
                ->delete();

            $comentario = 'se sube documento desde dropzone de paso 4';
            $insertData = [
                'folio_tramite' => (string) ($tramiteRow['folio'] ?? ''),
                'tramite_id' => $tramiteId,
                'documento_id' => $documentoId,
                'status_documento_id' => 11,
                'file' => $fileName,
                'comentario' => $comentario,
                'user_id' => $userId,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                'status' => 1,
            ];
            $db->table('tra_doc_status')->insert($insertData);

            $filePath = base_url('/assets/uploads/documentostatus/' . $fileName);
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Documento subido correctamente.',
                'filePath' => $filePath,
                'fileName' => $fileName,
                'documento_id' => $documentoId,
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Error en upload_final_doc: ' . $e->getMessage());
            @unlink($targetFile);
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Error al guardar el documento.']);
        }
    }

    public function delete_final_doc()
    {
        helper(['permissions', 'cliente_filter']);

        $session = session();
        $userId = (int) $session->get('id');
        if ($userId <= 0) {
            return $this->response->setStatusCode(401)->setJSON(['success' => false, 'message' => 'Sesión expirada.']);
        }

        $roles = $session->get('user_roles') ?? [];
        if (!is_array($roles)) {
            $roles = [$roles];
        }
        $perms = $session->get('user_permissions') ?? [];
        if (!is_array($perms)) {
            $perms = [$perms];
        }

        $request = \Config\Services::request();
        $tramiteId = (int) $request->getPost('tramite_id');
        $documentoId = (int) $request->getPost('documento_id');
        $fileName = trim((string) $request->getPost('file'));

        if ($tramiteId <= 0 || !in_array($documentoId, [16, 17], true)) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Parámetros inválidos.']);
        }

        $hasTenantAccess = (is_super_admin($roles) || is_admin($roles)) ? true : validate_tramite_access($tramiteId, $userId);
        if (!$hasTenantAccess) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Acceso denegado.']);
        }

        if (!(is_super_admin($roles) || is_admin($roles)) && !has_permission('section_final_costos', $perms, $roles)) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Acceso denegado.']);
        }

        $db = \Config\Database::connect();
        $tramiteRow = $db->table('tramite')
            ->select('id, tra_status_id, reembolso_status_id, cobro_status_id')
            ->where('id', $tramiteId)
            ->get(1)
            ->getRowArray();
        if (empty($tramiteRow)) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Trámite no encontrado.']);
        }

        $traStatusId = (int) ($tramiteRow['tra_status_id'] ?? 0);
        $reembolsoStatusId = (int) ($tramiteRow['reembolso_status_id'] ?? 0);
        $cobroStatusId = (int) ($tramiteRow['cobro_status_id'] ?? 0);

        if ($this->isLockedStatusId($traStatusId)) {
            return $this->response->setStatusCode(409)->setJSON(['success' => false, 'message' => 'El trámite está concluido o cancelado.']);
        }
        if (!puede_editar_modulo($roles, $traStatusId, 'upload_cobro_cliente', $reembolsoStatusId, $cobroStatusId, 5)) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Acceso denegado.']);
        }

        try {
            $builder = $db->table('tra_doc_status');
            $builder->where('tramite_id', $tramiteId);
            $builder->where('documento_id', $documentoId);
            if ($fileName !== '') {
                if ($fileName !== basename($fileName) || strpos($fileName, '..') !== false || strpos($fileName, "\0") !== false) {
                    return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Nombre de archivo inválido.']);
                }
                $builder->where('file', $fileName);
            }

            $rows = $builder->get()->getResultArray();
            if (empty($rows)) {
                return $this->response->setJSON(['success' => false, 'message' => 'No se encontró documento para eliminar.']);
            }

            $ds = DIRECTORY_SEPARATOR;
            $targetPath = FCPATH . 'assets/uploads/documentostatus' . $ds;

            foreach ($rows as $row) {
                $file = trim((string) ($row['file'] ?? ''));
                if ($file !== '' && $file === basename($file) && strpos($file, '..') === false) {
                    $fullPath = $targetPath . $file;
                    if (is_file($fullPath)) {
                        @unlink($fullPath);
                    }
                }
            }

            $deleteBuilder = $db->table('tra_doc_status')
                ->where('tramite_id', $tramiteId)
                ->where('documento_id', $documentoId);
            if ($fileName !== '') {
                $deleteBuilder->where('file', $fileName);
            }
            $deleteBuilder->delete();

            return $this->response->setJSON(['success' => true, 'message' => 'Documento eliminado correctamente.']);
        } catch (\Throwable $e) {
            log_message('error', 'Error en delete_final_doc: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Error al eliminar documento.']);
        }
    }

    public function encontrarDiferencias($datos1, $datos2)
    {
        $diferencias = [];
        foreach ($datos1 as $clave => $valor) {
            if (array_key_exists($clave, $datos2) && $datos2[$clave] !== $valor) {
                $diferencias[$clave] = [
                    'valor_original' => $valor,
                    'valor_nuevo' => $datos2[$clave]
                ];
            } else {
                $diferencias[$clave] = [
                    'valor_original' => $valor,
                    'valor_nuevo' => ''
                ];
            }
        }
        return $diferencias;
    }

    private function buildBitacoraChanges(array $changes)
    {
        $diferencias = [];
        foreach ($changes as $field => $values) {
            $diferencias[$field] = [
                'valor_original' => $values['old'] ?? null,
                'valor_nuevo' => $values['new'] ?? null,
            ];
        }
        return $diferencias;
    }
}
