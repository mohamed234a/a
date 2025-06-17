<?php
require_once __DIR__ . '/../models/UserModel.php';

$method = $_SERVER['REQUEST_METHOD'];
$route = $_GET['route'] ?? '';

// Register endpoint
if ($route === 'api/auth/register' && $method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validation
    if (!$input || !isset($input['email'], $input['password'], $input['first_name'], $input['last_name'])) {
        Response::error('Missing required fields: email, password, first_name, last_name');
    }
    
    if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        Response::error('Invalid email format');
    }
    
    if (strlen($input['password']) < 6) {
        Response::error('Password must be at least 6 characters');
    }
    
    try {
        $userModel = new UserModel();
        
        $userData = [
            'email' => trim($input['email']),
            'password' => $input['password'],
            'first_name' => trim($input['first_name']),
            'last_name' => trim($input['last_name']),
            'phone' => isset($input['phone']) ? trim($input['phone']) : null,
            'user_type' => $input['user_type'] ?? 'client'
        ];
        
        $userId = $userModel->create($userData);
        
        if ($userId) {
            Response::created(['user_id' => $userId], 'Registration successful');
        } else {
            Response::error('Registration failed');
        }
        
    } catch (Exception $e) {
        Response::error($e->getMessage());
    }
}

// Login endpoint
elseif ($route === 'api/auth/login' && $method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['email'], $input['password'])) {
        Response::error('Email and password are required');
    }
    
    try {
        $userModel = new UserModel();
        $user = $userModel->findByEmail($input['email']);
        
        if (!$user || !$userModel->verifyPassword($input['password'], $user['password'])) {
            Response::unauthorized('Invalid email or password');
        }
        
        // Update last login
        $userModel->updateLastLogin($user['id']);
        
        // Create JWT token
        $payload = [
            'user_id' => $user['id'],
            'email' => $user['email'],
            'user_type' => $user['user_type']
        ];
        
        $token = JWT::encode($payload);
        
        // Remove sensitive data
        unset($user['password']);
        
        Response::success([
            'token' => $token,
            'user' => $user
        ], 'Login successful');
        
    } catch (Exception $e) {
        Response::error($e->getMessage());
    }
}

// Invalid endpoint
else {
    Response::notFound('Auth endpoint not found');
}
