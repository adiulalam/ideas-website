<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;


function mailer($to, $subject, $body)
{
    include_once $_SERVER['DOCUMENT_ROOT'] . "/environment.php";
    include_once $_SERVER['DOCUMENT_ROOT'] . "/path.php";
    include_once "$_PATH[alertPath]";

    require $_SERVER['DOCUMENT_ROOT'] . '/connection/includes/standalone/phpmailer/PHPMailer.php';
    require $_SERVER['DOCUMENT_ROOT'] . '/connection/includes/standalone/phpmailer/Exception.php';
    require $_SERVER['DOCUMENT_ROOT'] . '/connection/includes/standalone/phpmailer/SMTP.php';

    $mail = new PHPMailer(true);

    try {
        //Server settings
        $mail->SMTPDebug = SMTP::DEBUG_OFF;                      //Enable verbose debug output
        $mail->isSMTP();                                            //Send using SMTP
        $mail->Host       = 'smtp.gmail.com';                     //Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
        $mail->Username   = $_ENV["EMAIL"];                     //SMTP username
        $mail->Password   = $_ENV["EMAIL_PASSWORD"];                               //SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;            //Enable implicit TLS encryption
        $mail->Port       = 587;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

        //Recipients
        $mail->setFrom($_ENV["EMAIL"], 'Adiul Alam Adil');
        $mail->addAddress($to);               //Name is optional

        //Content
        $mail->isHTML(true);                                  //Set email format to HTML
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
    } catch (Exception $e) {
        $message = "Message could not be sent.";
        generateAlert($message);
        exit();
    }
};
