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
    $areaId = $_GET['id'];

    // Consultar los datos del área
    $query = "SELECT id, name FROM area WHERE id = :id AND is_deleted = 0";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $areaId);
    $stmt->execute();
    $area = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$area) {
        header("Location: area.php");
        exit();
    }
} else {
    header("Location: area.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);

    if (empty($name)) {
        $error = "El nombre del área no puede estar vacío";
    } else {
        $updateQuery = "UPDATE area SET name = :name WHERE id = :id";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bindParam(':name', $name);
        $updateStmt->bindParam(':id', $areaId);

        if ($updateStmt->execute()) {
            header("Location: area.php");
            exit();
        } else {
            $error = "Ocurrió un error al actualizar el área";
        }
    }
}

$title = "Editar Área";
include __DIR__ . '/../../views/templates/header.php';
?>

<!-- Sidebar -->
<?php include __DIR__ . '/../../views/templates/sidebar.php'; ?>

<div class="edit-user-container">
        <h2 class="edit-user-title">
            <i class="bi bi-building"></i> Editar Área
        </h2>

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
                <label for="name" class="form-label">Nombre del Área</label>
                <input type="text" class="form-control" id="name" name="name" 
                       value="<?= htmlspecialchars($area['name']) ?>" required>
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    Guardar Cambios
                </button>
                <a href="area.php" class="btn btn-secondary">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../../views/templates/footer.php'; ?>