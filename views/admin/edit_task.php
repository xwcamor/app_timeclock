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
    $taskId = $_GET['id'];

    // Consultar datos de la tarea
    $query = "SELECT * FROM automated_tasks WHERE id = :id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $taskId);
    $stmt->execute();
    $task = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$task) {
        header("Location: automated_tasks.php");
        exit();
    }
} else {
    header("Location: automated_tasks.php");
    exit();
}

// Obtener lista de usuarios para el select
$usersStmt = $conn->prepare("SELECT id, name, lastname FROM users WHERE is_admin = 0");
$usersStmt->execute();
$users = $usersStmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $user_id = $_POST['user_id'] !== '' ? $_POST['user_id'] : null;
    $date_start = $_POST['date_start'];
    $date_end = $_POST['date_end'];
    $is_active = isset($_POST['is_active']) ? 0 : 1;

    $updateQuery = "UPDATE automated_tasks 
                    SET name = :name, user_id = :user_id, date_start = :date_start, date_end = :date_end, 
                        is_active = :is_active, updated_at = NOW() 
                    WHERE id = :id";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':date_start', $date_start);
    $stmt->bindParam(':date_end', $date_end);
    $stmt->bindParam(':is_active', $is_active);
    $stmt->bindParam(':id', $taskId);

    if ($stmt->execute()) {
        header("Location: automated_tasks.php");
        exit();
    } else {
        $error = "Ocurrió un error al actualizar la tarea.";
    }
}

$title = "Editar Tarea";
include __DIR__ . '/../../views/templates/header.php';
?>

<?php include __DIR__ . '/../../views/templates/sidebar.php'; ?>

<div class="edit-user-container">
    <h2 class="mb-4">Editar Tarea</h2>

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
            <label for="name" class="form-label">Asunto</label>
            <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($task['name']) ?>" required>
        </div>
        <div class="mb-3">
            <label for="user_id" class="form-label">Usuario (opcional)</label>
            <select class="form-select" name="user_id" id="user_id">
                <option value="">-- Todos los usuarios --</option>
                <?php foreach ($users as $user): ?>
                    <option value="<?= $user['id'] ?>" <?= ($task['user_id'] == $user['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($user['name'] . ' ' . $user['lastname']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="date_start" class="form-label">Fecha Inicio</label>
            <?php $dateStart = date('Y-m-d', strtotime($task['date_start'])); ?>
            <input type="date" class="form-control" id="date_start" name="date_start" value="<?= $dateStart ?>" required>

        </div>
        <div class="mb-3">
            <label for="date_end" class="form-label">Fecha Fin</label>
            <?php $dateEnd = date('Y-m-d', strtotime($task['date_end'])); ?>
            <input type="date" class="form-control" id="date_end" name="date_end" value="<?= $dateEnd ?>" required>

        </div>
        <div class="mb-3">
            <label for="is_active" class="form-label">Estado</label>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" <?= $task['is_active'] == 0 ? 'checked' : '' ?>>
                <label class="form-check-label" for="is_active">Activo</label>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
        <a href="automated_tasks.php" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

<?php include __DIR__ . '/../../views/templates/footer.php'; ?>
