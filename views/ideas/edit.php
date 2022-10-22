<?php
error_reporting(0);
function ideasEditForm()
{
    include $_SERVER['DOCUMENT_ROOT'] . "/path.php";
    include "$_PATH[databasePath]";
    require_once $_SERVER['DOCUMENT_ROOT'] . '/views/auth/login/index.php';

    try {
        $authorID = ((in_array("Content Editor", json_decode($_COOKIE['authorRole'])) && userHasRole('Content Editor')) ||
            (in_array("Site Administrator", json_decode($_COOKIE['authorRole'])) && userHasRole('Site Administrator')))
            ? ''
            : "AND AuthorID = $_COOKIE[aid]";
        $sql = "SELECT ID, IdeaText, Image, AuthorID FROM Idea WHERE ID= :ID $authorID";
        $s = $pdo->prepare($sql);
        $s->bindvalue(':ID', $_POST['ID']);
        $s->execute();
    } catch (PDOException $e) {
        $error = 'Error fetching idea details';
        include "$_PATH[errorPath]";
        exit();
    }
    $row = $s->fetch();
    if ($row[0] <= 0) {
        return false;
    };

    $pageTitle = 'Edit Idea';
    $action = 'editform';
    $text = $row['IdeaText'];
    $Image = $row['Image'];
    $AuthorID = $row['AuthorID'];
    $ID = $row['ID'];
    $button = 'Update Idea';

    //build list of authors
    try {
        $result = $pdo->query('SELECT Author_ID, Name FROM Author');
    } catch (PDOException $e) {
        $error = 'Error fetching list of authors';
        include "$_PATH[errorPath]";
        exit();
    }

    foreach ($result as $row) {
        $authors[] = array('ID' => $row['Author_ID'], 'Name' => $row['Name']);
    }

    //get list of categories containing this idea
    try {
        $sql = 'SELECT CategoryID FROM IdeaCategory WHERE IdeaID= :ID';
        $s = $pdo->prepare($sql);
        $s->bindvalue(':ID', $ID);
        $s->execute();
    } catch (PDOException $e) {
        $error = 'Error fetching lists of selected categories';
        include "$_PATH[errorPath]";
        exit();
    }
    $selectedCategories = (array) null;
    foreach ($s as $row) {
        $selectedCategories[] = $row['CategoryID'];
    }

    try {
        $result = $pdo->query('SELECT ID, Name FROM Category');
    } catch (PDOException $e) {
        $error = 'Error fetching list of categories';
        include "$_PATH[errorPath]";
        exit();
    }

    foreach ($result as $row) {
        $categories[] = array('ID' => $row['ID'], 'Name' => $row['Name'], 'selected' => in_array($row['ID'], $selectedCategories));
    }

    //get list of departments containing this idea
    try {
        $sql = 'SELECT DepartmentID FROM IdeaDepartment WHERE IdeaID= :ID';
        $s = $pdo->prepare($sql);
        $s->bindvalue(':ID', $ID);
        $s->execute();
    } catch (PDOException $e) {
        $error = 'Error fetching lists of selected Departments';
        include "$_PATH[errorPath]";
        exit();
    }
    $selectedDepartments = (array) null;
    foreach ($s as $row) {
        $selectedDepartments[] = $row['DepartmentID'];
    }

    try {
        $result = $pdo->query('SELECT ID, Name FROM Department');
    } catch (PDOException $e) {
        $error = 'Error fetching list of Departments';
        include "$_PATH[errorPath]";
        exit();
    }
    foreach ($result as $row) {
        $departments[] = array('ID' => $row['ID'], 'Name' => $row['Name'], 'selected' => in_array($row['ID'], $selectedDepartments));
    }

    include "$_PATH[ideasFormPath]";
    exit();
}

function ideasEditSubmit()
{
    include $_SERVER['DOCUMENT_ROOT'] . "/path.php";
    include "$_PATH[databasePath]";
    require_once "$_PATH[purifierPath]";

    $purifier = new HTMLPurifier();
    $text = $purifier->purify($_POST['text']);
    $Author = $_POST['Author'];
    $Category = isset($_POST['categories']);
    $Department = isset($_POST['departments']);


    if ($text == '' || $Author == '' || $Category == '' || !(isset($Category)) || $Department == '' || !(isset($Department))) {
        $error = 'Error: Field is empty';
        include "$_PATH[errorPath]";
        exit();
    }

    function updateImage($Image, $IdeaID)
    {
        include $_SERVER['DOCUMENT_ROOT'] . "/path.php";
        include "$_PATH[databasePath]";
        require_once $_SERVER['DOCUMENT_ROOT'] . '/views/auth/login/index.php';

        try {
            $authorID = ((in_array("Content Editor", json_decode($_COOKIE['authorRole'])) && userHasRole('Content Editor')) ||
                (in_array("Site Administrator", json_decode($_COOKIE['authorRole'])) && userHasRole('Site Administrator')))
                ? ''
                : "AND AuthorID = $_COOKIE[aid]";
            $sql = "UPDATE Idea SET
            Image= :Image
            WHERE ID= :ID $authorID";
            $s = $pdo->prepare($sql);
            $s->bindvalue(':ID', $IdeaID);
            $s->bindvalue(':Image', $Image);
            $s->execute();
        } catch (PDOException $e) {
            $error = 'Error updating submitted idea Image';
            include "$_PATH[errorPath]";
            exit();
        }
    }

    $filename = $_FILES['myfile']['name'];

    if ($_POST['Image'] && $filename) {
        $error = 'Error: Image was both uploaded and given a link';
        include "$_PATH[errorPath]";
        exit();
    } elseif ($_POST['Image'] && !$filename) {
        if (!str_contains($_POST['Image'], 'https://')) {
            $error = 'Error: Image has invalid link';
            include "$_PATH[errorPath]";
            exit();
        }
        updateImage($_POST['Image'], $_POST['ID']);
    } elseif (!$_POST['Image'] && $filename) {

        $destination = $_SERVER['DOCUMENT_ROOT'] . '/assets/img/';

        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if ($extension != "jpg" && $extension != "png") {
            $error = 'Error: Wrong file format';
            include "$_PATH[errorPath]";
            exit();
        }

        $filename = date("Y-m-d_h.i.s_") . $_COOKIE['aid'] . '_' . $filename;
        $file = $_FILES['myfile']['tmp_name'];

        if ($_POST['fileInputName'] && file_exists($destination . $_POST['fileInputName'])) {
            unlink($destination . $_POST['fileInputName']);
        }

        if (!move_uploaded_file($file, ($destination . $filename))) {
            $error = 'Error: Moving file';
            include "$_PATH[errorPath]";
            exit();
        }
        updateImage($filename, $_POST['ID']);
    }

    try {
        require_once $_SERVER['DOCUMENT_ROOT'] . '/views/auth/login/index.php';

        $authorID = ((in_array("Content Editor", json_decode($_COOKIE['authorRole'])) && userHasRole('Content Editor')) ||
            (in_array("Site Administrator", json_decode($_COOKIE['authorRole'])) && userHasRole('Site Administrator')))
            ? ''
            : "AND AuthorID = $_COOKIE[aid]";
        $sql = "UPDATE Idea SET
        IdeaText=:IdeaText
        WHERE ID=:ID $authorID";
        $s = $pdo->prepare($sql);
        $s->bindvalue(':ID', $_POST['ID']);
        $s->bindvalue(':IdeaText', $text);
        $s->execute();
    } catch (PDOException $e) {
        $error = 'Error updating submitted idea';
        include "$_PATH[errorPath]";
        exit();
    }

    try {
        $sql = 'DELETE FROM IdeaCategory WHERE IdeaID=:ID';
        $s = $pdo->prepare($sql);
        $s->bindvalue(':ID', $_POST['ID']);
        $s->execute();
    } catch (PDOException $e) {
        $error = 'Error removing obsolete idea category entries';
        include "$_PATH[errorPath]";
        exit();
    }

    if (isset($_POST['categories'])) {
        try {
            $sql = 'INSERT INTO IdeaCategory SET 
            IdeaID= :IdeaID, 
            CategoryID=:CategoryID';
            $s = $pdo->prepare($sql);
            foreach ($_POST['categories'] as $CategoryID) {
                $s->bindvalue(':IdeaID', $_POST['ID']);
                $s->bindvalue(':CategoryID', $CategoryID);
                $s->execute();
            }
        } catch (PDOException $e) {
            $error = 'Error inserting idea into selected categories';
            include "$_PATH[errorPath]";
            exit();
        }
    }

    try {
        $sql = 'DELETE FROM IdeaDepartment WHERE IdeaID=:ID';
        $s = $pdo->prepare($sql);
        $s->bindvalue(':ID', $_POST['ID']);
        $s->execute();
    } catch (PDOException $e) {
        $error = 'Error removing obsolete idea Department entries';
        include "$_PATH[errorPath]";
        exit();
    }

    if (isset($_POST['departments'])) {
        try {
            $sql = 'INSERT INTO IdeaDepartment SET 
            IdeaID= :IdeaID, 
            DepartmentID=:DepartmentID';
            $s = $pdo->prepare($sql);
            foreach ($_POST['departments'] as $DepartmentID) {
                $s->bindvalue(':IdeaID', $_POST['ID']);
                $s->bindvalue(':DepartmentID', $DepartmentID);
                $s->execute();
            }
        } catch (PDOException $e) {
            $error = 'Error inserting idea into selected Department';
            include "$_PATH[errorPath]";
            exit();
        }
    }

    echo "<script> location.href='/'; </script>";
    exit();
}
