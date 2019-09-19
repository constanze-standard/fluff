<?php

namespace ConstanzeStandard\Fluff\Interfaces;

/**
 * The dispatch data for dispatcher handler.
 */
interface DispatchDataInterface
{
    /**
     * Get the request callback.
     * 
     * @return callable|array|string
     */
    public function getHandler();

    /**
     * Get route middlewares.
     * 
     * @return \Psr\Http\Server\RequestHandlerInterface[]
     */
    public function getMiddlewares(): array;

    /**
     * Get route url arguments.
     * 
     * @return array
     */
    public function getArguments(): array;
}
