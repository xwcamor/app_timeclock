<?php
require_once __DIR__ . '/../../config/Database.php';

$db = new Database();
$conn = $db->connect();

// Obtener todos los usuarios activos que NO son admin
$stmt = $conn->prepare("SELECT id, name FROM users WHERE is_deleted = 0 AND is_admin = 0");
$stmt->execute();
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

$error = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'] ?? '';
    $user_id = $_POST['user_id'] ?? '';
    $date_start = $_POST['date_start'] ?? '';
    $date_end = $_POST['date_end'] ?? '';
    $now = date("Y-m-d H:i:s");

    // Si el user_id es 0, lo convertimos en NULL
    if ($user_id == 0) {
        $user_id = null;
    }

    if ($name && $date_start && $date_end) {  // Se quitó la validación para $user_id
        try {
            $sql = "INSERT INTO automated_tasks (name, user_id, date_start, date_end, is_active, is_deleted, created_at, updated_at)
                    VALUES (:name, :user_id, :date_start, :date_end, :is_active, :is_deleted, :created_at, :updated_at)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':name' => $name,
                ':user_id' => $user_id,  // Será NULL si seleccionaron "Todos los usuarios"
                ':date_start' => $date_start,
                ':date_end' => $date_end,
                ':is_active' => 0,
                ':is_deleted' => 0,
                ':created_at' => $now,
                ':updated_at' => $now
            ]);
            
            // Redirigir si se registró correctamente
            header("Location: automated_tasks.php");
            exit();

        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    } else {
        $error = "Todos los campos son obligatorios.";
    }
}

$title = "Registrar Tarea";
include __DIR__ . '/../../views/templates/header.php';
?>

<?php include __DIR__ . '/../../views/templates/sidebar.php'; ?>

<div class="container mt-5">
    <div class="edit-task-container">
        <h2 class="edit-task-title">📝 Registrar Nueva Tarea</h2>

        <!-- Mensajes -->
        <?php if ($error): ?>
            <div class="alert alert-danger">❌ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <style>
        <?php include __DIR__ . '/../../public/css/admin/register.css'; ?>
        </style>

        <!-- Formulario -->
        <form method="POST" class="edit-task-form">
            <div class="mb-3">
                <label for="name" class="form-label">Asunto</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>

            <div class="mb-3">
                <label for="user_id" class="form-label">Usuario Responsable</label>
                <select name="user_id" class="form-select" required>
                    <option value="">Seleccione un usuario</option>
                    <option value="0">Todos los usuarios</option> <!-- NUEVO -->
                    <?php foreach ($usuarios as $usuario): ?>
                        <option value="<?= $usuario['id'] ?>"><?= htmlspecialchars($usuario['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="date_start" class="form-label">Día de Inicio</label>
                <input type="date" class="form-control" id="date_start" name="date_start" required>
            </div>

            <div class="mb-3">
                <label for="date_end" class="form-label">Día Final</label>
                <input type="date" class="form-control" id="date_end" name="date_end" required>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100">
                     Registrar Tarea
                </button>
                <a href="automated_tasks.php" class="btn btn-secondary w-100">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../../views/templates/footer.php'; ?>
