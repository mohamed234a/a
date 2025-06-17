<?php
require_once __DIR__ . '/../config/database.php';

class UserModel {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }
    
    public function create($data) {
        try {
            $stmt = $this->db->prepare('
                INSERT INTO users (email, password, first_name, last_name, phone, user_type) 
                VALUES (?, ?, ?, ?, ?, ?)
            ');
            
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            
            $result = $stmt->execute([
                $data['email'],
                $hashedPassword,
                $data['first_name'],
                $data['last_name'],
                $data['phone'] ?? null,
                $data['user_type'] ?? 'client'
            ]);
            
            if ($result) {
                return $this->db->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                throw new Exception('Email already exists');
            }
            throw new Exception('Registration failed: ' . $e->getMessage());
        }
    }
    
    public function findByEmail($email) {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = ? AND is_active = 1');
        $stmt->execute([$email]);
        return $stmt->fetch();
    }
    
    public function findById($id) {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = ? AND is_active = 1');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    public function updateLastLogin($id) {
        $stmt = $this->db->prepare('UPDATE users SET updated_at = CURRENT_TIMESTAMP WHERE id = ?');
        return $stmt->execute([$id]);
    }
    
    public function getAutoentrepreneurs($limit = null, $offset = 0, $filters = []) {
        $sql = '
            SELECT u.id, u.email, u.first_name, u.last_name, u.avatar, u.created_at,
                   p.business_name, p.bio, p.specialties, p.location, p.experience_years,
                   p.hourly_rate, p.availability_status, p.languages,
                   COUNT(s.id) as services_count,
                   AVG(s.rating_average) as avg_rating
            FROM users u
            LEFT JOIN autoentrepreneur_profiles p ON u.id = p.user_id
            LEFT JOIN services s ON u.id = s.user_id AND s.is_active = 1
            WHERE u.user_type = "autoentrepreneur" AND u.is_active = 1
        ';
        
        $params = [];
        
        if (!empty($filters['location'])) {
            $sql .= ' AND p.location LIKE ?';
            $params[] = '%' . $filters['location'] . '%';
        }
        
        if (!empty($filters['specialty'])) {
            $sql .= ' AND JSON_CONTAINS(p.specialties, ?)';
            $params[] = '"' . $filters['specialty'] . '"';
        }
        
        $sql .= ' GROUP BY u.id ORDER BY u.created_at DESC';
        
        if ($limit) {
            $sql .= " LIMIT $limit OFFSET $offset";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function getAutoentrepreneurById($id) {
        $stmt = $this->db->prepare('
            SELECT u.id, u.email, u.first_name, u.last_name, u.phone, u.avatar, u.created_at,
                   p.business_name, p.bio, p.specialties, p.location, p.experience_years,
                   p.hourly_rate, p.portfolio_url, p.linkedin_url, p.website_url,
                   p.availability_status, p.languages, p.certifications,
                   COUNT(s.id) as services_count,
                   AVG(s.rating_average) as avg_rating,
                   SUM(s.views_count) as total_views
            FROM users u
            LEFT JOIN autoentrepreneur_profiles p ON u.id = p.user_id
            LEFT JOIN services s ON u.id = s.user_id AND s.is_active = 1
            WHERE u.id = ? AND u.user_type = "autoentrepreneur" AND u.is_active = 1
            GROUP BY u.id
        ');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function updateProfile($id, $data) {
        $fields = [];
        $params = [];
        
        $allowedFields = ['first_name', 'last_name', 'phone', 'avatar'];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $params[] = $id;
        $sql = 'UPDATE users SET ' . implode(', ', $fields) . ', updated_at = CURRENT_TIMESTAMP WHERE id = ?';
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    public function searchAutoentrepreneurs($query, $limit = 20, $offset = 0) {
        $sql = '
            SELECT u.id, u.first_name, u.last_name, u.avatar,
                   p.business_name, p.bio, p.specialties, p.location, p.hourly_rate,
                   COUNT(s.id) as services_count,
                   AVG(s.rating_average) as avg_rating
            FROM users u
            LEFT JOIN autoentrepreneur_profiles p ON u.id = p.user_id
            LEFT JOIN services s ON u.id = s.user_id AND s.is_active = 1
            WHERE u.user_type = "autoentrepreneur" AND u.is_active = 1
            AND (
                u.first_name LIKE ? OR 
                u.last_name LIKE ? OR 
                p.business_name LIKE ? OR 
                p.bio LIKE ? OR
                JSON_SEARCH(p.specialties, "one", ?) IS NOT NULL
            )
            GROUP BY u.id
            ORDER BY avg_rating DESC, services_count DESC
            LIMIT ? OFFSET ?
        ';
        
        $searchTerm = '%' . $query . '%';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $limit, $offset]);
        return $stmt->fetchAll();
    }
    
    public function getTotalAutoentrepreneurs() {
        $stmt = $this->db->prepare('SELECT COUNT(*) as total FROM users WHERE user_type = "autoentrepreneur" AND is_active = 1');
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'];
    }
}
