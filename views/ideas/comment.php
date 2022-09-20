<?php
include_once $_SERVER['DOCUMENT_ROOT'] . "/path.php";
include "$_PATH[databasePath]";

$ideaID = $_GET['ideaID'];
$pageTitle = 'Comment';

$select = 'SELECT ID, IdeaText, IdeaDate, Image, Vote, Document, AuthorID, Author.Name, Author.Email, Author.Author_ID FROM';
$from = ' Idea INNER JOIN Author ON Idea.AuthorID = Author.Author_ID';
$where = ' WHERE ID = :ID';
$limit = "LIMIT 1";
$placeholders = array();
$placeholders[':ID'] = $ideaID;

try {
    $sql = "$select $from $where $limit";
    $s = $pdo->prepare($sql);
    $s->execute($placeholders);
} catch (PDOException $e) {
    $error = 'Error fetching ideas';
    include "$_PATH[errorPath]";
    exit();
}
foreach ($s as $row) {
    $ideas[] = array('ID' => $row['ID'], 'text' => $row['IdeaText'], 'Image' => $row['Image'], 'IdeaDate' => $row['IdeaDate'], 'Name' => $row['Name'], 'Vote' => $row['Vote']);
    $Vote = $row['Vote'];
    $pageTitle = $row['IdeaText'];
}




include "$_PATH[commentPath]";
exit();
