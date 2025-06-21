<?php
session_start();
require_once __DIR__ . '/../app/controllers/UserController.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $dni = $_POST["dni"] ?? '';
    $password = $_POST["password"] ?? '';
    $name = $_POST["name"] ?? '';
    $lastname = $_POST["lastname"] ?? '';
    $email = $_POST["email"] ?? '';
    $is_admin = isset($_POST["is_admin"]) ? true : false; // Capturar si el usuario será admin

    if (empty($dni) || empty($password) || empty($name) || empty($lastname) || empty($email)) {
        $_SESSION['error'] = "⚠️ Todos los campos son obligatorios.";
    } else {
        $userController = new UserController();
        $result = $userController->register($dni, $password, $name, $lastname, $email, $is_admin);

        if ($result) {
            $_SESSION['alert'] = ["message" => "Usuario Nuevo Registrado", "type" => "success"];
            // Después de la alerta, redirigimos al login
            $_SESSION['alert_redirect'] = "../views/admin/user.php";
        }
    }
}

$title = "Registro de Trabajadores";
include __DIR__ . '/templates/header.php';
?>

<div class="card card-custom">
    <div class="card-body">
        <h2 class="card-title text-center mb-4">
            <i class="bi bi-person-plus"></i> Registro de Trabajadores
        </h2>
        <?php include __DIR__ . '/templates/alerts.php'; ?>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">DNI</label>
                <input type="text" name="dni" class="form-control" placeholder="Ingrese el DNI" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Contraseña</label>
                <input type="password" name="password" class="form-control" placeholder="Ingrese la contraseña" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Nombre</label>
                <input type="text" name="name" class="form-control" placeholder="Ingrese el nombre" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Apellido</label>
                <input type="text" name="lastname" class="form-control" placeholder="Ingrese el apellido" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" placeholder="Ingrese el email" required>
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" name="is_admin" class="form-check-input" id="isAdminCheckbox">
                <label class="form-check-label" for="isAdminCheckbox">Crear como Administrador</label>
            </div>
            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-person-check"></i> Registrar
            </button>

            <div class="mt-3 text-center">
                <a href="../views/admin/user.php">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    <?php if (isset($_SESSION['alert'])): ?>
        Swal.fire({
            title: "<?= $_SESSION['alert']['type'] === 'success' ? '¡Éxito!' : 'Atención' ?>",
            text: "<?= $_SESSION['alert']['message'] ?>",
            icon: "<?= $_SESSION['alert']['type'] ?>",
            confirmButtonText: "Entendido"
        }).then((result) => {
            // Después de que el usuario cierre la alerta, redirigimos a la página indicada
            window.location.href = "<?= $_SESSION['alert_redirect'] ?>";
        });
        <?php unset($_SESSION['alert']); unset($_SESSION['alert_redirect']); ?>
    <?php endif; ?>
</script>

<?php include __DIR__ . '/templates/footer.php'; ?>
