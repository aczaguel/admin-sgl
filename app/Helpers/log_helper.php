<?php

use App\Models\ApiLogModel;
use CodeIgniter\HTTP\IncomingRequest;

if (!function_exists('logOperation')) {
    function logOperation($postArray, $tableName)
    {
        $session = session(); // Obtener la sesión
        $request = service('request'); // Obtener el servicio de la solicitud
        $response = service('response'); // Obtener la respuesta
        $userId = $session->get('id'); // ID del usuario desde la sesión

        $uri = $request->getUri()->getPath(); // Obtener la URL completa

        // Verificar exclusiones
        if ($uri === '/' || strpos($uri, 'login') !== false) {
            return $postArray; // Omitir registros
        }

        $method = $request->getMethod();
        if (!in_array(strtolower($method), ['post', 'put', 'delete', 'patch'])) {
            return $postArray; // Salir si no es una operación relevante
        }

        // Extraer controlador y acción
        $controller = $request->uri->getSegment(2); // Segundo segmento
        $action = $request->uri->getSegment(3);     // Tercer segmento

        // Capturar IDs en la URL
        preg_match_all('/\d+/', $uri, $matches);
        $numbers = $matches[0] ?? [];
        $sent_id = isset($numbers[0]) ? (int)$numbers[0] : null; // Primer número
        $actionIds = count($numbers) > 1 ? implode(',', array_slice($numbers, 1)) : null; // Resto de IDs

        // Determinar el contenido de la respuesta
        $responseContent = $response->getHeaderLine('Content-Type');
        $responseBody = (strpos($responseContent, 'application/json') !== false) 
                        ? $response->getBody() 
                        : 'html response';

        // Registrar en la base de datos
        $logModel = new ApiLogModel();
        $logData = [
            'method'     => $method,
            'endpoint'   => $uri,
            'controller' => $controller,
            'action'     => $action,
            'sent_id'    => $sent_id,
            'vista'      => $actionIds,
            'body'       => json_encode($request->getPost() ?: $request->getJSON(true)),
            'tabla'      => $tableName,
            'response'   => $responseBody,
            'user_id'    => $userId,
            'ip_address' => $request->getIPAddress(),
            'user_agent' => $request->getUserAgent()->getAgentString(),
        ];

        $logModel->insert($logData);
        return $postArray;
    }
}
