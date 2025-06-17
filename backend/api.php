<?php
// Auto-Entrepreneur Tunisie API
error_reporting(E_ALL);
ini_set('display_errors', 1);

// CORS Headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Content-Type: application/json; charset=utf-8');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Load utilities
require_once __DIR__ . '/utils/response.php';
require_once __DIR__ . '/utils/jwt.php';

// Get route from URL
$route = $_GET['route'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

try {
    // Route to appropriate handler
    switch (true) {
        case strpos($route, 'api/auth/') === 0:
            require_once __DIR__ . '/routes/auth.php';
            break;
            
        case strpos($route, 'api/services') === 0:
            require_once __DIR__ . '/routes/services.php';
            break;
            
        case strpos($route, 'api/categories') === 0:
            require_once __DIR__ . '/routes/categories.php';
            break;
            
        case strpos($route, 'api/users') === 0:
        case strpos($route, 'api/autoentrepreneurs') === 0:
            require_once __DIR__ . '/routes/users.php';
            break;
            
        case strpos($route, 'api/dashboard') === 0:
            require_once __DIR__ . '/routes/dashboard.php';
            break;
            
        case strpos($route, 'api/reviews') === 0:
            require_once __DIR__ . '/routes/reviews.php';
            break;
            
        default:
            Response::notFound('API endpoint not found');
    }
} catch (Exception $e) {
    Response::serverError('Server error: ' . $e->getMessage());
}
