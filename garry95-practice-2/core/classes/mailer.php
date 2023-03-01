<?php
namespace core\classes;

use PHPMailer\PHPMailer\PHPMailer;

class mailer
{

    /**
     * E-mail sending method.
     *
     * @param string $mailTo
     *            The email address to send to.
     * @param string $subject
     *            The subject of the message.
     * @param string $msgBody
     *            An HTML message body.
     * @param string $fromMail
     *            The from email address for the message. Optional.
     * @param string $fromName
     *            The from name of the message. Optional.
     * @param string $replyTo
     *            The email address to reply to. Optional.
     * @param array|string $attachments
     *            Path(s) to the attachment. Optional.
     * @return boolean
     */
    public static function send($mailTo, $subject, $msgBody, $fromMail = null, $fromName = null, $replyTo = null, $attachments = null)
    {
        // Init mailer
        ob_start();
        $mailer = new PHPMailer();

        // Get mailer settings
        $config = (object) require_once "config/mailconfig.php";

        // Setup smtp server
        $mailer->isSMTP();
        $mailer->Host = $config->host;
        $mailer->SMTPAuth = $config->auth;
        if ($mailer->SMTPAuth) {
            $mailer->Port = $config->port;
            $mailer->Username = $config->username;
            $mailer->Password = $config->password;
            $mailer->SMTPSecure = $config->secure;
        }

        // Setup mail body
        $fromMail = $mailer->From = $fromMail ?: $config->email;
        $mailer->FromName = $fromName ?: $config->company;
        $mailer->CharSet = "utf-8";
        $mailer->SMTPDebug = 2;
        $mailer->Debugoutput = function ($msg, $level)
        {
            if ($level > 1) {
                echo $msg;
            }
        };
        $mailer->Timeout = 15;
        $mailer->Subject = $subject;
        $mailer->Body = $msgBody;
        $mailer->isHTML(true);

        // Add a mailto address
        $mailer->clearAddresses();
        $mailer->addAddress($mailTo);

        // Add a replyto address if is there any
        if ($replyTo) {
            $mailer->clearReplyTos();
            $mailer->addReplyTo($replyTo);
        }

        // Add attachment(s) if is/are there any
        if ($attachments) {
            if (! is_array($attachments)) {
                $attachments = [
                    $attachments
                ];
            }
            $mailer->clearAttachments();
            foreach ($attachments as $path) {
                $mailer->addAttachment($path);
            }
        }

        // Send email then close connection
        $isSent = $mailer->send();
        $errorInfo = $mailer->ErrorInfo;
        $mailer->smtpClose();
        unset($mailer);
        $log = ob_get_clean();

        // Log results and return
        $sendmail = [
            "from" => $fromMail,
            "mailto" => $mailTo,
            "subject" => $subject,
            "body" => $msgBody,
            "success" => $isSent,
            "log" => $log,
            "error_info" => $errorInfo
        ];
        new logger("mail", $sendmail);

        return $sendmail;
    }
}

