<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/connection/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <script src="https://cdn.tailwindcss.com"></script>
  <meta charset="utf-8">
  <title>All Ideas</title>
</head>

<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/components/nav.html.php'; ?>

<body>

  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/components/filter.html.php'; ?>

  <h1>All Ideas</h1>

  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/components/card.html.php'; ?>

  <?php if (isset($ideas)) : ?>
    <table style="overflow-x:auto;" border="1px">
      <tr>
        <th>Idea Text</th>
        <th>Image</th>
        <th>Idea Date</th>
        <th>Author</th>
        <th>Total Votes</th>
        <th>Vote</th>
        <th>Comment</th>
      </tr>
      <?php foreach ($ideas as $Idea) : ?>
        <tr>
          <td>
            <center><?php echo ($Idea['text']); ?></center>
          </td>
          <td><img src="../../assets/img/<?php echo htmlspecialchars($Idea['Image']); ?>" style="width:100px;height:100px;" /></td>
          <td>
            <center><?php html($Idea['IdeaDate']); ?></center>
          </td>
          <td>
            <center><?php html($Idea['Name']); ?></center>
          </td>
          <td>
            <center><?php html($Idea['Vote']); ?></center>
          </td>
          <td>
            <form action="../../userlogin" method="post">
              <input type="submit" name="action" value="Login To vote">
            </form>
          </td>
          <td>
            <form action="../../userlogin" method="post">
              <input type="submit" name="action" value="Login To Comment">
            </form>
          </td>

        </tr>
      <?php endforeach; ?>
    </table> <br>
  <?php endif; ?>
</body>

<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/components/footer.html.php'; ?>

</html>