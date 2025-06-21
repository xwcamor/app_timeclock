<?php
require_once __DIR__ . '/../../config/Database.php';

$db = new Database();
$conn = $db->connect();

$error = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $description = $_POST['description'] ?? '';
    $hour_time_ini = $_POST['hour_time_ini'] ?? '';
    $hour_time_end = $_POST['hour_time_end'] ?? '';
    $type_login = $_POST['type_login'] ?? '';

    if ($description && $hour_time_ini && $hour_time_end && $type_login) {
        try {
            $sql = "INSERT INTO work_times (description, hour_time_ini, hour_time_end, type_login, is_activated, is_deleted)
                    VALUES (:description, :hour_time_ini, :hour_time_end, :type_login, :is_activated, :is_deleted)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':description' => $description,
                ':hour_time_ini' => $hour_time_ini,
                ':hour_time_end' => $hour_time_end,
                ':type_login' => $type_login,
                ':is_activated' => 1,
                ':is_deleted' => 0
            ]);

            header("Location: workes_times.php");
            exit();
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    } else {
        $error = "Todos los campos son obligatorios.";
    }
}

$title = "Registrar Horario de Trabajo";
include __DIR__ . '/../../views/templates/header.php';
?>

<?php include __DIR__ . '/../../views/templates/sidebar.php'; ?>

<div class="container mt-5">
    <div class="edit-task-container">
        <h2 class="edit-task-title">⏱ Registrar Nuevo Horario</h2>

        <?php if ($error): ?>
            <div class="alert alert-danger">❌ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <style>
        <?php include __DIR__ . '/../../public/css/admin/register.css'; ?>
        </style>

        <form method="POST" class="edit-task-form">
            <div class="mb-3">
                <label for="description" class="form-label">Descripción</label>
                <input type="text" class="form-control" id="description" name="description" required>
            </div>

            <div class="mb-3">
                <label for="hour_time_ini" class="form-label">Hora de Inicio</label>
                <input type="time" class="form-control" id="hour_time_ini" name="hour_time_ini" required>
            </div>

            <div class="mb-3">
                <label for="hour_time_end" class="form-label">Hora de Fin</label>
                <input type="time" class="form-control" id="hour_time_end" name="hour_time_end" required>
            </div>

            <div class="mb-3">
                <label for="type_login" class="form-label">Tipo de Ingreso</label>
                <select name="type_login" class="form-select" required>
                    <option value="">Seleccione una opción</option>
                    <option value="1">Remoto</option>
                    <option value="2">Presencial</option>
                </select>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100">
                     Registrar Horario
                </button>
                <a href="workes_times.php" class="btn btn-secondary w-100">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../../views/templates/footer.php'; ?>
