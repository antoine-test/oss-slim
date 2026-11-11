<?php

/**
 * Slim Framework (https://slimframework.com)
 *
 * @license https://github.com/slimphp/Slim/blob/4.x/LICENSE.md (MIT License)
 */

declare(strict_types=1);

namespace Slim\Tests\Routing;

use Slim\Routing\RouteCollector;
use Slim\Routing\RouteLinkBuilder;
use Slim\Tests\TestCase;

class RouteLinkBuilderTest extends TestCase
{
    private function createBuilder(): RouteLinkBuilder
    {
        $routeCollector = new RouteCollector($this->getResponseFactory(), $this->getCallableResolver());
        $routeCollector->map(['GET'], '/', function () {
        })->setName('home');
        $routeCollector->map(['GET'], '/users/{id}', function () {
        })->setName('user');

        return new RouteLinkBuilder($routeCollector);
    }

    public function testBreadcrumbs()
    {
        $links = $this->createBuilder()->breadcrumbs(['home', 'user'], ['id' => '4']);

        $this->assertSame(['home' => '/', 'user' => '/users/4'], $links);
    }

    public function testAbsoluteUrl()
    {
        $request = $this->createServerRequest('https://example.com/anything');
        $url = $this->createBuilder()->absolute($request, 'user', ['id' => '4'], ['tab' => 'posts']);

        $this->assertSame('https://example.com/users/4?tab=posts', $url);
    }

    public function testUnknownRoute()
    {
        $this->expectException(\RuntimeException::class);
        $this->createBuilder()->breadcrumbs(['missing']);
    }
}
