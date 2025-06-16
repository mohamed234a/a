<?php
require_once __DIR__ . '/../utils/jwt.php';
require_once __DIR__ . '/../utils/response.php';

class AuthMiddleware {
    public static function authenticate() {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;
        
        if (!$authHeader || !preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            jsonResponse(['error' => 'Authorization token required'], 401);
        }
        
        $token = $matches[1];
        
        try {
            $payload = JWTHandler::decode($token);
            return $payload;
        } catch (Exception $e) {
            jsonResponse(['error' => 'Invalid or expired token'], 401);
        }
    }
    
    public static function requireRole($requiredRole) {
        $user = self::authenticate();
        if ($user['type'] !== $requiredRole) {
            jsonResponse(['error' => 'Insufficient permissions'], 403);
        }
        return $user;
    }
}
