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
}
