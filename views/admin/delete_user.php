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
    $_SESSION['error'] = "ID de usuario inválido";
    header("Location: user.php");
    exit();
}

$user_id = $_GET['id'];

// Obtener datos del usuario
$query = "SELECT u.username, u.name, u.lastname 
          FROM users u 
          WHERE u.id = ?";
$stmt = $conn->prepare($query);
$stmt->execute([$user_id]);
$data = $stmt->fetch();

if (!$data) {
    $_SESSION['error'] = "Usuario no encontrado";
    header("Location: user.php");
    exit();
}

// Si se confirma la eliminación (ocultación)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $updateQuery = "UPDATE users SET is_deleted = 1, is_activated = 1 WHERE id = ?";
        $stmt = $conn->prepare($updateQuery);
        if ($stmt->execute([$user_id])) {
            $_SESSION['success'] = "Usuario marcado como eliminado correctamente";
        } else {
            $_SESSION['error'] = "Error al marcar el usuario como eliminado";
        }
        header("Location: user.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error en base de datos: " . $e->getMessage();
        header("Location: user.php");
        exit();
    }
}



$title = "Eliminar Usuario";
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
                            ¿Estás seguro de que quieres eliminar el usuario 
                            <strong><?= htmlspecialchars($data['name'] . ' ' . $data['lastname']) ?></strong>?
                        </p>
                        <p class="mb-0">
                            <small>Este usuario no se eliminará de la base de datos, solo se marcará como eliminado.</small>
                        </p>
                    </div>
                    
                    <form method="post" class="d-flex justify-content-between">
                        <a href="user.php" class="btn btn-secondary">
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
