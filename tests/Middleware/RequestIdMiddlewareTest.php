<?php

/**
 * Slim Framework (https://slimframework.com)
 *
 * @license https://github.com/slimphp/Slim/blob/4.x/LICENSE.md (MIT License)
 */

declare(strict_types=1);

namespace Slim\Tests\Middleware;

use Psr\Http\Server\RequestHandlerInterface;
use Slim\Middleware\RequestIdMiddleware;
use Slim\Tests\TestCase;

use function strlen;

class RequestIdMiddlewareTest extends TestCase
{
    private function dispatch(RequestIdMiddleware $middleware, $request, &$seenId)
    {
        $inner = function ($request, $handler) use (&$seenId) {
            $seenId = $request->getAttribute(RequestIdMiddleware::ATTRIBUTE);
            return $this->createResponse();
        };

        $middlewareDispatcher = $this->createMiddlewareDispatcher(
            $this->createMock(RequestHandlerInterface::class),
            null
        );
        $middlewareDispatcher->addCallable($inner);
        $middlewareDispatcher->addMiddleware($middleware);

        return $middlewareDispatcher->handle($request);
    }

    public function testGeneratesRequestIdWhenHeaderIsMissing()
    {
        $response = $this->dispatch(new RequestIdMiddleware(), $this->createServerRequest('/'), $seenId);

        $this->assertSame(32, strlen($seenId));
        $this->assertSame($seenId, $response->getHeaderLine('X-Request-Id'));
    }

    public function testKeepsValidRequestIdFromHeader()
    {
        $request = $this->createServerRequest('/')->withHeader('X-Request-Id', 'abc-12345678');
        $response = $this->dispatch(new RequestIdMiddleware(), $request, $seenId);

        $this->assertSame('abc-12345678', $seenId);
        $this->assertSame('abc-12345678', $response->getHeaderLine('X-Request-Id'));
    }

    public function testReplacesMalformedRequestId()
    {
        $request = $this->createServerRequest('/')->withHeader('X-Request-Id', "bad id\n");
        $response = $this->dispatch(new RequestIdMiddleware(), $request, $seenId);

        $this->assertNotSame('bad id', $seenId);
        $this->assertSame($seenId, $response->getHeaderLine('X-Request-Id'));
    }

    public function testCustomHeaderName()
    {
        $response = $this->dispatch(
            new RequestIdMiddleware('X-Correlation-Id'),
            $this->createServerRequest('/'),
            $seenId
        );

        $this->assertSame($seenId, $response->getHeaderLine('X-Correlation-Id'));
        $this->assertFalse($response->hasHeader('X-Request-Id'));
    }
}
