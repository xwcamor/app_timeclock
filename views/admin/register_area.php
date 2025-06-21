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
    $now = date("Y-m-d H:i:s");

    if (!empty($name)) {
        try {
            // Verificar si el área ya existe
            $checkStmt = $conn->prepare("SELECT id FROM area WHERE name = :name AND is_deleted = 0");
            $checkStmt->bindParam(':name', $name);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() > 0) {
                $error = "El área ya existe";
            } else {
                // Insertar nueva área
                $sql = "INSERT INTO area (name, is_deleted, created_at, updated_at)
                        VALUES (:name, :is_deleted, :created_at, :updated_at)";
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    ':name' => $name,
                    ':is_deleted' => 0,
                    ':created_at' => $now,
                    ':updated_at' => $now
                ]);
                
                header("Location: area.php");
                exit();
            }
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    } else {
        $error = "El nombre del área es obligatorio";
    }
}

$title = "Registrar Área";
include __DIR__ . '/../../views/templates/header.php';
?>

<?php include __DIR__ . '/../../views/templates/sidebar.php'; ?>

<div class="container mt-5">
    <div class="edit-task-container">
        <h2 class="edit-task-title">🏢 Registrar Nueva Área</h2>

        <!-- Mensajes -->
        <?php if ($error): ?>
            <div class="alert alert-danger">❌ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <style>
        <?php include __DIR__ . '/../../public/css/admin/register.css'; ?>
        </style>

        <!-- Formulario -->
        <form method="POST" class="edit-task-form">
            <div class="mb-3">
                <label for="name" class="form-label">Nombre del Área</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100">
                    Registrar Área
                </button>
                <a href="area.php" class="btn btn-secondary w-100">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../../views/templates/footer.php'; ?>
