<?php
class Service {
    private $db;
    public function __construct($pdo) {
        $this->db = $pdo;
    }
    public function create($data) {
        $stmt = $this->db->prepare('INSERT INTO services (user_id, title, description, category, price, images, availability) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $data['user_id'],
            $data['title'],
            $data['description'],
            $data['category'],
            $data['price'],
            $data['images'],
            $data['availability']
        ]);
        return $this->db->lastInsertId();
    }
    public function find($filters = []) {
        $sql = 'SELECT * FROM services WHERE 1=1';
        $params = [];
        if (!empty($filters['category'])) {
            $sql .= ' AND category = ?';
            $params[] = $filters['category'];
        }
        if (!empty($filters['location'])) {
            $sql .= ' AND user_id IN (SELECT user_id FROM autoentrepreneur_profiles WHERE location = ?)';
            $params[] = $filters['location'];
        }
        if (!empty($filters['name'])) {
            $sql .= ' AND title LIKE ?';
            $params[] = '%' . $filters['name'] . '%';
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById($id) {
        $stmt = $this->db->prepare('SELECT s.*, u.name as user_name FROM services s JOIN users u ON s.user_id = u.id WHERE s.id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findByUserId($userId) {
        $stmt = $this->db->prepare('SELECT * FROM services WHERE user_id = ?');
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update($id, $data) {
        $stmt = $this->db->prepare('UPDATE services SET title = ?, description = ?, category = ?, price = ?, images = ?, availability = ? WHERE id = ?');
        return $stmt->execute([
            $data['title'],
            $data['description'],
            $data['category'],
            $data['price'],
            $data['images'],
            $data['availability'],
            $id
        ]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare('DELETE FROM services WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function incrementViews($id) {
        $stmt = $this->db->prepare('UPDATE services SET views = views + 1 WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
