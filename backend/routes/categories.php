<?php
require_once __DIR__ . '/../models/CategoryModel.php';

$method = $_SERVER['REQUEST_METHOD'];
$route = $_GET['route'] ?? '';

// Get all categories
if ($route === 'api/categories' && $method === 'GET') {
    try {
        $categoryModel = new CategoryModel();
        
        $withCount = isset($_GET['with_count']) && $_GET['with_count'] === 'true';
        
        if ($withCount) {
            $categories = $categoryModel->getWithServiceCount();
        } else {
            $categories = $categoryModel->getAll();
        }
        
        Response::success($categories);
        
    } catch (Exception $e) {
        Response::error($e->getMessage());
    }
}

// Get single category
elseif (preg_match('/api\/categories\/(\d+)/', $route, $matches) && $method === 'GET') {
    try {
        $categoryId = intval($matches[1]);
        $categoryModel = new CategoryModel();
        
        $category = $categoryModel->findById($categoryId);
        
        if (!$category) {
            Response::notFound('Category not found');
        }
        
        Response::success($category);
        
    } catch (Exception $e) {
        Response::error($e->getMessage());
    }
}

// Get category by slug
elseif (preg_match('/api\/categories\/slug\/([^\/]+)/', $route, $matches) && $method === 'GET') {
    try {
        $slug = $matches[1];
        $categoryModel = new CategoryModel();
        
        $category = $categoryModel->findBySlug($slug);
        
        if (!$category) {
            Response::notFound('Category not found');
        }
        
        Response::success($category);
        
    } catch (Exception $e) {
        Response::error($e->getMessage());
    }
}

// Invalid endpoint
else {
    Response::notFound('Category endpoint not found');
}
