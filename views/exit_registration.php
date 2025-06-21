<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/models/Assistance.php';
require_once __DIR__ . '/../app/models/Worker.php';

$db = new Database();
$conn = $db->connect();

$assistanceModel = new Assistance($conn);
$userId = $_SESSION['user_id'];
$assistance = $assistanceModel->getLatestAssistance($userId);

$workerModel = new Worker($conn);
$workerData = $workerModel->getWorkerDataByUserId($userId);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Salida</title>
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <style>
        <?php include __DIR__ . '/../public/css/user/exit_reg.css'; ?>
        #camara {
            width: 100%;
            position: relative;
            border-radius: 10px;
            overflow: hidden;
        }
        canvas {
            position: absolute;
            top: 0;
            left: 0;
            pointer-events: none;
        }
    </style>
</head>
<body>

<div class="content">
    <h1>Registrar Salida</h1>

    <div class="user-info">
        <p><strong>DNI:</strong> <?php echo htmlspecialchars($workerData['num_doc']); ?></p>
        <p><strong>Nombre:</strong> <?php echo htmlspecialchars($workerData['name']); ?></p>
        <p><strong>Apellido:</strong> <?php echo htmlspecialchars($workerData['lastname']); ?></p>
        <p><strong>Correo:</strong> <?php echo htmlspecialchars($workerData['email']); ?></p>
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

    <button id="registerExitBtn">Registrar Salida</button>
    <button id="cancelBtn" onclick="cancel()">Cancelar</button>
</div>

<!-- Scripts de JS -->
<script>
    <?php include __DIR__ . '/../public/js/user/exit_reg.js'; ?>
</script>

<!-- TensorFlow y BlazeFace para detección facial -->
<script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs"></script>
<script src="https://cdn.jsdelivr.net/npm/@tensorflow-models/blazeface"></script>

<script>
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const ctx = canvas.getContext('2d');
    const registerExitBtn = document.getElementById('registerExitBtn');

    // Desactivar botón al iniciar
    registerExitBtn.disabled = true;
    registerExitBtn.style.opacity = "0.6";
    registerExitBtn.style.cursor = "not-allowed";

    async function setupCamera() {
        const stream = await navigator.mediaDevices.getUserMedia({ video: true });
        video.srcObject = stream;
        video.onloadedmetadata = () => {
            video.play();
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
        };
    }

    async function startFaceDetection() {
        await setupCamera();
        const model = await blazeface.load();

        async function detect() {
            const predictions = await model.estimateFaces(video, false);
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            if (predictions.length > 0) {
                registerExitBtn.disabled = false;
                registerExitBtn.style.opacity = "1";
                registerExitBtn.style.cursor = "pointer";

                predictions.forEach(pred => {
                    const [x, y] = pred.topLeft;
                    const [x2, y2] = pred.bottomRight;
                    const width = x2 - x;
                    const height = y2 - y;

                    ctx.beginPath();
                    ctx.lineWidth = 3;
                    ctx.strokeStyle = "green";
                    ctx.rect(x, y, width, height);
                    ctx.stroke();
                });
            } else {
                registerExitBtn.disabled = true;
                registerExitBtn.style.opacity = "0.6";
                registerExitBtn.style.cursor = "not-allowed";
            }

            requestAnimationFrame(detect);
        }

        detect();
    }

    window.addEventListener("load", startFaceDetection);

    function cancel() {
        window.location.href = "login_user.php";
    }
</script>

</body>
</html>
