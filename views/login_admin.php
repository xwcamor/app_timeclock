<?php
session_start();
require_once __DIR__ . '/../app/controllers/UserController.php';

$message = "";
$alertType = "error";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $dni = $_POST["dni"] ?? '';
    $password = $_POST["password"] ?? '';

    if (empty($dni) || empty($password)) {
        $message = "⚠️ DNI y contraseña son obligatorios";
    } else {
        $userController = new UserController();
        $user = $userController->login($dni, $password);

        if ($user && $userController->isAdmin($dni)) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['is_admin'] = true;
            
            // 🔄 Redirección directa al dashboard sin alerta
            header("Location: ../views/admin/dashboard.php");
            exit();
        } else {
            $message = "🔒 Acceso denegado. Solo para administradores.";
        }
    }
}

$title = "Acceso Administrador";
include __DIR__ . '/templates/header.php';
?>

<div class="card card-custom">
    <div class="card-body">
        <h2 class="card-title text-center mb-4">
            <i class="bi bi-shield-lock"></i> Área Administrativa
        </h2>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">DNI</label>
                <input type="text" name="dni" class="form-control" placeholder="Ingrese su DNI" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Contraseña</label>
                <input type="password" name="password" class="form-control" placeholder="Ingrese su contraseña" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-box-arrow-in-right"></i> Ingresar
            </button>
        </form>

    </div>
</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    <?php if (!empty($message)): ?>
        Swal.fire({
            title: "Error",
            text: "<?= $message ?>",
            icon: "error",
            confirmButtonText: "Entendido"
        });
    <?php endif; ?>
</script>   

<?php include __DIR__ . '/templates/footer.php'; ?>