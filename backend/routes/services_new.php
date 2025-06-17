<?php
require_once __DIR__ . '/../models/ServiceModel.php';
require_once __DIR__ . '/../models/CategoryModel.php';

$method = $_SERVER['REQUEST_METHOD'];
$route = $_GET['route'] ?? '';

// Get all services
if ($route === 'api/services' && $method === 'GET') {
    try {
        $serviceModel = new ServiceModel();
        
        // Parse filters from query parameters
        $filters = [];
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = min(50, max(1, intval($_GET['limit'] ?? 20)));
        $offset = ($page - 1) * $limit;
        
        if (!empty($_GET['category_id'])) {
            $filters['category_id'] = intval($_GET['category_id']);
        }
        
        if (!empty($_GET['user_id'])) {
            $filters['user_id'] = intval($_GET['user_id']);
        }
        
        if (!empty($_GET['search'])) {
            $filters['search'] = trim($_GET['search']);
        }
        
        if (!empty($_GET['min_price'])) {
            $filters['min_price'] = floatval($_GET['min_price']);
        }
        
        if (!empty($_GET['max_price'])) {
            $filters['max_price'] = floatval($_GET['max_price']);
        }
        
        if (!empty($_GET['featured'])) {
            $filters['featured'] = true;
        }
        
        if (!empty($_GET['sort'])) {
            $filters['sort'] = $_GET['sort'];
        }
        
        $services = $serviceModel->getAll($filters, $limit, $offset);
        $total = $serviceModel->getTotalCount($filters);
        
        Response::success([
            'services' => $services,
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

// Get single service
elseif (preg_match('/api\/services\/(\d+)/', $route, $matches) && $method === 'GET') {
    try {
        $serviceId = intval($matches[1]);
        $serviceModel = new ServiceModel();
        
        $service = $serviceModel->findById($serviceId);
        
        if (!$service) {
            Response::notFound('Service not found');
        }
        
        // Increment view count
        $serviceModel->incrementViews($serviceId);
        
        Response::success($service);
        
    } catch (Exception $e) {
        Response::error($e->getMessage());
    }
}

// Create new service
elseif ($route === 'api/services' && $method === 'POST') {
    try {
        // Authenticate user
        $authUser = JWT::getAuthUser();
        
        if ($authUser['user_type'] !== 'autoentrepreneur') {
            Response::forbidden('Only auto-entrepreneurs can create services');
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Validation
        if (!$input || !isset($input['title'], $input['description'], $input['category_id'], $input['price'])) {
            Response::error('Missing required fields: title, description, category_id, price');
        }
        
        if (strlen($input['title']) < 5) {
            Response::error('Title must be at least 5 characters');
        }
        
        if (strlen($input['description']) < 20) {
            Response::error('Description must be at least 20 characters');
        }
        
        if ($input['price'] <= 0) {
            Response::error('Price must be greater than 0');
        }
        
        // Verify category exists
        $categoryModel = new CategoryModel();
        $category = $categoryModel->findById($input['category_id']);
        if (!$category) {
            Response::error('Invalid category');
        }
        
        $serviceModel = new ServiceModel();
        
        $serviceData = [
            'user_id' => $authUser['user_id'],
            'category_id' => intval($input['category_id']),
            'title' => trim($input['title']),
            'description' => trim($input['description']),
            'price' => floatval($input['price']),
            'delivery_time' => intval($input['delivery_time'] ?? 7),
            'revisions_included' => intval($input['revisions_included'] ?? 1),
            'images' => $input['images'] ?? [],
            'tags' => $input['tags'] ?? []
        ];
        
        $serviceId = $serviceModel->create($serviceData);
        
        if ($serviceId) {
            $service = $serviceModel->findById($serviceId);
            Response::created($service, 'Service created successfully');
        } else {
            Response::error('Failed to create service');
        }
        
    } catch (Exception $e) {
        Response::error($e->getMessage());
    }
}

// Update service
elseif (preg_match('/api\/services\/(\d+)/', $route, $matches) && $method === 'PUT') {
    try {
        $serviceId = intval($matches[1]);
        $authUser = JWT::getAuthUser();
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            Response::error('Invalid JSON data');
        }
        
        $serviceModel = new ServiceModel();
        
        // Verify service exists and user owns it
        $service = $serviceModel->findById($serviceId);
        if (!$service) {
            Response::notFound('Service not found');
        }
        
        if ($service['user_id'] != $authUser['user_id']) {
            Response::forbidden('You can only update your own services');
        }
        
        $result = $serviceModel->update($serviceId, $input, $authUser['user_id']);
        
        if ($result) {
            $updatedService = $serviceModel->findById($serviceId);
            Response::success($updatedService, 'Service updated successfully');
        } else {
            Response::error('Failed to update service');
        }
        
    } catch (Exception $e) {
        Response::error($e->getMessage());
    }
}

// Delete service
elseif (preg_match('/api\/services\/(\d+)/', $route, $matches) && $method === 'DELETE') {
    try {
        $serviceId = intval($matches[1]);
        $authUser = JWT::getAuthUser();
        
        $serviceModel = new ServiceModel();
        
        // Verify service exists and user owns it
        $service = $serviceModel->findById($serviceId);
        if (!$service) {
            Response::notFound('Service not found');
        }
        
        if ($service['user_id'] != $authUser['user_id']) {
            Response::forbidden('You can only delete your own services');
        }
        
        $result = $serviceModel->delete($serviceId, $authUser['user_id']);
        
        if ($result) {
            Response::success(null, 'Service deleted successfully');
        } else {
            Response::error('Failed to delete service');
        }
        
    } catch (Exception $e) {
        Response::error($e->getMessage());
    }
}

// Get featured services
elseif ($route === 'api/services/featured' && $method === 'GET') {
    try {
        $serviceModel = new ServiceModel();
        $limit = min(20, max(1, intval($_GET['limit'] ?? 6)));
        
        $services = $serviceModel->getFeatured($limit);
        
        Response::success($services);
        
    } catch (Exception $e) {
        Response::error($e->getMessage());
    }
}

// Invalid endpoint
else {
    Response::notFound('Service endpoint not found');
}
