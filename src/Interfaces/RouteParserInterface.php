<?php

namespace ConstanzeStandard\Fluff\Interfaces;

interface RouteParserInterface
{
    /**
     * Get the relative url by route.
     * 
     * @param string $name Name of route.
     * @param array $params Parameters of url.
     * @param array $queryParams The query parameters.
     * 
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * 
     * @return string The URL.
     */
    public function getRelativeUrlByName(string $name, array $params = [], array $queryParams = []): string;

    /**
     * Get the full url by route.
     * 
     * @param string $name Name of route.
     * @param array $params Parameters of url.
     * @param array $queryParams The query parameters.
     * 
     * @return string The URL.
     */
    public function getUrlByName(string $name, array $params = [], array $queryParams = []): string;
}
