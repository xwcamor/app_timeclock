<?php
require_once __DIR__ . '/../../config/Database.php';

class WorkTime {
    private $conn;
    private $table = "work_times";

    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }

    public function getAll() {
        $sql = "SELECT * FROM {$this->table}";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
