<?php
include_once $_SERVER['DOCUMENT_ROOT'] . "/path.php";


if (isset($_GET['action']) and $_GET['action'] == 'search') {
    include "$_PATH[databasePath]";

    $select = 'SELECT *, Author.Name FROM';
    $from = ' Idea INNER JOIN Author ON Idea.AuthorID = Author.Author_ID';
    $where = ' WHERE TRUE';
    $placeholders = array();
    $orderby = ' IdeaDate DESC';

    if ($_GET['Author'] != '') {
        $where .= " AND AuthorID = :AuthorID";
        $placeholders[':AuthorID'] = $_GET['Author'];
    }

    if ($_GET['Category'] != '') {
        $from .= ' INNER JOIN IdeaCategory ON ID= IdeaID';
        $where .= " AND CategoryID = :CategoryID";
        $placeholders[':CategoryID'] = $_GET['Category'];
    }

    if ($_GET['text'] != '') {
        $where .= " AND LOWER(IdeaText) LIKE LOWER(:IdeaText)";
        $placeholders[':IdeaText'] = '%' . $_GET['text'] . '%';
    }

    if (isset($_POST['limitData'])) {
        $limit = $_POST["limitData"];
    }

    $limit = isset($_POST["limit-records"]) ? $_POST["limit-records"] : 10;
    $page = isset($_GET['page']) ? $_GET['page'] : 1;
    $start = ($page - 1) * $limit;

    try {
        $result = $pdo->query("SELECT count(ID) AS id FROM Idea");
        $ideasCount = $result->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error = 'Error fetching ideas count';
        include "$_PATH[errorPath]";
        exit();
    }

    $total = $ideasCount[0]['id'];
    $pages = ceil($total / $limit);

    $Previous = ($page == 1) ? 1 : $page - 1;
    $Next = ($page == $pages) ? $pages : $page + 1;

    try {
        $sql = $select . $from . $where . " LIMIT $start, $limit";
        $s = $pdo->prepare($sql);
        $s->execute($placeholders);
    } catch (PDOException $e) {
        $error = 'Error fetching ideas';
        include "$_PATH[errorPath]";
        exit();
    }
    foreach ($s as $row) {
        $ideas[] = array('ID' => $row['ID'], 'text' => $row['IdeaText'], 'Image' => $row['Image'], 'IdeaDate' => $row['IdeaDate'], 'Name' => $row['Name'], 'Vote' => $row['Vote'], 'AuthorID' => $row['AuthorID']);
    }


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

    include "$_PATH[ideasPath]";
    exit();
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
    $result = $pdo->query("SELECT count(ID) AS id FROM Idea");
    $ideasCount = $result->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = 'Error fetching ideas count';
    include "$_PATH[errorPath]";
    exit();
}

$total = $ideasCount[0]['id'];
$pages = ceil($total / $limit);

$Previous = ($page == 1) ? 1 : $page - 1;
$Next = ($page == $pages) ? $pages : $page + 1;

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
