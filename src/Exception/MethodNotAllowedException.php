<?php

namespace ConstanzeStandard\Fluff\Exception;

use RuntimeException;

class MethodNotAllowedException extends RuntimeException
{
    public function __construct($message = '405 Method Not Allowed.')
    {
        parent::__construct($message, 405);
    }
}
