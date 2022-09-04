<?php
function userIsLoggedIn()
{
  if (isset($_POST['action']) and $_POST['action'] == 'login')
  {
    if (!isset($_POST['Email']) or $_POST['Email'] == '' or
      !isset($_POST['Password']) or $_POST['Password'] == '')
    {
      $GLOBALS['loginError'] = 'Please fill in both fields';
      return FALSE;
    }
    $Password = md5($_POST['Password'].'ijdb');
    if (databaseContainsAuthor($_POST['Email'], $Password))
    {
      session_start();
      $_SESSION['loggedIn'] = TRUE;
      $_SESSION['Email'] = $_POST['Email'];
      $_SESSION['Password'] = $Password;
      return TRUE;
    }
    else
    {
      session_start();
      unset($_SESSION['loggedIn']);
      unset($_SESSION['Email']);
      unset($_SESSION['Password']);
      $GLOBALS['loginError'] =
          'The specified Email address or Password was incorrect.';
      return FALSE;
    }
  }
  if (isset($_POST['action']) and $_POST['action'] == 'logout')
  {
    session_start();
    unset($_SESSION['loggedIn']);
    unset($_SESSION['Email']);
    unset($_SESSION['Password']);
    header('Location: ' . $_POST['goto']);
    exit();
  }
  session_start();
  if (isset($_SESSION['loggedIn']))
  {
    return databaseContainsAuthor($_SESSION['Email'], $_SESSION['Password']);
  }
}
function databaseContainsAuthor($Email, $Password)
{
  include 'db.inc.php';
  try
  {
    $sql = 'SELECT COUNT(*) FROM Author
        WHERE Email = :Email AND Password = :Password';
    $s = $pdo->prepare($sql);
    $s->bindValue(':Email', $Email);
    $s->bindValue(':Password', $Password);
    $s->execute();
  }
  catch (PDOException $e)
  {
    $error = 'Error searching for Author.';
    include 'error.html.php';
    exit();
  }
  $row = $s->fetch();
  if ($row[0] > 0)
  {
    return TRUE;
  }
  else
  {
    return FALSE;
  }
}
function userHasRole($Role)
{
  include 'db.inc.php';
  try
  {
    $sql = "SELECT COUNT(*) FROM Author
        INNER JOIN AuthorRole ON Author.Author_ID = AuthorID
        INNER JOIN Role ON RoleID = Role.ID
        WHERE Email = :Email AND Role.ID = :RoleID";
    $s = $pdo->prepare($sql);
    $s->bindValue(':Email', $_SESSION['Email']);
    $s->bindValue(':RoleID', $Role);
    $s->execute();
  }
  catch (PDOException $e)
  {
    $error = 'Error searching for Author roles.';
    include 'error.html.php';
    exit();
  }
  $row = $s->fetch();
  if ($row[0] > 0)
  {
    return TRUE;
  }
  else
  {
    return FALSE;
  }
}

function customer()
{
    include 'db.inc.php';
    try
    {
        $sql = "SELECT Author_ID FROM Author WHERE Email = :Email AND Password = :Password";
        $s = $pdo->prepare($sql);
        $s->bindValue(':Email', $_SESSION['Email']);
        $s->bindValue(':Password', $_SESSION['Password']);
        $s->execute();
    }
    catch (PDOException $e)
  {
    $error = 'Error selecting customers';
    include 'error.html.php';
    exit();
  }
    $row=$s->fetch();
    $_SESSION['aid'] = $row['Author_ID'];
}