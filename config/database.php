<?php
class Database {
    private $host = "localhost";
    private $dbname = "camola41_asistencia_db"; // Asegurate de que el nombre sea el correcto
    private $username = "root";
    private $password = ""; 
    public $conn;

    public function connect() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->dbname, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Error de conexi��n: " . $e->getMessage());
        }
        return $this->conn;
    }
}
?>
