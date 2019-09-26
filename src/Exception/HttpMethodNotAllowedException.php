<?php

namespace ConstanzeStandard\Fluff\Exception;

use ConstanzeStandard\Standard\Http\Server\MethodNotAllowedExceptionInterface;
use RuntimeException;

class HttpMethodNotAllowedException extends RuntimeException implements MethodNotAllowedExceptionInterface
{
    /**
     * @param string $message
     * @param string[] $allowedMethods
     */
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
    public function getAllowedMethods(): array
    {
        return $this->allowedMethods;
    }
}
