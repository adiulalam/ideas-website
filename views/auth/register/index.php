
<?php
include_once $_SERVER['DOCUMENT_ROOT'] . "/environment.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/path.php";

include $_SERVER['DOCUMENT_ROOT'] . "/views/auth/register/register.html.php";

if (isset($_POST['action']) and $_POST['action'] == 'register') {
    // $Name = $_POST['Name'];
    // $Email = $_POST['Email'];
    // $Password = $_POST['Password'];
    // $ConfirmPassword = $_POST['ConfirmPassword'];

    // echo "<script type='text/javascript'>alert('$Name');</script>";

    include $_SERVER['DOCUMENT_ROOT'] . '/components/modal.html.php';

    // $secretKey = $_ENV["CAPTCHA_SECRET_KEY"];
    // $responseKey = $_POST['g-recaptcha-response'];
    // $userIP = $_SERVER['REMOTE_ADDR'];

    // $url = "https://www.google.com/recaptcha/api/siteverify?secret=$secretKey&response=$responseKey&remoteip=$userIP";
    // $response = file_get_contents($url);
    // $response = json_decode($response);

    // if ($response->success) {
    //     include "$_PATH[databasePath]";

    //     try {
    //         $sql = 'INSERT INTO Author SET Name = :Name, Email = :Email, Password= :Password';
    //         $s = $pdo->prepare($sql);
    //         $s->bindvalue(':Name', $_POST['Name']);
    //         $s->bindvalue(':Email', $_POST['Email']);
    //         $s->bindvalue(':Password', md5($_POST['Password'] . 'ijdb'));
    //         $s->execute();
    //     } catch (PDOException $e) {
    //         $error = 'Error adding submitted Author.';
    //         include "$_PATH[errorPath]";
    //         exit();
    //     }

    //     $AuthorID = $pdo->lastInsertId();
    //     try {
    //         $sql = 'INSERT INTO AuthorRole SET AuthorID= :AuthorID, RoleID= :RoleID';
    //         $s = $pdo->prepare($sql);
    //         $s->bindValue(':AuthorID', $AuthorID);
    //         $s->bindValue(':RoleID', 'User');
    //         $s->execute();
    //     } catch (PDOException $e) {
    //         $error = 'Error assigning selected role to author.';
    //         include "$_PATH[errorPath]";
    //         exit();
    //     }

    //     // include 'welcome.html.php';
    // } else {
    //     echo "Verification failed!";
    // }
}
?>