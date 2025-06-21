<?php
session_start();
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header("Location: ../login_admin.php");
    exit();
}

require_once __DIR__ . '/../../config/database.php';

$db = new Database();
$conn = $db->connect();

// Validar ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "ID de asistencia inválido";
    header("Location: dashboard.php");
    exit();
}

$assistance_id = $_GET['id'];

// Obtener datos del trabajador
$query = "SELECT w.name, w.lastname 
          FROM assistances a
          JOIN workers w ON a.user_id = w.user_id
          WHERE a.id = ?";
$stmt = $conn->prepare($query);
$stmt->execute([$assistance_id]);
$data = $stmt->fetch();

if (!$data) {
    $_SESSION['error'] = "Registro no encontrado";
    header("Location: dashboard.php");
    exit();
}

// Si se confirma la eliminación (ocultación)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $updateQuery = "UPDATE assistances SET is_deleted = 1 WHERE id = ?";
        $stmt = $conn->prepare($updateQuery);
        if ($stmt->execute([$assistance_id])) {
            $_SESSION['success'] = "Asistencia eliminada correctamente";
        } else {
            $_SESSION['error'] = "Error al eliminar asistencia";
        }
        header("Location: dashboard.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error en base de datos: " . $e->getMessage();
        header("Location: dashboard.php");
        exit();
    }
}

$title = "Eliminar Asistencia";
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
                            ¿Estás seguro de que quieres eliminar el registro de asistencia de 
                            <strong><?= htmlspecialchars($data['name'] . ' ' . $data['lastname']) ?></strong>?
                        </p>
                        <p class="mb-0">
                            <small>Este registro no se eliminará de la base de datos, solo se ocultará.</small>
                        </p>
                    </div>
                    
                    <form method="post" class="d-flex justify-content-between">
                        <a href="dashboard.php" class="btn btn-secondary">
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
