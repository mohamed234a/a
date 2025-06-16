<?php
class JWT {
    private static function getConfig() {
        return require __DIR__ . '/../config/config.php';
    }

    public static function encode($payload) {
        $config = self::getConfig();
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);

        $payload['iat'] = time();
        $payload['exp'] = time() + $config['jwt_expiry'];
        $payload = json_encode($payload);

        $base64Header = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64Payload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

        $signature = hash_hmac('sha256', $base64Header . "." . $base64Payload, $config['jwt_secret'], true);
        $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        return $base64Header . "." . $base64Payload . "." . $base64Signature;
    }

    public static function decode($jwt) {
        if (!$jwt) {
            throw new Exception('Token is required');
        }

        $parts = explode('.', $jwt);
        if (count($parts) !== 3) {
            throw new Exception('Invalid token format');
        }

        $config = self::getConfig();
        list($base64Header, $base64Payload, $base64Signature) = $parts;

        $signature = base64_decode(str_replace(['-', '_'], ['+', '/'], $base64Signature));
        $expectedSignature = hash_hmac('sha256', $base64Header . "." . $base64Payload, $config['jwt_secret'], true);

        if (!hash_equals($signature, $expectedSignature)) {
            throw new Exception('Invalid token signature');
        }

        $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $base64Payload)), true);

        if (!$payload) {
            throw new Exception('Invalid token payload');
        }

        if (isset($payload['exp']) && $payload['exp'] < time()) {
            throw new Exception('Token has expired');
        }

        return $payload;
    }

    public static function getAuthUser() {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;

        if (!$authHeader || !preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            throw new Exception('Authorization token required');
        }

        return self::decode($matches[1]);
    }
}
