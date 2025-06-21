<?php
session_start();
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header("Location: ../login_admin.php");
    exit();
}

require_once __DIR__ . '/../../config/database.php';

$db = new Database();
$conn = $db->connect();

// Obtener lista de horarios Remoto (type_login = 1)
$timeQuery = "SELECT id, description, hour_time_ini, hour_time_end FROM work_times WHERE type_login = 1 AND is_deleted = 0";
$timeStmt = $conn->prepare($timeQuery);
$timeStmt->execute();
$timeOptions = $timeStmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener lista de horarios Presencial (type_login = 2)
$timePresencialQuery = "SELECT id, description, hour_time_ini, hour_time_end FROM work_times WHERE type_login = 2 AND is_deleted = 0";
$timePresencialStmt = $conn->prepare($timePresencialQuery);
$timePresencialStmt->execute();
$timePresencialOptions = $timePresencialStmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener áreas
$areaQuery = "SELECT id, name FROM area WHERE is_deleted = 0";
$areaStmt = $conn->prepare($areaQuery);
$areaStmt->execute();
$areaOptions = $areaStmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener posiciones
$positionQuery = "SELECT id, name FROM position WHERE is_deleted = 0";
$positionStmt = $conn->prepare($positionQuery);
$positionStmt->execute();
$positionOptions = $positionStmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener datos del trabajador
if (isset($_GET['id'])) {
    $workerId = $_GET['id'];

    $query = "SELECT w.id, w.name, w.lastname, w.num_doc, w.email, w.user_id, 
                     w.id_times, w.id_time_presencial, w.area_id, w.position_id,
                     u.name AS username, u.lastname AS user_lastname
              FROM workers w
              LEFT JOIN users u ON w.user_id = u.id
              WHERE w.id = :id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $workerId);
    $stmt->execute();
    $worker = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$worker) {
        header("Location: workers.php");
        exit();
    }
} else {
    header("Location: workers.php");
    exit();
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $num_doc = $_POST['num_doc'];
    $id_times = $_POST['id_times'];
    $id_time_presencial = $_POST['id_time_presencial'];
    $area_id = $_POST['area_id'];
    $position_id = $_POST['position_id'];

    $updateWorkerQuery = "UPDATE workers 
                          SET name = :name, lastname = :lastname, email = :email, 
                              num_doc = :num_doc, id_times = :id_times, id_time_presencial = :id_time_presencial,
                              area_id = :area_id, position_id = :position_id
                          WHERE id = :id";
    $updateWorkerStmt = $conn->prepare($updateWorkerQuery);
    $updateWorkerStmt->bindParam(':name', $name);
    $updateWorkerStmt->bindParam(':lastname', $lastname);
    $updateWorkerStmt->bindParam(':email', $email);
    $updateWorkerStmt->bindParam(':num_doc', $num_doc);
    $updateWorkerStmt->bindParam(':id_times', $id_times);
    $updateWorkerStmt->bindParam(':id_time_presencial', $id_time_presencial);
    $updateWorkerStmt->bindParam(':area_id', $area_id);
    $updateWorkerStmt->bindParam(':position_id', $position_id);
    $updateWorkerStmt->bindParam(':id', $workerId);

    if ($updateWorkerStmt->execute()) {
        header("Location: workers.php");
        exit();
    } else {
        $error = "Ocurrió un error al actualizar los datos del trabajador.";
    }
}

$title = "Editar Trabajador";
include __DIR__ . '/../../views/templates/header.php';
include __DIR__ . '/../../views/templates/sidebar.php';
?>

<div class="edit-user-container">
    <h2 class="mb-4">Editar Trabajador</h2>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <style>
        <?php include __DIR__ . '/../../public/css/admin/edit.css'; ?>
    </style>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Nombre</label>
            <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($worker['name']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Apellido</label>
            <input type="text" class="form-control" name="lastname" value="<?= htmlspecialchars($worker['lastname']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Correo</label>
            <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($worker['email']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">DNI</label>
            <input type="text" class="form-control" name="num_doc" value="<?= htmlspecialchars($worker['num_doc']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Área</label>
            <select class="form-control" name="area_id" required>
                <option value="">Seleccione un área</option>
                <?php foreach ($areaOptions as $option): ?>
                    <option value="<?= $option['id'] ?>" <?= ($option['id'] == $worker['area_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($option['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Cargo</label>
            <select class="form-control" name="position_id" required>
                <option value="">Seleccione un puesto</option>
                <?php foreach ($positionOptions as $option): ?>
                    <option value="<?= $option['id'] ?>" <?= ($option['id'] == $worker['position_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($option['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
    <label class="form-label">Horario Virtual</label>
    <select class="form-control" name="id_times" required>
        <option value="">Seleccione horario virtual</option>
        <?php foreach ($timeOptions as $option): ?>
            <option value="<?= $option['id'] ?>" <?= ($option['id'] == $worker['id_times']) ? 'selected' : '' ?>>
                <?= date('h:i a', strtotime($option['hour_time_ini'])) ?> - <?= date('h:i a', strtotime($option['hour_time_end'])) ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>
<div class="mb-3">
    <label class="form-label">Horario Presencial</label>
    <select class="form-control" name="id_time_presencial">
        <option value="">Seleccione horario presencial</option>
        <?php foreach ($timePresencialOptions as $option): ?>
            <option value="<?= $option['id'] ?>" <?= ($option['id'] == $worker['id_time_presencial']) ? 'selected' : '' ?>>
                <?= date('h:i a', strtotime($option['hour_time_ini'])) ?> - <?= date('h:i a', strtotime($option['hour_time_end'])) ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>
        <button type="submit" class="btn btn-primary">Guardar</button>
        <a href="workers.php" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

<?php include __DIR__ . '/../../views/templates/footer.php'; ?>
