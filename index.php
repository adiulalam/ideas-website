<?php

// todo add admin area
// todo add forgot password page

include_once $_SERVER['DOCUMENT_ROOT'] . "/path.php";

if ((isset($_POST['upvote']) and $_POST['upvote'] == 'true') ||
    (isset($_POST['downvote']) and $_POST['downvote'] == 'true')
) {
    include "$_PATH[voteIdeasPath]";
    ideasVote();
}

if (isset($_GET['action']) and $_GET['action'] == 'Comment') {
    include "$_PATH[commentIdeasPath]";
}

if (isset($_POST['action']) and $_POST['action'] == 'Delete') {
    include "$_PATH[deleteIdeasPath]";
    ideasDelete();
}

if (isset($_GET['editform'])) {
    include "$_PATH[editIdeasPath]";
    ideasEditSubmit();
}

if (isset($_POST['action']) and $_POST['action'] == 'Edit') {
    include "$_PATH[editIdeasPath]";
    ideasEditForm();
}

if (isset($_GET['addform'])) {
    include "$_PATH[addIdeasPath]";
    ideasAddSubmit();
}

if (isset($_GET['action']) and $_GET['action'] == 'addIdea') {
    include "$_PATH[addIdeasPath]";
    ideasAddForm();
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

$select = 'SELECT ID, IdeaText, IdeaDate, Image, COALESCE(SUM(Vote.VoteNumber), 0) Vote, Document, Idea.AuthorID, Author.Name, Author.Author_ID FROM';
$selectCount = 'SELECT COUNT(Idea.ID) as id FROM';
$from = ' Idea INNER JOIN Author ON Idea.AuthorID = Author.Author_ID LEFT JOIN Vote ON Idea.ID = Vote.IdeaID';
$where = ' WHERE TRUE';
$placeholders = array();
$orderby = isset($_GET["orderBy"]) ? "ORDER BY $_GET[orderBy]" : " ORDER BY IdeaDate DESC";
$groupby = 'GROUP BY ID';

$offset = isset($_GET["limitRecords"]) ? $_GET["limitRecords"] : 10;
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$start = ($page - 1) * $offset;
if (isset($_GET["page"])) {
    $pageSelected = $_GET["page"];
    $start = ($pageSelected - 1) * $offset;
}
$limit = "LIMIT $start, $offset";

if (isset($_GET['action']) and $_GET['action'] == 'search') {

    if (isset($_GET["Author"]) && $_GET['Author'] != '') {
        $where .= " AND Idea.AuthorID = :AuthorID";
        $placeholders[':AuthorID'] = $_GET['Author'];
    }

    if (isset($_GET["Category"]) && $_GET['Category'] != '') {
        $from .= ' INNER JOIN IdeaCategory ON ID= IdeaCategory.IdeaID';
        $where .= " AND CategoryID = :CategoryID";
        $placeholders[':CategoryID'] = $_GET['Category'];
    }

    if (isset($_GET["text"]) && $_GET['text'] != '') {
        $where .= " AND LOWER(Idea.IdeaText) LIKE LOWER(:IdeaText)";
        $placeholders[':IdeaText'] = "%" . $_GET['text'] . "%";
    }
}

try {
    $sql = "$selectCount $from $where $orderby";
    $s = $pdo->prepare($sql);
    $s->execute($placeholders);
} catch (PDOException $e) {
    $error = 'Error fetching ideas count';
    include "$_PATH[errorPath]";
    exit();
}
foreach ($s as $row) {
    $total[] = array('id' => $row['id']);
}

$total = $total[0]['id'];
$pages = ceil($total / $offset);

$previous = ($page == 1 || $page < 1) ? 1 : $page - 1;
$next = ($page == $pages || $page > $pages) ? $pages : $page + 1;


try {
    $sql = "$select $from $where $groupby $orderby $limit";
    $s = $pdo->prepare($sql);
    $s->execute($placeholders);
} catch (PDOException $e) {
    $error = 'Error fetching ideas';
    include "$_PATH[errorPath]";
    exit();
}
$ideasIDs = array();
foreach ($s as $row) {
    $ideas[] = array('ID' => $row['ID'], 'text' => $row['IdeaText'], 'Image' => $row['Image'], 'IdeaDate' => $row['IdeaDate'], 'Name' => $row['Name'], 'Vote' => $row['Vote']);
    $ideasIDs[] = $row['ID'];
}
$ideasIDsString = implode(', ', $ideasIDs);

if (!$ideasIDsString) {
    $ideasIDsString = 'null';
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/views/auth/login/index.php';
if (userIsLoggedIn() && $_SESSION['aid']) {
    $authorID =  $_SESSION['aid'];
    try {
        $subquery = "$select $from $where $groupby $orderby $limit";
        $sql = "SELECT ID FROM ($subquery) AS subquery WHERE AuthorID = $authorID";
        $s = $pdo->prepare($sql);
        $s->execute($placeholders);
    } catch (PDOException $e) {
        $error = 'Error fetching subquery ideas for author for Edit and Delete';
        include "$_PATH[errorPath]";
        exit();
    }
    foreach ($s as $row) {
        $totalIdeas[] = $row['ID'];
    }

    try {
        $sql = "SELECT IdeaID, AuthorID, VoteNumber 
                FROM Vote WHERE AuthorID = $authorID
                AND IdeaID IN ($ideasIDsString)";
        $s = $pdo->prepare($sql);
        $s->execute(array());
    } catch (PDOException $e) {
        $error = 'Error fetching vote counts for logged in users';
        include "$_PATH[errorPath]";
        exit();
    }
    $totalIdeaVotes = array();
    foreach ($s as $row) {
        $ideaVoteCounts[] = array('IdeaID' => $row['IdeaID'], 'AuthorID' => $row['AuthorID'], 'VoteNumber' => $row['VoteNumber']);
        $totalIdeaVotes[] = $row['IdeaID'];
    }
}

include "$_PATH[ideasPath]";
