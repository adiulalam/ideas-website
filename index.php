<?php
include_once $_SERVER['DOCUMENT_ROOT'] . "/path.php";

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
