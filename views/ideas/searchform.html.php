<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/connection/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <!--NEW CODE-->
  <meta charset="utf-8">
  <title>All Ideas</title>
</head>

<body>
  <h1>All Ideas</h1>

  <form action="" method="get">
    <p>Advanced Search</p>
    <div>
      <label for="Author">By Author:</label>
      <select Name="Author" ID="Author">
        <option value="">Any Author</option>
        <?php foreach ($authors as $Author) : ?>
          <option value="<?php html($Author['ID']); ?>"><?php html($Author['Name']); ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div>
      <label for="Category">By Category:</label>
      <select Name="Category" ID="Category">
        <option value="">Any Category</option>
        <?php foreach ($categories as $Category) : ?>
          <option value="<?php html($Category['ID']); ?>"><?php html($Category['Name']); ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div>
      <label for="text">Containing text</label>
      <input type="text" name="text" id="text">
    </div>

    <div>
      <input type="hidden" name="action" value="search">
      <input type="submit" value="search">
    </div>

  </form> <br>

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

  <!--<p><a href="../../">Return to CMS home</a></p>-->
  <footer>Created by greforum &copy; Copyright 2020</footer>
</body>

</html>