<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

/** An upload was rejected. The message is safe to show to the user. */
final class UploadException extends RuntimeException
{
}
