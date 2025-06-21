<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../lib/vendor/autoload.php';
require_once __DIR__ . '/../../config/database.php';

class MailNotifier {
    private $conn;
    private $mailer;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
        $this->mailer = new PHPMailer(true);
    }

    private function configureMailer() {
        $this->mailer->isSMTP();
        $this->mailer->Host       = 'smtp.gmail.com';
        $this->mailer->SMTPAuth   = true;
        $this->mailer->Username   = '3027joaquin@gmail.com';
        $this->mailer->Password   = 'cotbzshhozavshgn';
        $this->mailer->SMTPSecure = 'tls';
        $this->mailer->Port       = 587;
        $this->mailer->setFrom('3027joaquin@gmail.com', 'Sistema de Asistencia');
        $this->mailer->isHTML(true);
    }

    public function enviarNotificacionAlAdmin($trabajadores) {
        try {
            $this->configureMailer();
            $this->mailer->clearAddresses();
            $this->mailer->addAddress('3027joaquin@gmail.com', 'Administrador');
            $this->mailer->Subject = 'Trabajadores sin registro de salida';

            $html = '<p>Los siguientes trabajadores no han registrado su salida hoy después de su horario laboral + tolerancia:</p>';
            $html .= '<table border="1" cellpadding="8" cellspacing="0">';
            $html .= '<tr><th>Nombre</th><th>DNI</th><th>Email</th><th>Hora de salida esperada</th></tr>';

            foreach ($trabajadores as $trabajador) {
                $nombreCompleto = htmlspecialchars($trabajador['name'] . ' ' . $trabajador['lastname']);
                $dni = htmlspecialchars($trabajador['num_doc']);
                $email = htmlspecialchars($trabajador['email']);
                $horaSalida = date('h:i A', strtotime($trabajador['hour_time_end']));

                $html .= "<tr>
                            <td>$nombreCompleto</td>
                            <td>$dni</td>
                            <td>$email</td>
                            <td>$horaSalida</td>
                          </tr>";
            }

            $html .= '</table>';
            $this->mailer->Body = $html;
            $this->mailer->send();
            
            // Marcar como notificado después de enviar al admin
            foreach ($trabajadores as $trabajador) {
                $this->marcarComoNotificado($trabajador['assistance_id']);
            }
            
        } catch (Exception $e) {
            error_log("Error al enviar el correo al admin: {$this->mailer->ErrorInfo}");
        }
    }

    public function enviarNotificacionAUsuario($trabajador) {
        try {
            $this->configureMailer();
            $this->mailer->clearAddresses();
            
            if (empty($trabajador['email'])) {
                error_log("Usuario {$trabajador['id']} no tiene email registrado");
                return false;
            }

            $this->mailer->addAddress($trabajador['email'], $trabajador['name'] . ' ' . $trabajador['lastname']);
            $this->mailer->Subject = 'Recordatorio: Registro de salida pendiente';

            $nombreCompleto = htmlspecialchars($trabajador['name'] . ' ' . $trabajador['lastname']);
            $horaSalida = date('h:i A', strtotime($trabajador['hour_time_end']));
            
            $html = "<p>Hola $nombreCompleto,</p>";
            $html .= "<p>Según nuestros registros, no has marcado tu hora de salida hoy.</p>";
            $html .= "<p>Tu hora de salida esperada era: <strong>$horaSalida</strong></p>";
            $html .= "<p>Por favor, accede al sistema para registrar tu salida o contacta con el administrador si crees que esto es un error.</p>";
            $html .= "<p>Atentamente,<br>El equipo de Recursos Humanos</p>";

            $this->mailer->Body = $html;
            return $this->mailer->send();
        } catch (Exception $e) {
            error_log("Error al enviar el correo a {$trabajador['email']}: {$this->mailer->ErrorInfo}");
            return false;
        }
    }

    public function obtenerTrabajadoresSinSalida($toleranciaMinutos = 15) {
        $query = "SELECT a.id as assistance_id, w.*, wt.hour_time_end 
                  FROM assistances a
                  JOIN users u ON a.user_id = u.id
                  JOIN workers w ON u.id = w.user_id
                  JOIN work_times wt ON w.id_times = wt.id
                  WHERE DATE(a.date_start) = CURDATE() 
                  AND a.date_start IS NOT NULL
                  AND a.date_end IS NULL 
                  AND a.notificado = 0
                  AND a.is_deleted = 0
                  AND w.email IS NOT NULL
                  AND CURTIME() >= ADDTIME(wt.hour_time_end, SEC_TO_TIME(?))";
        
        $stmt = $this->conn->prepare($query);
        $toleranciaSegundos = $toleranciaMinutos * 60;
        $stmt->execute([$toleranciaSegundos]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function marcarComoNotificado($assistance_id) {
        $stmt = $this->conn->prepare("UPDATE assistances SET notificado = 1 WHERE id = ?");
        return $stmt->execute([$assistance_id]);
    }

    public function notificarAusencias($toleranciaMinutos = 15) {
        $trabajadores = $this->obtenerTrabajadoresSinSalida($toleranciaMinutos);

        if (!empty($trabajadores)) {
            $this->enviarNotificacionAlAdmin($trabajadores);
            
            foreach ($trabajadores as $trabajador) {
                $this->enviarNotificacionAUsuario($trabajador);
            }
        }
    }
}

// Ejecutar la notificación con tolerancia de 15 minutos
$notifier = new MailNotifier();
$notifier->notificarAusencias(15);