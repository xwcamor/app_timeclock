<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/models/Assistance.php';

header("Content-Type: application/json");
date_default_timezone_set('America/Lima');

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['photoBase64']) || !isset($data['userLocation']) || !isset($data['typeLogin'])) {
    echo json_encode(["success" => false, "message" => "❌ Datos incompletos recibidos."]);
    exit();
}

session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Usuario no autenticado."]);
    exit();
}

$userId = $_SESSION['user_id'];
$photoBase64 = preg_replace('/^data:image\/\w+;base64,/', '', $data['photoBase64']);
$userLocation = $data['userLocation'];
$typeLogin = $data['typeLogin'];
$dateStart = date('Y-m-d H:i:s');

// 📸 Guardar la imagen
$photoPath = "../public/img/user_{$userId}_" . time() . ".jpg";
$filePath = __DIR__ . '/' . $photoPath;
file_put_contents($filePath, base64_decode($photoBase64));

if (!file_exists($filePath)) {
    echo json_encode(["success" => false, "message" => "Error al guardar la imagen."]);
    exit();
}

$db = new Database();
$conn = $db->connect();
$assistanceModel = new Assistance($conn);

$success = $assistanceModel->create($userId, $photoPath, $userLocation, $dateStart, $typeLogin);

if ($success) {
    echo json_encode(["success" => true, "message" => "Asistencia registrada correctamente.", "hora_guardada" => $dateStart]);
} else {
    echo json_encode(["success" => false, "message" => "Error al registrar en la base de datos."]);
}
?>