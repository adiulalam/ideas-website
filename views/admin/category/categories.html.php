<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/connection/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/flowbite@1.5.3/dist/flowbite.js"></script>
  <link rel="stylesheet" href="https://unpkg.com/flowbite@1.5.3/dist/flowbite.min.css" />
  <meta charset="utf-8">
  <title>Manage Categories</title>
</head>

<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/components/nav.html.php'; ?>

<body>
  <section class="bg-gray-900 mx-auto min-h-screen lg:py-0">

    <?php if (isset($categories)) :
      foreach ($categories as $Category) : ?>
        <div class="flex <?php echo isMobileDevice() ? 'flex-row flex-wrap' : 'flex-col' ?> w-full items-center justify-center">
          <div class="<?php echo isMobileDevice() ? 'flex min-w-[70%]' : 'w-96' ?> m-3 max-w-sm rounded-lg border shadow-md bg-gray-800 border-gray-700">

            <div class="w-full">
              <img class="<?php echo isMobileDevice() ? 'w-full' : '' ?> rounded-t-lg" src="<?php html($Category['Image']) ?>" alt="" />
              <h5 class="<?php echo isMobileDevice() ? 'text-5xl' : 'text-2xl' ?> m-3 font-bold tracking-tight text-white"><?php echo ($Category['Name']); ?></h5>

              <form action='?' method='post' class=' float-right inline-flex items-center p-3'>
                <input type='hidden' name='ID' value='<?php echo ($Category['ID']); ?>'>
                <Button type='submit' name='action' value='Edit' class=' float-right inline-flex items-center mr-2 <?php echo isMobileDevice() ? 'p-3 text-3xl' : 'mx-1 p-2 text-sm' ?> font-medium text-center text-white rounded-lg focus:ring-4 focus:outline-none bg-blue-600 hover:bg-blue-700 focus:ring-blue-800'>Edit</Button>
                <Button type='button' data-modal-toggle='<?php echo ($Category['ID']); ?>' class=' float-right inline-flex items-center ml-2 <?php echo isMobileDevice() ? 'p-3 text-3xl' : 'mx-1 p-2 text-sm' ?> font-medium text-center text-white rounded-lg focus:ring-4 focus:outline-none bg-red-600 hover:bg-red-700 focus:ring-red-800'>Delete</Button>
              </form>

              <div id='<?php echo ($Category['ID']); ?>' tabindex='-1' class='hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 md:inset-0 h-modal md:h-full'>
                <div class='relative p-4 w-full max-w-2xl h-full md:h-auto'>
                  <div class='relative rounded-lg shadow bg-gray-700'>
                    <button type='button' class='absolute top-3 right-2.5 text-gray-400 bg-transparent rounded-lg <?php echo isMobileDevice() ? 'text-3xl' : 'text-sm' ?> p-1.5 ml-auto inline-flex items-center hover:bg-gray-800 hover:text-white' data-modal-toggle='<?php echo ($Category['ID']); ?>'>
                      <svg aria-hidden='true' class='w-5 h-5' fill='currentColor' viewBox='0 0 20 20' xmlns='http://www.w3.org/2000/svg'>
                        <path fill-rule='evenodd' d='M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z' clip-rule='evenodd'></path>
                      </svg>
                      <span class='sr-only'>Close modal</span>
                    </button>
                    <div class='p-6 text-center'>
                      <svg aria-hidden='true' class='mx-auto mb-4 w-14 h-14 text-gray-200' fill='none' stroke='currentColor' viewBox='0 0 24 24' xmlns='http://www.w3.org/2000/svg'>
                        <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'></path>
                      </svg>
                      <h3 class='mb-5 <?php echo isMobileDevice() ? 'text-3xl' : 'text-lg' ?> font-normal text-gray-400'>Are you sure you want to delete this Category?</h3>
                      <form action='?' method='post'>
                        <input type='hidden' name='ID' value='<?php echo ($Category['ID']); ?>'>
                        <button data-modal-toggle='<?php echo ($Category['ID']); ?>' type='submit' name='action' value='Delete' class='text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-800 font-medium rounded-lg <?php echo isMobileDevice() ? 'text-3xl' : 'text-sm' ?> inline-flex items-center px-5 py-2.5 text-center mr-2'>
                          Yes, I'm sure
                        </button>
                        <button data-modal-toggle='<?php echo ($Category['ID']); ?>' type='button' class='focus:ring-4 focus:outline-none rounded-lg border <?php echo isMobileDevice() ? 'text-3xl' : 'text-sm' ?> font-medium px-5 py-2.5 focus:z-10 bg-gray-700 text-gray-300 border-gray-500 hover:text-white hover:bg-gray-600 focus:ring-gray-600'>No, cancel</button>
                      </form>
                    </div>
                  </div>
                </div>
              </div>

            </div>
          </div>
      <?php endforeach;
    endif; ?>

      <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/components/addContent.html.php'; ?>

  </section>

</body>
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/components/footer.html.php'; ?>


</html>