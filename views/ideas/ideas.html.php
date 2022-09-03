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

  <!-- <div class="flex flex-col my-auto items-center bgimg bg-cover p-3">
    <div class="inline-flex">
      <button class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-l">
        Prev
      </button>
      <button class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-r">
        Next
      </button>
    </div>
  </div> -->
  <form method="post" action="">
    <div class="flex flex-col my-auto items-center bgimg bg-cover p-3">
      <select name='limitData' onchange='this.form.submit()' class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
        <option value="">Limit: <?php if (isset($_POST["limitData"])) {
                                  echo $_POST["limitData"];
                                } else {
                                  echo 10;
                                }
                                ?></option>
        <option value="10">10</option>
        <option value="20">20</option>
        <option value="50">50</option>
        <option value="100">100</option>
      </select>
    </div>
  </form>

  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/components/card.html.php'; ?>

</body>

<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/components/footer.html.php'; ?>

</html>