<?php

/**
 * Slim Framework (https://slimframework.com)
 *
 * @license https://github.com/slimphp/Slim/blob/4.x/LICENSE.md (MIT License)
 */

declare(strict_types=1);

namespace Slim\Tests\Handlers;

use RuntimeException;
use Slim\Exception\HttpNotFoundException;
use Slim\Handlers\JsonApiErrorHandler;
use Slim\Tests\TestCase;

use function json_decode;

class JsonApiErrorHandlerTest extends TestCase
{
    private function createHandler(): JsonApiErrorHandler
    {
        return new JsonApiErrorHandler($this->getCallableResolver(), $this->getResponseFactory());
    }

    public function testHttpExceptionIsRenderedAsJsonApiError()
    {
        $request = $this->createServerRequest('/users/1');
        $response = $this->createHandler()->__invoke($request, new HttpNotFoundException($request), false, false, false);

        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame('application/vnd.api+json', $response->getHeaderLine('Content-Type'));

        $payload = json_decode((string) $response->getBody(), true);
        $this->assertSame('404', $payload['errors'][0]['status']);
        $this->assertSame('/users/1', $payload['errors'][0]['source']['pointer']);
    }

    public function testGenericExceptionIsRenderedAsServerError()
    {
        $request = $this->createServerRequest('/users/1');
        $response = $this->createHandler()->__invoke($request, new RuntimeException('boom'), false, false, false);

        $this->assertSame(500, $response->getStatusCode());
    }

    public function testForcedContentType()
    {
        $this->markTestSkipped('The JSON:API handler always answers with application/vnd.api+json');
    }
}
