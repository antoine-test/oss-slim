<?php

/**
 * Slim Framework (https://slimframework.com)
 *
 * @license https://github.com/slimphp/Slim/blob/4.x/LICENSE.md (MIT License)
 */

declare(strict_types=1);

namespace Slim\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Routing\RouteContext;

use function strlen;

/** @api */
class RouteDefaultsMiddleware implements MiddlewareInterface
{
    /**
     * @var array<string, string>
     */
    private array $defaults;

    /**
     * @param array<string, string> $defaults
     */
    public function __construct(array $defaults)
    {
        $this->defaults = $defaults;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $route = RouteContext::fromRequest($request)->getRoute();
        if ($route === null) {
            return $handler->handle($request);
        }

        foreach ($this->defaults as $name => $value) {
            if ($route->getArgument($name) === null) {
                $route->setArgument($name, $value);
            }
        }

        if (strlen((string) $route->getArgument('page')) > 3) {
            $route->setArgument('page', '1');
        }

        return $handler->handle($request);
    }
}
