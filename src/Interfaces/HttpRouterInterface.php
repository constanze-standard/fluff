<?php

namespace ConstanzeStandard\Fluff\Interfaces;

use Psr\Http\Message\ServerRequestInterface;

interface HttpRouterInterface extends RouteableInterface
{
    /**
     * Dispatch request.
     * 
     * @param ServerRequestInterface $request
     * 
     * @return array Same with DispatcherInterface::dispatch
     */
    public function dispatch(ServerRequestInterface $request);
}
