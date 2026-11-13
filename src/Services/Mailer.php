<?php

declare(strict_types=1);

namespace App\Services;

use App\Config;
use PHPMailer\PHPMailer\Exception as MailException;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Sends email over SMTP (Gmail in production, Mailpit in development).
 *
 * Failures are logged and reported to the caller but never abort the request:
 * a comment should still be saved even if the notification email fails.
 */
final class Mailer
{
    public function __construct(private readonly Config $config)
    {
    }

    public function send(string $to, string $subject, string $htmlBody, ?string $replyTo = null, string $replyToName = ''): bool
    {
        if (!$this->config->mailEnabled) {
            error_log(sprintf('[mail] mail is disabled; skipped "%s"', $subject));

            return false;
        }

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = $this->config->mailHost;
            $mail->Port = $this->config->mailPort;
            $mail->Timeout = 10;
            $mail->CharSet = PHPMailer::CHARSET_UTF8;

            if ($this->config->mailUsername !== '') {
                $mail->SMTPAuth = true;
                $mail->Username = $this->config->mailUsername;
                $mail->Password = $this->config->mailPassword;
            }

            switch ($this->config->mailEncryption) {
                case 'ssl':
                case 'smtps':
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                    break;
                case 'none':
                case '':
                    $mail->SMTPSecure = '';
                    $mail->SMTPAutoTLS = false;
                    break;
                default:
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            }

            $mail->setFrom($this->config->mailFromAddress, $this->config->mailFromName);
            $mail->addAddress($to);
            if ($replyTo !== null && $replyTo !== '') {
                $mail->addReplyTo($replyTo, $replyToName);
            }

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $htmlBody;
            $mail->AltBody = html_entity_decode(strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>'], "\n", $htmlBody)), ENT_QUOTES | ENT_HTML5, 'UTF-8');

            $mail->send();

            return true;
        } catch (MailException $e) {
            // Never log recipient addresses (personal data); the subject is enough to debug.
            error_log(sprintf('[mail] failed to send "%s": %s', $subject, $mail->ErrorInfo));

            return false;
        }
    }
}
