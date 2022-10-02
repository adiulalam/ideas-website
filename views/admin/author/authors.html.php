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
            <img class="rounded-t-lg" src="<?php html($Author['Image']) ?>" alt="" />

            <div class="mb-3">
              <p class="mr-2 text-xs float-right italic font-light text-white-900 dark:text-gray-400"><?php html($Author['LoginTime']); ?></p>
              <p class="ml-4 text-base float-left font-normal text-white-500 dark:text-gray-400"><?php html($Author['Email']); ?></p>
            </div>

            <div class="p-5 mt-4">
              <h5 class=" mb-2 text-2xl font-bold tracking-tight dark:text-white"><?php echo ($Author['Name']); ?></h5>
              <input type='hidden' name='ideaID' value='<?php echo ($Author['ID']); ?>'>

              <form action='?' method='post' class=' float-right inline-flex items-center pb-3'>
                <input type='hidden' name='ID' value='<?php echo ($Author['ID']); ?>'>
                <Button type='submit' name='action' value='Edit' class=' float-right inline-flex items-center mx-1 py-1 px-3 text-sm font-medium text-center text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800'>Edit</Button>
                <Button type='button' data-modal-toggle='<?php echo ($Author['ID']); ?>' class=' float-right inline-flex items-center mx-1 py-1 px-3 text-sm font-medium text-center text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-red-300 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-800'>Delete</Button>
              </form>

              <div id='<?php echo ($Author['ID']); ?>' tabindex='-1' class='hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 md:inset-0 h-modal md:h-full'>
                <div class='relative p-4 w-full max-w-md h-full md:h-auto'>
                  <div class='relative bg-white rounded-lg shadow dark:bg-gray-700'>
                    <button type='button' class='absolute top-3 right-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-800 dark:hover:text-white' data-modal-toggle='<?php echo ($Author['ID']); ?>'>
                      <svg aria-hidden='true' class='w-5 h-5' fill='currentColor' viewBox='0 0 20 20' xmlns='http://www.w3.org/2000/svg'>
                        <path fill-rule='evenodd' d='M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z' clip-rule='evenodd'></path>
                      </svg>
                      <span class='sr-only'>Close modal</span>
                    </button>
                    <div class='p-6 text-center'>
                      <svg aria-hidden='true' class='mx-auto mb-4 w-14 h-14 text-gray-400 dark:text-gray-200' fill='none' stroke='currentColor' viewBox='0 0 24 24' xmlns='http://www.w3.org/2000/svg'>
                        <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'></path>
                      </svg>
                      <h3 class='mb-5 text-lg font-normal text-gray-500 dark:text-gray-400'>Are you sure you want to delete this Idea?</h3>
                      <form action='?' method='post'>
                        <input type='hidden' name='ID' value='<?php echo ($Author['ID']); ?>'>
                        <button data-modal-toggle='<?php echo ($Author['ID']); ?>' type='submit' name='action' value='Delete' class='text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center mr-2'>
                          Yes, I'm sure
                        </button>
                        <button data-modal-toggle='<?php echo ($Author['ID']); ?>' type='button' class='text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600'>No, cancel</button>
                      </form>
                    </div>
                  </div>
                </div>
              </div>

            </div>
          </div>
      <?php endforeach;
    endif; ?>

      <!-- <?php // include_once $_SERVER['DOCUMENT_ROOT'] . '/components/addAuthor.html.php'; 
            ?> -->

  </section>

</body>
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/components/footer.html.php'; ?>


</html>