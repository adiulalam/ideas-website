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

if (isset($_POST['action']) and $_POST['action'] == 'Delete') {

    $CategoryID = $_POST['ID'];

    try {
        $sql = 'DELETE FROM IdeaCategory WHERE CategoryID = :ID';
        $s = $pdo->prepare($sql);
        $s->bindValue(':ID', $CategoryID);
        $s->execute();
    } catch (PDOException $e) {
        $error = 'Error removing category from ideacategory';
        include "$_PATH[errorPath]";
        exit();
    }

    try {
        $sql = 'DELETE FROM Category WHERE ID = :ID';
        $s = $pdo->prepare($sql);
        $s->bindvalue(':ID', $CategoryID);
        $s->execute();
    } catch (PDOException $e) {
        $error = 'Error deleting category';
        include "$_PATH[errorPath]";
        exit();
    }

    header('Location: .');
    exit();
}

if (isset($_GET['editform'])) {

    $ID = $_POST['ID'];
    $Name = $_POST['Name'];

    if ($Name == '') {
        $error = 'Error: Field is empty';
        include "$_PATH[errorPath]";
        exit();
    }

    try {
        $sql = "UPDATE Category SET Name = :Name WHERE ID = :ID";
        $s = $pdo->prepare($sql);
        $s->bindvalue(':ID', $ID);
        $s->bindvalue(':Name', $Name);
        $s->execute();
    } catch (PDOException $e) {
        $error = 'Error adding submitted category';
        include "$_PATH[errorPath]";
        exit();
    }

    header('Location: /views/admin/category/');
    exit();
}

if (isset($_POST['action']) and $_POST['action'] == 'Edit') {
    try {
        $sql = 'SELECT ID, Name FROM Category WHERE ID = :ID';
        $s = $pdo->prepare($sql);
        $s->bindvalue(':ID', $_POST['ID']);
        $s->execute();
    } catch (PDOException $e) {
        $error = 'Error fetching category details';
        include "$_PATH[errorPath]";
        exit();
    }
    $row = $s->fetch();

    $pageTitle = 'Edit Category';
    $action = 'editform';
    $Name = $row['Name'];
    $ID = $row['ID'];
    $button = 'Update Category';

    include 'form.html.php';
    exit();
}

if (isset($_GET['addform'])) {

    $Name = $_POST['Name'];

    if ($Name == '') {
        $error = 'Error: Field is empty';
        include "$_PATH[errorPath]";
        exit();
    }

    try {
        $sql = 'INSERT INTO Category SET Name=:Name';
        $s = $pdo->prepare($sql);
        $s->bindvalue(':Name', $Name);
        $s->execute();
    } catch (PDOException $e) {
        $error = 'Error adding submitted category.';
        include "$_PATH[errorPath]";
        exit();
    }

    header('Location: /views/admin/category/');
    exit();
}

if (isset($_GET['action']) and $_GET['action'] == 'addContent') {

    $pageTitle = 'New Category';
    $action = 'addform';
    $Name = '';
    $Email = '';
    $ID = '';
    $button = 'Add Category';

    include 'form.html.php';
    exit();
}

try {
    $result = $pdo->query("SELECT ID, Name, Image From Category");
} catch (PDOException $e) {
    $error = 'Error fetching categories from the database';
    include "$_PATH[errorPath]";
    exit();
}

foreach ($result as $row) {
    $categories[] = array('ID' => $row['ID'], 'Name' => $row['Name'], 'Image' => $row['Image']);
}

include 'categories.html.php';
