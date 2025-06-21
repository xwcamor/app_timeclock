<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/models/Assistance.php';
require_once __DIR__ . '/../app/models/Worker.php';

// Establecer la zona horaria de Perú
date_default_timezone_set('America/Lima');

$db = new Database();
$conn = $db->connect();

$assistanceModel = new Assistance($conn);
$userId = $_SESSION['user_id'];

$assistance = $assistanceModel->getLatestAssistance($userId);
$isExitPending = $assistance && $assistance['date_start'] && !$assistance['date_end'] && $assistance['is_deleted'] == 0;

$workerModel = new Worker($conn);
$workerData = $workerModel->getWorkerDataByUserId($userId);

// Obtener la hora actual del servidor en la zona horaria correcta (Perú)
$currentHour = date("H:i");

// Obtener hora de ingreso presencial
$presencialHour = null;
if (!empty($workerData['id_time_presencial'])) {
    $stmt = $conn->prepare("SELECT hour_time_ini FROM work_times WHERE id = :id");
    $stmt->execute(['id' => $workerData['id_time_presencial']]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result) {
        $presencialHour = $result['hour_time_ini'];
    }
}

// Determinar si habilitar el botón presencial
$enablePresencial = $presencialHour && $currentHour >= $presencialHour;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <style>
        <?php include __DIR__ . '/../public/css/user/dashboard.css'; ?>
    </style>
</head>
<body>

<?php if ($isExitPending): ?>
    <div class="alert">
        <h2>¿Deseas registrar tu hora de salida?</h2>
        <div class="action-buttons">
            <button onclick="registerExit()">Sí</button>
            <button onclick="logout()">No</button>
        </div>
    </div>

    <script>
        function registerExit() {
            window.location.href = "exit_registration.php";
        }

        function logout() {
            window.location.href = "login_user.php";
        }
    </script>

<?php else: ?>

<div class="content">
    <h1>Registro Ingreso</h1>

    <div class="user-info">
        <p><strong>DNI:</strong> <?= htmlspecialchars($workerData['num_doc']) ?></p>
        <p><strong>Nombre:</strong> <?= htmlspecialchars($workerData['name']) ?></p>
        <p><strong>Apellido:</strong> <?= htmlspecialchars($workerData['lastname']) ?></p>
        <p><strong>Correo:</strong> <?= htmlspecialchars($workerData['email']) ?></p>

        <p><strong>Tipo de Registro:</strong></p>
        <div class="radio-group">
            <label class="radio-circle">
                <input type="radio" name="type_login" value="1" checked>
                <span class="circle"></span> Virtual
            </label>
            <label class="radio-circle">
                <input type="radio" name="type_login" value="2" <?= $enablePresencial ? '' : 'disabled' ?>>
                <span class="circle"></span> Presencial
            </label>
        </div>

        <div class="container">
            <div class="section-container">
                <h3><strong>Cámara</strong></h3>
                <div class="video-container" id="camara">
                    <video id="video" autoplay muted></video>
                    <canvas id="canvas"></canvas>
                </div>
            </div>

            <div class="section-container">
                <h3><strong>Reloj</strong></h3>
                <div class="clock-container">
                    <span id="clock"></span>
                </div>
            </div>

            <div class="section-container">
                <h3><strong>Ubicación</strong></h3>
                <div id="map-container">
                    <div id="map"></div>
                </div>
            </div>
        </div>

        <button id="registerBtn">Registrar Ingreso</button>
        <button id="cancelBtn" onclick="cancel()">Cancelar</button>
    </div>
</div>

<script>
    <?php include __DIR__ . '/../public/js/user/dashboard.js'; ?>
</script>
<script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs"></script>
<script src="https://cdn.jsdelivr.net/npm/@tensorflow-models/blazeface"></script>

<?php endif; ?>

</body>
</html>
