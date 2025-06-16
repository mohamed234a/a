<?php
class Admin {
    private $db;
    public function __construct($pdo) {
        $this->db = $pdo;
    }
    public function getUsers() {
        $stmt = $this->db->query('SELECT * FROM users');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getServices() {
        $stmt = $this->db->query('SELECT * FROM services');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function addAnnouncement($data) {
        $stmt = $this->db->prepare('INSERT INTO announcements (title, content) VALUES (?, ?)');
        $stmt->execute([$data['title'], $data['content']]);
        return $this->db->lastInsertId();
    }
    // ...moderation methods...
}
