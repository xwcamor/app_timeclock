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
    $_SESSION['error'] = "ID de trabajador inválido";
    header("Location: workers.php");
    exit();
}

$worker_id = $_GET['id'];

// Obtener datos del trabajador y su usuario asociado
$query = "SELECT w.name, w.lastname, w.user_id
          FROM workers w
          WHERE w.id = ? AND w.is_deleted = 0";
$stmt = $conn->prepare($query);
$stmt->execute([$worker_id]);
$data = $stmt->fetch();

if (!$data) {
    $_SESSION['error'] = "Trabajador no encontrado";
    header("Location: worker.php");
    exit();
}

$user_id = $data['user_id'];

// Si se confirma la eliminación
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $conn->beginTransaction();

        // Marcar al trabajador como eliminado
        $stmtWorker = $conn->prepare("UPDATE workers SET is_deleted = 1 WHERE id = ?");
        $stmtWorker->execute([$worker_id]);

        // Marcar al usuario relacionado como eliminado y desactivado
        $stmtUser = $conn->prepare("UPDATE users SET is_deleted = 1, is_activated = 1 WHERE id = ?");
        $stmtUser->execute([$user_id]);

        $conn->commit();

        $_SESSION['success'] = "Trabajador y usuario relacionados eliminados correctamente";
        header("Location: workers.php");
        exit();
    } catch (PDOException $e) {
        $conn->rollBack();
        $_SESSION['error'] = "Error en base de datos: " . $e->getMessage();
        header("Location: workers.php");
        exit();
    }
}

$title = "Eliminar Trabajador";
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
                            ¿Estás seguro de que quieres eliminar al trabajador 
                            <strong><?= htmlspecialchars($data['name'] . ' ' . $data['lastname']) ?></strong>?
                        </p>
                        <p class="mb-0">
                            <small>Este trabajador y su usuario relacionado no serán eliminados de la base de datos, solo se marcarán como eliminados.</small>
                        </p>
                    </div>
                    
                    <form method="post" class="d-flex justify-content-between">
                        <a href="workers.php" class="btn btn-secondary">
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
