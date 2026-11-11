<?php

/**
 * Slim Framework (https://slimframework.com)
 *
 * @license https://github.com/slimphp/Slim/blob/4.x/LICENSE.md (MIT License)
 */

declare(strict_types=1);

namespace Slim\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function bin2hex;
use function preg_match;
use function random_bytes;

/** @api */
class RequestIdMiddleware implements MiddlewareInterface
{
    public const ATTRIBUTE = 'request_id';

    private string $headerName;

    public function __construct(string $headerName = 'X-Request-Id')
    {
        $this->headerName = $headerName;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $requestId = $request->getHeaderLine($this->headerName);
        if (preg_match('/^[A-Za-z0-9\-]{8,64}$/', $requestId) !== 1) {
            $requestId = bin2hex(random_bytes(16));
        }

        $response = $handler->handle($request->withAttribute(self::ATTRIBUTE, $requestId));

        return $response->withHeader($this->headerName, $requestId);
    }
}
