<?php
session_start();
require_once __DIR__ . '/../app/models/assistance.php';
require_once __DIR__ . '/../config/Database.php';

date_default_timezone_set('America/Lima');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $dni = $_POST["dni"] ?? '';
    $password = $_POST["password"] ?? '';

    if (empty($dni) || empty($password)) {
        $_SESSION['alert'] = ["message" => "⚠️ DNI y contraseña son obligatorios", "type" => "error"];
    } else {
        $assistance = new Assistance();
        $user = $assistance->login($dni, $password);

        if (is_array($user) && isset($user['id'])) {
            if ($user['is_admin'] == 1) {
                $_SESSION['alert'] = ["message" => "🔒 Los administradores no pueden ingresar por esta página.", "type" => "error"];
            } else {
                $db = new Database();
                $conn = $db->connect();
                $stmt = $conn->prepare("SELECT * FROM workers WHERE user_id = ?");
                $stmt->execute([$user['id']]);
                $worker = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$worker) {
                    $_SESSION['alert'] = ["message" => "🔒 No tienes permisos para ingresar por esta página.", "type" => "error"];
                } else {
                    $id_times = $worker['id_times'];
                    $stmt = $conn->prepare("SELECT hour_time_ini, hour_time_end FROM work_times WHERE id = ?");
                    $stmt->execute([$id_times]);
                    $work_times = $stmt->fetch(PDO::FETCH_ASSOC);

                    if (!$work_times) {
                        $_SESSION['alert'] = ["message" => "🔒 No se encontraron horas de trabajo para este trabajador.", "type" => "error"];
                    } else {
                        $current_time = time();
                        $today = date('Y-m-d');

                        // Bloquear si ya tiene una asistencia registrada hoy sin ingreso (falta, permiso o justificación)
                        $stmt = $conn->prepare("
                            SELECT * FROM assistances 
                            WHERE user_id = ? 
                              AND DATE(created_at) = ? 
                              AND state_assistance IN (0, 2, 3) 
                              AND date_start IS NULL
                              AND is_deleted = 0
                        ");
                        $stmt->execute([$user['id'], $today]);
                        $registro_bloqueado = $stmt->fetch(PDO::FETCH_ASSOC);

                        if ($registro_bloqueado) {
                            $_SESSION['alert'] = ["message" => "❌ No puedes ingresar, ya tienes una asistencia registrada hoy (falta, permiso o justificación).", "type" => "error"];
                        } else {
                            $hour_time_ini = strtotime("$today " . $work_times['hour_time_ini']);
                            $hour_time_end = strtotime("$today " . $work_times['hour_time_end']);

                            // Verificar si ya tiene asistencia hoy
                            $today_assistance = $assistance->getAssistanceByUserAndDate($user['id'], $today);

                            if (!$today_assistance) {
                                $hora_entrada_permitida = $hour_time_ini - 60; // 1 minuto antes

                                if ($current_time < $hora_entrada_permitida) {
                                    $_SESSION['alert'] = ["message" => "⚠️ Aún no puedes registrar tu entrada.", "type" => "warning"];
                                } else {
                                    $_SESSION['user_id'] = $user['id'];
                                    $_SESSION['is_admin'] = false;
                                    $_SESSION['alert'] = ["message" => "¡Bienvenido! Registra tu ingreso.", "type" => "success"];
                                    $_SESSION['alert_redirect'] = "dashboard.php";
                                }
                            } else {
                                // Ya registró entrada
                                if ($today_assistance['date_end']) {
                                    $_SESSION['alert'] = ["message" => "🔒 Ya registraste tu salida hoy. Debes esperar hasta mañana.", "type" => "warning"];
                                } else {
                                    // Determinar si usa horario virtual o presencial
                                    $type_login = $today_assistance['type_login'] ?? 1;
                                    $id_time_salida = ($type_login == 1) ? $worker['id_times'] : $worker['id_time_presencial'];

                                    // Obtener hora de salida correcta
                                    $stmt = $conn->prepare("SELECT hour_time_end FROM work_times WHERE id = ?");
                                    $stmt->execute([$id_time_salida]);
                                    $hora_salida = $stmt->fetchColumn();

                                    if ($hora_salida) {
                                        $hour_time_end_salida = strtotime("$today $hora_salida");

                                        if ($current_time < $hour_time_end_salida) {
                                            $_SESSION['alert'] = ["message" => "⏳ Aún no es tu hora de salida.", "type" => "warning"];
                                        } else {
                                            $_SESSION['user_id'] = $user['id'];
                                            $_SESSION['is_admin'] = false;
                                            $_SESSION['alert'] = ["message" => "¡Bienvenido! Registra tu salida.", "type" => "success"];
                                            $_SESSION['alert_redirect'] = "dashboard.php";
                                        }
                                    } else {
                                        $_SESSION['alert'] = ["message" => "❌ No se pudo obtener tu hora de salida.", "type" => "error"];
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } else {
            $_SESSION['alert'] = ["message" => "🔒 Acceso denegado. Usuario o contraseña incorrectos o cuenta desactivada.", "type" => "error"];
        }
    }
}

$title = "Acceso Trabajadores";
include __DIR__ . '/templates/header.php';
?>

<div class="card card-custom">
    <div class="card-body">
        <h2 class="card-title text-center mb-4">
            <i class="bi bi-person-badge"></i> Acceso Trabajadores
        </h2>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">DNI</label>
                <input type="text" name="dni" class="form-control" placeholder="Ingrese su DNI" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Contraseña</label>
                <input type="password" name="password" class="form-control" placeholder="Ingrese su contraseña" required>
            </div>
            <button type="submit" class="btn btn-success w-100">
                <i class="bi bi-box-arrow-in-right"></i> Ingresar
            </button>
        </form>
    </div>
</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    <?php if (isset($_SESSION['alert'])): ?>
        Swal.fire({
            title: "<?= $_SESSION['alert']['type'] === 'success' ? '¡Éxito!' : ($_SESSION['alert']['type'] === 'question' ? 'Confirmación' : 'Atención') ?>",
            text: "<?= $_SESSION['alert']['message'] ?>",
            icon: "<?= $_SESSION['alert']['type'] ?>",
            confirmButtonText: "Entendido"
        }).then((result) => {
            <?php if (isset($_SESSION['alert_redirect'])): ?>
                window.location.href = "<?= $_SESSION['alert_redirect'] ?>";
                <?php unset($_SESSION['alert_redirect']); ?>
            <?php endif; ?>
        });
        <?php unset($_SESSION['alert']); ?>
    <?php endif; ?>
</script>
