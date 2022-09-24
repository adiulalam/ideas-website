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
        60 * 60                 =>  'hour',
        60                      =>  'minute',
        1                       =>  'second'
    );

    foreach ($a as $secs => $str) {
        $d = $etime / $secs;

        if ($d >= 1) {
            $r = round($d);
            return 'about ' . $r . ' ' . $str . ($r > 1 ? 's' : '') . ' ago';
        }
    }
}

if (isset($_POST['action']) and $_POST['action'] == 'deleteComment') {

    include $_SERVER['DOCUMENT_ROOT'] . "/path.php";
    include "$_PATH[databasePath]";

    try {
        $commentID = $_POST['ID'];
        session_start();
        $authorID =  $_SESSION['aid'];
        $sql = "DELETE FROM Comment WHERE CommentID = :ID AND AuthorID = :AuthorID";
        $s = $pdo->prepare($sql);
        $s->bindvalue(':ID', $commentID);
        $s->bindvalue(':AuthorID', $authorID);
        $s->execute();
    } catch (PDOException $e) {
        $error = 'Error deleting comment from Idea';
        include "$_PATH[errorPath]";
        exit();
    }

    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit();
}

$ideaID = $_GET['ideaID'];
$pageTitle = 'Comment';

$select = 'SELECT ID, IdeaText, IdeaDate, Image, COALESCE(SUM(Vote.VoteNumber), 0) Vote, Document, Idea.AuthorID, Author.Name, Author.Email, Author.Author_ID FROM';
$from = ' Idea INNER JOIN Author ON Idea.AuthorID = Author.Author_ID LEFT JOIN Vote ON Idea.ID = Vote.IdeaID';
$where = ' WHERE ID = :ID';
$limit = "LIMIT 1";
$groupby = 'GROUP BY ID';
$placeholders = array();
$placeholders[':ID'] = $ideaID;

try {
    $sql = "$select $from $where $groupby $limit";
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

require_once $_SERVER['DOCUMENT_ROOT'] . '/views/auth/login/index.php';
if (userIsLoggedIn() && $_SESSION['aid']) {
    $authorID =  $_SESSION['aid'];
    $select = 'SELECT CommentID FROM';
    $from = ' Comment';
    $where = ' WHERE AuthorID = :AuthorID';
    $placeholders = array();
    $placeholders[':AuthorID'] = $authorID;
    try {
        $sql = "$select $from $where";
        $s = $pdo->prepare($sql);
        $s->execute($placeholders);
    } catch (PDOException $e) {
        $error = 'Error fetching Comments count for mutation';
        include "$_PATH[errorPath]";
        exit();
    }
    foreach ($s as $row) {
        $totalComments[] = $row['CommentID'];
    }
    try {
        $sql = "SELECT IdeaID, AuthorID, VoteNumber 
                FROM Vote WHERE AuthorID = $authorID
                AND IdeaID = $ideaID";
        $s = $pdo->prepare($sql);
        $s->execute(array());
    } catch (PDOException $e) {
        $error = 'Error fetching vote counts for logged in users on comment section';
        include "$_PATH[errorPath]";
        exit();
    }
    $totalIdeaVotes = array();
    foreach ($s as $row) {
        $ideaVoteCounts[] = array('IdeaID' => $row['IdeaID'], 'AuthorID' => $row['AuthorID'], 'VoteNumber' => $row['VoteNumber']);
        $totalIdeaVotes[] = $row['IdeaID'];
    }
}

if (isset($_POST['postComment'])) {
    include "$_PATH[phpMailer]";
    require_once $_SERVER['DOCUMENT_ROOT'] . '/views/auth/login/index.php';

    session_start();
    $authorID = $_SESSION['aid'];
    $comment = $_POST['comment'];
    $ideaID = $_GET['ideaID'];

    if (!userIsLoggedIn() || !$authorID || !$ideaID) {
        header('Location: ' . '/views/auth/login/login.html.php');
        exit();
    }

    if (!$comment) {
        $error = 'Error: Comment is empty';
        include "$_PATH[errorPath]";
        exit();
    }

    try {
        $sql = 'INSERT INTO Comment SET Comment = :Comment, IdeaID= :IdeaID, AuthorID= :AuthorID';
        $s = $pdo->prepare($sql);
        $s->bindvalue(':Comment', $comment);
        $s->bindvalue(':IdeaID', $ideaID);
        $s->bindvalue(':AuthorID', $authorID);
        $s->execute();
    } catch (PDOException $e) {
        $error = 'Error inserting Comment';
        include "$_PATH[errorPath]";
        exit();
    }

    //Send Mail Function
    $to = $emailAddress;
    $subject = 'New Comment on Your Idea';
    $body = 'A new comment has been posted on your Idea. See Now..';
    mailer($to, $subject, $body);

    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit();
}

include "$_PATH[commentPath]";
exit();
