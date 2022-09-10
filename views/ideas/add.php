<?php
function ideasAddForm()
{
    include $_SERVER['DOCUMENT_ROOT'] . "/path.php";
    include "$_PATH[databasePath]";

    $pageTitle = 'New Idea';
    $action = 'addform';
    $text = '';
    $AuthorID = '';
    $ID = '';
    $button = 'Add Idea';

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

    //build list of departments    
    try {
        $result = $pdo->query('SELECT ID, Name FROM Department');
    } catch (PDOException $e) {
        $error = 'Error fetching list of department';
        include "$_PATH[errorPath]";
        exit();
    }

    foreach ($result as $row) {
        $departments[] = array('ID' => $row['ID'], 'Name' => $row['Name'], 'selected' => FALSE);
    }


    //build list of categories    
    try {
        $result = $pdo->query('SELECT ID, Name FROM Category');
    } catch (PDOException $e) {
        $error = 'Error fetching list of categories';
        include "$_PATH[errorPath]";
        exit();
    }

    foreach ($result as $row) {
        $categories[] = array('ID' => $row['ID'], 'Name' => $row['Name'], 'selected' => FALSE);
    }

    include "$_PATH[ideasFormPath]";
    exit();
}

function ideasAddSubmit()
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

    $date = new DateTime();
    $datetime = $date->format('Y-m-d H:i:s');

    $filename = $_FILES['myfile']['name'];

    $destination = $_SERVER['DOCUMENT_ROOT'] . '/assets/img/';

    $extension = pathinfo($filename, PATHINFO_EXTENSION);

    if ($extension && ($extension != 'jpg' || !$extension != 'png')) {
        $error = 'Error: Wrong file format';
        include "$_PATH[errorPath]";
        exit();
    }

    $Document = $datetime . $filename;

    $file = $_FILES['myfile']['tmp_name'];
    move_uploaded_file($file, $destination . $Document);

    try {
        $sql = 'INSERT INTO Idea SET
        IdeaText=:IdeaText,
        IdeaDate=CURDATE(),
        AuthorID=:AuthorID,
        Document=:Document';
        $s = $pdo->prepare($sql);
        $s->bindvalue(':IdeaText', $text);
        $s->bindvalue(':AuthorID', $_POST['Author']);
        $s->bindvalue(':Document', $Document);
        $s->execute();
    } catch (PDOException $e) {
        $error = 'Error adding submitted idea';
        include "$_PATH[errorPath]";
        exit();
    }

    $IdeaID = $pdo->lastInsertId();


    /*****************************************************/
    //Insert record into ideacategory table --category

    if (isset($_POST['categories'])) {
        try {
            $sql = 'INSERT INTO IdeaCategory SET
        IdeaID=:IdeaID,
        CategoryID=:CategoryID';
            $s = $pdo->prepare($sql);
            foreach ($_POST['categories'] as $CategoryID) {
                $s->bindvalue(':IdeaID', $IdeaID);
                $s->bindvalue(':CategoryID', $CategoryID);
                $s->execute();
            }
        } catch (PDOException $e) {
            $error = 'Error inserting idea into selected categories';
            include "$_PATH[errorPath]";
            exit();
        }
    }

    /*****************************************************/
    //Insert record into ideadepartment table --department

    if (isset($_POST['departments'])) {
        try {
            $sql = 'INSERT INTO IdeaDepartment SET
        IdeaID=:IdeaID,
        DepartmentID=:DepartmentID';
            $s = $pdo->prepare($sql);

            foreach ($_POST['departments'] as $DepartmentID) {
                $s->bindvalue(':IdeaID', $IdeaID);
                $s->bindvalue(':DepartmentID', $DepartmentID);
                $s->execute();
            }
        } catch (PDOException $e) {
            $error = 'Error inserting idea into selected departments';
            include "$_PATH[errorPath]";
            exit();
        }
    }
}
