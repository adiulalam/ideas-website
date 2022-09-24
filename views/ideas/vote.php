<?php
function ideasVote()
{
    include $_SERVER['DOCUMENT_ROOT'] . "/path.php";
    include "$_PATH[databasePath]";

    require_once $_SERVER['DOCUMENT_ROOT'] . '/views/auth/login/index.php';
    if (!userIsLoggedIn() || !$_SESSION['aid']) {
        header('Location: ' . '/views/auth/login/login.html.php');
        exit();
    }

    $ideaID = $_POST['ID'];
    $authorID = $_SESSION['aid'];
    $voteNum = 0;
    if (isset($_POST['upvote']) and $_POST['upvote'] == 'true') {
        $voteNum = 1;
    } elseif (isset($_POST['downvote']) and $_POST['downvote'] == 'true') {
        $voteNum = -1;
    } else {
        $voteNum = 0;
    }

    try {
        $sql = "DELETE FROM Vote WHERE IdeaID = :IdeaID AND AuthorID = :AuthorID";
        $s = $pdo->prepare($sql);
        $s->bindvalue(':IdeaID', $ideaID);
        $s->bindvalue(':AuthorID', $authorID);
        $s->execute();
    } catch (PDOException $e) {
        $error = 'Error deleting vote from ideas';
        include "$_PATH[errorPath]";
        exit();
    }

    try {
        $sql = 'INSERT INTO Vote SET
        IdeaID = :IdeaID,
        AuthorID = :AuthorID,
        VoteNumber = :VoteNumber';
        $s = $pdo->prepare($sql);
        $s->bindvalue(':IdeaID', $ideaID);
        $s->bindvalue(':AuthorID', $authorID);
        $s->bindvalue(':VoteNumber', $voteNum);
        $s->execute();

        header('Location: ' . $_SERVER['REQUEST_URI']);
    } catch (PDOException $e) {
        $error = 'Error adding vote for idea';
        include "$_PATH[errorPath]";
        exit();
    }
}
