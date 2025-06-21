<?php
require_once __DIR__ . '/../../config/Database.php';

$db = new Database();
$conn = $db->connect();

if (!isset($_GET['id'])) {
    echo "ID no proporcionado.";
    exit;
}

$id = $_GET['id'];

// Obtener los datos de asistencia y del usuario
$stmt = $conn->prepare("
    SELECT a.id AS asistencia_id, a.state_assistance, u.name AS nombre, u.lastname AS apellido, w.email AS correo
    FROM assistances a
    JOIN users u ON a.user_id = u.id
    JOIN workers w ON w.user_id = u.id
    WHERE a.id = ?
");
$stmt->execute([$id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) {
    echo "Asistencia no encontrada.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nuevo_estado = $_POST['state_assistance'];

    $update = $conn->prepare("UPDATE assistances SET state_assistance = ? WHERE id = ?");
    $update->execute([$nuevo_estado, $id]);

    header("Location: dashboard.php"); // Redirige sin mostrar la alerta
    exit;
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Estado de Asistencia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="container mt-5">


    <h2 class="mb-4">Editar Estado de Asistencia</h2>

    <form method="POST">
        <div class="mb-3">
            <label>Nombre:</label>
            <input type="text" class="form-control" value="<?= $data['nombre'] ?>" disabled>
        </div>
        <div class="mb-3">
            <label>Apellido:</label>
            <input type="text" class="form-control" value="<?= $data['apellido'] ?>" disabled>
        </div>
        <div class="mb-3">
            <label>Correo:</label>
            <input type="text" class="form-control" value="<?= $data['correo'] ?>" disabled>
        </div>
        <div class="mb-3">
            <label>Justificación:</label>
            <select name="state_assistance" class="form-select" required>
                <option value="0" <?= $data['state_assistance'] == 0 ? 'selected' : '' ?>>Falta</option>
                <option value="2" <?= $data['state_assistance'] == 2 ? 'selected' : '' ?>>Justificada</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Guardar cambios</button>
        <a href="dashboard.php" class="btn btn-secondary">Cancelar</a>
    </form>

</body>
</html>
