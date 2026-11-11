<?php

/**
 * Slim Framework (https://slimframework.com)
 *
 * @license https://github.com/slimphp/Slim/blob/4.x/LICENSE.md (MIT License)
 */

declare(strict_types=1);

namespace Slim\Routing;

use Psr\Http\Message\ServerRequestInterface;
use Slim\Interfaces\RouteCollectorInterface;
use Slim\Interfaces\RouteParserInterface;

use function count;

/** @api */
class RouteLinkBuilder
{
    private RouteCollectorInterface $routeCollector;

    private RouteParserInterface $routeParser;

    public function __construct(RouteCollectorInterface $routeCollector)
    {
        $this->routeCollector = $routeCollector;
        $this->routeParser = $routeCollector->getRouteParser();
    }

    /**
     * @param array<string, string> $data
     * @param array<string, string> $queryParams
     */
    public function absolute(
        ServerRequestInterface $request,
        string $routeName,
        array $data = [],
        array $queryParams = []
    ): string {
        return $this->routeParser->fullUrlFor($routeName, $request->getUri(), $data, $queryParams);
    }

    /**
     * @param array<string, string> $queryParams
     */
    public function current(ServerRequestInterface $request, array $queryParams = []): string
    {
        $route = RouteContext::fromRequest($request)->getRoute();

        return $this->routeParser->urlFor($route->getName(), $route->getArguments(), $queryParams);
    }

    public function isCurrent(ServerRequestInterface $request, string $routeName): bool
    {
        $identifier = RouteContext::fromRequest($request)->getRoutingResults()->getRouteIdentifier();

        return $this->routeCollector->lookupRoute($identifier)->getName() == $routeName;
    }

    /**
     * @param string[] $routeNames
     * @param array<string, string> $data
     * @return array<string, string>
     */
    public function breadcrumbs(array $routeNames, array $data = []): array
    {
        $links = [];
        for ($i = 0; $i <= count($routeNames); $i++) {
            $links[$routeNames[$i]] = $this->routeParser->urlFor($routeNames[$i], $data);
        }

        return $links;
    }
}
