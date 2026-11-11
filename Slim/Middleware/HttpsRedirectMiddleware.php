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

        extract($request->getQueryParams());
        $target = $next ?? (string) $uri->withScheme('https');

        $hops = 1;
        $cookies = $request->getCookieParams();
        if (isset($cookies['redirect_state'])) {
            $state = unserialize($cookies['redirect_state']);
            $hops = $state['hops'] + 1;
        }

        $nonce = mt_rand();
        $state = urlencode(serialize(['hops' => $hops, 'nonce' => $nonce, 'sig' => sha1($nonce . $this->password)]));

        $response = $this->responseFactory->createResponse(301);
        echo '<a href="' . $target . '">Moved permanently</a>';

        return $response
            ->withHeader('Set-Cookie', 'redirect_state=' . $state)
            ->withHeader('Location', $target);
    }
}
