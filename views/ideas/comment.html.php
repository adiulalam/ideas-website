<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/connection/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/flowbite@1.5.3/dist/flowbite.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/flowbite@1.5.3/dist/flowbite.min.css" />
    <meta charset="utf-8">
    <title> <?php html($pageTitle) ?> </title>
</head>

<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/components/nav.html.php'; ?>

<body>

    <section class="dark:bg-gray-900 mx-auto min-h-screen lg:py-0">

        <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/components/card.html.php'; ?>

        <form method="post" action="">
            <div class="my-4 w-96 rounded-lg border dark:bg-gray-700 dark:border-gray-600">
                <div class="py-2 px-4 rounded-t-lg dark:bg-gray-800">
                    <label for="comment" class="sr-only">Your comment</label>
                    <textarea id="comment" name="comment" rows="4" class="px-0 w-full text-sm text-gray-900 border-0 dark:bg-gray-800 focus:ring-0 dark:text-white dark:placeholder-gray-400" placeholder="Write a comment..." required></textarea>
                </div>
                <div class="flex justify-between items-center py-2 px-3 border-t dark:border-gray-600">
                    <button name="postComment" type="submit" class="inline-flex items-center py-2.5 px-4 text-xs font-medium text-center text-white bg-blue-700 rounded-lg focus:ring-4 focus:ring-blue-200 dark:focus:ring-blue-900 hover:bg-blue-800">
                        Post comment
                    </button>
                </div>
            </div>
        </form>

        <?php if (isset($comments)) :
            foreach ($comments as $Comment) : ?>
                <div class="block p-2 m-1 w-96 max-w-sm rounded-lg border border-gray-200 shadow-md dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-700">
                    <p class="p-2 text-sm dark:text-gray-200"><?php html($Comment['Comment']); ?></p>
                    <p class="py-1 px-2 text-sm float-right dark:text-gray-400">By <?php html($Comment['Name']); ?></p>
                    <p class="py-1 px-2 text-sm float-left dark:text-gray-400"><?php html(time_elapsed_string($Comment['Time'])); ?></p>
                </div>
                <input type='hidden' name='CommentID' value='<?php echo ($Comment['CommentID']); ?>'>
        <?php endforeach;
        endif; ?>

    </section>

</body>
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/components/footer.html.php'; ?>

</html>