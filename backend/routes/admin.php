<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/response.php';

// Set CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Get request method
$method = $_SERVER['REQUEST_METHOD'];
$user_id = $_GET['user_id'] ?? null;

// For now, just return a simple test response
jsonResponse(true, 'Admin API working', ['method' => $method, 'user_id' => $user_id]);
exit;

try {
    $pdo = getDBConnection();

    switch ($method) {
        case 'GET':
            if ($user_id) {
                // Get specific user
                $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user) {
                    // Remove password from response
                    unset($user['password']);
                    jsonResponse(true, 'User retrieved successfully', $user);
                } else {
                    jsonResponse(false, 'User not found', null, 404);
                }
            } else {
                // Get all users
                $stmt = $pdo->query("SELECT id, name, email, phone, type, created_at FROM users ORDER BY created_at DESC");
                $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                jsonResponse(true, 'Users retrieved successfully', $users);
            }
            break;

        case 'PUT':
            if (!$user_id) {
                jsonResponse(false, 'User ID required for update', null, 400);
                break;
            }

            $input = json_decode(file_get_contents('php://input'), true);

            if (!$input) {
                jsonResponse(false, 'Invalid JSON data', null, 400);
                break;
            }

            // Validate required fields
            if (empty($input['name']) || empty($input['email'])) {
                jsonResponse(false, 'Name and email are required', null, 400);
                break;
            }

            // Check if email already exists for another user
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$input['email'], $user_id]);
            if ($stmt->fetch()) {
                jsonResponse(false, 'Email already exists', null, 400);
                break;
            }

            // Update user
            $updateFields = [];
            $updateValues = [];

            if (isset($input['name'])) {
                $updateFields[] = "name = ?";
                $updateValues[] = $input['name'];
            }

            if (isset($input['email'])) {
                $updateFields[] = "email = ?";
                $updateValues[] = $input['email'];
            }

            if (isset($input['phone'])) {
                $updateFields[] = "phone = ?";
                $updateValues[] = $input['phone'];
            }

            if (isset($input['type'])) {
                $updateFields[] = "type = ?";
                $updateValues[] = $input['type'];
            }

            if (!empty($updateFields)) {
                $updateValues[] = $user_id;
                $sql = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($updateValues);

                jsonResponse(true, 'User updated successfully', ['user_id' => $user_id]);
            } else {
                jsonResponse(false, 'No fields to update', null, 400);
            }
            break;

        case 'DELETE':
            if (!$user_id) {
                jsonResponse(false, 'User ID required for deletion', null, 400);
                break;
            }

            // Check if user exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            if (!$stmt->fetch()) {
                jsonResponse(false, 'User not found', null, 404);
                break;
            }

            // Delete user's services first (cascade delete)
            $stmt = $pdo->prepare("DELETE FROM services WHERE user_id = ?");
            $stmt->execute([$user_id]);

            // Delete user
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$user_id]);

            jsonResponse(true, 'User deleted successfully', ['user_id' => $user_id]);
            break;

        default:
            jsonResponse(false, 'Method not allowed', null, 405);
            break;
    }

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    jsonResponse(false, 'Database error occurred', null, 500);
} catch (Exception $e) {
    error_log("General error: " . $e->getMessage());
    jsonResponse(false, 'An error occurred', null, 500);
}
?>
