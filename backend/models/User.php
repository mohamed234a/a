<?php
require_once __DIR__ . '/../config/config.php';

class User {
    private $db;
    public function __construct($pdo) {
        $this->db = $pdo;
    }
    public static function connect() {
        $config = require __DIR__ . '/../config/config.php';
        $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
        return new PDO($dsn, $config['user'], $config['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    }
    public function findByEmail($email) {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function create($data) {
        $stmt = $this->db->prepare('INSERT INTO users (email, phone, password, name, type, is_verified) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $data['email'],
            $data['phone'],
            $data['password'],
            $data['name'],
            $data['type'],
            $data['is_verified'] ?? 0
        ]);
        return $this->db->lastInsertId();
    }
    public function findById($id) {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update($id, $data) {
        $fields = [];
        $params = [];

        if (isset($data['name'])) {
            $fields[] = 'name = ?';
            $params[] = $data['name'];
        }
        if (isset($data['email'])) {
            $fields[] = 'email = ?';
            $params[] = $data['email'];
        }
        if (isset($data['phone'])) {
            $fields[] = 'phone = ?';
            $params[] = $data['phone'];
        }
        if (isset($data['photo'])) {
            $fields[] = 'photo = ?';
            $params[] = $data['photo'];
        }

        if (empty($fields)) {
            return false;
        }

        $params[] = $id;
        $sql = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = ?';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function getAutoentrepreneurs() {
        $stmt = $this->db->query("
            SELECT u.*, p.bio, p.location, p.categories, p.prices, p.experience,
                   p.portfolio, p.availability, p.contact_method
            FROM users u
            LEFT JOIN autoentrepreneur_profiles p ON u.id = p.user_id
            WHERE u.type = 'autoentrepreneur'
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAutoentrepreneurById($id) {
        $stmt = $this->db->prepare("
            SELECT u.*, p.bio, p.location, p.categories, p.prices, p.experience,
                   p.portfolio, p.availability, p.contact_method
            FROM users u
            LEFT JOIN autoentrepreneur_profiles p ON u.id = p.user_id
            WHERE u.id = ? AND u.type = 'autoentrepreneur'
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
