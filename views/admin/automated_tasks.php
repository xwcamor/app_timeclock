<?php
session_start();
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header("Location: ../login_admin.php");
    exit();
}

require_once __DIR__ . '/../../config/Database.php';

$db = new Database();
$conn = $db->connect();

// Consulta con LEFT JOIN para obtener el nombre del usuario relacionado, permitiendo que 'user_id' sea NULL
$query = "SELECT at.id, at.name, at.date_start, at.date_end, at.is_active, at.created_at, at.updated_at,
                 u.name AS user_name
          FROM automated_tasks at
          LEFT JOIN users u ON at.user_id = u.id
          WHERE at.is_deleted = 0";  // Solo mostrar tareas no eliminadas
$records = $conn->query($query)->fetchAll(PDO::FETCH_ASSOC);

$title = "Panel de Administración - Tareas Automáticas";
include __DIR__ . '/../../views/templates/header.php';
?>

<!-- Sidebar -->
<?php include __DIR__ . '/../../views/templates/sidebar.php'; ?>

<div class="main-container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">
            <i class="bi bi-calendar-check"></i> Tareas Automáticas
        </h2>
        <a href="register_task.php" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Nueva Tarea
        </a>
    </div>

    <style>
        <?php include __DIR__ . '/../../public/css/admin/tasks.css'; ?>
    </style>

    <div class="main-content transition">
        <table id="dataTable" class="table table-striped table-bordered table-hover compact-table">
            <thead>
                <tr>
                    <th>Asunto</th>
                    <th>Usuario</th>
                    <th>Inicio</th>
                    <th>Final</th>
                    <th>Estado</th>
                    <th>Creado</th>
                    <th>Actualizado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($records as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['user_name'] ?? 'Todos los usuarios') ?></td>
                    <td><?= htmlspecialchars($row['date_start']) ?></td>
                    <td><?= htmlspecialchars($row['date_end']) ?></td>
                    <td>
                        <span class="estado-badge <?= $row['is_active'] == 0 ? 'estado-activado' : 'estado-desactivado' ?>">
                            <?= $row['is_active'] == 0 ? 'Activado' : 'Inactivo' ?>
                        </span>
                    </td>
                    <td><?= htmlspecialchars($row['created_at']) ?></td>
                    <td><?= htmlspecialchars($row['updated_at']) ?></td>
                    <td>
                        <div class="d-flex gap-2">
                            <a href="edit_task.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning btn-sm-compact" title="Editar">
                                <i class="bi bi-pencil"></i> Editar
                            </a>
                            <a href="delete_task.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger btn-sm-compact" title="Eliminar">
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
