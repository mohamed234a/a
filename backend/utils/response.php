<?php
class Response {
    public static function json($data, $statusCode = 200, $message = null) {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');

        $response = [
            'success' => $statusCode < 400,
            'status' => $statusCode,
            'data' => $data
        ];

        if ($message) {
            $response['message'] = $message;
        }

        if ($statusCode >= 400) {
            $response['error'] = $data;
            unset($response['data']);
        }

        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function success($data = null, $message = null) {
        self::json($data, 200, $message);
    }

    public static function created($data = null, $message = null) {
        self::json($data, 201, $message);
    }

    public static function error($message, $statusCode = 400, $details = null) {
        $error = ['message' => $message];
        if ($details) {
            $error['details'] = $details;
        }
        self::json($error, $statusCode);
    }

    public static function unauthorized($message = 'Unauthorized') {
        self::error($message, 401);
    }

    public static function forbidden($message = 'Forbidden') {
        self::error($message, 403);
    }

    public static function notFound($message = 'Not found') {
        self::error($message, 404);
    }

    public static function serverError($message = 'Internal server error') {
        self::error($message, 500);
    }
}

// Legacy function for backward compatibility
function jsonResponse($data, $statusCode = 200) {
    Response::json($data, $statusCode);
}
