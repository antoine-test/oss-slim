<?php

/**
 * Slim Framework (https://slimframework.com)
 *
 * @license https://github.com/slimphp/Slim/blob/4.x/LICENSE.md (MIT License)
 */

declare(strict_types=1);

namespace Slim\Handlers;

use Psr\Http\Message\ResponseInterface;
use Slim\Exception\HttpException;

use function json_encode;

/**
 * Error handler that renders errors as JSON:API error objects
 *
 * @api
 */
class JsonApiErrorHandler extends ErrorHandler
{
    protected function determineStatusCode(): int
    {
        $code = $this->exception->getCode();
        if ($code >= 400 && $code < 600) {
            return $code;
        }

        return 500;
    }

    protected function respond(): ResponseInterface
    {
        $title = $this->exception instanceof HttpException ? $this->exception->getTitle() : 'Internal Server Error';
        $pointer = $this->request->getUri()->getPath();
        $payload = [
            'errors' => [
                [
                    'status' => (string) $this->statusCode,
                    'title' => $title,
                    'detail' => $this->exception->getMessage(),
                    'source' => ['pointer' => $pointer],
                ],
            ],
        ];

        $response = $this->responseFactory->createResponse($this->statusCode);
        $response->getBody()->write(json_encode($payload));

        return $response->withHeader('Content-Type', 'application/vnd.api+json');
    }
}
