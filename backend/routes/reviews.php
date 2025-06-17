<?php
require_once __DIR__ . '/../models/Review.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Service.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../utils/response.php';

$pdo = User::connect();
$reviewModel = new Review($pdo);
$serviceModel = new Service($pdo);

// Parse URL for service ID
$requestUri = $_SERVER['REQUEST_URI'];
$serviceId = null;
if (preg_match('/\/api\/services\/(\d+)\/reviews/', $requestUri, $matches)) {
    $serviceId = $matches[1];
}

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        if ($serviceId) {
            $reviews = $reviewModel->findByServiceWithUsers($serviceId);
            jsonResponse($reviews);
        } elseif (isset($_GET['service_id'])) {
            $reviews = $reviewModel->findByServiceWithUsers($_GET['service_id']);
            jsonResponse($reviews);
        } else {
            jsonResponse(['error' => 'Service ID required'], 400);
        }
        break;

    case 'POST':
        $user = AuthMiddleware::authenticate();
        if ($user['type'] !== 'client') {
            jsonResponse(['error' => 'Only clients can leave reviews'], 403);
        }

        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['service_id']) || !isset($input['rating']) || !isset($input['comment'])) {
            jsonResponse(['error' => 'Missing required fields'], 400);
        }

        if ($input['rating'] < 1 || $input['rating'] > 5) {
            jsonResponse(['error' => 'Rating must be between 1 and 5'], 400);
        }

        // Check if service exists
        $service = $serviceModel->findById($input['service_id']);
        if (!$service) {
            jsonResponse(['error' => 'Service not found'], 404);
        }

        // Check if user already reviewed this service
        $existingReview = $reviewModel->findByServiceAndReviewer($input['service_id'], $user['id']);
        if ($existingReview) {
            jsonResponse(['error' => 'You have already reviewed this service'], 409);
        }

        $input['reviewer_id'] = $user['id'];
        $reviewId = $reviewModel->create($input);
        jsonResponse(['success' => true, 'review_id' => $reviewId]);
        break;

    default:
        jsonResponse(['error' => 'Method not allowed'], 405);
}
