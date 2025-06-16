<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/AutoentrepreneurProfile.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../utils/response.php';

$pdo = User::connect();
$userModel = new User($pdo);
$profileModel = new AutoentrepreneurProfile($pdo);

// Parse URL for user ID
$requestUri = $_SERVER['REQUEST_URI'];
$userId = null;
if (preg_match('/\/api\/(?:users|autoentrepreneurs)\/(\d+)/', $requestUri, $matches)) {
    $userId = $matches[1];
}

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        if (strpos($requestUri, '/api/users/me') !== false) {
            // Get current user profile
            $user = AuthMiddleware::authenticate();
            $userProfile = $userModel->findById($user['id']);

            if ($userProfile['type'] === 'autoentrepreneur') {
                $profile = $profileModel->findByUserId($user['id']);
                $userProfile = array_merge($userProfile, $profile ?: []);
            }

            // Remove password from response
            unset($userProfile['password']);
            jsonResponse($userProfile);

        } elseif ($userId) {
            // Get specific user/autoentrepreneur
            if (strpos($requestUri, '/api/autoentrepreneurs/') !== false) {
                $user = $userModel->getAutoentrepreneurById($userId);
            } else {
                $user = $userModel->findById($userId);
            }

            if (!$user) {
                jsonResponse(['error' => 'User not found'], 404);
            }

            // Remove password from response
            unset($user['password']);
            jsonResponse($user);

        } else {
            // List all auto-entrepreneurs
            if (strpos($requestUri, 'api/autoentrepreneurs') !== false || $_GET['route'] === 'api/autoentrepreneurs') {
                $list = $userModel->getAutoentrepreneurs();
                // Remove passwords from response
                foreach ($list as &$user) {
                    unset($user['password']);
                }
                jsonResponse($list);
            } else {
                jsonResponse(['error' => 'Invalid endpoint'], 400);
            }
        }
        break;

    case 'PUT':
        if (strpos($requestUri, '/api/users/me') !== false) {
            // Update current user profile
            $user = AuthMiddleware::authenticate();
            $input = json_decode(file_get_contents('php://input'), true);

            // Update user basic info
            $userUpdated = false;
            if (isset($input['name']) || isset($input['email']) || isset($input['phone']) || isset($input['photo'])) {
                $userUpdated = $userModel->update($user['id'], $input);
            }

            // Update autoentrepreneur profile if applicable
            $profileUpdated = false;
            if ($user['type'] === 'autoentrepreneur' &&
                (isset($input['bio']) || isset($input['location']) || isset($input['categories']) ||
                 isset($input['prices']) || isset($input['experience']) || isset($input['portfolio']) ||
                 isset($input['availability']) || isset($input['contact_method']))) {
                $profileUpdated = $profileModel->upsert($user['id'], $input);
            }

            jsonResponse(['success' => $userUpdated || $profileUpdated]);
        } else {
            jsonResponse(['error' => 'Invalid endpoint'], 400);
        }
        break;

    default:
        jsonResponse(['error' => 'Method not allowed'], 405);
}
