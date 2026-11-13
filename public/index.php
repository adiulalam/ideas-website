<?php

declare(strict_types=1);

/*
 * Front controller. Apache sends every request that is not a real file under
 * public/ here (FallbackResource). Application code lives outside the web
 * root, so nothing in src/, templates/, vendor/ or environment.php can be
 * requested directly.
 */

require dirname(__DIR__) . '/vendor/autoload.php';

App\App::fromGlobals(dirname(__DIR__))->run();
