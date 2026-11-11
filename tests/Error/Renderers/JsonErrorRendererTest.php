<?php

/**
 * Slim Framework (https://slimframework.com)
 *
 * @license https://github.com/slimphp/Slim/blob/4.x/LICENSE.md (MIT License)
 */

declare(strict_types=1);

namespace Slim\Tests\Error\Renderers;

use LogicException;
use RuntimeException;
use Slim\Error\Renderers\JsonErrorRenderer;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpForbiddenException;
use Slim\Exception\HttpNotFoundException;
use Slim\Tests\TestCase;

use function json_decode;

class JsonErrorRendererTest extends TestCase
{
    public function testRendersOnlyTheTitleWithoutDetails()
    {
        $renderer = new JsonErrorRenderer();
        $output = $renderer(new RuntimeException('Oops..'), false);

        $this->assertSame(['message' => 'Slim Application Error'], json_decode($output, true));
    }

    public function testRendersPreviousExceptionsWhenDisplayingDetails()
    {
        $exception = new RuntimeException('Oops..', 0, new LogicException('root cause'));
        $renderer = new JsonErrorRenderer();
        $decoded = json_decode($renderer($exception, true), true);

        $this->assertCount(2, $decoded['exception']);
        $this->assertSame(LogicException::class, $decoded['exception'][1]['type']);
    }

    public static function httpExceptionProvider(): array
    {
        return [
            [HttpNotFoundException::class, '404 Not Found'],
            [HttpForbiddenException::class, '403 Forbidden'],
            [HttpBadRequestException::class, '400 Bad Request'],
        ];
    }

    /**
     * @dataProvider httpExceptionProvider
     */
    public function testUsesTheTitleOfHttpExceptions(string $class, string $title)
    {
        $renderer = new JsonErrorRenderer();
        $output = $renderer(new $class($this->createServerRequest('/')), false);

        $this->assertSame($title, json_decode($output, true)['message']);
    }

    public function testRendererDoesNotFailOnInvalidUtf8()
    {
        $renderer = new JsonErrorRenderer();
        $renderer(new RuntimeException("bad \xB1 byte"), true);
    }
}
