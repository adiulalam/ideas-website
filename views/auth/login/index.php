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

        // $secretKey = $_ENV["CAPTCHA_SECRET_KEY"];
        // $responseKey = $_POST['g-recaptcha-response'];
        // $userIP = $_SERVER['REMOTE_ADDR'];

        // $url = "https://www.google.com/recaptcha/api/siteverify?secret=$secretKey&response=$responseKey&remoteip=$userIP";
        // $response = file_get_contents($url);
        // $response = json_decode($response);

        // if (!$response->success) {
        //     $message = 'Error: Captcha Failed';
        //     generateAlert($message);
        //     return FALSE;
        // }

        $Password = md5($Password . 'ijdb');
        if (databaseContainsAuthor($Email, $Password)) {

            setcookie("loggedIn", true, time() + 3600, "/");
            $_COOKIE['loggedIn'] = true;

            setcookie("Password", $Password, time() + 3600, "/");
            $_COOKIE['Password'] = $Password;

            setcookie("Email", $Email, time() + 3600, "/");
            $_COOKIE['Email'] = $Email;

            customer();

            // header('Location: ' . '/');
            return TRUE;
        } else {
            unset($_COOKIE['loggedIn']);
            setcookie('loggedIn', false, time() - 3600, '/');

            unset($_COOKIE['Email']);
            setcookie("Email", "", time() - 3600, "/");

            unset($_COOKIE['Password']);
            setcookie("Password", "", time() - 3600, "/");

            unset($_COOKIE['authorRole']);
            setcookie("authorRole", "", time() + 3600, "/");

            unset($_COOKIE['aid']);
            setcookie("aid", "", time() + 3600, "/");

            $error = 'The specified email address or password was incorrect.';
            generateAlert($error);
            return FALSE;
        }
    }
    if (isset($_POST['action']) and $_POST['action'] == 'logout') {

        unset($_COOKIE['loggedIn']);
        setcookie('loggedIn', null, time() - 3600, '/');

        unset($_COOKIE['Email']);
        setcookie("Email", null, time() - 3600, "/");

        unset($_COOKIE['Password']);
        setcookie("Password", null, time() - 3600, "/");

        unset($_COOKIE['authorRole']);
        setcookie("authorRole", "", time() + 3600, "/");

        unset($_COOKIE['aid']);
        setcookie("aid", null, time() + 3600, "/");

        header('Location: ' . '/');
        exit();
    }

    if (isset($_COOKIE['loggedIn'])) {
        return databaseContainsAuthor($_COOKIE['Email'], $_COOKIE['Password']);
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
        $s->bindValue(':Email', $_COOKIE['Email']);
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
        $s->bindValue(':Email', $_COOKIE['Email']);
        $s->bindValue(':Password', $_COOKIE['Password']);
        $s->execute();
    } catch (PDOException $e) {
        $error = 'Error selecting customers';
        generateAlert($error);
        exit();
    }
    $row = $s->fetch();

    setcookie("aid", $row['Author_ID'], time() + 3600, "/");
    $_COOKIE['aid'] = $row['Author_ID'];

    try {
        $sql = "SELECT RoleID FROM AuthorRole WHERE AuthorID = :AuthorID";
        $s = $pdo->prepare($sql);
        $s->bindValue(':AuthorID', $_COOKIE['aid']);
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

    setcookie("authorRole", json_encode($totalAuthorRoles), time() + 3600, "/");
    $_COOKIE['authorRole'] = json_encode($totalAuthorRoles);
}
