<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/models/Assistance.php';

header("Content-Type: application/json");
date_default_timezone_set('America/Lima');

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['photoBase64']) || !isset($data['userLocation'])) {
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
$dateEnd = date("Y-m-d H:i:s");

// 📸 Guardar la imagen en la carpeta "fotos/"
$photoPath = "../public/img/user_{$userId}_" . time() . "_exit.jpg";
$filePath = __DIR__ . '/' . $photoPath;
file_put_contents($filePath, base64_decode($photoBase64));

if (!file_exists($filePath)) {
    echo json_encode(["success" => false, "message" => "Error al guardar la imagen de salida."]);
    exit();
}

$db = new Database();
$conn = $db->connect();
$assistanceModel = new Assistance($conn);

$success = $assistanceModel->registerExit($userId, $photoPath, $userLocation, $dateEnd);

if ($success) {
    session_destroy(); // Cerrar sesión después de registrar salida
    echo json_encode(["success" => true, "message" => "Salida registrada correctamente.", "redirect" => "login_user.php"]);
} else {
    echo json_encode(["success" => false, "message" => "Error al registrar salida en la base de datos."]);
}
?>