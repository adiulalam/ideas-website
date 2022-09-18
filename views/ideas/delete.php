<?php
function ideasDelete()
{
    include $_SERVER['DOCUMENT_ROOT'] . "/path.php";
    include "$_PATH[databasePath]";

    $ideasID = $_POST['ID'];

    //delete the idea category
    try {
        $sql = 'DELETE FROM IdeaCategory WHERE IdeaID= :ID';
        $s = $pdo->prepare($sql);
        $s->bindvalue(':ID', $ideasID);
        $s->execute();
    } catch (PDOException $e) {
        $error = 'Error removing idea from categories';
        include "$_PATH[errorPath]";
        exit();
    }

    //delete the idea department
    try {
        $sql = 'DELETE FROM IdeaDepartment WHERE IdeaID= :ID';
        $s = $pdo->prepare($sql);
        $s->bindvalue(':ID', $ideasID);
        $s->execute();
    } catch (PDOException $e) {
        $error = 'Error removing idea from categories';
        include "$_PATH[errorPath]";
        exit();
    }

    //delete the idea
    try {
        $sql = 'DELETE FROM Idea WHERE ID= :ID';
        $s = $pdo->prepare($sql);
        $s->bindvalue(':ID', $ideasID);
        $s->execute();
    } catch (PDOException $e) {
        $error = 'Error deleting idea';
        include "$_PATH[errorPath]";
        exit();
    }

    //delete the Comment
    try {
        $sql = 'DELETE FROM Comment WHERE IdeaID= :ID';
        $s = $pdo->prepare($sql);
        $s->bindvalue(':ID', $ideasID);
        $s->execute();
    } catch (PDOException $e) {
        $error = 'Error deleting idea';
        include "$_PATH[errorPath]";
        exit();
    }

    header('Location: .');
    exit();
}
