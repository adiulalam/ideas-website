<?php
function ideasDelete()
{
    include $_SERVER['DOCUMENT_ROOT'] . "/path.php";
    include "$_PATH[databasePath]";

    $ideasID = $_POST['ID'];

    try {
        session_start();
        $authorID =  $_SESSION['aid'];
        $sql = "DELETE FROM Idea WHERE ID = :ID AND AuthorID = :AuthorID";
        $s = $pdo->prepare($sql);
        $s->bindvalue(':ID', $ideasID);
        $s->bindvalue(':AuthorID', $authorID);
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

    header('Location: .');
    exit();
}
