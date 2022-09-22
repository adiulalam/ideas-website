<?php
include_once $_SERVER['DOCUMENT_ROOT'] . "/path.php";
include "$_PATH[databasePath]";

function time_elapsed_string($datetime)
{
    $etime = time() - strtotime($datetime);

    if ($etime < 1) {
        return 'less than ' . $etime . ' second ago';
    }

    $a = array(
        12 * 30 * 24 * 60 * 60  =>  'year',
        30 * 24 * 60 * 60       =>  'month',
        24 * 60 * 60            =>  'day',
        60 * 60             =>  'hour',
        60                  =>  'minute',
        1                   =>  'second'
    );

    foreach ($a as $secs => $str) {
        $d = $etime / $secs;

        if ($d >= 1) {
            $r = round($d);
            return 'about ' . $r . ' ' . $str . ($r > 1 ? 's' : '') . ' ago';
        }
    }
}

if (isset($_POST['postComment'])) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/views/auth/login/index.php';

    session_start();
    $authorID =  $_SESSION['aid'];
    $comment = $_POST['comment'];
    $ideaID = $_GET['ideaID'];

    if (!userIsLoggedIn() || !$authorID || !$ideaID) {
        $error = 'Error: You need to login to comment';
        include "$_PATH[errorPath]";
        exit();
    }

    if (!$comment) {
        $error = 'Error: Comment is empty';
        include "$_PATH[errorPath]";
        exit();
    }

    // try {
    //     $sql = 'INSERT INTO Comment SET Comment = :Comment, IdeaID= :IdeaID, AuthorID= :AuthorID ;';
    //     $s = $pdo->prepare($sql);
    //     $s->bindvalue(':Comment', $comment);
    //     $s->bindvalue(':IdeaID', $ideaID);
    //     $s->bindvalue(':AuthorID', $authorID);
    //     $s->execute();
    // } catch (PDOException $e) {
    //     $error = 'Error inserting Comment';
    //     include "$_PATH[errorPath]";
    //     exit();
    // }

    // $time = '2022-09-22 00:04:52';
    $date = new DateTime('Europe/London');
    $result = $date->format('Y-m-d H:i:s');

    echo $result;
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
$orderby = "ORDER BY Time DESC";
$placeholders = array();
$placeholders[':ID'] = $ideaID;

try {
    $sql = "$select $from $where $orderby";
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
