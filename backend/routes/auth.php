<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../utils/bcrypt.php';
require_once __DIR__ . '/../utils/jwt.php';
require_once __DIR__ . '/../utils/response.php';

$pdo = User::connect();
$userModel = new User($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (strpos($_SERVER['REQUEST_URI'], 'register') !== false) {
        // Registration logic (no email/phone verification, no Google)
        if (!$input['email'] || !$input['password'] || !$input['name'] || !$input['type']) {
            jsonResponse(['error' => 'Missing fields'], 400);
        }
        if ($userModel->findByEmail($input['email'])) {
            jsonResponse(['error' => 'Email already exists'], 409);
        }
        $hash = Bcrypt::hash($input['password']);
        $userId = $userModel->create([
            'email' => $input['email'],
            'phone' => $input['phone'] ?? null,
            'password' => $hash,
            'name' => $input['name'],
            'type' => $input['type'],
            'is_verified' => 1 // Always verified for now
        ]);
        jsonResponse(['success' => true, 'user_id' => $userId]);
    } elseif (strpos($_SERVER['REQUEST_URI'], 'login') !== false) {
        // Login logic
        $user = $userModel->findByEmail($input['email']);
        if (!$user || !Bcrypt::verify($input['password'], $user['password'])) {
            jsonResponse(['error' => 'Invalid credentials'], 401);
        }
        $token = JWTHandler::encode(['id' => $user['id'], 'type' => $user['type']]);
        jsonResponse(['token' => $token, 'user' => $user]);
    }
}
jsonResponse(['error' => 'Invalid request'], 400);
