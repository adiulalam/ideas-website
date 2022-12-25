<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/connection/helpers.inc.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/views/auth/login/index.php';

if (!userIsLoggedIn()) {
    header('Location: ' . '/views/auth/login/login.html.php');
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://unpkg.com/tailwindcss@2.2.19/dist/tailwind.min.css" rel=" stylesheet">
    <script src="https://unpkg.com/flowbite@1.5.3/dist/flowbite.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/flowbite@1.5.3/dist/flowbite.min.css" />
    <meta charset="utf-8">
    <title><?php html($pageTitle); ?></title>
</head>

<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/components/nav.html.php'; ?>

<body>
    <section class="bg-gray-900">
        <div class="flex flex-row flex-wrap justify-center items-center p-6 py-6 mx-auto min-h-screen lg:py-0">
            <div class="<?php echo isMobileDevice() ? 'flex min-w-[70%]' : 'w-full' ?> rounded-lg shadow border md:mt-0 sm:max-w-md xl:p-0 bg-gray-800 border-gray-700">
                <div class="w-full p-6 space-y-4 md:space-y-6 sm:p-8">
                    <h1 class="<?php echo isMobileDevice() ? 'text-5xl' : 'text-xl md:text-2xl' ?> font-bold leading-tight tracking-tight text-white">
                        <?php html($pageTitle); ?>
                    </h1>
                    <form class="space-y-4 md:space-y-6" action="?<?php html($action); ?>" method="POST" enctype="multipart/form-data">

                        <div>
                            <label for="name" class="block mb-2 <?php echo isMobileDevice() ? 'text-3xl' : 'text-sm' ?> font-medium text-white">Name</label>
                            <input value="<?php html($Name) ?>" type="name" name="Name" id="name" class="border <?php echo isMobileDevice() ? 'text-3xl' : 'sm:text-sm' ?> rounded-lg block w-full p-2.5 bg-gray-700 border-gray-600 placeholder-gray-400 text-white focus:ring-blue-500 focus:border-blue-500" placeholder="Category Name" required>
                        </div>

                        <input type="hidden" name="ID" value="<?php html($ID); ?>">
                        <button type="submit" value="<?php html($button); ?>" class="modal-open w-full text-white focus:ring-4 focus:outline-none font-medium rounded-lg <?php echo isMobileDevice() ? 'text-3xl' : 'text-sm' ?> px-5 py-2.5 text-center bg-blue-600 hover:bg-blue-700 focus:ring-blue-800"><?php html($button); ?></button>
                    </form>
                </div>
            </div>
        </div>
    </section>

</body>

<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/components/footer.html.php'; ?>

</html>