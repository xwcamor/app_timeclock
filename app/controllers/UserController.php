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
        $user_id = $user->create($dni, $password, $name, $lastname, $is_admin);
    
        // Si el usuario no es admin, entonces lo insertamos en workers
        if ($user_id) {
            // Si el usuario es admin, lo creamos también en la tabla workers
            // En caso de ser administrador, podemos dejar los campos de worker vacíos o colocar un valor por defecto
            $worker_created = $worker->create($dni, $name, $lastname, $email, $user_id);

            // Si no es admin, lo insertamos en la tabla workers como trabajador regular
            if (!$is_admin) {
                return $worker_created;
            }
            // Si es admin, no es necesario crear una relación más compleja, pero asegúrate de asignar el 'user_id' a la tabla 'workers'
            return true;
        }
        return false;
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
            $_SESSION['is_admin'] = $this->isAdmin($dni); // Ahora verifica si es admin con la nueva lógica
        }

        return $result;
    }

    public function isAdmin($dni) {
        $user = new User();
        $db = $user->getConnection();

        // Ahora verificamos directamente el valor de is_admin
        $sql = "SELECT is_admin FROM users WHERE username = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$dni]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result && $result['is_admin'] == 1; // Retorna true si es admin
    }
}
?>
