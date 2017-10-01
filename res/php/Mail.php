<?php
/**
 * Created by IntelliJ IDEA.
 * User: david
 * Date: 01.10.2017
 * Time: 14:00
 */

namespace Mail;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mail {

    public static function sendMail($to, $subject, $body, $altBody = "") {

        require 'PHPMailer/Exception.php';
        require 'PHPMailer/PHPMailer.php';
        require 'PHPMailer/SMTP.php';
        require "config.php";

        if(!isset($mailCfg)) {
            return false;
        }

        $mail = new PHPMailer(true);
        try {
            //$mail->SMTPDebug = 2;
            $mail->isSMTP();
            $mail->Host = $mailCfg["smtp"];
            $mail->SMTPAuth = true;
            $mail->Username = $mailCfg["username"];
            $mail->Password = $mailCfg["pw"];
            $mail->SMTPSecure = "tls";
            $mail->Port = 587;

            //Recipients
            $mail->setFrom($mailCfg["sender"], $mailCfg["senderName"]);
            foreach ($to as $toMail => $toName) {
                $mail->addAddress($toMail, $toName);
            }

            //Content
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->AltBody = $altBody;

            $mail->send();
            if($mailCfg["save"]) {
                Mail::save_mail($mail, $mailCfg);
            }
            return true;
        } catch(Exception $e) {
            //echo 'Message could not be sent.';
            //echo 'Mailer Error: ' . $mail->ErrorInfo;
            return false;
        }
    }

    public static function save_mail($mail, $cfg) {
        //You can change 'Sent Mail' to any other folder or tag
        $path = $cfg["imap"];
        //Tell your server to open an IMAP connection using the same username and password as you used for SMTP
        $imapStream = imap_open($path, $mail->Username, $mail->Password);
        $result = imap_append($imapStream, $path, $mail->getSentMIMEMessage());
        imap_close($imapStream);
        return $result;
    }

}