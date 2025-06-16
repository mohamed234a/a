<?php
class Review {
    private $db;
    public function __construct($pdo) {
        $this->db = $pdo;
    }
    public function create($data) {
        $stmt = $this->db->prepare('INSERT INTO reviews (service_id, reviewer_id, rating, comment) VALUES (?, ?, ?, ?)');
        $stmt->execute([
            $data['service_id'],
            $data['reviewer_id'],
            $data['rating'],
            $data['comment']
        ]);
        return $this->db->lastInsertId();
    }
    public function findByService($service_id) {
        $stmt = $this->db->prepare('SELECT * FROM reviews WHERE service_id = ?');
        $stmt->execute([$service_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByServiceWithUsers($service_id) {
        $stmt = $this->db->prepare('
            SELECT r.*, u.name as reviewer_name
            FROM reviews r
            JOIN users u ON r.reviewer_id = u.id
            WHERE r.service_id = ?
            ORDER BY r.created_at DESC
        ');
        $stmt->execute([$service_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByServiceAndReviewer($service_id, $reviewer_id) {
        $stmt = $this->db->prepare('SELECT * FROM reviews WHERE service_id = ? AND reviewer_id = ?');
        $stmt->execute([$service_id, $reviewer_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAverageRating($service_id) {
        $stmt = $this->db->prepare('SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews FROM reviews WHERE service_id = ?');
        $stmt->execute([$service_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
