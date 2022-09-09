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
    <script type="text/javascript" src="https://code.jquery.com/jquery-3.6.1.min.js"></script>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <meta charset="utf-8">
    <title><?php html($pageTitle); ?></title>
</head>

<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/components/nav.html.php'; ?>

<body>
    <section class=" dark:bg-gray-900">

        <div class="flex flex-col my-auto items-center bgimg bg-cover p-6 py-6">
            <div class="w-full rounded-lg shadow dark:border md:mt-0 sm:max-w-md xl:p-0 dark:bg-gray-800 dark:border-gray-700">
                <div class="p-6 space-y-4 md:space-y-6 sm:p-8">
                    <h1 class="text-xl font-bold leading-tight tracking-tight md:text-2xl dark:text-white">
                        <?php html($pageTitle); ?>
                    </h1>
                    <form class="space-y-4 md:space-y-6" action="?<?php html($action); ?>" method="POST">
                        <div>
                            <label for="ideaText" class="block mb-2 text-sm font-medium dark:text-white">Your idea here:</label>
                            <textarea name="text" rows="3" class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Write your idea" required><?php html($text); ?></textarea>
                            <input type="hidden" name="Author" value="<?php html($_SESSION['aid']); ?>">
                        </div>

                        <fieldset class="rounded border border-gray-300 dark:border-gray-700 p-3">
                            <legend class="block mb-2 text-sm font-medium dark:text-white">Categories:</legend>
                            <ul class="w-full text-sm font-medium text-gray-900 bg-white rounded-lg border border-gray-200 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <li class="w-full rounded-t-lg ">
                                    <?php foreach ($categories as $Category) : ?>
                                        <div class="flex items-center pl-3">
                                            <input type="checkbox" name="categories[]" value="<?php html($Category['ID']); ?>" <?php if ($Category['selected']) echo ' checked' ?> class="w-4 h-4 text-blue-600 bg-gray-100 rounded border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                                            <label for="Category<?php html($Category['ID']); ?>" class="py-3 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300"><?php html($Category['Name']); ?></label>
                                        </div>
                                    <?php endforeach; ?>
                                </li>
                            </ul>
                        </fieldset>

                        <fieldset class="rounded border border-gray-300 dark:border-gray-700 p-3">
                            <legend class="block mb-2 text-sm font-medium dark:text-white">Departments:</legend>
                            <ul class="w-full text-sm font-medium text-gray-900 bg-white rounded-lg border border-gray-200 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <li class="w-full rounded-t-lg ">
                                    <?php foreach ($departments as $Department) : ?>
                                        <div class="flex items-center pl-3">
                                            <input type="checkbox" name="departments[]" value="<?php html($Departmenty['ID']); ?>" <?php if ($Department['selected']) echo ' checked' ?> class="w-4 h-4 text-blue-600 bg-gray-100 rounded border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                                            <label for="Category<?php html($Department['ID']); ?>" class="py-3 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300"><?php html($Department['Name']); ?></label>
                                        </div>
                                    <?php endforeach; ?>
                                </li>
                            </ul>
                        </fieldset>

                        <div>
                            <label for="ideaText" class="block mb-2 text-sm font-medium dark:text-white">Upload File</label>
                            <input class="block w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 cursor-pointer dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400" aria-describedby="file_input_help" id="file_input" type="file">
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-300" id="file_input_help">PNG or JPG</p>
                        </div>

                        <input type="hidden" name="ID" value="<?php html($ID); ?>">
                        <button type="submit" value="<?php html($button); ?>" class="modal-open w-full text-white bg-blue-600 hover:bg-primary-700 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800"><?php html($button); ?></button>
                    </form>
                </div>
            </div>
        </div>
    </section>

</body>


<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/components/footer.html.php'; ?>

</html>