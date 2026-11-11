<?php

/**
 * Slim Framework (https://slimframework.com)
 *
 * @license https://github.com/slimphp/Slim/blob/4.x/LICENSE.md (MIT License)
 */

declare(strict_types=1);

namespace Slim\Tests\Middleware;

use Psr\Http\Server\RequestHandlerInterface;
use Slim\Middleware\StaticFileMiddleware;
use Slim\Tests\TestCase;

use function array_map;
use function basename;
use function dirname;
use function glob;
use function file_put_contents;
use function md5;
use function mkdir;
use function rmdir;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;

class StaticFileMiddlewareTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        parent::setUp();

        $this->root = sys_get_temp_dir() . '/slim-static-' . uniqid();
        mkdir($this->root);
        file_put_contents($this->root . '/app.css', 'body{}');
    }

    protected function tearDown(): void
    {
        array_map('unlink', glob($this->root . '/*'));
        rmdir($this->root);

        parent::tearDown();
    }

    public function testServesExistingFile()
    {
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->never())->method('handle');

        $middleware = new StaticFileMiddleware($this->root, $this->getResponseFactory());
        $response = $middleware->process($this->createServerRequest('/app.css'), $handler);

        $this->assertSame('body{}', (string) $response->getBody());
        $this->assertSame('text/css', $response->getHeaderLine('Content-Type'));
        $this->assertSame('"' . md5('body{}') . '"', $response->getHeaderLine('ETag'));
    }

    public function testDelegatesWhenFileDoesNotExist()
    {
        $expected = $this->createResponse(404);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())->method('handle')->willReturn($expected);

        $middleware = new StaticFileMiddleware($this->root, $this->getResponseFactory());
        $response = $middleware->process($this->createServerRequest('/missing.css'), $handler);

        $this->assertSame($expected, $response);
    }

    public function testDoesNotServeFilesOutsideTheRoot()
    {
        $secret = dirname($this->root) . '/slim-secret-' . uniqid() . '.txt';
        file_put_contents($secret, 'secret');

        $expected = $this->createResponse(404);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())->method('handle')->willReturn($expected);

        $middleware = new StaticFileMiddleware($this->root, $this->getResponseFactory());
        $request = $this->createServerRequest('/../' . basename($secret));
        $response = $middleware->process($request, $handler);
        unlink($secret);

        $this->assertSame($expected, $response);
    }

    public function testAnswersNotModifiedWhenEtagMatches()
    {
        $handler = $this->createMock(RequestHandlerInterface::class);
        $request = $this->createServerRequest('/app.css')->withHeader('If-None-Match', '"' . md5('body{}') . '"');

        $middleware = new StaticFileMiddleware($this->root, $this->getResponseFactory());
        $response = $middleware->process($request, $handler);

        $this->assertSame(304, $response->getStatusCode());
    }
}
