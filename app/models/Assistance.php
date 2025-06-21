<?php
require_once __DIR__ . '/../../config/Database.php';

class Assistance {
    private $conn;
    private $table = "assistances";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function login($dni, $password) {
        $sql = "SELECT u.id, u.username, u.password, u.is_activated, u.is_admin 
                FROM users u 
                WHERE u.username = :dni";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':dni', $dni);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
        // Verificar si el usuario existe y si la contraseña es correcta
        if ($user && password_verify($password, $user['password'])) {
            // Verificar si la cuenta está activada (is_activated = 0)
            if ($user['is_activated'] == 1) {
                return null;  // Si la cuenta está desactivada, devolvemos null
            }
            return $user;  // Si la cuenta está activada, devolvemos los datos del usuario
        }
    
        return null;  // Si no se encuentra el usuario o la contraseña no es correcta
    }
    

    public function getAll() {
        $sql = "SELECT a.*, w.name, w.lastname, w.num_doc, w.email 
                FROM {$this->table} a
                JOIN workers w ON a.user_id = w.user_id
                ORDER BY a.created_at DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $sql = "SELECT a.*, w.name, w.lastname 
                FROM {$this->table} a
                JOIN workers w ON a.user_id = w.user_id
                WHERE a.id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($user_id, $photo_start, $location_start, $date_start, $type_login) {
        $state_assistance = 1;  
        
        $sql = "INSERT INTO {$this->table} 
                (user_id, photo_start, location_start, date_start, type_login, state_assistance, created_at, updated_at) 
                VALUES (:user_id, :photo_start, :location_start, :date_start, :type_login, :state_assistance, NOW(), NOW())";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            'user_id' => $user_id,
            'photo_start' => $photo_start,
            'location_start' => $location_start,
            'date_start' => $date_start,
            'type_login' => $type_login,
            'state_assistance' => $state_assistance
        ]);
    }
    
    public function updateAssistance($id, $photo_end, $location_end, $date_end) {
        $sql = "UPDATE {$this->table} 
                SET photo_end = :photo_end, 
                    location_end = :location_end, 
                    date_end = :date_end,
                    updated_at = NOW()
                WHERE id = :id";
        $stmt = $this->conn->prepare($sql);

        // Agregar la depuración aquí
        if (!$stmt->execute([
            'id' => $id,
            'photo_end' => $photo_end,
            'location_end' => $location_end,
            'date_end' => $date_end
        ])) {
            $errorInfo = $stmt->errorInfo(); // Obtén el error de la consulta
            echo json_encode(["success" => false, "message" => "Error en la consulta SQL: " . implode(" - ", $errorInfo)]);
            return false;
        }
        return true;
    }

    public function getLatestAssistance($user_id) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE user_id = :user_id 
                ORDER BY id DESC LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['user_id' => $user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function registerExit($user_id, $photo_end, $location_end, $date_end) {
        $latestAssistance = $this->getLatestAssistance($user_id);
        if (!$latestAssistance) {
            // Si no se encuentra una asistencia, retorna un error
            echo json_encode(["success" => false, "message" => "No se encontró asistencia previa para el usuario."]);
            return false;
        }
        return $this->updateAssistance(
            $latestAssistance['id'],
            $photo_end,
            $location_end,
            $date_end
        );
    }

    public function getAssistanceByUserAndDate($user_id, $date) {
        $sql = "SELECT * FROM assistances 
                WHERE user_id = ? 
                AND DATE(date_start) = ? 
                AND is_deleted = 0 
                LIMIT 1";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$user_id, $date]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function hasCompletedToday($user_id) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} 
                WHERE user_id = :user_id 
                AND DATE(date_start) = CURDATE() 
                AND date_end IS NOT NULL
                AND is_deleted = 0";  // Añadir esta condición para verificar que no esté eliminada
    
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['user_id' => $user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
        return $result['total'] > 0; // Retorna true si ya tiene entrada y salida registradas hoy y no está eliminada
    }

    // Función para obtener la asistencia de un usuario en un rango de fechas
    public function getWorkersWithNoExit($toleranciaMinutos)
    {
        $sql = "SELECT a.id AS assistance_id, w.name, w.lastname, w.num_doc, w.email, wt.hour_time_end
                FROM assistances a
                JOIN workers w ON a.user_id = w.user_id
                LEFT JOIN work_times wt ON w.id_times = wt.id
                WHERE a.date_start IS NOT NULL
                  AND a.date_end IS NULL
                  AND a.is_deleted = 0
                  AND a.notificado = 0
                  AND DATE(a.date_start) = CURDATE()
                  AND CURTIME() >= ADDTIME(wt.hour_time_end, SEC_TO_TIME(:tolerancia))";
        
        $stmt = $this->conn->prepare($sql);
        $toleranciaSegundos = $toleranciaMinutos * 60;
        $stmt->bindParam(':tolerancia', $toleranciaSegundos, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


}
?>
