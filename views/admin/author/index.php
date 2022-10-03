<?php
include_once $_SERVER['DOCUMENT_ROOT'] . "/environment.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/path.php";
require_once $_SERVER['DOCUMENT_ROOT'] . '/views/auth/login/index.php';

if (!userIsLoggedIn()) {
    include $_SERVER['DOCUMENT_ROOT'] . '/views/auth/login/login.html.php';
    exit();
}

if (!(userHasRole("Site Administrator") || (userHasRole("Account Administrator")))) {
    header('Location: /');
    exit();
}

include "$_PATH[databasePath]";

if (isset($_GET['action']) and $_GET['action'] == 'addContent') {
    // include "$_PATH[addIdeasPath]";
    // ideasAddForm();

    $pageTitle = 'New Author';
    $action = 'addform';
    $Name = '';
    $Email = '';
    $ID = '';
    $button = 'Add Author';

    try {
        $result = $pdo->query('SELECT ID, Description FROM Role');
    } catch (PDOException $e) {
        $error = 'Error fetching list of Roles.';
        include 'error.html.php';
        exit();
    }
    foreach ($result as $row) {
        $Roles[] = array('ID' => $row['ID'], 'Description' => $row['Description'], 'selected' => FALSE);
    }

    include 'form.html.php';
    exit();
}

try {
    $result = $pdo->query("SELECT Author_ID, Name, Email, Author_Image, LoginTime From Author");
} catch (PDOException $e) {
    $error = 'Error fetching authors from the database';
    include "$_PATH[errorPath]";
    exit();
}

foreach ($result as $row) {
    $authors[] = array('ID' => $row['Author_ID'], 'Name' => $row['Name'], 'Email' => $row['Email'], 'Image' => $row['Author_Image'], 'LoginTime' => $row['LoginTime']);
}

include 'authors.html.php';
