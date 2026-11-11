<?php

/**
 * Slim Framework (https://slimframework.com)
 *
 * @license https://github.com/slimphp/Slim/blob/4.x/LICENSE.md (MIT License)
 */

declare(strict_types=1);

namespace Slim\Tests\Error\Renderers;

use LogicException;
use RuntimeException;
use Slim\Error\Renderers\PlainTextErrorRenderer;
use Slim\Tests\TestCase;

class PlainTextErrorRendererTest extends TestCase
{
    public function testRendersOnlyTheTitleWithoutDetails()
    {
        $renderer = new PlainTextErrorRenderer();

        $this->assertSame("Slim Application Error\n", $renderer(new RuntimeException('Oops..'), false));
    }

    public function testRendersTypeAndMessageWithDetails()
    {
        $renderer = new PlainTextErrorRenderer();
        $output = $renderer(new RuntimeException('Oops..'), true);

        $this->assertStringContainsString('Type: RuntimeException', $output);
        $this->assertStringContainsString('Message: Oops..', $output);
    }

    public function testRendersPreviousErrorSection()
    {
        $renderer = new PlainTextErrorRenderer();
        $output = $renderer(new RuntimeException('Oops..', 0, new LogicException('root cause')), true);

        $this->assertStringContainsString("Previous Error:\nType: LogicException", $output);
    }

    public function testRendersToStderr()
    {
        $this->markTestSkipped('Not implemented yet');
    }
}
