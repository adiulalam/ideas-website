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

  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/components/limiter.html.php'; ?>

  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/components/card.html.php'; ?>

</body>

<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/components/footer.html.php'; ?>

</html>