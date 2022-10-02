<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/connection/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/flowbite@1.5.3/dist/flowbite.js"></script>
  <link rel="stylesheet" href="https://unpkg.com/flowbite@1.5.3/dist/flowbite.min.css" />
  <meta charset="utf-8">
  <title>Manage Authors</title>
</head>

<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/components/nav.html.php'; ?>

<body class="dark:bg-gray-900">
  <section class="dark:bg-gray-900">

    <?php if (isset($authors)) :
      foreach ($authors as $Author) : ?>
        <div class="flex flex-col items-center justify-center">
          <div class="w-96 m-3 max-w-sm rounded-lg border border-gray-200 shadow-md dark:bg-gray-800 dark:border-gray-700">
            <form method="get" action="?">
              <!-- <a href="<?php //html("/?action=Comment&ideaID=$Author[ID]") 
                            ?>" class="button">
                <button type="submit" name="action" value="Comment">
                  <img class="rounded-t-lg" src="<?php html($Author['Image']) ?>" alt="" />
                </button>
              </a> -->
              <img class="rounded-t-lg" src="<?php html($Author['Image']) ?>" alt="" />

              <div class="mb-3">
                <p class="mr-2 text-xs float-right italic font-light text-white-900 dark:text-gray-400"><?php html($Author['LoginTime']); ?></p>
                <p class="ml-4 text-base float-left font-normal text-white-500 dark:text-gray-400"><?php html($Author['Email']); ?></p>
              </div>

              <div class="p-5 mt-4">
                <h5 class=" mb-2 text-2xl font-bold tracking-tight dark:text-white"><?php echo ($Author['Name']); ?></h5>
                <!-- <?php //commentCheck($Author['ID'],  $_SERVER['REQUEST_URI']) 
                      ?> -->
                <input type='hidden' name='ideaID' value='<?php echo ($Author['ID']); ?>'>
            </form>
            <!-- <?php //mutationCheck($Author['ID'], $totalAuthors) 
                  ?>
                <?php //votingCheck($Author['ID'], $ideaVoteCounts, $totalAuthorVotes, $Author['Vote']) 
                ?> -->
          </div>
        </div>
    <?php endforeach;
    endif; ?>

    <!-- <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/components/addAuthor.html.php'; ?> -->

  </section>

</body>
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/components/footer.html.php'; ?>


</html>