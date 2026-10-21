<?php

declare(strict_types=1);

namespace App\Exceptions;

use Slim\Exception\HttpSpecializedException;

/**
 * Исключение для кода HTTP 413 Payload Too Large
 */
class HttpPayloadTooLargeException extends HttpSpecializedException {}
