<?php
class AutoentrepreneurProfile {
    private $db;
    public function __construct($pdo) {
        $this->db = $pdo;
    }
    public function findByUserId($user_id) {
        $stmt = $this->db->prepare('SELECT * FROM autoentrepreneur_profiles WHERE user_id = ?');
        $stmt->execute([$user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function update($user_id, $data) {
        $stmt = $this->db->prepare('UPDATE autoentrepreneur_profiles SET bio=?, location=?, categories=?, prices=?, experience=?, portfolio=?, availability=?, contact_method=? WHERE user_id=?');
        $stmt->execute([
            $data['bio'], $data['location'], $data['categories'], $data['prices'], $data['experience'], $data['portfolio'], $data['availability'], $data['contact_method'], $user_id
        ]);
        return $stmt->rowCount();
    }

    public function create($user_id, $data) {
        $stmt = $this->db->prepare('INSERT INTO autoentrepreneur_profiles (user_id, bio, location, categories, prices, experience, portfolio, availability, contact_method) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        return $stmt->execute([
            $user_id,
            $data['bio'] ?? '',
            $data['location'] ?? '',
            $data['categories'] ?? '',
            $data['prices'] ?? '',
            $data['experience'] ?? '',
            $data['portfolio'] ?? '',
            $data['availability'] ?? '',
            $data['contact_method'] ?? ''
        ]);
    }

    public function upsert($user_id, $data) {
        $existing = $this->findByUserId($user_id);
        if ($existing) {
            return $this->update($user_id, $data);
        } else {
            return $this->create($user_id, $data);
        }
    }
}
