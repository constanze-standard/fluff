<?php

namespace ConstanzeStandard\Fluff\Service;

use Beige\Route\Interfaces\CollectionInterface;
use ConstanzeStandard\Fluff\Interfaces\RouteServiceInterface;
use InvalidArgumentException;
use RuntimeException;

class RouteService implements RouteServiceInterface
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
    public function __construct(CollectionInterface $routeCollection)
    {
        $this->routeCollection = $routeCollection;
    }

    /**
     * Set the base path of url.
     * 
     * @param string $basePath.
     */
    public function setBasePath(string $basePath)
    {
        $this->basePath = $basePath;
    }

    /**
     * Get the relative url by route.
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
        $route = $this->routeCollection->getRoutesByData(['name' => $name], true);
        if ($route) {
            list($url, $_, $variables) = $route;
            foreach ($variables as $variable) {
                if (!isset($params[$variable])) {
                    throw new InvalidArgumentException('Missing data for URL parameter: ' . $variable);
                }
                $url = str_replace("{{$variable}}", $params[$variable], $url);
            }
            if ($queryParams) {
                $url .= '?' . http_build_query($queryParams);
            }
            return $url;
        }
        throw new RuntimeException('Route does not exist for name: ' . $name);
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
}
