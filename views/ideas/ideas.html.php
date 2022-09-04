<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/connection/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <script src="https://cdn.tailwindcss.com"></script>
  <script type="text/javascript" src="https://code.jquery.com/jquery-3.6.1.min.js"></script>
  <meta charset="utf-8">
  <title>All Ideas</title>
</head>

<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/components/nav.html.php'; ?>

<body>

  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/components/filter.html.php'; ?>

  <?php include $_SERVER['DOCUMENT_ROOT'] . '/components/pagination.html.php'; ?>

  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/components/card.html.php'; ?>

  <?php include $_SERVER['DOCUMENT_ROOT'] . '/components/pagination.html.php'; ?>

  <?php include $_SERVER['DOCUMENT_ROOT'] . '/components/addIdea.html.php'; ?>

</body>

<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/components/footer.html.php'; ?>

</html>