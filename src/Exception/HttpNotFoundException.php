<?php

namespace ConstanzeStandard\Fluff\Exception;

use ConstanzeStandard\Standard\Http\Server\NotFoundExceptionInterface;
use RuntimeException;

class HttpNotFoundException extends RuntimeException implements NotFoundExceptionInterface
{
    /**
     * @param string $message
     */
    public function __construct($message = '404 Not Found.')
    {
        parent::__construct($message, 404);
    }
}
