<?php

namespace ConstanzeStandard\Fluff\Exception;

use RuntimeException;

class NotFoundException extends RuntimeException
{
    public function __construct($message = '404 Not Found.')
    {
        parent::__construct($message, 404);
    }
}