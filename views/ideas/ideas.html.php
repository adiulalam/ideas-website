<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/connection/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <script src="https://cdn.tailwindcss.com"></script>
  <meta charset="utf-8">
  <title>Manage Ideas: Search Results</title>
</head>

<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/components/nav.html.php'; ?>

<body>
  <h1>Search Results</h1>
  <?php if (isset($ideas)) : ?>
    <table border="1px">
      <tr>
        <th>Idea Text</th>
        <th>Image</th>
        <th>Idea Date</th>
        <th>Author</th>
        <th>Total Votes</th>
      </tr>
      <?php foreach ($ideas as $Idea) : ?>
        <tr>
          <td>
            <center><?php echo ($Idea['text']); ?></center>
          </td>
          <td>
            <center><img src="../../assets/img/<?php echo htmlspecialchars($Idea['Image']); ?>" style="width:100px;height:100px;" /></center>
          </td>
          <td>
            <center><?php html($Idea['IdeaDate']); ?></center>
          </td>
          <td>
            <center><?php html($Idea['Name']); ?></center>
          </td>
          <td>
            <center><?php html($Idea['Vote']); ?></center>
          </td>
        </tr>
      <?php endforeach; ?>
    </table>
  <?php endif; ?>
  <p><a href="?">New Search</a></p>
</body>

<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/components/footer.html.php'; ?>

</html>