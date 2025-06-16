<?php
require_once __DIR__ . '/../models/Admin.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../utils/response.php';

$pdo = User::connect();
$adminModel = new Admin($pdo);

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        if (strpos($_SERVER['REQUEST_URI'], 'users') !== false) {
            $users = $adminModel->getUsers();
            jsonResponse($users);
        } elseif (strpos($_SERVER['REQUEST_URI'], 'services') !== false) {
            $services = $adminModel->getServices();
            jsonResponse($services);
        }
        break;
    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        if (strpos($_SERVER['REQUEST_URI'], 'announcements') !== false) {
            $id = $adminModel->addAnnouncement($input);
            jsonResponse(['success' => true, 'announcement_id' => $id]);
        }
        // ...moderation logic...
        break;
    default:
        jsonResponse(['error' => 'Invalid request'], 400);
}
