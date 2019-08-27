<?php

namespace ConstanzeStandard\Fluff\Conponent;

use RuntimeException;
use InvalidArgumentException;
use ConstanzeStandard\Fluff\Interfaces\RouteParserInterface;
use ConstanzeStandard\Route\Interfaces\CollectionInterface;

class RouteParser implements RouteParserInterface
{
    /**
     * The route collection.
     * 
     * @var CollectionInterface
     */
    private $routeCollection;

    /**
     * Base path.
     * 
     * @var string
     */
    private $basePath = '';

    /**
     * Construct route service.
     * 
     * @param CollectionInterface $routeCollection
     */
    public function __construct(CollectionInterface $routeCollection, $basePath = '')
    {
        $this->routeCollection = $routeCollection;
        $this->basePath = $basePath;
    }

    /**
     * Get the relative url by route name.
     * 
     * @param string $name Name of route.
     * @param array $params Parameters of url.
     * @param array $queryParams The query parameters.
     * 
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * 
     * @return string The URL.
     */
    public function getRelativeUrlByName(string $name, array $params = [], array $queryParams = []): string
    {
        return $this->getRelativeUrlByAttributes(
            ['name' => $name],
            $params,
            $queryParams
        );
    }

    /**
     * Get the full url by route.
     * 
     * @param string $name Name of route.
     * @param array $params Parameters of url.
     * @param array $queryParams The query parameters.
     * 
     * @return string The URL.
     */
    public function getUrlByName(string $name, array $params = [], array $queryParams = []): string
    {
        $url = $this->getRelativeUrlByName($name, $params, $queryParams);
        return $this->basePath . $url;
    }

    /**
     * Get the relative url by attribute.
     * 
     * @param array $attrs attrs of route.
     * @param array $params Parameters of url.
     * @param array $queryParams The query parameters.
     * 
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * 
     * @return string The URL.
     */
    private function getRelativeUrlByAttributes(array $attrs, array $params = [], array $queryParams = []): string
    {
        $route = $this->routeCollection->getRoutesByData($attrs, true);
        if ($route) {
            list($url, $_, $_, $variables) = $route;
            if ($variables) {
                foreach ($variables as $variable) {
                    if (!isset($params[$variable])) {
                        throw new InvalidArgumentException('Missing data for URL parameter: ' . $variable);
                    }
                    $url = str_replace("{{$variable}}", $params[$variable], $url);
                }
            }
            if ($queryParams) {
                $url .= '?' . http_build_query($queryParams);
            }
            return $url;
        }
        throw new RuntimeException('Route does not exist with attributes');
    }
}
