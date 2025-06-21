<?php
session_start();
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header("Location: ../login_admin.php");
    exit();
}

require_once __DIR__ . '/../../config/Database.php';

$db = new Database();
$conn = $db->connect();

$error = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name'] ?? '');

    if (!empty($name)) {
        try {
            // Verificar si la posición ya existe
            $checkStmt = $conn->prepare("SELECT id FROM position WHERE name = :name AND is_deleted = 0");
            $checkStmt->bindParam(':name', $name);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() > 0) {
                $error = "El cargo/posición ya existe";
            } else {
                // Insertar nueva posición
                $sql = "INSERT INTO position (name, is_deleted) VALUES (:name, :is_deleted)";
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    ':name' => $name,
                    ':is_deleted' => 0
                ]);
                
                header("Location: position.php");
                exit();
            }
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    } else {
        $error = "El nombre del cargo es obligatorio";
    }
}

$title = "Registrar Cargo";
include __DIR__ . '/../../views/templates/header.php';
?>

<?php include __DIR__ . '/../../views/templates/sidebar.php'; ?>

<div class="container mt-5">
    <div class="edit-task-container">
        <h2 class="edit-task-title">    
            <i class="bi bi-person-badge"></i> Registrar Nuevo Cargo
        </h2>

        <!-- Mensajes -->
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <style>
        <?php include __DIR__ . '/../../public/css/admin/register.css'; ?>
        </style>

        <!-- Formulario -->
        <form method="POST" class="edit-task-form">
            <div class="mb-3">
                <label for="name" class="form-label">Nombre del Cargo</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100">
                    Registrar Cargo
                </button>
                <a href="position.php" class="btn btn-secondary w-100">
                   Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../../views/templates/footer.php'; ?>