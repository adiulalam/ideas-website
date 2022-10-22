<?php
function ideasDelete()
{
    include $_SERVER['DOCUMENT_ROOT'] . "/path.php";
    include "$_PATH[databasePath]";

    $ideasID = $_POST['ID'];

    try {
        require_once $_SERVER['DOCUMENT_ROOT'] . '/views/auth/login/index.php';

        $authorID = ((in_array("Content Editor", json_decode($_COOKIE['authorRole'])) && userHasRole('Content Editor')) ||
            (in_array("Site Administrator", json_decode($_COOKIE['authorRole'])) && userHasRole('Site Administrator')))
            ? ''
            : "AND AuthorID = $_COOKIE[aid]";
        $sql = "DELETE FROM Idea WHERE ID = :ID $authorID";
        $s = $pdo->prepare($sql);
        $s->bindvalue(':ID', $ideasID);
        $s->execute();
    } catch (PDOException $e) {
        $error = 'Error deleting idea from idea';
        include "$_PATH[errorPath]";
        exit();
    }

    try {
        $sql = "DELETE FROM IdeaCategory WHERE IdeaID= :ID";
        $s = $pdo->prepare($sql);
        $s->bindvalue(':ID', $ideasID);
        $s->execute();
    } catch (PDOException $e) {
        $error = 'Error removing idea from category';
        include "$_PATH[errorPath]";
        exit();
    }

    try {
        $sql = "DELETE FROM IdeaDepartment WHERE IdeaID= :ID";
        $s = $pdo->prepare($sql);
        $s->bindvalue(':ID', $ideasID);
        $s->execute();
    } catch (PDOException $e) {
        $error = 'Error removing idea from department';
        include "$_PATH[errorPath]";
        exit();
    }

    try {
        $sql = "DELETE FROM Comment WHERE IdeaID= :ID";
        $s = $pdo->prepare($sql);
        $s->bindvalue(':ID', $ideasID);
        $s->execute();
    } catch (PDOException $e) {
        $error = 'Error deleting idea from comment';
        include "$_PATH[errorPath]";
        exit();
    }

    try {
        $sql = "DELETE FROM Vote WHERE IdeaID= :ID";
        $s = $pdo->prepare($sql);
        $s->bindvalue(':ID', $ideasID);
        $s->execute();
    } catch (PDOException $e) {
        $error = 'Error deleting vote from idea';
        include "$_PATH[errorPath]";
        exit();
    }

    header('Location: .');
    exit();
}
