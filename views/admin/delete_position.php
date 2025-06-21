a<?php
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
    $_SESSION['error'] = "ID de cargo inválido";
    header("Location: position.php");
    exit();
}

$position_id = $_GET['id'];

// Obtener datos del cargo/posición
$query = "SELECT name FROM position WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->execute([$position_id]);
$position = $stmt->fetch();

if (!$position) {
    $_SESSION['error'] = "Cargo/posición no encontrado";
    header("Location: position.php");
    exit();
}

// Si se confirma la eliminación (soft delete)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Verificar si hay trabajadores asignados a este cargo
        $checkWorkers = $conn->prepare("SELECT COUNT(*) FROM workers WHERE position_id = ?");
        $checkWorkers->execute([$position_id]);
        $workerCount = $checkWorkers->fetchColumn();

        if ($workerCount > 0) {
            $_SESSION['error'] = "No se puede eliminar el cargo porque tiene trabajadores asignados";
            header("Location: position.php");
            exit();
        }

        $updateQuery = "UPDATE position SET is_deleted = 1 WHERE id = ?";
        $stmt = $conn->prepare($updateQuery);
        
        if ($stmt->execute([$position_id])) {
            $_SESSION['success'] = "Cargo/posición marcado como eliminado correctamente";
        } else {
            $_SESSION['error'] = "Error al marcar el cargo como eliminado";
        }
        
        header("Location: position.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error en base de datos: " . $e->getMessage();
        header("Location: position.php");
        exit();
    }
}

$title = "Eliminar Cargo/Posición";
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
                            ¿Estás seguro de que quieres eliminar el cargo 
                            <strong><?= htmlspecialchars($position['name']) ?></strong>?
                        </p>
                        <p class="mb-0">
                            <small>Este cargo no se eliminará de la base de datos, solo se marcará como eliminado.</small>
                        </p>
                    </div>
                    
                    <form method="post" class="d-flex justify-content-between">
                        <a href="position.php" class="btn btn-secondary">
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