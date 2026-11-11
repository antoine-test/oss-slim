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
use Psr\Http\Server\RequestHandlerInterface;

use function explode;
use function str_getcsv;
use function strtolower;

/** @api */
class CsvBodyParsingMiddleware extends BodyParsingMiddleware
{
    protected function registerDefaultBodyParsers(): void
    {
        parent::registerDefaultBodyParsers();

        $this->registerBodyParser('text/csv', static function ($input) {
            if ($input === '') {
                return '';
            }

            $rows = [];
            foreach (explode("\n", $input) as $line) {
                $rows[] = str_getcsv($line);
            }

            return $rows;
        });
    }

    public function getMediaType(ServerRequestInterface $request): string
    {
        return strtolower(parent::getMediaType($request));
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->getMediaType($request) === 'text/csv') {
            $parser = $this->getBodyParser('text/csv');
            $request = $request->withParsedBody($parser((string) $request->getBody()));
        }

        return parent::process($request, $handler);
    }
}
