<?php 
session_start();
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header("Location: ../login_admin.php");
    exit();
}

require_once __DIR__ . '/../../config/Database.php';

$db = new Database();
$conn = $db->connect();

// Validar ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "ID de tarea inválido";
    header("Location: automated_tasks.php");
    exit();
}

$task_id = $_GET['id'];

// Obtener datos de la tarea y nombre del usuario
$query = "SELECT at.name, u.name AS user_name, u.lastname 
          FROM automated_tasks at
          LEFT JOIN users u ON at.user_id = u.id
          WHERE at.id = ?";
$stmt = $conn->prepare($query);
$stmt->execute([$task_id]);
$data = $stmt->fetch();

if (!$data) {
    $_SESSION['error'] = "Tarea no encontrada";
    header("Location: automated_tasks.php");
    exit();
}

// Si se confirma la eliminación (ocultación)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $updateQuery = "UPDATE automated_tasks 
                        SET is_deleted = 1, is_active = 1 
                        WHERE id = ?";
        $stmt = $conn->prepare($updateQuery);
        if ($stmt->execute([$task_id])) {
            $_SESSION['success'] = "Tarea marcada como eliminada correctamente";
        } else {
            $_SESSION['error'] = "Error al marcar la tarea como eliminada";
        }
        header("Location: automated_tasks.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error en base de datos: " . $e->getMessage();
        header("Location: automated_tasks.php");
        exit();
    }
}

$title = "Eliminar Tarea";
include __DIR__ . '/../../views/templates/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-danger text-white">
                    <h4 class="mb-0">
                        <i class="bi bi-trash"></i> Confirmar Eliminación
                    </h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-danger">
                        <p class="h5">
                            ¿Estás seguro de que quieres eliminar la tarea 
                            <strong><?= htmlspecialchars($data['name']) ?></strong>?
                        </p>
                        <p class="mb-0">
                            <small>Esta tarea no se eliminará de la base de datos, solo se marcará como eliminada.</small>
                        </p>
                    </div>
                    
                    <form method="post" class="d-flex justify-content-between">
                        <a href="automated_tasks.php" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash"></i> Eliminar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../views/templates/footer.php'; ?>
