<?php

/**
 * Slim Framework (https://slimframework.com)
 *
 * @license https://github.com/slimphp/Slim/blob/4.x/LICENSE.md (MIT License)
 */

declare(strict_types=1);

namespace Slim\Tests\Handlers;

use RuntimeException;
use Slim\Handlers\ErrorHandler;
use Slim\Tests\TestCase;

class ErrorHandlerNegotiationTest extends TestCase
{
    public static function acceptProvider(): array
    {
        return [
            'higher q wins' => ['text/html;q=0.5,application/json;q=0.9', 'application/json'],
            'xml over plain' => ['application/xml;q=0.8,text/plain;q=0.1', 'application/xml'],
            'wildcard falls back to json' => ['image/png,*/*;q=0.5', 'application/json'],
            'zero q is ignored' => ['application/json;q=0,text/html;q=0.2', 'text/html'],
        ];
    }

    /**
     * @dataProvider acceptProvider
     */
    public function testContentTypeIsNegotiatedByQuality(string $accept, string $expected)
    {
        $handler = new ErrorHandler($this->getCallableResolver(), $this->getResponseFactory());
        $request = $this->createServerRequest('/')->withHeader('Accept', $accept);

        $response = $handler->__invoke($request, new RuntimeException('Oops..'), false, false, false);

        $this->assertSame($expected, $response->getHeaderLine('Content-type'));
    }
}
