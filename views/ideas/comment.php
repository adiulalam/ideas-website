<?php
include_once $_SERVER['DOCUMENT_ROOT'] . "/path.php";
include "$_PATH[databasePath]";

function time_elapsed_string($datetime, $full = false)
{
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}

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
    $ideas[] = array('ID' => $row['ID'], 'text' => $row['IdeaText'], 'Image' => $row['Image'], 'IdeaDate' => $row['IdeaDate'], 'Name' => $row['Name'], 'Email' => $row['Email'], 'Vote' => $row['Vote']);
    $Vote = $row['Vote'];
    $pageTitle = $row['IdeaText'];
    $emailAddress = $row['Email'];
}

$select = 'SELECT CommentID, Comment, Time, Author.Name FROM';
$from = ' Comment INNER JOIN Author ON Comment.AuthorID = Author.Author_ID';
$where = ' WHERE IdeaID = :ID';
$placeholders = array();
$placeholders[':ID'] = $ideaID;

try {
    $sql = "$select $from $where";
    $s = $pdo->prepare($sql);
    $s->execute($placeholders);
} catch (PDOException $e) {
    $error = 'Error fetching comments';
    include "$_PATH[errorPath]";
    exit();
}
foreach ($s as $row) {
    $comments[] = array('CommentID' => $row['CommentID'], 'Comment' => $row['Comment'], 'Time' => $row['Time'], 'Name' => $row['Name']);
}



include "$_PATH[commentPath]";
exit();
