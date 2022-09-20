<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/connection/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/flowbite@1.5.3/dist/flowbite.js"></script>
    <meta charset="utf-8">
    <title> <?php html($pageTitle) ?> </title>
</head>

<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/components/nav.html.php'; ?>

<body>

    <section class="bg-gray-50 dark:bg-gray-900 mx-auto h-screen lg:py-0">

        <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/components/card.html.php'; ?>

    </section>

</body>
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/components/footer.html.php'; ?>


</html>