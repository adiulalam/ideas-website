<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/connection/helpers.inc.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/views/auth/login/index.php';
if (userIsLoggedIn()) {
    header('Location: ' . '/');
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
    <link rel="stylesheet" href="https://unpkg.com/flowbite@1.5.3/dist/flowbite.min.css" />
    <script src="https://unpkg.com/flowbite@1.5.3/dist/flowbite.js"></script>
    <meta charset="utf-8">
    <title>Login</title>
</head>

<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/components/nav.html.php'; ?>

<body>
    <section class="bg-gray-900">
        <div class="flex <?php echo isMobileDevice() ? 'flex-row flex-wrap' : 'flex-col' ?> items-center justify-center px-6 py-8 mx-auto min-h-screen lg:py-0">
            <div class="<?php echo isMobileDevice() ? 'flex min-w-[70%]' : 'w-full' ?> rounded-lg shadow border md:mt-0 sm:max-w-md xl:p-0 bg-gray-800 border-gray-700">
                <div class="w-full p-6 space-y-4 md:space-y-6 sm:p-8">
                    <h1 class="<?php echo isMobileDevice() ? 'text-5xl' : 'text-xl md:text-2xl' ?> font-bold leading-tight tracking-tight text-white">
                        Login
                    </h1>
                    <form class="space-y-4 md:space-y-6" action="" method="POST">
                        <div>
                            <label for="email" class="block mb-2 <?php echo isMobileDevice() ? 'text-3xl' : 'text-sm' ?> font-medium text-white">Your email</label>
                            <input type="email" name="Email" id="email" class="border <?php echo isMobileDevice() ? 'text-3xl' : 'sm:text-sm' ?> rounded-lg block w-full p-2.5 bg-gray-700 border-gray-600 placeholder-gray-400 text-white focus:ring-blue-500 focus:border-blue-500" placeholder="joedavis@domain.com" required>
                        </div>
                        <div>
                            <label for="password" class="block mb-2 <?php echo isMobileDevice() ? 'text-3xl' : 'text-sm' ?> font-medium text-white">Password</label>
                            <input type="password" name="Password" id="password" minlength="8" placeholder="••••••••" class="border <?php echo isMobileDevice() ? 'text-3xl' : 'sm:text-sm' ?> rounded-lg  block w-full p-2.5 bg-gray-700 border-gray-600 placeholder-gray-400 text-white focus:ring-blue-500 focus:border-blue-500" required>
                        </div>
                        <div class=" flex items-center justify-center g-recaptcha <?php echo isMobileDevice() ? 'transform scale-150 p-4' : '' ?>" data-sitekey="6LfzRtIhAAAAAPlVMVWxaishNuL6inEyPsTIFSD6"></div>
                        <input type="hidden" name="action" value="login">
                        <button type="submit" class="modal-open w-full text-white focus:ring-4 focus:outline-none font-medium rounded-lg <?php echo isMobileDevice() ? 'text-3xl' : 'text-sm' ?> px-5 py-2.5 text-center bg-blue-600 hover:bg-blue-700 focus:ring-blue-800">Login</button>

                        <p class="<?php echo isMobileDevice() ? 'text-3xl' : 'text-sm' ?> font-light text-gray-400">
                            Don't have an account? <a href="<?php echo '/views/auth/register/' ?>" class="font-medium hover:underline text-primary-500">Regsiter here</a>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </section>
</body>


<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/components/footer.html.php'; ?>

</html>