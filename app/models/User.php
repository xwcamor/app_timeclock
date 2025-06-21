<?php
require_once __DIR__ . '/../../config/database.php';

class User {
    private $conn;
    private $table = "users";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function getConnection() {
        return $this->conn;
    }

    public function getAll() {
        $sql = "SELECT * FROM users";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Actualizado: ahora recibe el parámetro 'is_admin'
    public function create($dni, $password, $name, $lastname, $is_admin = false) {
        if (!$dni || !$password || !$name || !$lastname) {
            return false; // Evitar inserción de valores nulos
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Modificamos la consulta para incluir 'is_admin'
        $sql = "INSERT INTO users (username, password, name, lastname, is_admin) VALUES (:username, :password, :name, :lastname, :is_admin)";
        $stmt = $this->conn->prepare($sql);

        // Ejecutamos la consulta con el valor de 'is_admin' incluido
        return $stmt->execute([
            'username' => $dni,
            'password' => $hashedPassword,
            'name' => $name,
            'lastname' => $lastname,
            'is_admin' => $is_admin ? 1 : 0 // Si 'is_admin' es true, guardamos 1, sino guardamos 0
        ]) ? $this->conn->lastInsertId() : false;
    }

    public function login($dni, $password) {
        $sql = "SELECT * FROM " . $this->table . " WHERE username = :dni";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['dni' => $dni]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }

    public function getUserById($id) {
        $sql = "SELECT * FROM users WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function exists($dni) {
        $sql = "SELECT COUNT(*) as total FROM users WHERE username = :dni";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['dni' => $dni]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result['total'] > 0; // Retorna true si ya existe
    }

    public function getTodayAssistance($user_id) {
        $sql = "SELECT * FROM assistances WHERE user_id = ? AND DATE(created_at) = CURDATE() AND is_deleted = 0 LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
}
?>
