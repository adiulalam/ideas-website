<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/connection/helpers.inc.php';

function commentMutationCheck($CommentID, $totalComments)
{
    require_once $_SERVER['DOCUMENT_ROOT'] . '/views/auth/login/index.php';

    $authorID = $_COOKIE['aid'];

    if (!userIsLoggedIn() || !$authorID || !$totalComments || !$CommentID) {
        return false;
    }

    if (in_array($CommentID, $totalComments)) {
        $mutationForm = "
        <form action='' method='post' class=' float-right inline-flex items-center '>
            <input type='hidden' name='ID' value='$CommentID'>
            <Button type='button' data-modal-toggle='$CommentID' class=' float-right inline-flex items-center mx-1 py-1 px-3 text-sm font-medium text-center text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-red-300 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-800'>Delete</Button>
        </form>

        <div id='$CommentID' tabindex='-1' class='hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 md:inset-0 h-modal md:h-full'>
            <div class='relative p-4 w-full max-w-md h-full md:h-auto'>
                <div class='relative bg-white rounded-lg shadow dark:bg-gray-700'>
                    <button type='button' class='absolute top-3 right-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-800 dark:hover:text-white' data-modal-toggle='$CommentID'>
                        <svg aria-hidden='true' class='w-5 h-5' fill='currentColor' viewBox='0 0 20 20' xmlns='http://www.w3.org/2000/svg'><path fill-rule='evenodd' d='M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z' clip-rule='evenodd'></path></svg>
                        <span class='sr-only'>Close modal</span>
                    </button>
                    <div class='p-6 text-center'>
                        <svg aria-hidden='true' class='mx-auto mb-4 w-14 h-14 text-gray-400 dark:text-gray-200' fill='none' stroke='currentColor' viewBox='0 0 24 24' xmlns='http://www.w3.org/2000/svg'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'></path></svg>
                        <h3 class='mb-5 text-lg font-normal text-gray-500 dark:text-gray-400'>Are you sure you want to delete this Comment?</h3>
                        <form action='' method='post'>
                            <input type='hidden' name='ID' value='$CommentID'>
                            <button data-modal-toggle='$CommentID' type='submit' name='action' value='deleteComment' class='text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center mr-2'>
                                Yes, I'm sure
                            </button>
                        <button data-modal-toggle='$CommentID' type='button' class='text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600'>No, cancel</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        ";

        echo $mutationForm;
    } else {
        return false;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/flowbite@1.5.3/dist/flowbite.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/flowbite@1.5.3/dist/flowbite.min.css" />
    <meta charset="utf-8">
    <title> <?php html("$pageTitle - Idea") ?> </title>
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
                <div class="flex justify-end items-center py-2 px-3 border-t dark:border-gray-600">
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
                    <p class="py-1 px-2 text-sm float-left dark:text-gray-400"><?php html(time_elapsed_string($Comment['Time'])); ?></p>
                    <?php commentMutationCheck($Comment['CommentID'], $totalComments) ?>
                    <p class="py-1 px-2 text-sm float-right dark:text-gray-400">By <?php html($Comment['Name']); ?></p>
                </div>

        <?php endforeach;
        endif; ?>

    </section>

</body>
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/components/footer.html.php'; ?>

</html>