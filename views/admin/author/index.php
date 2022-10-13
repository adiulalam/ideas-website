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

    $AuthorID = $_POST['ID'];

    try {
        $sql = 'DELETE FROM AuthorRole WHERE AuthorID = :ID';
        $s = $pdo->prepare($sql);
        $s->bindValue(':ID', $AuthorID);
        $s->execute();
    } catch (PDOException $e) {
        $error = 'Error removing Author from Roles.';
        include "$_PATH[errorPath]";
        exit();
    }

    try {
        $sql = 'DELETE FROM Author WHERE Author_ID = :ID';
        $s = $pdo->prepare($sql);
        $s->bindvalue(':ID', $AuthorID);
        $s->execute();
    } catch (PDOException $e) {
        $error = 'Error deleting Author.';
        include "$_PATH[errorPath]";
        exit();
    }

    try {
        $sql = "DELETE FROM Idea WHERE AuthorID = :ID";
        $s = $pdo->prepare($sql);
        $s->bindvalue(':ID', $AuthorID);
        $s->execute();
    } catch (PDOException $e) {
        $error = 'Error deleting ideas of author';
        include "$_PATH[errorPath]";
        exit();
    }

    try {
        $sql = "DELETE FROM Comment WHERE AuthorID = :ID";
        $s = $pdo->prepare($sql);
        $s->bindvalue(':ID', $AuthorID);
        $s->execute();
    } catch (PDOException $e) {
        $error = 'Error deleting comment of author';
        include "$_PATH[errorPath]";
        exit();
    }

    try {
        $sql = "DELETE FROM Vote WHERE AuthorID= :ID";
        $s = $pdo->prepare($sql);
        $s->bindvalue(':ID', $AuthorID);
        $s->execute();
    } catch (PDOException $e) {
        $error = 'Error deleting vote of author';
        include "$_PATH[errorPath]";
        exit();
    }

    header('Location: .');
    exit();
}

if (isset($_GET['editform'])) {
    $ID = $_POST['ID'];
    $Name = $_POST['Name'];
    $Email = $_POST['Email'];
    $Password = $_POST['Password'];
    $ConfirmPassword = $_POST['ConfirmPassword'];

    if ($Name == '' || $Email == '' || $ID == '' || !(isset($_POST['Roles']))) {
        $error = 'Error: Field is empty';
        include "$_PATH[errorPath]";
        exit();
    }

    if ($Password != '' || $ConfirmPassword != '') {

        if ($Password != $ConfirmPassword) {
            $error = 'Error: Password not matched';
            include "$_PATH[errorPath]";
            exit();
        }

        $Password = md5($Password . 'ijdb');
        try {
            $sql = 'UPDATE Author SET Password = :Password WHERE Author_ID = :ID';
            $s = $pdo->prepare($sql);
            $s->bindValue(':Password', $Password);
            $s->bindValue(':ID', $ID);
            $s->execute();
        } catch (PDOException $e) {
            $error = 'Error setting Author Password.';
            include "$_PATH[errorPath]";
            exit();
        }
    }

    try {
        $sql = 'UPDATE Author SET
		Name = :Name,
		Email = :Email WHERE Author_ID = :ID';
        $s = $pdo->prepare($sql);
        $s->bindvalue(':ID', $ID);
        $s->bindvalue(':Name', $Name);
        $s->bindvalue(':Email', $Email);
        $s->execute();
    } catch (PDOException $e) {
        $error = 'Error updating submitted Author.';
        include "$_PATH[errorPath]";
        exit();
    }

    try {
        $sql = 'DELETE FROM AuthorRole WHERE AuthorID = :ID';
        $s = $pdo->prepare($sql);
        $s->bindValue(':ID', $ID);
        $s->execute();
    } catch (PDOException $e) {
        $error = 'Error removing obsolete Author Role entries.';
        include "$_PATH[errorPath]";;
        exit();
    }
    if (isset($_POST['Roles'])) {
        foreach ($_POST['Roles'] as $Role) {
            try {
                $sql = 'INSERT INTO AuthorRole SET AuthorID = :AuthorID, RoleID = :RoleID';
                $s = $pdo->prepare($sql);
                $s->bindValue(':AuthorID', $ID);
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

if (isset($_POST['action']) and $_POST['action'] == 'Edit') {
    try {
        $sql = 'SELECT Author_ID, Name, Email FROM Author WHERE Author_ID = :ID';
        $s = $pdo->prepare($sql);
        $s->bindvalue(':ID', $_POST['ID']);
        $s->execute();
    } catch (PDOException $e) {
        $error = 'Error fetching Author details.';
        include "$_PATH[errorPath]";
        exit();
    }
    $row = $s->fetch();

    $pageTitle = 'Edit Author';
    $action = 'editform';
    $Name = $row['Name'];
    $Email = $row['Email'];
    $ID = $row['Author_ID'];
    $button = 'update Author';

    try {
        $sql = 'SELECT RoleID FROM AuthorRole WHERE AuthorID = :ID';
        $s = $pdo->prepare($sql);
        $s->bindValue(':ID', $ID);
        $s->execute();
    } catch (PDOException $e) {
        $error = 'Error fetching list of assigned Roles.';
        include "$_PATH[errorPath]";
        exit();
    }
    $selectedRoles = array();
    foreach ($s as $row) {
        $selectedRoles[] = $row['RoleID'];
    }

    try {
        $result = $pdo->query('SELECT ID, Description FROM Role');
    } catch (PDOException $e) {
        $error = 'Error fetching list of Roles.';
        include "$_PATH[errorPath]";
        exit();
    }
    foreach ($result as $row) {
        $Roles[] = array(
            'ID' => $row['ID'],
            'Description' => $row['Description'],
            'selected' => in_array($row['ID'], $selectedRoles)
        );
    }

    include 'form.html.php';
    exit();
}

if (isset($_GET['addform'])) {

    $Name = $_POST['Name'];
    $Email = $_POST['Email'];
    $Password = $_POST['Password'];
    $ConfirmPassword = $_POST['ConfirmPassword'];

    if ($Name == '' || $Email == '' || $Password == '' || $ConfirmPassword == '' || !(isset($_POST['Roles']))) {
        $error = 'Error: Field is empty';
        include "$_PATH[errorPath]";
        exit();
    }

    if ($Password != $ConfirmPassword) {
        $error = 'Error: Password not matched';
        include "$_PATH[errorPath]";
        exit();
    }

    try {
        $sql = 'INSERT INTO Author SET Name = :Name, Email = :Email, Password = :Password';
        $s = $pdo->prepare($sql);
        $s->bindvalue(':Name', $Name);
        $s->bindvalue(':Email', $Email);
        $s->bindvalue(':Password', md5($Password . 'ijdb'));
        $s->execute();
    } catch (PDOException $e) {
        $error = 'Error adding submitted Author.';
        include "$_PATH[errorPath]";
        exit();
    }

    $AuthorID = $pdo->lastInsertId();

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
