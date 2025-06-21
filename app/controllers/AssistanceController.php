<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Worker.php';
require_once __DIR__ . '/../models/Assistance.php';

class UserController {
    public function register($dni, $password, $name, $lastname, $email, $is_admin = false) {
        $user = new User();
        $worker = new Worker();
    
        // Verificar si el usuario ya existe antes de crearlo
        if ($user->exists($dni)) {
            $_SESSION['error'] = "⚠️ El usuario con DNI $dni ya está registrado.";
            return false;
        }
    
        // Crear usuario en la tabla users
        $user_id = $user->create($dni, $password, $name, $lastname);
    
        if ($user_id && !$is_admin) {
            return $worker->create($dni, $name, $lastname, $email, $user_id);
        }
        return $user_id;
    }
    

    public function login($dni, $password) {
        $user = new User();
        $assistance = new Assistance();
        $result = $user->login($dni, $password);

        if ($result) {
            // Verificar si el usuario ya registró salida hoy
            $user_id = $result['id'];
            if ($assistance->hasCompletedToday($user_id)) {
                return "registered_exit";
            }

            // Guardar en sesión
            $_SESSION['user_id'] = $user_id;
            $_SESSION['is_admin'] = $this->isAdmin($dni);
        }

        return $result;
    }

    public function isAdmin($dni) {
        $user = new User();
        $db = $user->getConnection();

        $sql = "SELECT COUNT(*) AS total FROM users WHERE username = ? AND id NOT IN (SELECT user_id FROM workers)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$dni]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result['total'] > 0;
    }
}
?>