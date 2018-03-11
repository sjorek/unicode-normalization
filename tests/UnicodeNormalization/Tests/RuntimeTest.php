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
use Sjorek\UnicodeNormalization\Tests\Helper\NormalizationTestHandler;

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
     * @group runtime
     * @coversNothing
     */
    public function testImplementationIsNative()
    {
        $this->assertTrue(extension_loaded('intl'));
        $this->assertFalse(ConfigurationHandler::isPolyfillImplementation());
    }

    /**
     * @group symfony
     * @group runtime
     * @coversNothing
     */
    public function testRuntimeIsSymfony()
    {
        $this->assertFalse(extension_loaded('intl'));
        $this->assertTrue(ConfigurationHandler::isPolyfillImplementation());
        $this->assertTrue(is_a('Normalizer', ConfigurationHandler::SYMFONY_IMPLEMENTATION, true));
    }

    /**
     * @group patchwork
     * @group runtime
     * @coversNothing
     */
    public function testRuntimeIsPatchwork()
    {
        $this->assertFalse(extension_loaded('intl'));
        $this->assertTrue(ConfigurationHandler::isPolyfillImplementation());
        $this->assertTrue(is_a('Normalizer', ConfigurationHandler::PATCHWORK_IMPLEMENTATION, true));
    }

    /**
     * @group native
     * @group symfony
     * @group patchwork
     * @group runtime
     * @coversNothing
     */
    public function testKnownUnicodeVersionIsUpToDate()
    {
        $localVersion = NormalizationTestHandler::UPDATE_CHECK_VERSION_LATEST;
        $latestVersion = NormalizationTestHandler::detectLatestVersion();
        $this->assertTrue(version_compare($localVersion, $latestVersion, '='));
    }
}
