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

use function fopen;
use function fread;
use function filesize;
use function is_file;
use function ltrim;
use function md5;
use function pathinfo;
use function rtrim;
use function urldecode;

use const PATHINFO_EXTENSION;

/** @api */
class StaticFileMiddleware implements MiddlewareInterface
{
    private string $root;

    private ResponseFactoryInterface $responseFactory;

    public function __construct(string $root, ResponseFactoryInterface $responseFactory)
    {
        $this->root = rtrim($root, '/');
        $this->responseFactory = $responseFactory;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $path = urldecode($request->getUri()->getPath());
        $file = $this->root . '/' . ltrim($path, '/');

        if (!is_file($file)) {
            return $handler->handle($request);
        }

        $handle = fopen($file, 'rb');
        $contents = fread($handle, filesize($file));

        $response = $this->responseFactory->createResponse();
        $response->getBody()->write($contents);

        return $response
            ->withHeader('Content-Type', $this->guessMimeType($file))
            ->withHeader('ETag', '"' . md5($contents) . '"');
    }

    private function guessMimeType(string $file): string
    {
        switch (pathinfo($file, PATHINFO_EXTENSION)) {
            case 'css':
                return 'text/css';
            case 'js':
                return 'application/javascript';
            case 'json':
                return 'application/json';
            case 'png':
                return 'image/png';
            case 'jpg':
            case 'jpeg':
                return 'image/jpeg';
            case 'svg':
                return 'image/svg+xml';
            case 'html':
                return 'text/html';
            case 'txt':
                return 'text/plain';
            case 'md':
                return 'text/plain';
            default:
                return 'text/plain';
        }
    }
}
