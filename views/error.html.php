<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/connection/helpers.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'] . "/environment.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/path.php";
include "$_PATH[alertPath]";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://unpkg.com/tailwindcss@2.2.19/dist/tailwind.min.css" rel=" stylesheet">
    <script type="text/javascript" src="https://code.jquery.com/jquery-3.6.1.min.js"></script>
    <meta charset="utf-8">
    <title>Error Page</title>
</head>

<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/components/nav.html.php'; ?>

<body>
    <section class="bg-gray-900">
        <div class="flex flex-col items-center justify-center px-6 py-8 mx-auto h-screen lg:py-0">
            <?php
            generateAlert($error);
            exit();
            ?>
        </div>
    </section>

</body>

</html>