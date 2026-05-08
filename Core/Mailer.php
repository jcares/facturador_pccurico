<?php
namespace Core;

class Mailer
{
    public static function sendReminder($to, $subject, $body, $attachmentContent = null, $attachmentName = null)
    {
        $settings = self::settings();

        if (class_exists('\PHPMailer\PHPMailer\PHPMailer') && !empty($settings['smtp_host'])) {
            return self::sendWithPhpMailer($settings, $to, $subject, $body, $attachmentContent, $attachmentName);
        }

        if (!empty($settings['smtp_host'])) {
            $msg = "Se ha configurado SMTP pero no se encuentra la librería PHPMailer. El sistema no puede enviar correos mediante SMTP sin esta dependencia.";
            Logger::error($msg);
            throw new \Exception($msg);
        }

        // Si no hay SMTP configurado, intentamos usar mail() nativo como último recurso
        return self::sendWithNativeMail($settings, $to, $subject, $body, $attachmentContent, $attachmentName);
    }

    private static function sendWithPhpMailer($settings, $to, $subject, $body, $attachmentContent, $attachmentName)
    {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = $settings['smtp_host'];
        $mail->Port = (int)($settings['smtp_port'] ?? 587);
        $mail->SMTPAuth = !empty($settings['smtp_user']);
        $mail->Username = $settings['smtp_user'] ?? '';
        $mail->Password = $settings['smtp_pass'] ?? '';
        $mail->SMTPSecure = $settings['smtp_secure'] ?? 'tls';
        $mail->CharSet = 'UTF-8';
        $mail->setFrom($settings['smtp_from'] ?? $settings['biz_email'] ?? 'no-reply@localhost', $settings['biz_name'] ?? 'Facturador');
        $mail->addAddress($to);
        $mail->Subject = $subject;
        if (self::isHtml($body)) {
            $mail->isHTML(true);
            $mail->AltBody = trim(strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $body)));
        }
        $mail->Body = $body;

        if ($attachmentContent && $attachmentName) {
            $mail->addStringAttachment($attachmentContent, $attachmentName, 'base64', 'application/pdf');
        }

        return $mail->send();
    }

    private static function sendWithNativeMail($settings, $to, $subject, $body, $attachmentContent, $attachmentName)
    {
        $from = $settings['smtp_from'] ?? $settings['biz_email'] ?? 'no-reply@localhost';

        if (!$attachmentContent || !$attachmentName) {
            $contentType = self::isHtml($body) ? 'text/html' : 'text/plain';
            $result = @mail($to, $subject, $body, "From: {$from}\r\nContent-Type: {$contentType}; charset=UTF-8");
            if (!$result) {
                $error = error_get_last();
                $msg = "La función mail() nativa falló. Verifique la configuración de su servidor web o active SMTP con PHPMailer. Error: " . ($error['message'] ?? 'Desconocido');
                Logger::error($msg);
                throw new \Exception($msg);
            }
            return $result;
        }

        $boundary = '=_facturador_' . bin2hex(random_bytes(12));
        $headers = [
            "From: {$from}",
            'MIME-Version: 1.0',
            "Content-Type: multipart/mixed; boundary=\"{$boundary}\""
        ];

        $contentType = self::isHtml($body) ? 'text/html' : 'text/plain';
        $message = "--{$boundary}\r\n";
        $message .= "Content-Type: {$contentType}; charset=UTF-8\r\n\r\n{$body}\r\n";
        $message .= "--{$boundary}\r\n";
        $message .= "Content-Type: application/pdf; name=\"{$attachmentName}\"\r\n";
        $message .= "Content-Transfer-Encoding: base64\r\n";
        $message .= "Content-Disposition: attachment; filename=\"{$attachmentName}\"\r\n\r\n";
        $message .= chunk_split(base64_encode($attachmentContent));
        $message .= "--{$boundary}--";

        $result = @mail($to, $subject, $message, implode("\r\n", $headers));
        if (!$result) {
            $error = error_get_last();
            $msg = "La función mail() nativa falló al enviar adjunto. Error: " . ($error['message'] ?? 'Desconocido');
            Logger::error($msg);
            throw new \Exception($msg);
        }
        return $result;
    }

    private static function settings()
    {
        try {
            $db = Database::getInstance();
            $rows = $db->query("SELECT * FROM settings")->fetchAll();
            $settings = [];
            foreach ($rows as $row) {
                $settings[$row['key']] = $row['value'];
            }
            return $settings;
        } catch (\Exception $e) {
            Logger::error("Mailer settings load failed: " . $e->getMessage());
            return [];
        }
    }

    private static function isHtml($body)
    {
        return (string)$body !== strip_tags((string)$body);
    }
}
