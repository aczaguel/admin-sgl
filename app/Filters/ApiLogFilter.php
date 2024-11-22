<?php
namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use App\Models\ApiLogModel;

class ApiLogFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // No se necesita acción antes de procesar la solicitud
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        $userId = session()->get('id'); // Obtener el ID del usuario desde la sesión
        $uri = $request->getUri()->getPath(); // Obtener el path completo de la URL

        // Verificar exclusiones: si es la raíz ("/") o contiene 'login', omitir
        if ($uri === '/' || strpos($uri, 'login') !== false) {
            return; // Omitir registros de la raíz y de login
        }

        // Filtrar solo solicitudes de tipo insert, update o delete
        $method = $request->getMethod();
        if (!in_array(strtolower($method), ['post', 'put', 'delete', 'patch'])) {
            return; // Si no es POST, PUT o DELETE, salir
        }

        // Conectar a la base de datos
        $db = \Config\Database::connect();
        $lastQuery = $db->getLastQuery(); // Obtener la última consulta ejecutada
        $queryString = $lastQuery ? $lastQuery->__toString() : '';

        // Extraer el nombre de la tabla con una expresión regular
        preg_match('/(?:into|update|delete from)\s+`?(\w+)`?/i', $queryString, $matches);
        $tableName = $matches[1] ?? 'unknown_table'; // Asignar 'unknown_table' si no se encuentra coincidencia 

        // Extraer controlador y acción
        $controller = $request->uri->getSegment(2); // Segundo segmento como controlador
        $action = $request->uri->getSegment(3);     // Tercer segmento como acción

        // Capturar todos los números de la URL como IDs
        preg_match_all('/\d+/', $uri, $matches);
        $numbers = $matches[0] ?? []; // Acceder al primer índice del arreglo

        $sent_id = isset($numbers[0]) ? (int)$numbers[0] : null; // Primer número encontrado
        $actionIds = count($numbers) > 1 ? implode(',', array_slice($numbers, 1)) : null; // Resto de los números concatenados

        // Determinar el contenido de la respuesta
        $responseContent = $response->getHeaderLine('Content-Type');
        $responseBody = (strpos($responseContent, 'application/json') !== false) 
                        ? $response->getBody() 
                        : 'html response';

        // Instanciar el modelo
        $logModel = new ApiLogModel();

        // Preparar datos para registrar en la base de datos
        $logData = [
            'method'     => $method,
            'endpoint'   => $uri,
            'controller' => $controller,
            'action'     => $action,
            'sent_id'      => $sent_id,              // Primer número encontrado
            'vista' => $actionIds,         // Números restantes concatenados
            'body'       => json_encode($request->getPost() ?: $request->getJSON(true)), // Datos enviados
            'tabla'      => $tableName,
            'response'   => $responseBody,
            'user_id'    => $userId,
            'ip_address' => $request->getIPAddress(),
            'user_agent' => $request->getUserAgent()->getAgentString(),
        ];
        // Insertar el log en la base de datos
        $logModel->insert($logData);
    }
}
