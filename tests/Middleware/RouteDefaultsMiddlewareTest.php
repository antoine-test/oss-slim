<?php

/**
 * Slim Framework (https://slimframework.com)
 *
 * @license https://github.com/slimphp/Slim/blob/4.x/LICENSE.md (MIT License)
 */

declare(strict_types=1);

namespace Slim\Tests\Middleware;

use Slim\App;
use Slim\Middleware\RouteDefaultsMiddleware;
use Slim\Tests\TestCase;

class RouteDefaultsMiddlewareTest extends TestCase
{
    private function createApp(array $defaults): App
    {
        $app = new App($this->getResponseFactory());
        $app->get('/items[/{page}]', function ($request, $response, $args) {
            $response->getBody()->write($args['page']);
            return $response;
        });
        $app->add(new RouteDefaultsMiddleware($defaults));
        $app->addRoutingMiddleware();

        return $app;
    }

    public function testAppliesDefaultArgument()
    {
        $app = $this->createApp(['page' => '1']);
        $response = $app->handle($this->createServerRequest('/items'));

        $this->assertSame('1', (string) $response->getBody());
    }

    public function testKeepsProvidedArgument()
    {
        $app = $this->createApp(['page' => '1']);
        $response = $app->handle($this->createServerRequest('/items/7'));

        $this->assertSame('7', (string) $response->getBody());
    }

    public function testEmptyDefaults()
    {
        $app = $this->createApp([]);
        $app->handle($this->createServerRequest('/items/2'));
    }
}
