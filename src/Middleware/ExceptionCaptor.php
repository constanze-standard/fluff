<?php

namespace ConstanzeStandard\Fluff\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ExceptionCaptor implements MiddlewareInterface
{
    /**
     * Exception handlers.
     * 
     * @var callable[]
     */
    private $exceptionHandlers = [];

    /**
     * Process an incoming server request.
     *
     * Processes an incoming server request in order to produce a response.
     * If unable to produce the response itself, it may delegate to the provided
     * request handler to do so.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (\Throwable $e) {
            return $this->exceptionHandlerProcess($request, $e);
        }
    }

    /**
     * Add a exception handler.
     * 
     * @param string $typeName
     * @param callable $handler
     */
    public function withExceptionHandler(string $typeName, callable $handler)
    {
        $this->exceptionHandlers[$typeName] = $handler;
    }

    /**
     * Process exception handler.
     * 
     * @param \Throwable $e
     * 
     * @throws \Throwable
     * 
     * @return ResponseInterface
     */
    private function exceptionHandlerProcess(ServerRequestInterface $request, \Throwable $e): ResponseInterface
    {
        $response = null;
        $className = get_class($e);
        if (array_key_exists($className, $this->exceptionHandlers)) {
            $handler = $this->exceptionHandlers[$className];
            $response = call_user_func($handler, $request, $e);
        }

        if (!$response) {
            foreach ($this->exceptionHandlers as $className => $handler) {
                if (is_a($e, $className)) {
                    $response = call_user_func($handler, $request, $e);
                    break;
                }
            }
        }

        if ($response instanceof ResponseInterface) {
            return $response;
        }

        throw $e;
    }
}
