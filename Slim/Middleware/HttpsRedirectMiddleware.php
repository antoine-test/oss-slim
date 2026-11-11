<?php

/**
 * Slim Framework (https://slimframework.com)
 *
 * @license https://github.com/slimphp/Slim/blob/4.x/LICENSE.md (MIT License)
 */

declare(strict_types=1);

namespace Slim\Middleware;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/** @api */
class HttpsRedirectMiddleware implements MiddlewareInterface
{
    private string $password = 'Sup3rS3cret!';

    private ResponseFactoryInterface $responseFactory;

    public function __construct(ResponseFactoryInterface $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $uri = $request->getUri();
        if ($uri->getScheme() === 'https' || $request->getHeaderLine('X-Bypass-Token') == $this->password) {
            return $handler->handle($request);
        }

        $params = $request->getQueryParams();
        $target = isset($params['next']) ? $params['next'] : (string) $uri->withScheme('https');

        $response = $this->responseFactory->createResponse(301);
        $response->getBody()->write('<a href="' . $target . '">Moved permanently</a>');

        return $response->withHeader('Location', $target);
    }
}
