<?php
error_reporting(0);
include_once $_SERVER['DOCUMENT_ROOT'] . "/environment.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/path.php";
include "$_PATH[alertPath]";

function userIsLoggedIn()
{
    if (isset($_POST['action']) and $_POST['action'] == 'login') {
        $Email = $_POST['Email'];
        $Password = $_POST['Password'];

        if ($Email == '' || $Password == '') {
            $error = 'Please fill in both fields';
            generateAlert($error);
            return FALSE;
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
            return FALSE;
        }

        $Password = md5($Password . 'ijdb');
        if (databaseContainsAuthor($Email, $Password)) {
            session_start();
            $_SESSION['loggedIn'] = TRUE;
            $_SESSION['Email'] = $Email;
            $_SESSION['Password'] = $Password;
            customer();
            header('Location: ' . '/');
            return TRUE;
        } else {
            session_start();
            unset($_SESSION['loggedIn']);
            unset($_SESSION['Email']);
            unset($_SESSION['Password']);
            unset($_SESSION['authorRole']);
            $error = 'The specified email address or password was incorrect.';
            generateAlert($error);
            return FALSE;
        }
    }
    if (isset($_POST['action']) and $_POST['action'] == 'logout') {
        session_start();
        unset($_SESSION['loggedIn']);
        unset($_SESSION['Email']);
        unset($_SESSION['Password']);
        unset($_SESSION['authorRole']);
        header('Location: ' . '/');
        exit();
    }
    session_start();
    if (isset($_SESSION['loggedIn'])) {
        return databaseContainsAuthor($_SESSION['Email'], $_SESSION['Password']);
    }
}
function databaseContainsAuthor($Email, $Password)
{
    include $_SERVER['DOCUMENT_ROOT'] . "/environment.php";
    include $_SERVER['DOCUMENT_ROOT'] . "/path.php";
    include "$_PATH[databasePath]";
    try {
        $sql = 'SELECT COUNT(*) FROM Author
        WHERE Email = :Email AND Password = :Password';
        $s = $pdo->prepare($sql);
        $s->bindValue(':Email', $Email);
        $s->bindValue(':Password', $Password);
        $s->execute();
    } catch (PDOException $e) {
        $error = 'Error searching for Author.';
        generateAlert($error);
        exit();
    }
    $row = $s->fetch();
    if ($row[0] > 0) {
        return TRUE;
    } else {
        return FALSE;
    }
}
function userHasRole($Role)
{
    include $_SERVER['DOCUMENT_ROOT'] . "/path.php";
    include "$_PATH[databasePath]";
    try {
        $sql = "SELECT COUNT(*) FROM Author
        INNER JOIN AuthorRole ON Author.Author_ID = AuthorID
        INNER JOIN Role ON RoleID = Role.ID
        WHERE Email = :Email AND Role.ID = :RoleID";
        $s = $pdo->prepare($sql);
        $s->bindValue(':Email', $_SESSION['Email']);
        $s->bindValue(':RoleID', $Role);
        $s->execute();
    } catch (PDOException $e) {
        $error = 'Error searching for Author roles.';
        generateAlert($error);
        exit();
    }
    $row = $s->fetch();
    if ($row[0] > 0) {
        return TRUE;
    } else {
        return FALSE;
    }
}

function customer()
{
    include $_SERVER['DOCUMENT_ROOT'] . "/environment.php";
    include $_SERVER['DOCUMENT_ROOT'] . "/path.php";
    include "$_PATH[databasePath]";
    try {
        $sql = "SELECT Author_ID FROM Author WHERE Email = :Email AND Password = :Password";
        $s = $pdo->prepare($sql);
        $s->bindValue(':Email', $_SESSION['Email']);
        $s->bindValue(':Password', $_SESSION['Password']);
        $s->execute();
    } catch (PDOException $e) {
        $error = 'Error selecting customers';
        generateAlert($error);
        exit();
    }
    $row = $s->fetch();
    $_SESSION['aid'] = $row['Author_ID'];

    try {
        $sql = "SELECT RoleID FROM AuthorRole WHERE AuthorID = :AuthorID";
        $s = $pdo->prepare($sql);
        $s->bindValue(':AuthorID', $_SESSION['aid']);
        $s->execute();
    } catch (PDOException $e) {
        $error = 'Error selecting author role';
        generateAlert($error);
        exit();
    }
    $totalAuthorRoles = array();
    foreach ($s as $row) {
        $totalAuthorRoles[] = $row['RoleID'];
    }
    $_SESSION['authorRole'] = $totalAuthorRoles;
}
