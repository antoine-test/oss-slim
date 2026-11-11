<?php

/**
 * Slim Framework (https://slimframework.com)
 *
 * @license https://github.com/slimphp/Slim/blob/4.x/LICENSE.md (MIT License)
 */

declare(strict_types=1);

namespace Slim\Tests\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Middleware\CsvBodyParsingMiddleware;
use Slim\Tests\TestCase;

class CsvBodyParsingMiddlewareTest extends TestCase
{
    private function createRequestHandler(): RequestHandlerInterface
    {
        $response = $this->createResponse();
        return new class ($response) implements RequestHandlerInterface {
            private $response;
            public $request;

            public function __construct(ResponseInterface $response)
            {
                $this->response = $response;
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $this->request = $request;
                return $this->response;
            }
        };
    }

    public function testParsesCsvBody()
    {
        $request = $this->createServerRequest('/', 'POST')
            ->withHeader('Content-Type', 'text/csv; charset=utf-8')
            ->withBody($this->createStream("a,b\n1,2"));
        $handler = $this->createRequestHandler();

        (new CsvBodyParsingMiddleware())->process($request, $handler);

        $this->assertSame([['a', 'b'], ['1', '2']], $handler->request->getParsedBody());
    }

    public function testStillParsesJson()
    {
        $request = $this->createServerRequest('/', 'POST')
            ->withHeader('Content-Type', 'application/json')
            ->withBody($this->createStream('{"foo":"bar"}'));
        $handler = $this->createRequestHandler();

        (new CsvBodyParsingMiddleware())->process($request, $handler);

        $this->assertSame(['foo' => 'bar'], $handler->request->getParsedBody());
    }

    public function testRequestWithoutContentTypeIsNotParsed()
    {
        $request = $this->createServerRequest('/', 'POST')->withBody($this->createStream('a,b'));
        $handler = $this->createRequestHandler();

        (new CsvBodyParsingMiddleware())->process($request, $handler);

        $this->assertNull($handler->request->getParsedBody());
    }
}
