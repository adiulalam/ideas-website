
<?php
include_once $_SERVER['DOCUMENT_ROOT'] . "/environment.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/path.php";

include $_SERVER['DOCUMENT_ROOT'] . "/views/auth/register/register.html.php";

if (isset($_POST['action']) and $_POST['action'] == 'register') {
    include $_SERVER['DOCUMENT_ROOT'] . '/components/alert.html.php';

    $Name = $_POST['Name'];
    $Email = $_POST['Email'];
    $Password = $_POST['Password'];
    $ConfirmPassword = $_POST['ConfirmPassword'];

    if ($Password != $ConfirmPassword) {
        $message = 'Error: Password not matched';
        generateAlert($message);
        exit();
    }

    $secretKey = $_ENV["CAPTCHA_SECRET_KEY"];
    $responseKey = $_POST['g-recaptcha-response'];
    $userIP = $_SERVER['REMOTE_ADDR'];

    $url = "https://www.google.com/recaptcha/api/siteverify?secret=$secretKey&response=$responseKey&remoteip=$userIP";
    $response = file_get_contents($url);
    $response = json_decode($response);

    if (!$response->success) {
        $message = 'Error: Captcha Failed';
        generateAlert($message);
        exit();
    }

    include "$_PATH[databasePath]";
    try {
        $sql = 'INSERT INTO Author SET Name = :Name, Email = :Email, Password= :Password';
        $s = $pdo->prepare($sql);
        $s->bindvalue(':Name', $Name);
        $s->bindvalue(':Email', $Email);
        $s->bindvalue(':Password', md5($Password . 'ijdb'));
        $s->execute();
    } catch (PDOException $e) {
        $error = 'Error adding submitted Author/User.';
        generateAlert($error);
        // include "$_PATH[errorPath]";
        exit();
    }

    $AuthorID = $pdo->lastInsertId();
    try {
        $sql = 'INSERT INTO AuthorRole SET AuthorID= :AuthorID, RoleID= :RoleID';
        $s = $pdo->prepare($sql);
        $s->bindValue(':AuthorID', $AuthorID);
        $s->bindValue(':RoleID', 'User');
        $s->execute();
    } catch (PDOException $e) {
        $error = 'Error assigning selected role to author.';
        include "$_PATH[errorPath]";
        exit();
    }

    $message = 'Success: Sign up completed, please check your email - Redirecting to Login page';
    generateAlert($message);

    sleep(5);
    echo "<script>location.href='/';</script>";
}
?>