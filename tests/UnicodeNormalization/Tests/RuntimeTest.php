<?php

declare(strict_types=1);

/*
 * This file is part of the Unicode Normalization project.
 *
 * (c) Stephan Jorek <stephan.jorek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sjorek\UnicodeNormalization\Tests;

use PHPUnit\Framework\TestCase;
use Sjorek\UnicodeNormalization\Tests\Helper\ConfigurationHandler;

/**
 * Runtime tests.
 *
 * @coversNothing
 *
 * @author Stephan Jorek <stephan.jorek@gmail.com>
 */
class RuntimeTest extends TestCase
{
    /**
     * @group native
     * @group implementation
     * @coversNothing
     */
    public function testImplementationIsNative()
    {
        $this->assertTrue(extension_loaded('intl'));
        $this->assertFalse(ConfigurationHandler::isPolyfillImplementation());
    }

    /**
     * @group symfony
     * @group implementation
     * @coversNothing
     */
    public function testImplementationIsSymfony()
    {
        $this->assertFalse(extension_loaded('intl'));
        $this->assertTrue(ConfigurationHandler::isPolyfillImplementation());
        $this->assertTrue(is_a('Normalizer', ConfigurationHandler::SYMFONY_IMPLEMENTATION, true));
    }

    /**
     * @group patchwork
     * @group implementation
     * @coversNothing
     */
    public function testImplementationIsPatchwork()
    {
        $this->assertFalse(extension_loaded('intl'));
        $this->assertTrue(ConfigurationHandler::isPolyfillImplementation());
        $this->assertTrue(is_a('Normalizer', ConfigurationHandler::PATCHWORK_IMPLEMENTATION, true));
    }
}
