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
    $positionId = $_GET['id'];

    // Consultar los datos del cargo/posición
    $query = "SELECT id, name FROM position WHERE id = :id AND is_deleted = 0";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $positionId);
    $stmt->execute();
    $position = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$position) {
        header("Location: position.php");
        exit();
    }
} else {
    header("Location: position.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);

    if (empty($name)) {
        $error = "El nombre del cargo no puede estar vacío";
    } else {
        // Verificar si ya existe otro cargo con el mismo nombre
        $checkQuery = "SELECT id FROM position WHERE name = :name AND id != :id AND is_deleted = 0";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bindParam(':name', $name);
        $checkStmt->bindParam(':id', $positionId);
        $checkStmt->execute();
        
        if ($checkStmt->rowCount() > 0) {
            $error = "Ya existe un cargo con ese nombre";
        } else {
            $updateQuery = "UPDATE position SET name = :name WHERE id = :id";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bindParam(':name', $name);
            $updateStmt->bindParam(':id', $positionId);

            if ($updateStmt->execute()) {
                header("Location: position.php");
                exit();
            } else {
                $error = "Ocurrió un error al actualizar el cargo";
            }
        }
    }
}

$title = "Editar Cargo";
include __DIR__ . '/../../views/templates/header.php';
?>

<!-- Sidebar -->
<?php include __DIR__ . '/../../views/templates/sidebar.php'; ?>

<div class="edit-user-container">
        <h2 class="edit-user-title">
            Editar Cargo
        </h2>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <style>
            <?php include __DIR__ . '/../../public/css/admin/edit.css'; ?>
        </style>

        <form method="POST">
            <div class="mb-3">
                <label for="name" class="form-label">Nombre del Cargo</label>
                <input type="text" class="form-control" id="name" name="name" 
                       value="<?= htmlspecialchars($position['name']) ?>" required>
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                     Guardar Cambios
                </button>
                <a href="position.php" class="btn btn-secondary">
                     Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../../views/templates/footer.php'; ?>