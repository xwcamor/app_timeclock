<?php
require_once __DIR__ . '/../../config/database.php';

class Worker {
    private $conn;
    private $table = "workers";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function getAll() {
        $sql = "SELECT * FROM " . $this->table;
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $sql = "SELECT * FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

      // Obtener los datos del trabajador por user_id
      public function getWorkerDataByUserId($userId) {
        $query = "SELECT w.num_doc, w.name, w.lastname, w.email, w.id_time_presencial, t.hour_time_ini
                  FROM {$this->table} w
                  INNER JOIN users u ON w.user_id = u.id
                  LEFT JOIN work_times t ON w.id_time_presencial = t.id
                  WHERE u.id = :user_id";
    
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
    
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    

    public function create($dni, $name, $lastname, $email, $user_id) {
        $sql = "INSERT INTO " . $this->table . " (num_doc, name, lastname, email, user_id) VALUES (:dni, :name, :lastname, :email, :user_id)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            'dni' => $dni,
            'name' => $name,
            'lastname' => $lastname,
            'email' => $email,
            'user_id' => $user_id
        ]);
    }

    public function update($id, $dni, $name, $lastname, $email) {
        $sql = "UPDATE " . $this->table . " SET num_doc = :dni, name = :name, lastname = :lastname, email = :email WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            'dni' => $dni,
            'name' => $name,
            'lastname' => $lastname,
            'email' => $email,
            'id' => $id
        ]);
    }

    public function delete($id) {
        $sql = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
}
?>