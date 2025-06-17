<?php
require_once __DIR__ . '/../models/UserModel.php';

$method = $_SERVER['REQUEST_METHOD'];
$route = $_GET['route'] ?? '';

// Get all auto-entrepreneurs
if ($route === 'api/autoentrepreneurs' && $method === 'GET') {
    try {
        $userModel = new UserModel();
        
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = min(50, max(1, intval($_GET['limit'] ?? 20)));
        $offset = ($page - 1) * $limit;
        
        $filters = [];
        if (!empty($_GET['location'])) {
            $filters['location'] = trim($_GET['location']);
        }
        
        if (!empty($_GET['specialty'])) {
            $filters['specialty'] = trim($_GET['specialty']);
        }
        
        $autoentrepreneurs = $userModel->getAutoentrepreneurs($limit, $offset, $filters);
        $total = $userModel->getTotalAutoentrepreneurs();
        
        Response::success([
            'autoentrepreneurs' => $autoentrepreneurs,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => ceil($total / $limit)
            ]
        ]);
        
    } catch (Exception $e) {
        Response::error($e->getMessage());
    }
}

// Get single auto-entrepreneur
elseif (preg_match('/api\/autoentrepreneurs\/(\d+)/', $route, $matches) && $method === 'GET') {
    try {
        $userId = intval($matches[1]);
        $userModel = new UserModel();
        
        $autoentrepreneur = $userModel->getAutoentrepreneurById($userId);
        
        if (!$autoentrepreneur) {
            Response::notFound('Auto-entrepreneur not found');
        }
        
        // Parse JSON fields
        if ($autoentrepreneur['specialties']) {
            $autoentrepreneur['specialties'] = json_decode($autoentrepreneur['specialties'], true);
        }
        if ($autoentrepreneur['languages']) {
            $autoentrepreneur['languages'] = json_decode($autoentrepreneur['languages'], true);
        }
        if ($autoentrepreneur['certifications']) {
            $autoentrepreneur['certifications'] = json_decode($autoentrepreneur['certifications'], true);
        }
        
        Response::success($autoentrepreneur);
        
    } catch (Exception $e) {
        Response::error($e->getMessage());
    }
}

// Search auto-entrepreneurs
elseif ($route === 'api/autoentrepreneurs/search' && $method === 'GET') {
    try {
        $query = trim($_GET['q'] ?? '');
        
        if (empty($query)) {
            Response::error('Search query is required');
        }
        
        $userModel = new UserModel();
        
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = min(50, max(1, intval($_GET['limit'] ?? 20)));
        $offset = ($page - 1) * $limit;
        
        $results = $userModel->searchAutoentrepreneurs($query, $limit, $offset);
        
        Response::success([
            'results' => $results,
            'query' => $query,
            'pagination' => [
                'page' => $page,
                'limit' => $limit
            ]
        ]);
        
    } catch (Exception $e) {
        Response::error($e->getMessage());
    }
}

// Get current user profile
elseif ($route === 'api/users/me' && $method === 'GET') {
    try {
        $authUser = JWT::getAuthUser();
        $userModel = new UserModel();
        
        if ($authUser['user_type'] === 'autoentrepreneur') {
            $user = $userModel->getAutoentrepreneurById($authUser['user_id']);
        } else {
            $user = $userModel->findById($authUser['user_id']);
        }
        
        if (!$user) {
            Response::notFound('User not found');
        }
        
        // Remove sensitive data
        unset($user['password']);
        
        Response::success($user);
        
    } catch (Exception $e) {
        Response::error($e->getMessage());
    }
}

// Update current user profile
elseif ($route === 'api/users/me' && $method === 'PUT') {
    try {
        $authUser = JWT::getAuthUser();
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            Response::error('Invalid JSON data');
        }
        
        $userModel = new UserModel();
        
        $result = $userModel->updateProfile($authUser['user_id'], $input);
        
        if ($result) {
            $updatedUser = $userModel->findById($authUser['user_id']);
            unset($updatedUser['password']);
            Response::success($updatedUser, 'Profile updated successfully');
        } else {
            Response::error('Failed to update profile');
        }
        
    } catch (Exception $e) {
        Response::error($e->getMessage());
    }
}

// Invalid endpoint
else {
    Response::notFound('User endpoint not found');
}
