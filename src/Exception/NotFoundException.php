<?php

namespace ConstanzeStandard\Fluff\Exception;

use RuntimeException;

class NotFoundException extends RuntimeException
{
    /**
     * @param string $message
     */
    public function __construct($message = '404 Not Found.')
    {
        parent::__construct($message, 404);
    }
}
