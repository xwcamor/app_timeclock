<?php
session_start();
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header("Location: ../login_admin.php");
    exit();
}

require_once __DIR__ . '/../../config/Database.php';

$db = new Database();
$conn = $db->connect();

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Consultar horario por ID
    $query = "SELECT * FROM work_times WHERE id = :id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $work_time = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$work_time) {
        header("Location: workes_times.php");
        exit();
    }
} else {
    header("Location: workes_times.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $description = $_POST['description'] ?? '';
    $hour_time_ini = $_POST['hour_time_ini'] ?? '';
    $hour_time_end = $_POST['hour_time_end'] ?? '';
    $type_login = $_POST['type_login'] ?? '';
    $is_activated = isset($_POST['is_activated']) ? 1 : 0;

    if ($description && $hour_time_ini && $hour_time_end && $type_login) {
        $update = "UPDATE work_times 
                   SET description = :description, hour_time_ini = :hour_time_ini, hour_time_end = :hour_time_end, 
                       type_login = :type_login, is_activated = :is_activated
                   WHERE id = :id";
        $stmt = $conn->prepare($update);
        $stmt->execute([
            ':description' => $description,
            ':hour_time_ini' => $hour_time_ini,
            ':hour_time_end' => $hour_time_end,
            ':type_login' => $type_login,
            ':is_activated' => $is_activated,
            ':id' => $id
        ]);

        header("Location: workes_times.php");
        exit();
    } else {
        $error = "Todos los campos son obligatorios.";
    }
}

$title = "Editar Horario de Trabajo";
include __DIR__ . '/../../views/templates/header.php';
?>

<?php include __DIR__ . '/../../views/templates/sidebar.php'; ?>

<div class="edit-user-container">
    <h2 class="mb-4">✏️ Editar Horario</h2>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <style>
        <?php include __DIR__ . '/../../public/css/admin/edit.css'; ?>
    </style>

    <form method="POST">
        <div class="mb-3">
            <label for="description" class="form-label">Descripción</label>
            <input type="text" class="form-control" name="description" value="<?= htmlspecialchars($work_time['description']) ?>" required>
        </div>

        <div class="mb-3">
            <label for="hour_time_ini" class="form-label">Hora de Inicio</label>
            <input type="time" class="form-control" name="hour_time_ini" value="<?= htmlspecialchars($work_time['hour_time_ini']) ?>" required>
        </div>

        <div class="mb-3">
            <label for="hour_time_end" class="form-label">Hora de Fin</label>
            <input type="time" class="form-control" name="hour_time_end" value="<?= htmlspecialchars($work_time['hour_time_end']) ?>" required>
        </div>

        <div class="mb-3">
            <label for="type_login" class="form-label">Tipo de Ingreso</label>
            <select class="form-select" name="type_login" required>
                <option value="">Seleccione una opción</option>
                <option value="1" <?= $work_time['type_login'] == 1 ? 'selected' : '' ?>>Remoto</option>
                <option value="2" <?= $work_time['type_login'] == 2 ? 'selected' : '' ?>>Presencial</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="is_activated" class="form-label">Estado</label>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="is_activated" name="is_activated" <?= $work_time['is_activated'] == 1 ? 'checked' : '' ?>>
                <label class="form-check-label" for="is_activated">Activo</label>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
        <a href="workes_times.php" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

<?php include __DIR__ . '/../../views/templates/footer.php'; ?>
