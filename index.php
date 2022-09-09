<?php
include_once $_SERVER['DOCUMENT_ROOT'] . "/path.php";


if (isset($_GET['addform'])) {
    include "$_PATH[databasePath]";


    $text = $_POST['text'];
    $Author = $_POST['Author'];
    $Category = $_POST['categories'];
    $Department = $_POST['departments'];


    // $message = json_encode($clean);
    // $message = json_encode($clean);
    // generateAlert($message);
    // echo "<script type='text/javascript'>alert('$message');</script>";

    if ($text == '' || $Author == '' || $Category == '' || $Department == '') {
        $error = 'Error: Field is empty';
        include "$_PATH[errorPath]";
        exit();
    }

    exit();

    // if ($_POST['Author'] == '' || $_POST['text'] == '' || $_POST['Email'] == '') {
    //     $error = 'You must choose an author or enter text for this idea, Click back and try again';
    //     include 'error.html.php';
    //     exit();
    // }

    /*****************************************************/
    //Insert documents

    // date time
    $date = new DateTime();
    $datetime = $date->format('-Y-m-d H:i:s');

    // name of the uploaded file
    $filename = $_FILES['myfile']['name'];

    // destination of the file on the server
    $destination = '../documents/';

    // get the file extension
    $extension = pathinfo($filename, PATHINFO_EXTENSION);

    $filename =  str_replace('.' . $extension, '', $filename);

    // the physical file on a temporary uploads directory on the server
    $file = $_FILES['myfile']['tmp_name'];

    //new file name with date
    $Document = $filename . $datetime . '.' . $extension;

    try {
        move_uploaded_file($file, $destination . $Document);

        require_once '../admin/includes/HTMLPurifier.standalone.php';
        // $purifier = new HTMLPurifier();
        // $clean = $purifier->purify($_POST['text']);
        $clean = $_POST['text'];


        $sql = 'INSERT INTO Idea SET
        IdeaText=:IdeaText,
        IdeaDate=CURDATE(),
        AuthorID=:AuthorID,
        Document=:Document';
        $s = $pdo->prepare($sql);
        $s->bindvalue(':IdeaText', $clean);
        $s->bindvalue(':AuthorID', $_POST['Author']);
        $s->bindvalue(':Document', $Document);
        $s->execute();
    } catch (PDOException $e) {
        $error = 'Error adding submitted idea';
        include 'error.html.php';
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
            include 'error.html.php';
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
            include 'error.html.php';
            exit();
        }
    }
}

if (isset($_GET['action']) and $_GET['action'] == 'addIdea') {
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


if (isset($_GET['action']) and $_GET['action'] == 'search') {
    include "$_PATH[searchIdeasPath]";
    searchIdeas();
}

include "$_PATH[databasePath]";
try {
    $result = $pdo->query('SELECT Author_ID, Name FROM Author');
} catch (PDOException $e) {
    $error = 'Error fetching authors from the database';
    include "$_PATH[errorPath]";
    exit();
}

foreach ($result as $row) {
    $authors[] = array('ID' => $row['Author_ID'], 'Name' => $row['Name']);
}

try {
    $result = $pdo->query('SELECT ID, Name FROM Category');
} catch (PDOException $e) {
    $error = 'Error fetching categories from the database';
    include "$_PATH[errorPath]";
    exit();
}
foreach ($result as $row) {
    $categories[] = array('ID' => $row['ID'], 'Name' => $row['Name']);
}

$limit = isset($_POST["limit-records"]) ? $_POST["limit-records"] : 10;
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$start = ($page - 1) * $limit;

try {
    $sql = "SELECT count(ID) AS id FROM Idea";
    $s = $pdo->prepare($sql);
    $s->execute(array());
} catch (PDOException $e) {
    $error = 'Error fetching ideas count';
    include "$_PATH[errorPath]";
    exit();
}
foreach ($s as $row) {
    $total[] = array('id' => $row['id']);
}

$total = $total[0]['id'];
$pages = ceil($total / $limit);

$previous = ($page == 1) ? 1 : $page - 1;
$next = ($page == $pages) ? $pages : $page + 1;

$orderby = ' IdeaDate DESC';

if (isset($_POST["orderBy"])) {
    $orderby = $_POST["orderBy"];
}

try {
    $sql = "SELECT *, Author.Name FROM Idea INNER JOIN Author 
    ON Idea.AuthorID = Author.Author_ID ORDER BY $orderby LIMIT $start, $limit ";

    $s = $pdo->prepare($sql);
    $s->execute(array());
} catch (PDOException $e) {
    $error = 'Error fetching ideas';
    include "$_PATH[errorPath]";
    exit();
}
foreach ($s as $row) {
    $ideas[] = array('ID' => $row['ID'], 'text' => $row['IdeaText'], 'Image' => $row['Image'], 'IdeaDate' => $row['IdeaDate'], 'Name' => $row['Name'], 'Vote' => $row['Vote']);
    $Vote = $row['Vote'];
}

include "$_PATH[ideasPath]";
