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

if (isset($_GET['addform'])) {

    try {
        $sql = 'INSERT INTO Author SET Name = :Name, Email = :Email';
        $s = $pdo->prepare($sql);
        $s->bindvalue(':Name', $_POST['Name']);
        $s->bindvalue(':Email', $_POST['Email']);
        $s->execute();
    } catch (PDOException $e) {
        $error = 'Error adding submitted Author.';
        include "$_PATH[errorPath]";
        exit();
    }

    $AuthorID = $pdo->lastInsertId();

    if ($_POST['Password'] != '') {
        $Password = md5($_POST['Password'] . 'ijdb');

        try {
            $sql = 'UPDATE Author SET
				Password = :Password
				WHERE Author_ID = :ID';
            $s = $pdo->prepare($sql);
            $s->bindvalue(':Password', $Password);
            $s->bindvalue(':ID', $AuthorID);
            $s->execute();
        } catch (PDOException $e) {
            $error = 'Error setting Author Password.';
            include "$_PATH[errorPath]";
            exit();
        }
    }

    if (isset($_POST['Roles'])) {
        foreach ($_POST['Roles'] as $Role) {
            try {
                $sql = 'INSERT INTO AuthorRole SET
				AuthorID = :AuthorID,
				RoleID = :RoleID';
                $s = $pdo->prepare($sql);
                $s->bindValue(':AuthorID', $AuthorID);
                $s->bindValue(':RoleID', $Role);
                $s->execute();
            } catch (PDOException $e) {
                $error = 'Error assigning selected Role to Author.';
                include "$_PATH[errorPath]";
                exit();
            }
        }
    }

    header('Location: /views/admin/author/');
    exit();
}

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
        include "$_PATH[errorPath]";
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
