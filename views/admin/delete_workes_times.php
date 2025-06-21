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
    $_SESSION['error'] = "ID de horario inválido";
    header("Location: workes_times.php");
    exit();
}

$time_id = $_GET['id'];

// Obtener datos del horario
$query = "SELECT description FROM work_times WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->execute([$time_id]);
$data = $stmt->fetch();

if (!$data) {
    $_SESSION['error'] = "Horario no encontrado";
    header("Location: workes_times.php");
    exit();
}

// Si se confirma la eliminación (ocultación)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $updateQuery = "UPDATE work_times 
                        SET is_deleted = 1, is_activated = 1 
                        WHERE id = ?";
        $stmt = $conn->prepare($updateQuery);
        if ($stmt->execute([$time_id])) {
            $_SESSION['success'] = "Horario marcado como eliminado correctamente";
        } else {
            $_SESSION['error'] = "Error al marcar el horario como eliminado";
        }
        header("Location: workes_times.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error en base de datos: " . $e->getMessage();
        header("Location: workes_times.php");
        exit();
    }
}

$title = "Eliminar Horario";
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
                            ¿Estás seguro de que quieres eliminar el horario 
                            <strong><?= htmlspecialchars($data['description']) ?></strong>?
                        </p>
                        <p class="mb-0">
                            <small>Este horario no se eliminará de la base de datos, solo se marcará como eliminado.</small>
                        </p>
                    </div>
                    
                    <form method="post" class="d-flex justify-content-between">
                        <a href="workes_times.php" class="btn btn-secondary">
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
