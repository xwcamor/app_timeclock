<?php
session_start();
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header("Location: ../login_admin.php");
    exit();
}

require_once __DIR__ . '/../../config/database.php';

$db = new Database();
$conn = $db->connect();

if (isset($_GET['id'])) {
    $userId = $_GET['id'];

    // Consultar los datos del usuario
    $query = "SELECT id, username, name, lastname, is_activated FROM users WHERE id = :id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $userId);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        // Si el usuario no existe
        header("Location: user.php");
        exit();
    }
} else {
    // Si no se pasa el ID
    header("Location: user.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener los datos del formulario
    $username = $_POST['username'];
    $name = $_POST['name'];
    $lastname = $_POST['lastname'];
    // Cambiar la lógica del checkbox para asignar correctamente el estado activado
    $is_activated = isset($_POST['is_activated']) ? 0 : 1;  // 0 es activado, 1 es desactivado

    // Actualizar los datos del usuario
    $updateQuery = "UPDATE users SET username = :username, name = :name, lastname = :lastname, is_activated = :is_activated WHERE id = :id";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bindParam(':username', $username);
    $updateStmt->bindParam(':name', $name);
    $updateStmt->bindParam(':lastname', $lastname);
    $updateStmt->bindParam(':is_activated', $is_activated);
    $updateStmt->bindParam(':id', $userId);

    // Ejecutar la actualización de la tabla users
    if ($updateStmt->execute()) {
        // Ahora actualizamos la tabla workers con los mismos datos
        $updateWorkerQuery = "UPDATE workers SET num_doc = :username, name = :name, lastname = :lastname WHERE user_id = :user_id";
        $updateWorkerStmt = $conn->prepare($updateWorkerQuery);
        $updateWorkerStmt->bindParam(':username', $username);
        $updateWorkerStmt->bindParam(':name', $name);
        $updateWorkerStmt->bindParam(':lastname', $lastname);
        $updateWorkerStmt->bindParam(':user_id', $userId);

        // Ejecutar la actualización de la tabla workers
        if ($updateWorkerStmt->execute()) {
            // Si ambas actualizaciones son exitosas, redirigir al listado de usuarios
            header("Location: user.php"); // Redirige a user.php
            exit();
        } else {
            $error = "Ocurrió un error al actualizar los datos en la tabla workers.";
        }
    } else {
        $error = "Ocurrió un error al actualizar los datos del usuario.";
    }
}

$title = "Editar Usuario";
include __DIR__ . '/../../views/templates/header.php';
?>

<!-- Sidebar -->
<?php include __DIR__ . '/../../views/templates/sidebar.php'; ?>


<div class="edit-user-container">
    <h2 class="mb-4">Editar Usuario</h2>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <style>
      <?php include __DIR__ . '/../../public/css/admin/edit.css'; ?>
    </style>

    <form method="POST">
        <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
        </div>
        <div class="mb-3">
            <label for="name" class="form-label">Nombre</label>
            <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
        </div>
        <div class="mb-3">
            <label for="lastname" class="form-label">Apellido</label>
            <input type="text" class="form-control" id="lastname" name="lastname" value="<?= htmlspecialchars($user['lastname']) ?>" required>
        </div>
        <div class="mb-3">
            <label for="is_activated" class="form-label">Estado de Activación</label>
            <div class="form-check">
                <!-- La lógica aquí es cambiar el valor para que cuando esté activado se marque -->
                <input type="checkbox" class="form-check-input" id="is_activated" name="is_activated" <?= $user['is_activated'] == 0 ? 'checked' : '' ?>>
                <label class="form-check-label" for="is_activated">Activado</label>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
        <a href="user.php" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

<?php include __DIR__ . '/../../views/templates/footer.php'; ?>
