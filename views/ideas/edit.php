<?php
function ideasEditForm()
{
    include $_SERVER['DOCUMENT_ROOT'] . "/path.php";
    include "$_PATH[databasePath]";

    try {
        $sql = 'SELECT ID, IdeaText, AuthorID FROM Idea WHERE ID= :ID';
        $s = $pdo->prepare($sql);
        $s->bindvalue(':ID', $_POST['ID']);
        $s->execute();
    } catch (PDOException $e) {
        $error = 'Error fetching idea details';
        include "$_PATH[errorPath]";
        exit();
    }

    $row = $s->fetch();
    $pageTitle = 'Edit Idea';
    $action = 'editform';
    $text = $row['IdeaText'];
    $AuthorID = $row['AuthorID'];
    $ID = $row['ID'];
    $button = 'Update Idea';

    //build list of authors
    try {
        $result = $pdo->query('SELECT Author_ID, Name FROM Author');
    } catch (PDOException $e) {
        $error = 'Error fetching list of authors';
        include "$_PATH[errorPath]";
        exit();
    }

    foreach ($result as $row) {
        $authors[] = array('ID' => $row['Author_ID'], 'Name' => $row['Name']);
    }

    //get list of categories containing this idea
    try {
        $sql = 'SELECT CategoryID FROM IdeaCategory WHERE IdeaID= :ID';
        $s = $pdo->prepare($sql);
        $s->bindvalue(':ID', $ID);
        $s->execute();
    } catch (PDOException $e) {
        $error = 'Error fetching lists of selected categories';
        include "$_PATH[errorPath]";
        exit();
    }
    $selectedCategories = (array) null;
    foreach ($s as $row) {
        $selectedCategories[] = $row['CategoryID'];
    }

    try {
        $result = $pdo->query('SELECT ID, Name FROM Category');
    } catch (PDOException $e) {
        $error = 'Error fetching list of categories';
        include "$_PATH[errorPath]";
        exit();
    }

    foreach ($result as $row) {
        $categories[] = array('ID' => $row['ID'], 'Name' => $row['Name'], 'selected' => in_array($row['ID'], $selectedCategories));
    }

    //get list of departments containing this idea
    try {
        $sql = 'SELECT DepartmentID FROM IdeaDepartment WHERE IdeaID= :ID';
        $s = $pdo->prepare($sql);
        $s->bindvalue(':ID', $ID);
        $s->execute();
    } catch (PDOException $e) {
        $error = 'Error fetching lists of selected Departments';
        include "$_PATH[errorPath]";
        exit();
    }
    $selectedDepartments = (array) null;
    foreach ($s as $row) {
        $selectedDepartments[] = $row['DepartmentID'];
    }

    try {
        $result = $pdo->query('SELECT ID, Name FROM Department');
    } catch (PDOException $e) {
        $error = 'Error fetching list of Departments';
        include "$_PATH[errorPath]";
        exit();
    }
    foreach ($result as $row) {
        $departments[] = array('ID' => $row['ID'], 'Name' => $row['Name'], 'selected' => in_array($row['ID'], $selectedDepartments));
    }

    include "$_PATH[ideasFormPath]";
    exit();
}

function ideasEditSubmit()
{
    include $_SERVER['DOCUMENT_ROOT'] . "/path.php";
    include "$_PATH[databasePath]";
    require_once "$_PATH[purifierPath]";

    $purifier = new HTMLPurifier();
    $text = $purifier->purify($_POST['text']);
    $Author = $_POST['Author'];
    $Category = isset($_POST['categories']);
    $Department = isset($_POST['departments']);


    if ($text == '' || $Author == '' || $Category == '' || !(isset($Category)) || $Department == '' || !(isset($Department))) {
        $error = 'Error: Field is empty';
        include "$_PATH[errorPath]";
        exit();
    }

    try {
        $sql = 'UPDATE Idea SET
        IdeaText=:IdeaText,
        AuthorID=:AuthorID
        WHERE ID=:ID';
        $s = $pdo->prepare($sql);
        $s->bindvalue(':ID', $_POST['ID']);
        $s->bindvalue(':IdeaText', $text);
        $s->bindvalue(':AuthorID', $Author);
        $s->execute();
    } catch (PDOException $e) {
        $error = 'Error updating submitted idea';
        include "$_PATH[errorPath]";
        exit();
    }
    // Category
    try {
        $sql = 'DELETE FROM IdeaCategory WHERE IdeaID=:ID';
        $s = $pdo->prepare($sql);
        $s->bindvalue(':ID', $_POST['ID']);
        $s->execute();
    } catch (PDOException $e) {
        $error = 'Error removing obsolete idea category entries';
        include "$_PATH[errorPath]";
        exit();
    }
    if (isset($_POST['categories'])) {
        try {
            $sql = 'INSERT INTO IdeaCategory SET 
        IdeaID= :IdeaID, 
        CategoryID=:CategoryID';
            $s = $pdo->prepare($sql);
            foreach ($_POST['categories'] as $CategoryID) {
                $s->bindvalue(':IdeaID', $_POST['ID']);
                $s->bindvalue(':CategoryID', $CategoryID);
                $s->execute();
            }
        } catch (PDOException $e) {
            $error = 'Error inserting idea into selected categories';
            include "$_PATH[errorPath]";
            exit();
        }
    }

    // Department
    try {
        $sql = 'DELETE FROM IdeaDepartment WHERE IdeaID=:ID';
        $s = $pdo->prepare($sql);
        $s->bindvalue(':ID', $_POST['ID']);
        $s->execute();
    } catch (PDOException $e) {
        $error = 'Error removing obsolete idea Department entries';
        include "$_PATH[errorPath]";
        exit();
    }

    if (isset($_POST['departments'])) {
        try {
            $sql = 'INSERT INTO IdeaDepartment SET 
        IdeaID= :IdeaID, 
        DepartmentID=:DepartmentID';
            $s = $pdo->prepare($sql);
            foreach ($_POST['departments'] as $DepartmentID) {
                $s->bindvalue(':IdeaID', $_POST['ID']);
                $s->bindvalue(':DepartmentID', $DepartmentID);
                $s->execute();
            }
        } catch (PDOException $e) {
            $error = 'Error inserting idea into selected Department';
            include "$_PATH[errorPath]";
            exit();
        }
    }

    header('Location: /');
    exit();
}
