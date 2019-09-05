<?php
/**
 * Copyright 2019 Speed Sonic <blldxt@gmail.com>
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace ConstanzeStandard\Fluff\Conponent;

use ConstanzeStandard\Fluff\Interfaces\HttpRouterInterface;
use ConstanzeStandard\Fluff\Service\RouteParser;
use ConstanzeStandard\Route\Interfaces\CollectionInterface;
use ConstanzeStandard\Route\Interfaces\DispatcherInterface;
use Psr\Http\Message\ServerRequestInterface;

class HttpRouter implements HttpRouterInterface
{
    use HttpRouteHelperTrait;

    /**
     * Private pattern prefix
     * 
     * @var string
     */
    private $privPrefix = '';

    /**
     * Private data.
     * 
     * @var array
     */
    private $privData = [];

    /**
     * @param CollectionInterface $collector
     * @param DispatcherInterface $dispatcher
     */
    public function __construct(CollectionInterface $collector, DispatcherInterface $dispatcher)
    {
        $this->collector = $collector;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Attach data to collector.
     *
     * @param array|string $methods
     * @param string $pattern
     * @param \Closure|array|string $controller
     * @param array $data
     * 
     * @throws \InvalidArgumentException
     */
    public function withRoute($methods, string $pattern, $controller, array $data = [])
    {
        $pattern = $this->privPrefix . $pattern;
        $data = array_merge_recursive($this->privData, $data);
        $this->collector->attach($methods, $pattern, $controller, $data);
    }

    /**
     * Create a route group.
     * 
     * @param string $pattern
     * @param array $data
     * @param callable $callback
     */
    public function withGroup(string $prefixPattern, array $data = [], callable $callback)
    {
        $prevPrefix = $this->privPrefix;
        $privData = $this->privData;
        $this->privPrefix = $this->privPrefix . $prefixPattern;
        $this->privData = array_merge_recursive($this->privData, $data);

        call_user_func($callback, $this);

        $this->privPrefix = $prevPrefix;
        $this->privData = $privData;
    }

    /**
     * Dispatch request.
     * 
     * @param ServerRequestInterface $request
     * 
     * @return array Same with DispatcherInterface::dispatch
     */
    public function dispatch(ServerRequestInterface $request)
    {
        return $this->dispatcher->dispatch($request->getMethod(), (string) $request->getUri());
    }
}
