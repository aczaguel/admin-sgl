<?php

/**
 * ============================================================================
 * FILTRO DE AUTORIZACIÓN POR CLIENTE - MULTI-TENANCY
 * ============================================================================
 * 
 * Este filtro se aplica automáticamente en las rutas que requieren
 * validación de acceso basada en la relación cliente_user.
 * 
 * PROPÓSITO:
 * - Validar que el usuario tenga acceso al recurso solicitado
 * - Prevenir acceso no autorizado a información de otros clientes
 * - Registrar intentos de acceso no autorizado
 * 
 * USO:
 * En app/Config/Filters.php agregar:
 * 
 * public $filters = [
 *     'cliente_access' => [
 *         'before' => [
 *             'deskapp/tramites/*',
 *             'deskapp/customers/*'
 *         ]
 *     ]
 * ];
 * 
 * ============================================================================
 */

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class ClienteAccessFilter implements FilterInterface
{
    /**
     * Validación antes de ejecutar el controlador
     *
     * @param RequestInterface $request
     * @param mixed $arguments
     * @return mixed
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        helper('cliente_filter');
        
        $session = session();
        
        // Verificar que el usuario esté autenticado
        if (!$session->get('logged_in')) {
            return redirect()->to('/deskapp/login')
                ->with('error', 'Debe iniciar sesión para acceder');
        }
        
        $userId = $session->get('id');
        
        // Verificar si es una solicitud a un trámite específico
        // Ejemplo: /deskapp/tramites/ver/123
        $uri = $request->getUri();
        $segments = $uri->getSegments();
        
        // Buscar ID de trámite en los segmentos
        $tramiteId = null;
        foreach ($segments as $index => $segment) {
            if (in_array($segment, ['ver', 'edit', 'view', 'detalle']) && isset($segments[$index + 1])) {
                $tramiteId = $segments[$index + 1];
                break;
            }
        }
        
        // Si se está accediendo a un trámite específico, validar acceso
        if ($tramiteId && is_numeric($tramiteId)) {
            if (!validate_tramite_access($tramiteId, $userId)) {
                // Registrar intento de acceso no autorizado
                log_unauthorized_access_attempt('tramite', $tramiteId, $userId);
                
                return redirect()->back()
                    ->with('error', '⛔ No tienes permiso para acceder a este recurso');
            }
        }
        
        // Verificar que el usuario tenga al menos un cliente asignado
        $clienteIds = get_user_cliente_ids($userId);
        
        if (empty($clienteIds)) {
            // Usuario sin clientes asignados
            log_message('warning', sprintf(
                'Usuario %d (%s) no tiene clientes asignados',
                $userId,
                $session->get('username')
            ));
            
            // Permitir acceso pero sin datos
            // El filtro SQL en las consultas se encargará de no mostrar nada
        }
        
        return $request;
    }

    /**
     * Procesamiento después de ejecutar el controlador
     *
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param mixed $arguments
     * @return mixed
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No se requiere procesamiento posterior
        return $response;
    }
}
