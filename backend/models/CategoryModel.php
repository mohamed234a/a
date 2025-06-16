<?php
require_once __DIR__ . '/../config/database.php';

class CategoryModel {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }
    
    public function getAll($activeOnly = true) {
        $sql = 'SELECT * FROM categories';
        if ($activeOnly) {
            $sql .= ' WHERE is_active = 1';
        }
        $sql .= ' ORDER BY sort_order ASC, name ASC';
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function findById($id) {
        $stmt = $this->db->prepare('SELECT * FROM categories WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function findBySlug($slug) {
        $stmt = $this->db->prepare('SELECT * FROM categories WHERE slug = ? AND is_active = 1');
        $stmt->execute([$slug]);
        return $stmt->fetch();
    }
    
    public function getWithServiceCount() {
        $stmt = $this->db->prepare('
            SELECT c.*, COUNT(s.id) as services_count
            FROM categories c
            LEFT JOIN services s ON c.id = s.category_id AND s.is_active = 1
            WHERE c.is_active = 1
            GROUP BY c.id
            ORDER BY c.sort_order ASC, c.name ASC
        ');
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
