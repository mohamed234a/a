<?php
require_once __DIR__ . '/../config/database.php';

class ServiceModel {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }
    
    public function create($data) {
        try {
            $slug = $this->generateSlug($data['title']);
            
            $stmt = $this->db->prepare('
                INSERT INTO services (user_id, category_id, title, slug, description, short_description, 
                                    price, delivery_time, revisions_included, images, tags) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ');
            
            $images = isset($data['images']) ? json_encode($data['images']) : null;
            $tags = isset($data['tags']) ? json_encode($data['tags']) : null;
            $shortDescription = substr($data['description'], 0, 500);
            
            $result = $stmt->execute([
                $data['user_id'],
                $data['category_id'],
                $data['title'],
                $slug,
                $data['description'],
                $shortDescription,
                $data['price'],
                $data['delivery_time'] ?? 7,
                $data['revisions_included'] ?? 1,
                $images,
                $tags
            ]);
            
            if ($result) {
                return $this->db->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            throw new Exception('Service creation failed: ' . $e->getMessage());
        }
    }
    
    public function findById($id) {
        $stmt = $this->db->prepare('
            SELECT s.*, c.name as category_name, c.slug as category_slug,
                   u.first_name, u.last_name, u.avatar as user_avatar,
                   p.business_name
            FROM services s
            JOIN categories c ON s.category_id = c.id
            JOIN users u ON s.user_id = u.id
            LEFT JOIN autoentrepreneur_profiles p ON u.id = p.user_id
            WHERE s.id = ? AND s.is_active = 1
        ');
        $stmt->execute([$id]);
        $service = $stmt->fetch();
        
        if ($service) {
            // Decode JSON fields
            $service['images'] = json_decode($service['images'], true) ?? [];
            $service['tags'] = json_decode($service['tags'], true) ?? [];
        }
        
        return $service;
    }
    
    public function getAll($filters = [], $limit = 20, $offset = 0) {
        $sql = '
            SELECT s.id, s.title, s.slug, s.short_description, s.price, s.delivery_time,
                   s.images, s.rating_average, s.rating_count, s.views_count, s.created_at,
                   c.name as category_name, c.slug as category_slug,
                   u.first_name, u.last_name, u.avatar as user_avatar,
                   p.business_name
            FROM services s
            JOIN categories c ON s.category_id = c.id
            JOIN users u ON s.user_id = u.id
            LEFT JOIN autoentrepreneur_profiles p ON u.id = p.user_id
            WHERE s.is_active = 1
        ';
        
        $params = [];
        
        if (!empty($filters['category_id'])) {
            $sql .= ' AND s.category_id = ?';
            $params[] = $filters['category_id'];
        }
        
        if (!empty($filters['user_id'])) {
            $sql .= ' AND s.user_id = ?';
            $params[] = $filters['user_id'];
        }
        
        if (!empty($filters['search'])) {
            $sql .= ' AND (s.title LIKE ? OR s.description LIKE ? OR JSON_SEARCH(s.tags, "one", ?) IS NOT NULL)';
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $filters['search'];
        }
        
        if (!empty($filters['min_price'])) {
            $sql .= ' AND s.price >= ?';
            $params[] = $filters['min_price'];
        }
        
        if (!empty($filters['max_price'])) {
            $sql .= ' AND s.price <= ?';
            $params[] = $filters['max_price'];
        }
        
        if (!empty($filters['featured'])) {
            $sql .= ' AND s.is_featured = 1';
        }
        
        // Sorting
        $orderBy = 'ORDER BY ';
        switch ($filters['sort'] ?? 'newest') {
            case 'price_low':
                $orderBy .= 's.price ASC';
                break;
            case 'price_high':
                $orderBy .= 's.price DESC';
                break;
            case 'rating':
                $orderBy .= 's.rating_average DESC, s.rating_count DESC';
                break;
            case 'popular':
                $orderBy .= 's.views_count DESC';
                break;
            default:
                $orderBy .= 's.created_at DESC';
        }
        
        $sql .= " $orderBy LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $services = $stmt->fetchAll();
        
        // Decode JSON fields for each service
        foreach ($services as &$service) {
            $service['images'] = json_decode($service['images'], true) ?? [];
            $service['tags'] = json_decode($service['tags'], true) ?? [];
        }
        
        return $services;
    }
    
    public function update($id, $data, $userId) {
        $fields = [];
        $params = [];
        
        $allowedFields = ['title', 'description', 'category_id', 'price', 'delivery_time', 'revisions_included', 'images', 'tags'];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                if ($field === 'images' || $field === 'tags') {
                    $fields[] = "$field = ?";
                    $params[] = json_encode($data[$field]);
                } elseif ($field === 'title') {
                    $fields[] = "$field = ?";
                    $fields[] = "slug = ?";
                    $params[] = $data[$field];
                    $params[] = $this->generateSlug($data[$field]);
                } else {
                    $fields[] = "$field = ?";
                    $params[] = $data[$field];
                }
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $params[] = $id;
        $params[] = $userId;
        
        $sql = 'UPDATE services SET ' . implode(', ', $fields) . ', updated_at = CURRENT_TIMESTAMP WHERE id = ? AND user_id = ?';
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    public function delete($id, $userId) {
        $stmt = $this->db->prepare('UPDATE services SET is_active = 0 WHERE id = ? AND user_id = ?');
        return $stmt->execute([$id, $userId]);
    }
    
    public function incrementViews($id) {
        $stmt = $this->db->prepare('UPDATE services SET views_count = views_count + 1 WHERE id = ?');
        return $stmt->execute([$id]);
    }
    
    public function updateRating($serviceId) {
        $stmt = $this->db->prepare('
            UPDATE services s
            SET rating_average = (
                SELECT AVG(rating) FROM reviews WHERE service_id = s.id
            ),
            rating_count = (
                SELECT COUNT(*) FROM reviews WHERE service_id = s.id
            )
            WHERE s.id = ?
        ');
        return $stmt->execute([$serviceId]);
    }
    
    public function getFeatured($limit = 6) {
        return $this->getAll(['featured' => true], $limit);
    }
    
    public function getByCategory($categoryId, $limit = 20, $offset = 0) {
        return $this->getAll(['category_id' => $categoryId], $limit, $offset);
    }
    
    public function getByUser($userId, $limit = 20, $offset = 0) {
        return $this->getAll(['user_id' => $userId], $limit, $offset);
    }
    
    public function search($query, $limit = 20, $offset = 0) {
        return $this->getAll(['search' => $query], $limit, $offset);
    }
    
    public function getTotalCount($filters = []) {
        $sql = 'SELECT COUNT(*) as total FROM services s WHERE s.is_active = 1';
        $params = [];
        
        if (!empty($filters['category_id'])) {
            $sql .= ' AND s.category_id = ?';
            $params[] = $filters['category_id'];
        }
        
        if (!empty($filters['user_id'])) {
            $sql .= ' AND s.user_id = ?';
            $params[] = $filters['user_id'];
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['total'];
    }
    
    private function generateSlug($title) {
        $slug = strtolower(trim($title));
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');
        
        // Check if slug exists and make it unique
        $originalSlug = $slug;
        $counter = 1;
        
        while ($this->slugExists($slug)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }
    
    private function slugExists($slug) {
        $stmt = $this->db->prepare('SELECT id FROM services WHERE slug = ?');
        $stmt->execute([$slug]);
        return $stmt->fetch() !== false;
    }
}
