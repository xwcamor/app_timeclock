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
    $_SESSION['error'] = "ID de área inválido";
    header("Location: area.php");
    exit();
}

$area_id = $_GET['id'];

// Obtener datos del área
$query = "SELECT name FROM area WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->execute([$area_id]);
$area = $stmt->fetch();

if (!$area) {
    $_SESSION['error'] = "Área no encontrada";
    header("Location: area.php");
    exit();
}

// Si se confirma la eliminación (soft delete)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $updateQuery = "UPDATE area SET is_deleted = 1 WHERE id = ?";
        $stmt = $conn->prepare($updateQuery);
        
        if ($stmt->execute([$area_id])) {
            $_SESSION['success'] = "Área marcada como eliminada correctamente";
        } else {
            $_SESSION['error'] = "Error al marcar el área como eliminada";
        }
        
        header("Location: area.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error en base de datos: " . $e->getMessage();
        header("Location: area.php");
        exit();
    }
}

$title = "Eliminar Área";
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
                            ¿Estás seguro de que quieres eliminar el área 
                            <strong><?= htmlspecialchars($area['name']) ?></strong>?
                        </p>
                        <p class="mb-0">
                            <small>Esta área no se eliminará de la base de datos, solo se marcará como eliminada.</small>
                        </p>
                    </div>
                    
                    <form method="post" class="d-flex justify-content-between">
                        <a href="area.php" class="btn btn-secondary">
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