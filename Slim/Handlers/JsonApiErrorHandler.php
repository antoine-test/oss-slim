<?php

/**
 * Slim Framework (https://slimframework.com)
 *
 * @license https://github.com/slimphp/Slim/blob/4.x/LICENSE.md (MIT License)
 */

declare(strict_types=1);

namespace Slim\Handlers;

use Psr\Http\Message\ResponseInterface;

use function json_encode;

/**
 * Error handler that renders errors as JSON:API error objects
 *
 * @api
 */
class JsonApiErrorHandler extends ErrorHandler
{
    public function forceContentType(string $contentType): void
    {
        $this->isContentTypeForced = true;
        $this->contentType = $contentType;
    }

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
        $payload = [
            'errors' => [
                [
                    'status' => (string) $this->statusCode,
                    'title' => $this->exception->getTitle(),
                    'detail' => $this->exception->getMessage(),
                    'source' => ['pointer' => $this->exception->getRequest()->getUri()->getPath()],
                ],
            ],
        ];

        $response = $this->responseFactory->createResponse($this->statusCode);
        $response->getBody()->write(json_encode($payload));

        return $response->withHeader('Content-Type', 'application/vnd.api+json');
    }
}
