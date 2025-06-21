<?php
session_start();
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header("Location: ../login_admin.php");
    exit();
}

require_once __DIR__ . '/../../config/Database.php';
$db = new Database();
$conn = $db->connect();

// Consulta para obtener todas las áreas no eliminadas
$query = "SELECT id, name FROM area WHERE is_deleted = 0";
$records = $conn->query($query)->fetchAll(PDO::FETCH_ASSOC);

$title = "Panel de Administración - Áreas";
include __DIR__ . '/../../views/templates/header.php';
?>

<!-- Sidebar -->
<?php include __DIR__ . '/../../views/templates/sidebar.php'; ?>

<div class="main-container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">
            <i class="bi bi-building"></i> Gestión de Áreas
        </h2>
        <a href="register_area.php" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Nueva Área
        </a>
    </div>

    <style>
      <?php include __DIR__ . '/../../public/css/admin/area.css'; ?>
    </style>

<div class="main-content transition">
    <div class="table-responsive">
        <table id="dataTable" class="table table-striped table-bordered table-hover compact-table">
            <thead class="table-light">
                <tr>
                    <th>Nombre del Área</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($records as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td>
                        <div class="d-flex gap-2">
                            <a href="edit_area.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning btn-sm-compact" title="Editar">
                                <i class="bi bi-pencil"></i> Editar
                            </a>
                            <a href="delete_area.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger btn-sm-compact" title="Eliminar">
                                <i class="bi bi-trash"></i> Eliminar
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    <?php include __DIR__ . '/../../public/js/admin/export.js'; ?>
</script>

<?php include __DIR__ . '/../../views/templates/footer.php'; ?>