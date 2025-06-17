<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Service.php';
require_once __DIR__ . '/../models/Review.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../utils/response.php';

$pdo = User::connect();
$serviceModel = new Service($pdo);
$reviewModel = new Review($pdo);

$user = AuthMiddleware::authenticate();

// Get user statistics
$stats = [];

if ($user['type'] === 'autoentrepreneur') {
    // Get services count
    $services = $serviceModel->findByUserId($user['id']);
    $stats['services'] = count($services);

    // Get total views
    $totalViews = 0;
    foreach ($services as $service) {
        $totalViews += $service['views'];
    }
    $stats['views'] = $totalViews;

    // Get reviews count
    $totalReviews = 0;
    foreach ($services as $service) {
        $reviews = $reviewModel->findByService($service['id']);
        $totalReviews += count($reviews);
    }
    $stats['reviews'] = $totalReviews;

    // Get recent services
    $stats['recent_services'] = array_slice($services, 0, 5);

} elseif ($user['type'] === 'client') {
    // Get reviews given by this client
    $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM reviews WHERE reviewer_id = ?');
    $stmt->execute([$user['id']]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['reviews_given'] = $result['count'];

} elseif ($user['type'] === 'admin') {
    // Admin stats
    $stmt = $pdo->query('SELECT COUNT(*) as count FROM users');
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['total_users'] = $result['count'];

    $stmt = $pdo->query('SELECT COUNT(*) as count FROM services');
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['total_services'] = $result['count'];

    $stmt = $pdo->query('SELECT COUNT(*) as count FROM reviews');
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['total_reviews'] = $result['count'];
}

jsonResponse($stats);
