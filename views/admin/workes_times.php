<?php
session_start();
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header("Location: ../login_admin.php");
    exit();
}

require_once __DIR__ . '/../../config/Database.php';

$db = new Database();
$conn = $db->connect();

// Obtener los registros que no están eliminados
$query = "SELECT * FROM work_times WHERE is_deleted = 0";
$records = $conn->query($query)->fetchAll(PDO::FETCH_ASSOC);

$title = "Panel de Administración - Horarios de Trabajo";
include __DIR__ . '/../../views/templates/header.php';
?>

<!-- Sidebar -->
<?php include __DIR__ . '/../../views/templates/sidebar.php'; ?>

<div class="main-container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">
            <i class="bi bi-clock-history"></i> Gestión de Horarios de Trabajo
        </h2>
        <a href="register_workes_times.php" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Registrar Horario
        </a>
    </div>

    <style>
      <?php include __DIR__ . '/../../public/css/admin/workes_times.css'; ?>
    </style>

    <div class="main-content transition">
        <table id="dataTable" class="table table-striped table-bordered table-hover compact-table">
            <thead>
                <tr>
                    <th>Descripción</th>
                    <th>Hora de Inicio</th>
                    <th>Hora de Fin</th>
                    <th>Tipo de Ingreso</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($records as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['description']) ?></td>
                    <td><?= htmlspecialchars($row['hour_time_ini']) ?></td>
                    <td><?= htmlspecialchars($row['hour_time_end']) ?></td>
                    <td>
                        <?= $row['type_login'] == 1 ? 'Remoto' : ($row['type_login'] == 2 ? 'Presencial' : 'No especificado') ?>
                    </td>
                    <td>
                        <span class="estado-badge <?= $row['is_activated'] == 1 ? 'estado-activado' : 'estado-desactivado' ?>">
                            <?= $row['is_activated'] == 1 ? 'Activo' : 'Inactivo' ?>
                        </span>
                    </td>
                    <td>
                        <div class="d-flex gap-2">
                            <a href="edit_workes_times.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning btn-sm-compact" title="Editar">
                                <i class="bi bi-pencil"></i> Editar
                            </a>
                            <a href="delete_workes_times.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger btn-sm-compact" title="Eliminar">
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
