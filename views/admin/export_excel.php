<?php
session_start();
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header("Location: ../login_admin.php");
    exit();
}

require_once __DIR__ . '/../../config/database.php';

$db = new Database();
$conn = $db->connect();

// Obtener todos los parámetros de filtrado del GET
$filters = $_GET;

// Construir la consulta base
$query = "SELECT 
             w.name, w.lastname, w.num_doc, w.email, 
             a.name AS area_name,
             p.name AS position_name,
             CASE 
                WHEN a1.type_login = 2 AND w.id_time_presencial IS NOT NULL THEN wtp.hour_time_ini 
                ELSE wt.hour_time_ini 
             END AS hour_time_ini,
             CASE 
                WHEN a1.type_login = 2 AND w.id_time_presencial IS NOT NULL THEN wtp.hour_time_end 
                ELSE wt.hour_time_end 
             END AS hour_time_end,
             a1.id AS assistance_id,
             a1.date_start, 
             a1.date_end,
             a1.state_assistance,
             a1.type_login,
             a1.created_at
          FROM workers w
          JOIN assistances a1 ON w.user_id = a1.user_id
          LEFT JOIN work_times wt ON w.id_times = wt.id
          LEFT JOIN work_times wtp ON w.id_time_presencial = wtp.id
          LEFT JOIN area a ON w.area_id = a.id
          LEFT JOIN position p ON w.position_id = p.id
          WHERE a1.is_deleted = 0";

// Aplicar los mismos filtros que en dashboard.php
$params = [];

// Filtros por fecha - Versión mejorada
if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
    $query .= " AND (
        (DATE(a1.date_start) BETWEEN :start_date AND :end_date) 
        OR 
        (a1.date_start IS NULL AND DATE(a1.created_at) BETWEEN :start_date AND :end_date)
    )";
    $params[':start_date'] = $filters['start_date'];
    $params[':end_date'] = $filters['end_date'];
} elseif (!empty($filters['start_date'])) {
    $query .= " AND (
        (DATE(a1.date_start) >= :start_date) 
        OR 
        (a1.date_start IS NULL AND DATE(a1.created_at) >= :start_date)
    )";
    $params[':start_date'] = $filters['start_date'];
} elseif (!empty($filters['end_date'])) {
    $query .= " AND (
        (DATE(a1.date_start) <= :end_date) 
        OR 
        (a1.date_start IS NULL AND DATE(a1.created_at) <= :end_date)
    )";
    $params[':end_date'] = $filters['end_date'];
}

// Filtro por nombre
if (!empty($filters['name']) && $filters['name'] != 'all') {
    $query .= " AND CONCAT(w.name, ' ', w.lastname) = :name";
    $params[':name'] = $filters['name'];
}

// Filtro por DNI
if (!empty($filters['dni']) && $filters['dni'] != 'all') {
    $query .= " AND w.num_doc = :dni";
    $params[':dni'] = $filters['dni'];
}

// Filtro por email
if (!empty($filters['email']) && $filters['email'] != 'all') {
    $query .= " AND w.email = :email";
    $params[':email'] = $filters['email'];
}

// Filtro por área
if (!empty($filters['area']) && $filters['area'] != 'all') {
    $query .= " AND a.name = :area";
    $params[':area'] = $filters['area'];
}

// Filtro por puesto
if (!empty($filters['position']) && $filters['position'] != 'all') {
    $query .= " AND p.name = :position";
    $params[':position'] = $filters['position'];
}

// Filtro por horario de ingreso
if (!empty($filters['work_schedule']) && $filters['work_schedule'] != 'all') {
    $query .= " AND CASE 
                   WHEN a1.type_login = 2 AND w.id_time_presencial IS NOT NULL 
                   THEN wtp.hour_time_ini 
                   ELSE wt.hour_time_ini 
                END = :work_schedule";
    $params[':work_schedule'] = $filters['work_schedule'];
}

// Filtro por horario de salida
if (!empty($filters['work_end_schedule']) && $filters['work_end_schedule'] != 'all') {
    $query .= " AND CASE 
                   WHEN a1.type_login = 2 AND w.id_time_presencial IS NOT NULL 
                   THEN wtp.hour_time_end 
                   ELSE wt.hour_time_end 
                END = :work_end_schedule";
    $params[':work_end_schedule'] = $filters['work_end_schedule'];
}

// Filtro por tipo de login
if (!empty($filters['login_type'])) {
    if ($filters['login_type'] == '0') {
        $query .= " AND a1.type_login IS NULL";
    } else {
        $query .= " AND a1.type_login = :login_type";
        $params[':login_type'] = $filters['login_type'];
    }
}

// Filtro por estado de asistencia
if (!empty($filters['assistance_status'])) {
    $query .= " AND a1.state_assistance = :assistance_status";
    $params[':assistance_status'] = $filters['assistance_status'];
}

// Filtro por minutos retrasados
if (!empty($filters['late_minutes']) && $filters['late_minutes'] != 'all') {
    if ($filters['late_minutes'] == 'yes') {
        $query .= " AND TIMESTAMPDIFF(MINUTE, 
                    CONCAT(DATE(a1.date_start), ' ', 
                        CASE 
                            WHEN a1.type_login = 2 AND w.id_time_presencial IS NOT NULL 
                            THEN wtp.hour_time_ini 
                            ELSE wt.hour_time_ini 
                        END), 
                    a1.date_start) > 
                    CASE WHEN a1.type_login = 1 THEN 5 ELSE 15 END";
    } elseif ($filters['late_minutes'] == 'no') {
        $query .= " AND TIMESTAMPDIFF(MINUTE, 
                    CONCAT(DATE(a1.date_start), ' ', 
                        CASE 
                            WHEN a1.type_login = 2 AND w.id_time_presencial IS NOT NULL 
                            THEN wtp.hour_time_ini 
                            ELSE wt.hour_time_ini 
                        END), 
                    a1.date_start) <= 
                    CASE WHEN a1.type_login = 1 THEN 5 ELSE 15 END";
    }
}

// Filtro por horas extras
if (!empty($filters['overtime']) && $filters['overtime'] != 'all') {
    if ($filters['overtime'] == 'yes') {
        $query .= " AND TIMESTAMPDIFF(MINUTE, 
                    CONCAT(DATE(a1.date_end), ' ', 
                        CASE 
                            WHEN a1.type_login = 2 AND w.id_time_presencial IS NOT NULL 
                            THEN wtp.hour_time_end 
                            ELSE wt.hour_time_end 
                        END), 
                    a1.date_end) > 0";
    } elseif ($filters['overtime'] == 'no') {
        $query .= " AND TIMESTAMPDIFF(MINUTE, 
                    CONCAT(DATE(a1.date_end), ' ', 
                        CASE 
                            WHEN a1.type_login = 2 AND w.id_time_presencial IS NOT NULL 
                            THEN wtp.hour_time_end 
                            ELSE wt.hour_time_end 
                        END), 
                    a1.date_end) <= 0";
    }
}

// Filtro por horas trabajadas
if (!empty($filters['worked_hours']) || !empty($filters['worked_minutes'])) {
    $totalMinutes = (intval($filters['worked_hours'] ?? 0) * 60) + intval($filters['worked_minutes'] ?? 0);
    $query .= " AND TIMESTAMPDIFF(MINUTE, a1.date_start, a1.date_end) >= :worked_minutes";
    $params[':worked_minutes'] = $totalMinutes;
}

$query .= " ORDER BY a1.created_at DESC";

$stmt = $conn->prepare($query);

// Bind parameters
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}

$stmt->execute();
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Configurar cabeceras para Excel
header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename="asistencias_' . date('Y-m-d') . '.xls"');
header('Pragma: no-cache');
header('Expires: 0');

// BOM para UTF-8 (solución caracteres especiales)
echo "\xEF\xBB\xBF";

// Inicio del documento HTML (que Excel interpretará)
?>
<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <!--[if gte mso 9]>
    <xml>
        <x:ExcelWorkbook>
            <x:ExcelWorksheets>
                <x:ExcelWorksheet>
                    <x:Name>Asistencias</x:Name>
                    <x:WorksheetOptions>
                        <x:DisplayGridlines/>
                    </x:WorksheetOptions>
                </x:ExcelWorksheet>
            </x:ExcelWorksheets>
        </x:ExcelWorkbook>
    </xml>
    <![endif]-->
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th {
            background-color: #4472C4;
            color: white;
            font-weight: bold;
            text-align: center;
            padding: 5px;
            border: 1px solid #ddd;
        }
        td {
            padding: 5px;
            border: 1px solid #ddd;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .text { mso-number-format:\@; }
    </style>
</head>
<body>
<table>
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
        <th>Estado de Salida</th>
    </tr>
    <?php foreach ($records as $row): ?>
    <tr>
        <td class="text"><?= date('d/m/Y', strtotime($row['created_at'])) ?></td>
        <td class="text"><?= $row['hour_time_ini'] ? date('h:i A', strtotime($row['hour_time_ini'])) : '-' ?></td>
        <td class="text"><?= $row['date_start'] ? date('d/m/Y h:i:s A', strtotime($row['date_start'])) : 'N/A' ?></td>
        <td class="text">
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
        <td class="text"><?= $row['hour_time_end'] ? date('h:i A', strtotime($row['hour_time_end'])) : '-' ?></td>
        <td class="text"><?= $row['date_end'] ? date('d/m/Y h:i:s A', strtotime($row['date_end'])) : 'N/A' ?></td>
        <td class="text">
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
        <td class="text">
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
        <td class="text"><?= htmlspecialchars($row['name'] . ' ' . $row['lastname']) ?></td>
        <td class="text"><?= htmlspecialchars($row['num_doc']) ?></td>
        <td class="text"><?= htmlspecialchars($row['email']) ?></td>
        <td class="text"><?= htmlspecialchars($row['area_name'] ?? 'N/A') ?></td>
        <td class="text"><?= htmlspecialchars($row['position_name'] ?? 'N/A') ?></td>
        <td class="text">
            <?php
                if ($row['type_login'] == 1) {
                    echo "Virtual";
                } elseif ($row['type_login'] == 2) {
                    echo "Presencial";
                } else {
                    echo "N/A";
                }
            ?>
        </td>
        <td class="text">
            <?php
                switch ($row['state_assistance']) {
                    case 0: echo "Falto"; break;
                    case 1: echo "Asistió"; break;
                    case 2: echo "Justificado"; break;
                    case 3: echo "Permiso"; break;
                    default: echo "N/A";
                }
            ?>
        </td>
        <td class="text">
            <?php
                if ($row['date_start'] && $row['state_assistance'] != 0) {
                    $horaIngreso = strtotime($row['date_start']);
                    $fechaIngreso = date('Y-m-d', strtotime($row['date_start']));
                    $horaEsperada = strtotime($fechaIngreso . ' ' . $row['hour_time_ini']);
                    $toleranciaSegundos = ($row['type_login'] == 1 ? 5 : 15) * 60;

                    if ($horaIngreso <= ($horaEsperada + $toleranciaSegundos)) {
                        echo "Puntual";
                    } else {
                        echo "Tardanza";
                    }
                } else {
                    echo "N/A";
                }
            ?>
        </td>
        <td class="text">
            <?php
                if (!empty($row['date_end'])) {
                    $horaSalida = strtotime($row['date_end']);
                    $fechaSalida = date('Y-m-d', strtotime($row['date_end']));
                    $horaEsperada = strtotime($fechaSalida . ' ' . $row['hour_time_end']);
                    
                    $tolerancia = 15 * 60; // 15 minutos en segundos
                    $horaLimite = $horaEsperada + $tolerancia;
                    
                    if ($horaSalida <= $horaLimite) {
                        echo "Puntual";
                    } else {
                        echo "Tardanza";
                    }
                } else {
                    echo "Sin salida";
                }
            ?>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
</body>
</html>