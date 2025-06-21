<?php
session_start();
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header("Location: ../login_admin.php");
    exit();
}

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../app/models/User.php';

$db = new Database();
$conn = $db->connect();
$userModel = new User();

// Excluir administradores de la consulta
$query = "SELECT u.id, u.username, u.name, u.lastname, u.is_activated, u.password
          FROM users u
          INNER JOIN workers w ON u.id = w.user_id
          WHERE u.is_deleted = 0 AND u.is_admin = 0";  // Solo usuarios regulares
$records = $conn->query($query)->fetchAll(PDO::FETCH_ASSOC);

$title = "Panel de Administración - Usuarios";
include __DIR__ . '/../../views/templates/header.php';
?>

<!-- Sidebar -->
<?php include __DIR__ . '/../../views/templates/sidebar.php'; ?>

<div class="main-container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">
            <i class="bi bi-person-circle"></i> Gestión de Usuarios
        </h2>
        <a href="../register.php" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Registrar Usuario
        </a>
    </div>

    <style>
      <?php include __DIR__ . '/../../public/css/admin/user.css'; ?>
    </style>

    <div class="main-content transition">
        <table id="dataTable" class="table table-striped table-bordered table-hover compact-table">
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>Contraseña</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($records as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['username']) ?></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['lastname']) ?></td>
                    <td>••••</td>
                    <td>
                        <span class="estado-badge <?= $row['is_activated'] == 0 ? 'estado-activado' : 'estado-desactivado' ?>">
                            <?= $row['is_activated'] == 0 ? 'Activo' : 'Inactivo' ?>
                        </span>
                    </td>
                    <td>
                        <div class="d-flex gap-2">
                            <a href="edit_user.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning btn-sm-compact" title="Editar">
                                <i class="bi bi-pencil"></i> Editar
                            </a>
                            <a href="delete_user.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger btn-sm-compact" title="Eliminar">
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