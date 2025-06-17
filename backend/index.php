<?php
// Main entry point and router
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/utils/response.php';

// Enable CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Simple router
$route = $_GET['route'] ?? '';
$requestUri = $_SERVER['REQUEST_URI'];

// Route matching with patterns
error_log("Route requested: " . $route);

if (preg_match('/api\/auth\/(login|register)/', $route)) {
    error_log("Routing to auth");
    require_once __DIR__ . '/routes/auth.php';
} elseif ($route === 'api/admin' || preg_match('/api\/admin/', $route)) {
    error_log("Routing to admin");
    require_once __DIR__ . '/routes/admin.php';
} elseif (preg_match('/api\/services/', $route) || strpos($requestUri, '/api/services/') !== false) {
    error_log("Routing to services");
    require_once __DIR__ . '/routes/services.php';
} elseif (preg_match('/api\/reviews/', $route) || strpos($requestUri, '/reviews') !== false) {
    error_log("Routing to reviews");
    require_once __DIR__ . '/routes/reviews.php';
} elseif (preg_match('/api\/(users|autoentrepreneurs)/', $route) || strpos($requestUri, '/api/users/') !== false || strpos($requestUri, '/api/autoentrepreneurs/') !== false) {
    error_log("Routing to users");
    require_once __DIR__ . '/routes/users.php';
} elseif ($route === 'api/dashboard') {
    error_log("Routing to dashboard");
    require_once __DIR__ . '/routes/dashboard.php';
} else {
    error_log("Route not found: " . $route);
    jsonResponse(['error' => 'Route not found: ' . $route], 404);
}
