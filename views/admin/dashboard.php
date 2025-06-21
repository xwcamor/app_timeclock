<?php
session_start();
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header("Location: ../login_admin.php");
    exit();
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/models/Assistance.php';
require_once __DIR__ . '/../../lib/vendor/autoload.php';
require_once __DIR__ . '/../../app/helpers/mail_notifier.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$db = new Database();
$conn = $db->connect();
$assistanceModel = new Assistance($conn);

// Notificación de trabajadores sin salida
$sinSalida = $assistanceModel->getWorkersWithNoExit(15);
if (!empty($sinSalida)) {
    enviarNotificacionAlAdmin($sinSalida);
}

// Configuración de paginación
$perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $perPage;

// Parámetros de filtrado
$filterParams = [
    'start_date' => $_GET['start_date'] ?? null,
    'end_date' => $_GET['end_date'] ?? null,
    'name' => $_GET['name'] ?? null,
    'dni' => $_GET['dni'] ?? null,
    'email' => $_GET['email'] ?? null,
    'area' => $_GET['area'] ?? null,
    'position' => $_GET['position'] ?? null,
    'work_schedule' => $_GET['work_schedule'] ?? null,
    'work_end_schedule' => $_GET['work_end_schedule'] ?? null,
    'late_minutes' => $_GET['late_minutes'] ?? null,
    'overtime' => $_GET['overtime'] ?? null,
    'worked_hours' => $_GET['worked_hours'] ?? null,
    'worked_minutes' => $_GET['worked_minutes'] ?? null,
    'login_type' => $_GET['login_type'] ?? null,
    'assistance_status' => $_GET['assistance_status'] ?? null,
    'entry_status' => $_GET['entry_status'] ?? null,
    'exit_status' => $_GET['exit_status'] ?? null
];

// Construcción de condiciones WHERE
$where = ["a1.is_deleted = 0"];
$params = [];

// Filtros por fecha
// Filtros por fecha - Versión mejorada
if (!empty($filterParams['start_date']) && !empty($filterParams['end_date'])) {
    $where[] = "(
        (DATE(a1.date_start) BETWEEN :start_date AND :end_date) 
        OR 
        (a1.date_start IS NULL AND DATE(a1.created_at) BETWEEN :start_date AND :end_date)
    )";
    $params[':start_date'] = $filterParams['start_date'];
    $params[':end_date'] = $filterParams['end_date'];
} elseif (!empty($filterParams['start_date'])) {
    $where[] = "(
        (DATE(a1.date_start) >= :start_date) 
        OR 
        (a1.date_start IS NULL AND DATE(a1.created_at) >= :start_date)
    )";
    $params[':start_date'] = $filterParams['start_date'];
} elseif (!empty($filterParams['end_date'])) {
    $where[] = "(
        (DATE(a1.date_start) <= :end_date) 
        OR 
        (a1.date_start IS NULL AND DATE(a1.created_at) <= :end_date)
    )";
    $params[':end_date'] = $filterParams['end_date'];
}

// Filtros por datos personales
if (!empty($filterParams['name']) && $filterParams['name'] != 'all') {
    $where[] = "CONCAT(w.name, ' ', w.lastname) = :name";
    $params[':name'] = $filterParams['name'];
}

if (!empty($filterParams['dni']) && $filterParams['dni'] != 'all') {
    $where[] = "w.num_doc = :dni";
    $params[':dni'] = $filterParams['dni'];
}

if (!empty($filterParams['email']) && $filterParams['email'] != 'all') {
    $where[] = "w.email = :email";
    $params[':email'] = $filterParams['email'];
}

// Filtros por área y puesto
if (!empty($filterParams['area']) && $filterParams['area'] != 'all') {
    $where[] = "a.name = :area";
    $params[':area'] = $filterParams['area'];
}

if (!empty($filterParams['position']) && $filterParams['position'] != 'all') {
    $where[] = "p.name = :position";
    $params[':position'] = $filterParams['position'];
}

// Filtros por horarios
if (!empty($filterParams['work_schedule']) && $filterParams['work_schedule'] != 'all') {
    $where[] = "CASE 
                   WHEN a1.type_login = 2 AND w.id_time_presencial IS NOT NULL 
                   THEN wtp.hour_time_ini 
                   ELSE wt.hour_time_ini 
                END = :work_schedule";
    $params[':work_schedule'] = $filterParams['work_schedule'];
}

if (!empty($filterParams['work_end_schedule']) && $filterParams['work_end_schedule'] != 'all') {
    $where[] = "CASE 
                   WHEN a1.type_login = 2 AND w.id_time_presencial IS NOT NULL 
                   THEN wtp.hour_time_end 
                   ELSE wt.hour_time_end 
                END = :work_end_schedule";
    $params[':work_end_schedule'] = $filterParams['work_end_schedule'];
}

// Filtro por minutos retrasados
if (!empty($filterParams['late_minutes']) && $filterParams['late_minutes'] != 'all') {
    if ($filterParams['late_minutes'] == 'yes') {
        $where[] = "TIMESTAMPDIFF(MINUTE, 
                    CONCAT(DATE(a1.date_start), ' ', 
                        CASE 
                            WHEN a1.type_login = 2 AND w.id_time_presencial IS NOT NULL 
                            THEN wtp.hour_time_ini 
                            ELSE wt.hour_time_ini 
                        END), 
                    a1.date_start) > 
                    CASE WHEN a1.type_login = 1 THEN 5 ELSE 15 END";
    } elseif ($filterParams['late_minutes'] == 'no') {
        $where[] = "TIMESTAMPDIFF(MINUTE, 
                    CONCAT(DATE(a1.date_start), ' ', 
                        CASE 
                            WHEN a1.type_login = 2 AND w.id_time_presencial IS NOT NULL 
                            THEN wtp.hour_time_ini 
                            ELSE wt.hour_time_ini 
                        END), 
                    a1.date_start) <= 
                    CASE WHEN a1.type_login = 1 THEN 5 ELSE 15 END";
    } elseif ($filterParams['late_minutes'] == 'Vacio') {
        $where[] = "a1.date_start IS NULL";
    }
}

// Filtro por horas extras
if (!empty($filterParams['overtime']) && $filterParams['overtime'] != 'all') {
    if ($filterParams['overtime'] == 'yes') {
        $where[] = "TIMESTAMPDIFF(MINUTE, 
                    CONCAT(DATE(a1.date_end), ' ', 
                        CASE 
                            WHEN a1.type_login = 2 AND w.id_time_presencial IS NOT NULL 
                            THEN wtp.hour_time_end 
                            ELSE wt.hour_time_end 
                        END), 
                    a1.date_end) > 0";
    } elseif ($filterParams['overtime'] == 'no') {
        $where[] = "TIMESTAMPDIFF(MINUTE, 
                    CONCAT(DATE(a1.date_end), ' ', 
                        CASE 
                            WHEN a1.type_login = 2 AND w.id_time_presencial IS NOT NULL 
                            THEN wtp.hour_time_end 
                            ELSE wt.hour_time_end 
                        END), 
                    a1.date_end) <= 0";
    } elseif ($filterParams['overtime'] == 'none') {
        $where[] = "a1.date_end IS NULL";
    }
}

// Filtro por horas trabajadas
if (!empty($filterParams['worked_hours']) || !empty($filterParams['worked_minutes'])) {
    $totalMinutes = ((int)($filterParams['worked_hours'] ?? 0) * 60) + ((int)($filterParams['worked_minutes'] ?? 0));
    $where[] = "TIMESTAMPDIFF(MINUTE, a1.date_start, a1.date_end) >= :worked_minutes";
    $params[':worked_minutes'] = $totalMinutes;
}

// Filtros por estados
if (!empty($filterParams['assistance_status']) && $filterParams['assistance_status'] != 'all') {
    $where[] = "a1.state_assistance = :assistance_status";
    $params[':assistance_status'] = $filterParams['assistance_status'];
}

if (!empty($filterParams['login_type']) && $filterParams['login_type'] != 'all') {
    $where[] = "a1.type_login = :login_type";
    $params[':login_type'] = $filterParams['login_type'];
}

// Filtro por estado de ingreso
if (!empty($filterParams['entry_status']) && $filterParams['entry_status'] != 'all') {
    if ($filterParams['entry_status'] == 'Puntual') {
        $where[] = "TIMESTAMPDIFF(MINUTE, 
                    CONCAT(DATE(a1.date_start), ' ', 
                        CASE 
                            WHEN a1.type_login = 2 AND w.id_time_presencial IS NOT NULL 
                            THEN wtp.hour_time_ini 
                            ELSE wt.hour_time_ini 
                        END), 
                    a1.date_start) <= 
                    CASE WHEN a1.type_login = 1 THEN 5 ELSE 15 END";
    } elseif ($filterParams['entry_status'] == 'Tardanza') {
        $where[] = "TIMESTAMPDIFF(MINUTE, 
                    CONCAT(DATE(a1.date_start), ' ', 
                        CASE 
                            WHEN a1.type_login = 2 AND w.id_time_presencial IS NOT NULL 
                            THEN wtp.hour_time_ini 
                            ELSE wt.hour_time_ini 
                        END), 
                    a1.date_start) > 
                    CASE WHEN a1.type_login = 1 THEN 5 ELSE 15 END";
    } elseif ($filterParams['entry_status'] == 'Sin salida') {
        $where[] = "a1.date_start IS NULL";
    }
}

// Filtro por estado de salida
if (!empty($filterParams['exit_status']) && $filterParams['exit_status'] != 'all') {
    if ($filterParams['exit_status'] == 'Puntual') {
        $where[] = "TIMESTAMPDIFF(MINUTE, 
                    CONCAT(DATE(a1.date_end), ' ', 
                        CASE 
                            WHEN a1.type_login = 2 AND w.id_time_presencial IS NOT NULL 
                            THEN wtp.hour_time_end 
                            ELSE wt.hour_time_end 
                        END), 
                    a1.date_end) <= 0";
    } elseif ($filterParams['exit_status'] == 'Tardanza') {
        $where[] = "TIMESTAMPDIFF(MINUTE, 
                    CONCAT(DATE(a1.date_end), ' ', 
                        CASE 
                            WHEN a1.type_login = 2 AND w.id_time_presencial IS NOT NULL 
                            THEN wtp.hour_time_end 
                            ELSE wt.hour_time_end 
                        END), 
                    a1.date_end) > 0";
    } elseif ($filterParams['exit_status'] == 'Sin salida') {
        $where[] = "a1.date_end IS NULL";
    }
}

// Consulta principal
$query = "SELECT 
    a1.id AS assistance_id,
    a1.date_start, 
    a1.photo_start, 
    a1.location_start, 
    a1.date_end, 
    a1.photo_end, 
    a1.location_end,
    a1.state_assistance,
    a1.type_login,
    a1.created_at, 
    a1.updated_at,
    w.name, 
    w.lastname, 
    w.num_doc, 
    w.email,
    a.name AS area_name,
    p.name AS position_name,
    CASE 
        WHEN a1.type_login = 2 AND w.id_time_presencial IS NOT NULL THEN wtp.hour_time_ini 
        ELSE wt.hour_time_ini 
    END AS hour_time_ini,
    CASE 
        WHEN a1.type_login = 2 AND w.id_time_presencial IS NOT NULL THEN wtp.hour_time_end 
        ELSE wt.hour_time_end 
    END AS hour_time_end
FROM workers w
JOIN assistances a1 ON w.user_id = a1.user_id
LEFT JOIN work_times wt ON w.id_times = wt.id
LEFT JOIN work_times wtp ON w.id_time_presencial = wtp.id
LEFT JOIN area a ON w.area_id = a.id
LEFT JOIN position p ON w.position_id = p.id
WHERE " . implode(' AND ', $where) . " ORDER BY a1.created_at DESC LIMIT :limit OFFSET :offset";

// Consulta para total de registros
$countQuery = "SELECT COUNT(*) as total 
               FROM assistances a1 
               JOIN workers w ON w.user_id = a1.user_id
               LEFT JOIN work_times wt ON w.id_times = wt.id
               LEFT JOIN work_times wtp ON w.id_time_presencial = wtp.id
               LEFT JOIN area a ON w.area_id = a.id
               LEFT JOIN position p ON w.position_id = p.id
               WHERE " . implode(' AND ', $where);

// Ejecución de consultas
try {
    $stmt = $conn->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $countStmt = $conn->prepare($countQuery);
    foreach ($params as $key => $value) {
        $countStmt->bindValue($key, $value);
    }
    $countStmt->execute();
    $totalRecords = $countStmt->fetchColumn();
    $totalPages = ceil($totalRecords / $perPage);
} catch (PDOException $e) {
    die("Error al ejecutar la consulta: " . $e->getMessage());
}

// Configuración de la vista
$title = "Panel de Administración";
include __DIR__ . '/../../views/templates/header.php';
include __DIR__ . '/../../views/templates/sidebar.php';
?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">


    <style>
      <?php include __DIR__ . '/../../public/css/admin/dashboard.css'; ?>
    </style>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="bi bi-clipboard-data"></i> Control de Asistencia
        </h2>
    </div>



<div class="card mb-4" id="advancedFilterCard">
    <div class="card-header bg-primary text-white" style="cursor: pointer;">
        <h5 class="mb-0 d-flex justify-content-between align-items-center">
            <span><i class="bi bi-funnel-fill"></i> Filtros Avanzados</span>
            <i class="bi bi-chevron-up collapse-icon"></i>
        </h5>
    </div>

        <div class="card-body collapse show" id="filterBody">
            <form id="advancedFilterForm" onsubmit="return false;">
                <div class="row g-3">
                    <!-- Fila 1: Fechas y Horarios -->
                    <div class="col-md-3">
                        <label for="filterStartDate" class="form-label">Fecha de Inicio</label>
                        <input type="date" class="form-control" id="filterStartDate" value="<?= htmlspecialchars($filterParams['start_date'] ?? '') ?>">
                    </div>
                    
                    <div class="col-md-3">
                        <label for="filterEndDate" class="form-label">Fecha de Fin</label>
                        <input type="date" class="form-control" id="filterEndDate" value="<?= htmlspecialchars($filterParams['end_date'] ?? '') ?>">
                    </div>
                    
                    <div class="col-md-3">
                        <label for="filterWorkSchedule" class="form-label">Ingreso Laboral</label>
                        <select class="form-select select2-search" id="filterWorkSchedule" data-placeholder="Seleccione horario">
                            <option value="all">Todos</option>
                            <?php
                            $schedules = $conn->query("SELECT DISTINCT hour_time_ini FROM work_times ORDER BY hour_time_ini")->fetchAll(PDO::FETCH_COLUMN);
                            foreach ($schedules as $schedule) {
                                $selected = ($filterParams['work_schedule'] ?? '') == $schedule ? 'selected' : '';
                                echo "<option value='{$schedule}' $selected>" . date('h:i A', strtotime($schedule)) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="filterWorkEndSchedule" class="form-label">Salida Laboral</label>
                        <select class="form-select select2-search" id="filterWorkEndSchedule" data-placeholder="Seleccione horario">
                            <option value="all">Todos</option>
                            <?php
                            $schedules = $conn->query("SELECT DISTINCT hour_time_end FROM work_times ORDER BY hour_time_end")->fetchAll(PDO::FETCH_COLUMN);
                            foreach ($schedules as $schedule) {
                                $selected = ($filterParams['work_end_schedule'] ?? '') == $schedule ? 'selected' : '';
                                echo "<option value='{$schedule}' $selected>" . date('h:i A', strtotime($schedule)) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    
                    <!-- Fila 2: Estados y Minutos -->
                    <div class="col-md-3">
                        <label class="form-label">Minutos Retrasados</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="filterLateMinutes" id="filterLateYes" value="yes" <?= ($filterParams['late_minutes'] ?? '') == 'yes' ? 'checked' : '' ?> autocomplete="off">
                            <label class="btn btn-outline-primary" for="filterLateYes">Sí</label>
                            
                            <input type="radio" class="btn-check" name="filterLateMinutes" id="filterLateNo" value="no" <?= ($filterParams['late_minutes'] ?? '') == 'no' ? 'checked' : '' ?> autocomplete="off">
                            <label class="btn btn-outline-primary" for="filterLateNo">No</label>

                            <input type="radio" class="btn-check" name="filterLateMinutes" id="filterLateNone" value="Vacio" <?= ($filterParams['late_minutes'] ?? '') == 'Vacio' ? 'checked' : '' ?> autocomplete="off">
                            <label class="btn btn-outline-primary" for="filterLateNone">Vacío</label>
                            
                            <input type="radio" class="btn-check" name="filterLateMinutes" id="filterLateAll" value="all" <?= empty($filterParams['late_minutes']) || ($filterParams['late_minutes'] ?? '') == 'all' ? 'checked' : '' ?> autocomplete="off">
                            <label class="btn btn-outline-primary" for="filterLateAll">Todos</label>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">Horas Extras</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="filterOvertime" id="filterOvertimeYes" value="yes" <?= ($filterParams['overtime'] ?? '') == 'yes' ? 'checked' : '' ?> autocomplete="off">
                            <label class="btn btn-outline-primary" for="filterOvertimeYes">Sí</label>
                            
                            <input type="radio" class="btn-check" name="filterOvertime" id="filterOvertimeNo" value="no" <?= ($filterParams['overtime'] ?? '') == 'no' ? 'checked' : '' ?> autocomplete="off">
                            <label class="btn btn-outline-primary" for="filterOvertimeNo">No</label>

                            <input type="radio" class="btn-check" name="filterOvertime" id="filterOvertimeNone" value="none" <?= ($filterParams['overtime'] ?? '') == 'none' ? 'checked' : '' ?> autocomplete="off">
                            <label class="btn btn-outline-primary" for="filterOvertimeNone">Vacío</label>
                            
                            <input type="radio" class="btn-check" name="filterOvertime" id="filterOvertimeAll" value="all" <?= empty($filterParams['overtime']) || ($filterParams['overtime'] ?? '') == 'all' ? 'checked' : '' ?> autocomplete="off">
                            <label class="btn btn-outline-primary" for="filterOvertimeAll">Todos</label>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="filterWorkedHours" class="form-label">Horas Trabajadas (≥)</label>
                        <div class="input-group">
                            <input type="number" min="0" max="23" class="form-control" id="filterWorkedHours" placeholder="Horas" value="<?= htmlspecialchars($filterParams['worked_hours'] ?? '') ?>">
                            <span class="input-group-text">:</span>
                            <input type="number" min="0" max="59" class="form-control" id="filterWorkedMinutes" placeholder="Min" value="<?= htmlspecialchars($filterParams['worked_minutes'] ?? '') ?>">
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="filterLoginType" class="form-label">Tipo de Logeo</label>
                        <select class="form-select" id="filterLoginType">
                            <option value="all" <?= empty($filterParams['login_type']) ? 'selected' : '' ?>>Todos</option>
                            <option value="1" <?= ($filterParams['login_type'] ?? '') == '1' ? 'selected' : '' ?>>Virtual</option>
                            <option value="2" <?= ($filterParams['login_type'] ?? '') == '2' ? 'selected' : '' ?>>Presencial</option>
                            <option value="0" <?= ($filterParams['login_type'] ?? '') == '0' ? 'selected' : '' ?>>Vacío</option>
                        </select>
                    </div>
                    
                    <!-- Fila 3: Datos Personales -->
                    <div class="col-md-3">
                        <label for="filterName" class="form-label">Nombre</label>
                        <select class="form-select select2-search" id="filterName" data-placeholder="Escriba para buscar">
                            <option value="all">Todos</option>
                            <?php
                            $names = $conn->query("SELECT DISTINCT CONCAT(w.name, ' ', w.lastname) AS fullname FROM workers w JOIN users u ON w.user_id = u.id WHERE u.is_admin = 0 ORDER BY fullname")->fetchAll(PDO::FETCH_COLUMN);
                            foreach ($names as $name) {
                                $selected = ($filterParams['name'] ?? '') == $name ? 'selected' : '';
                                echo "<option value='" . htmlspecialchars($name) . "' $selected>" . htmlspecialchars($name) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="filterDNI" class="form-label">DNI</label>
                        <select class="form-select select2-search" id="filterDNI" data-placeholder="Escriba para buscar">
                            <option value="all">Todos</option>
                            <?php
                            $dnis = $conn->query("SELECT DISTINCT w.num_doc FROM workers w JOIN users u ON w.user_id = u.id WHERE u.is_admin = 0 ORDER BY w.num_doc")->fetchAll(PDO::FETCH_COLUMN);
                            foreach ($dnis as $dni) {
                                $selected = ($filterParams['dni'] ?? '') == $dni ? 'selected' : '';
                                echo "<option value='" . htmlspecialchars($dni) . "' $selected>" . htmlspecialchars($dni) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="filterEmail" class="form-label">Email</label>
                        <select class="form-select select2-search" id="filterEmail" data-placeholder="Escriba para buscar">
                            <option value="all">Todos</option>
                            <?php
                            $emails = $conn->query("SELECT DISTINCT w.email FROM workers w JOIN users u ON w.user_id = u.id WHERE u.is_admin = 0 ORDER BY w.email")->fetchAll(PDO::FETCH_COLUMN);
                            foreach ($emails as $email) {
                                $selected = ($filterParams['email'] ?? '') == $email ? 'selected' : '';
                                echo "<option value='" . htmlspecialchars($email) . "' $selected>" . htmlspecialchars($email) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="filterArea" class="form-label">Área</label>
                        <select class="form-select select2-search" id="filterArea" data-placeholder="Escriba para buscar">
                            <option value="all">Todos</option>
                            <?php
                            $areas = $conn->query("SELECT DISTINCT a.name FROM area a JOIN workers w ON a.id = w.area_id JOIN users u ON w.user_id = u.id WHERE u.is_admin = 0 ORDER BY a.name")->fetchAll(PDO::FETCH_COLUMN);
                            foreach ($areas as $area) {
                                $selected = ($filterParams['area'] ?? '') == $area ? 'selected' : '';
                                echo "<option value='" . htmlspecialchars($area) . "' $selected>" . htmlspecialchars($area) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    
                    <!-- Fila 4: Más Filtros -->
                    <div class="col-md-3">
                        <label for="filterPosition" class="form-label">Puesto</label>
                        <select class="form-select select2-search" id="filterPosition" data-placeholder="Escriba para buscar">
                            <option value="all">Todos</option>
                            <?php
                            $positions = $conn->query("SELECT DISTINCT p.name FROM position p JOIN workers w ON p.id = w.position_id JOIN users u ON w.user_id = u.id WHERE u.is_admin = 0 ORDER BY p.name")->fetchAll(PDO::FETCH_COLUMN);
                            foreach ($positions as $position) {
                                $selected = ($filterParams['position'] ?? '') == $position ? 'selected' : '';
                                echo "<option value='" . htmlspecialchars($position) . "' $selected>" . htmlspecialchars($position) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="filterAssistanceStatus" class="form-label">Estado de Asistencia</label>
                        <select class="form-select" id="filterAssistanceStatus">
                            <option value="all" <?= empty($filterParams['assistance_status']) ? 'selected' : '' ?>>Todos</option>
                            <option value="1" <?= ($filterParams['assistance_status'] ?? '') == '1' ? 'selected' : '' ?>>Asistió</option>
                            <option value="0" <?= ($filterParams['assistance_status'] ?? '') == '0' ? 'selected' : '' ?>>Faltó</option>
                            <option value="2" <?= ($filterParams['assistance_status'] ?? '') == '2' ? 'selected' : '' ?>>Justificado</option>
                            <option value="3" <?= ($filterParams['assistance_status'] ?? '') == '3' ? 'selected' : '' ?>>Permiso</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="filterEntryStatus" class="form-label">Estado de Ingreso</label>
                        <select class="form-select" id="filterEntryStatus">
                            <option value="all" <?= empty($filterParams['entry_status']) ? 'selected' : '' ?>>Todos</option>
                            <option value="Puntual" <?= ($filterParams['entry_status'] ?? '') == 'Puntual' ? 'selected' : '' ?>>Puntual</option>
                            <option value="Tardanza" <?= ($filterParams['entry_status'] ?? '') == 'Tardanza' ? 'selected' : '' ?>>Tardanza</option>
                            <option value="Sin salida" <?= ($filterParams['entry_status'] ?? '') == 'Sin salida' ? 'selected' : '' ?>>Sin Ingreso</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="filterExitStatus" class="form-label">Estado de Salida</label>
                        <select class="form-select" id="filterExitStatus">
                            <option value="all" <?= empty($filterParams['exit_status']) ? 'selected' : '' ?>>Todos</option>
                            <option value="Puntual" <?= ($filterParams['exit_status'] ?? '') == 'Puntual' ? 'selected' : '' ?>>Puntual</option>
                            <option value="Tardanza" <?= ($filterParams['exit_status'] ?? '') == 'Tardanza' ? 'selected' : '' ?>>Tardanza</option>
                            <option value="Sin salida" <?= ($filterParams['exit_status'] ?? '') == 'Sin salida' ? 'selected' : '' ?>>Sin salida</option>
                        </select>
                    </div>
                    
                    <!-- Botones de Acción -->
                    <div class="col-12 d-flex justify-content-end mt-3">
                        <button type="button" id="btnClearFilters" class="btn btn-secondary me-2">
                            <i class="bi bi-eraser"></i> Limpiar Filtros
                        </button>
                        <button type="button" id="btnApplyFilters" class="btn btn-primary">
                            <i class="bi bi-search"></i> Aplicar Filtros
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

        <!-- Tabla de Resultados -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-table"></i> Registros de Asistencia</h5>
            <div class="d-flex align-items-center">
            <a href="export_excel.php?<?= http_build_query($_GET) ?>" class="btn btn-success btn-sm me-2">
                <i class="bi bi-file-earmark-excel"></i> Exportar
            </a>
            <label for="perPageSelect" class="me-2 mb-0">Mostrar:</label>
            <select id="perPageSelect" class="form-select form-select-sm" style="width: auto;">
                <option value="10" <?= $perPage == 10 ? 'selected' : '' ?>>10</option>
                <option value="25" <?= $perPage == 25 ? 'selected' : '' ?>>25</option>
                <option value="50" <?= $perPage == 50 ? 'selected' : '' ?>>50</option>
                <option value="100" <?= $perPage == 100 ? 'selected' : '' ?>>100</option>
            </select>
        </div>
    </div>
    
        
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-bordered" id="dataTable">
                    <thead>
                        <tr>
                            <th>Registro</th>
                            <th>Ingreso Laboral</th>
                            <th>Ingreso Registrado</th>
                            <th>Minutos Retraso</th>
                            <th>Salida Laboral</th>
                            <th>Salida Registrado</th>
                            <th>Horas Extras</th>
                            <th>Horas trabajadas</th>
                            <th>Nombre</th>
                            <th>DNI</th>
                            <th>Email</th>
                            <th>Area</th>
                            <th>Puesto</th>
                            <th>Tipo Logeo</th>
                            <th>Estado Asistencia</th>
                            <th>Estado de ingreso</th>
                            <th>Foto Ingreso</th>
                            <th>Ubicación Ingreso</th>
                            <th>Estado de Salida</th>
                            <th>Foto Salida</th>
                            <th>Ubicación Salida</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    
                    <tbody>
                        <?php foreach ($records as $row): ?>
                        <tr id="row-<?= $row['assistance_id'] ?>">
                            <td><?= date('d/m/Y', strtotime($row['created_at'])) ?></td>
                            <td><?= $row['hour_time_ini'] ? date('h:i A', strtotime($row['hour_time_ini'])) : '-' ?></td>
                            <td><?= $row['date_start'] ? date('d/m/Y h:i:s A', strtotime($row['date_start'])) : 'N/A' ?></td>
                            <td>
                                <?php
                                if (!empty($row['hour_time_ini']) && !empty($row['date_start'])) {
                                    $fecha = date('Y-m-d', strtotime($row['date_start']));
                                    $horaEsperada = strtotime($fecha . ' ' . $row['hour_time_ini']);
                                    $horaReal = strtotime($row['date_start']);

                                    $toleranciaMinutos = $row['type_login'] == 1 ? 5 : 15;
                                    $horaEsperadaConTolerancia = strtotime("+{$toleranciaMinutos} minutes", $horaEsperada);

                                    if ($horaReal > $horaEsperadaConTolerancia) {
                                        $diff = $horaReal - $horaEsperadaConTolerancia;
                                        $horas = floor($diff / 3600);
                                        $minutos = floor(($diff % 3600) / 60);
                                        echo "{$horas}h {$minutos}min";
                                    } else {
                                        echo "0h 0min";
                                    }
                                } else {
                                    echo "N/A";
                                }
                                ?>
                            </td>
                                                        <td><?= $row['hour_time_end'] ? date('h:i A', strtotime($row['hour_time_end'])) : '-' ?></td>
                            <td><?= $row['date_end'] ? date('d/m/Y h:i:s A', strtotime($row['date_end'])) : 'N/A' ?></td>
                            <td>
                                <?php
                                if (!empty($row['date_start']) && !empty($row['date_end']) && !empty($row['hour_time_end'])) {
                                    $horaSalidaReal = strtotime($row['date_end']);
                                    $fechaSalida = date('Y-m-d', strtotime($row['date_end']));
                                    $horaSalidaEsperada = strtotime($fechaSalida . ' ' . $row['hour_time_end']);

                                    if ($horaSalidaReal > $horaSalidaEsperada) {
                                        $diff = $horaSalidaReal - $horaSalidaEsperada;
                                        $horas = floor($diff / 3600);
                                        $minutos = floor(($diff % 3600) / 60);
                                        echo "{$horas}h {$minutos}min";
                                    } else {
                                        echo "0h 0min";
                                    }
                                } else {
                                    echo "N/A";
                                }
                                ?>
                            </td>
                            <td>
                                <?php
                                if (!empty($row['date_start']) && !empty($row['date_end'])) {
                                    $inicio = strtotime($row['date_start']);
                                    $fin = strtotime($row['date_end']);
                                    $diff = $fin - $inicio;
                                    $horas = floor($diff / 3600);
                                    $minutos = floor(($diff % 3600) / 60);
                                    echo "{$horas}h {$minutos}min";
                                } else {
                                    echo "N/A";
                                }
                                ?>
                            </td>
                            <td><?= htmlspecialchars($row['name'] . ' ' . $row['lastname']) ?></td>
                            <td><?= htmlspecialchars($row['num_doc']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><?= htmlspecialchars($row['area_name'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($row['position_name'] ?? 'N/A') ?></td>
                            <td>
                                <?php
                                if ($row['type_login'] == 1) {
                                    echo '<span class="badge bg-primary">Virtual</span>';
                                } elseif ($row['type_login'] == 2) {
                                    echo '<span class="badge bg-success">Presencial</span>';
                                } else {
                                    echo '<span class="badge bg-secondary">N/A</span>';
                                }
                                ?>
                            </td>
                            <td>
                                <?php
                                switch ($row['state_assistance']) {
                                    case 0: echo '<span class="badge bg-danger text-light">Falto</span>'; break;
                                    case 1: echo '<span class="badge bg-info text-dark">Asistió</span>'; break;
                                    case 2: echo '<span class="badge bg-warning text-dark">Justificado</span>'; break;
                                    case 3: echo '<span class="badge bg-success text-light">Permiso</span>'; break;
                                    default: echo '<span class="badge bg-secondary">N/A</span>';
                                }
                                ?>
                            </td>
                            <td>
                                <?php
                                if ($row['date_start'] && $row['state_assistance'] != 0) {
                                    $horaIngreso = strtotime($row['date_start']);
                                    $fechaIngreso = date('Y-m-d', strtotime($row['date_start']));
                                    $horaEsperada = strtotime($fechaIngreso . ' ' . $row['hour_time_ini']);
                                    $toleranciaSegundos = ($row['type_login'] == 1 ? 5 : 15) * 60;

                                    if ($horaIngreso <= ($horaEsperada + $toleranciaSegundos)) {
                                        echo '<span class="badge bg-success">Puntual</span>';
                                    } else {
                                        echo '<span class="badge bg-warning text-dark">Tardanza</span>';
                                    }
                                } else {
                                    echo '<span>N/A</span>';
                                }
                                ?>
                            </td>
                            <td>
                                <?php if (!empty($row['photo_start']) && file_exists(__DIR__ . "/../../public/img/" . basename($row['photo_start']))): ?>
                                    <a href="../../img/<?= htmlspecialchars($row['photo_start']) ?>" 
                                       data-lightbox="photo-<?= $row['assistance_id'] ?>-start"
                                       data-title="Foto de Ingreso"
                                       class="btn btn-primary btn-sm">Mostrar</a>
                                <?php else: ?>
                                    <span class="text-muted">N/A</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($row['location_start'])): ?>
                                    <a href="javascript:void(0)" class="btn btn-primary btn-sm" 
                                       onclick="showMap('<?= htmlspecialchars($row['location_start']) ?>', 'Ingreso')">
                                        Mapa
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">N/A</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                if (!empty($row['date_end'])) {
                                    $horaSalida = strtotime($row['date_end']);
                                    $fechaSalida = date('Y-m-d', strtotime($row['date_end']));
                                    $horaEsperada = strtotime($fechaSalida . ' ' . $row['hour_time_end']);
                                    
                                    // Calcular la tolerancia (15 minutos en segundos)
                                    $tolerancia = 15 * 60; // 15 minutos en segundos
                                    $horaLimite = $horaEsperada + $tolerancia;
                                    
                                    if ($horaSalida <= $horaLimite) {
                                        echo '<span class="badge bg-success">Puntual</span>';
                                    } else {
                                        echo '<span class="badge bg-warning text-dark">Tardanza</span>';
                                    }
                                } else {
                                    echo '<span class="badge bg-secondary">Sin salida</span>';
                                }
                                ?>
                            </td>
                            <td>
                                <?php if (!empty($row['photo_end']) && file_exists(__DIR__ . "/../../public/img/" . basename($row['photo_end']))): ?>
                                    <a href="../../img/<?= htmlspecialchars($row['photo_end']) ?>" 
                                       data-lightbox="photo-<?= $row['assistance_id'] ?>-end"
                                       data-title="Foto de Salida"
                                       class="btn btn-primary btn-sm">Mostrar</a>
                                <?php else: ?>
                                    <span class="text-muted">N/A</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($row['location_end'])): ?>
                                    <a href="javascript:void(0)" class="btn btn-primary btn-sm" 
                                       onclick="showMap('<?= htmlspecialchars($row['location_end']) ?>', 'Salida')">
                                        Mapa
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">N/A</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="edit.php?id=<?= $row['assistance_id'] ?>" class="btn btn-sm btn-warning me-1" title="Editar">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <a href="delete.php?id=<?= $row['assistance_id'] ?>" class="btn btn-sm btn-danger" title="Eliminar">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo; Anterior</span>
                        </a>
                    </li>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    
                    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" aria-label="Next">
                            <span aria-hidden="true">Siguiente &raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- Modal para mostrar el mapa -->
<div class="modal fade" id="mapModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mapModalTitle">Ubicación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div id="mapContainer" style="height: 70vh;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
<?php include __DIR__ . '/../../public/js/admin/dashboard.js'; ?>
</script>

<?php include __DIR__ . '/../../views/templates/footer.php'; ?>