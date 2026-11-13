<?php

declare(strict_types=1);

namespace App;

use RuntimeException;

/** Thrown when the MySQL server cannot be reached. Rendered as a 503 page. */
final class DatabaseUnavailable extends RuntimeException
{
}
