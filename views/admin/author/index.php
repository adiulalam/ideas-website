<?php

include_once $_SERVER['DOCUMENT_ROOT'] . "/environment.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/path.php";
require_once $_SERVER['DOCUMENT_ROOT'] . '/views/auth/login/index.php';

if (!userIsLoggedIn()) {
    include $_SERVER['DOCUMENT_ROOT'] . '/views/auth/login/login.html.php';
    exit();
}

if (!(userHasRole("Site Administrator") || (userHasRole("Account Administrator")))) {
    header('Location: /');
    exit();
}

echo 'hello';
