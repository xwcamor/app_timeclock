<?php
session_start();
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header("Location: ../login_admin.php");
    exit();
}

require_once __DIR__ . '/../../config/Database.php';

$db = new Database();
$conn = $db->connect();

$query = "SELECT w.id, w.name, w.lastname, w.num_doc, w.email, w.user_id, w.id_times, w.id_time_presencial,
                 w.area_id, w.position_id,
                 u.name AS username, u.lastname AS user_lastname, u.is_deleted,
                 wt.description AS work_time_virtual,
                 wt.hour_time_ini AS virtual_ini,
                 wt.hour_time_end AS virtual_end,
                 wtp.description AS work_time_presencial,
                 wtp.hour_time_ini AS presencial_ini,
                 wtp.hour_time_end AS presencial_end,
                 a.name AS area_name,
                 p.name AS position_name
          FROM workers w
          LEFT JOIN users u ON w.user_id = u.id
          LEFT JOIN work_times wt ON w.id_times = wt.id
          LEFT JOIN work_times wtp ON w.id_time_presencial = wtp.id
          LEFT JOIN area a ON w.area_id = a.id
          LEFT JOIN position p ON w.position_id = p.id
          WHERE u.is_deleted = 0 AND u.is_admin = 0";


$records = $conn->query($query)->fetchAll(PDO::FETCH_ASSOC);

$title = "Panel de Administración - Trabajadores";
include __DIR__ . '/../../views/templates/header.php';
?>

<!-- Sidebar -->
<?php include __DIR__ . '/../../views/templates/sidebar.php'; ?>

<div class="main-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="bi bi-person-circle"></i> Gestión de Trabajadores
        </h2>
    </div>

    <style>
        <?php include __DIR__ . '/../../public/css/admin/worker.css'; ?>
    </style>

    <div class="table-responsive">
        <table id="dataTable" class="table table-striped table-bordered table-hover">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>DNI</th>
                    <th>Email</th>
                    <th>Horario Virtual</th>
                    <th>Horario Presencial</th>
                    <th>Área</th>
                    <th>Puesto</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($records as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['lastname']) ?></td>
                    <td><?= htmlspecialchars($row['num_doc']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td>
                        <?php if (!empty($row['virtual_ini']) && !empty($row['virtual_end'])): ?>
                            <?= date('h:i a', strtotime($row['virtual_ini'])) ?> - <?= date('h:i a', strtotime($row['virtual_end'])) ?>
                        <?php else: ?>
                            <span class="text-muted">Sin horario virtual</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (!empty($row['presencial_ini']) && !empty($row['presencial_end'])): ?>
                            <?= date('h:i a', strtotime($row['presencial_ini'])) ?> - <?= date('h:i a', strtotime($row['presencial_end'])) ?>
                        <?php else: ?>
                            <span class="text-muted">Sin horario presencial</span>
                        <?php endif; ?>
                    </td>
                    <td><?= $row['area_name'] ?? '<span class="text-muted">Sin área</span>' ?></td>
                    <td><?= $row['position_name'] ?? '<span class="text-muted">Sin puesto</span>' ?></td>
                    <td>
                        <div class="d-flex gap-2">
                            <a href="edit_worker.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning" title="Editar">
                                <i class="bi bi-pencil"></i> <span class="action-text">Editar</span>
                            </a>
                            <a href="delete_worker.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" title="Eliminar">
                                <i class="bi bi-trash"></i> <span class="action-text">Eliminar</span>
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
