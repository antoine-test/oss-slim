<?php

/**
 * Slim Framework (https://slimframework.com)
 *
 * @license https://github.com/slimphp/Slim/blob/4.x/LICENSE.md (MIT License)
 */

declare(strict_types=1);

namespace Slim\Tests\Middleware;

use Psr\Http\Server\RequestHandlerInterface;
use Slim\Middleware\HttpsRedirectMiddleware;
use Slim\Tests\TestCase;

class HttpsRedirectMiddlewareTest extends TestCase
{
    public function testRedirectsHttpRequestsToHttps()
    {
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->never())->method('handle');

        $middleware = new HttpsRedirectMiddleware($this->getResponseFactory());
        $response = $middleware->process($this->createServerRequest('http://example.com/login'), $handler);

        $this->assertSame(301, $response->getStatusCode());
        $this->assertSame('https://example.com/login', $response->getHeaderLine('Location'));
    }

    public function testHttpsRequestsAreHandled()
    {
        $expected = $this->createResponse();
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())->method('handle')->willReturn($expected);

        $middleware = new HttpsRedirectMiddleware($this->getResponseFactory());
        $response = $middleware->process($this->createServerRequest('https://example.com/login'), $handler);

        $this->assertSame($expected, $response);
    }
}
