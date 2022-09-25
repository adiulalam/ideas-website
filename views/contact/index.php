<?php
include_once $_SERVER['DOCUMENT_ROOT'] . "/path.php";

if (isset($_POST['action']) and $_POST['action'] == 'contactMe') {
    include_once $_SERVER['DOCUMENT_ROOT'] . "/environment.php";
    include "$_PATH[phpMailer]";
    require_once "$_PATH[purifierPath]";

    $purifier = new HTMLPurifier();
    $message = $purifier->purify($_POST['message']);
    $name = $_POST['name'];
    $subject = $_POST['subject'];
    $email = $_POST['email'];

    if ($message == '' || $name == '' || $email == '') {
        $error = 'Error: Field is empty';
        include "$_PATH[errorPath]";
        exit();
    }

    $secretKey = $_ENV["CAPTCHA_SECRET_KEY"];
    $responseKey = $_POST['g-recaptcha-response'];
    $userIP = $_SERVER['REMOTE_ADDR'];

    $url = "https://www.google.com/recaptcha/api/siteverify?secret=$secretKey&response=$responseKey&remoteip=$userIP";
    $response = file_get_contents($url);
    $response = json_decode($response);

    if (!$response->success) {
        $error = 'Error: Captcha Failed';
        include "$_PATH[errorPath]";
        return FALSE;
    }

    //Send Mail Function
    $to = $email;
    $subject = $subject;
    $body = "From $name, <br> $message";
    mailer($to, $subject, $body);

    header('Location:/');
}

include "$_PATH[contactPath]";
