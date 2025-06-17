<?php
require_once __DIR__ . '/../models/Service.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../utils/response.php';

$pdo = User::connect();
$serviceModel = new Service($pdo);

// Parse service ID from query parameter or URL
$serviceId = $_GET['id'] ?? null;
if (!$serviceId) {
    $requestUri = $_SERVER['REQUEST_URI'];
    if (preg_match('/\/api\/services\/(\d+)/', $requestUri, $matches)) {
        $serviceId = $matches[1];
    }
}

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        if ($serviceId) {
            // Get specific service
            $service = $serviceModel->findById($serviceId);
            if (!$service) {
                jsonResponse(['error' => 'Service not found'], 404);
            }
            // Increment view count
            $serviceModel->incrementViews($serviceId);
            jsonResponse($service);
        } else {
            // List services with filters
            $filters = [
                'category' => $_GET['category'] ?? null,
                'location' => $_GET['location'] ?? null,
                'name' => $_GET['name'] ?? null
            ];
            $services = $serviceModel->find($filters);
            jsonResponse($services);
        }
        break;

    case 'POST':
        $user = AuthMiddleware::authenticate();
        if ($user['type'] !== 'autoentrepreneur') {
            jsonResponse(['error' => 'Only auto-entrepreneurs can create services'], 403);
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input['title'] || !$input['description'] || !$input['category'] || !$input['price']) {
            jsonResponse(['error' => 'Missing required fields'], 400);
        }

        $input['user_id'] = $user['id'];
        $serviceId = $serviceModel->create($input);
        jsonResponse(['success' => true, 'service_id' => $serviceId]);
        break;

    case 'PUT':
        if (!$serviceId) {
            jsonResponse(['error' => 'Service ID required'], 400);
        }

        $user = AuthMiddleware::authenticate();
        $service = $serviceModel->findById($serviceId);

        if (!$service) {
            jsonResponse(['error' => 'Service not found'], 404);
        }

        if ($service['user_id'] != $user['id'] && $user['type'] !== 'admin') {
            jsonResponse(['error' => 'Permission denied'], 403);
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $success = $serviceModel->update($serviceId, $input);
        jsonResponse(['success' => $success]);
        break;

    case 'DELETE':
        if (!$serviceId) {
            jsonResponse(['error' => 'Service ID required'], 400);
        }

        $user = AuthMiddleware::authenticate();
        $service = $serviceModel->findById($serviceId);

        if (!$service) {
            jsonResponse(['error' => 'Service not found'], 404);
        }

        if ($service['user_id'] != $user['id'] && $user['type'] !== 'admin') {
            jsonResponse(['error' => 'Permission denied'], 403);
        }

        $success = $serviceModel->delete($serviceId);
        jsonResponse(['success' => $success]);
        break;

    default:
        jsonResponse(['error' => 'Method not allowed'], 405);
}
