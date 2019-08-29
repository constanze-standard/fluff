<?php

namespace ConstanzeStandard\Fluff\Exception;

use RuntimeException;

class MethodNotAllowedException extends RuntimeException
{
    public function __construct($message = '405 Method Not Allowed.', $allowedMethods = [])
    {
        parent::__construct($message, 405);
        $this->allowedMethods = $allowedMethods;
    }

    /**
     * Get allowed methods.
     * 
     * @return array
     */
    public function getAllowedMethods()
    {
        return $this->allowedMethods;
    }
}
