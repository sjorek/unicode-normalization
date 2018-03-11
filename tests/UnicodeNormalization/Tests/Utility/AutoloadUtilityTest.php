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

namespace Sjorek\UnicodeNormalization\Tests\Utility;

use Sjorek\UnicodeNormalization\Implementation\InvalidNormalizer;
// DO NOT USE HERE, TO PREVENT TOO EARLY AUTOLOADING
// use Sjorek\UnicodeNormalization\Implementation\MacNormalizer;
use Sjorek\UnicodeNormalization\Implementation\StrictNormalizer;
use Sjorek\UnicodeNormalization\Tests\AbstractTestCase;
use Sjorek\UnicodeNormalization\Tests\Helper\ConfigurationHandler;
use Sjorek\UnicodeNormalization\Utility\AutoloadUtility;
use Sjorek\UnicodeNormalization\Validation\Implementation\StringValidatorBugfix65732;
use Sjorek\UnicodeNormalization\Validation\Implementation\StringValidatorImpl;

/**
 * AutoloadUtility tests.
 *
 * @coversDefaultClass \Sjorek\UnicodeNormalization\Utility\AutoloadUtility
 *
 * @author Stephan Jorek <stephan.jorek@gmail.com>
 */
class AutoloadUtilityTest extends AbstractTestCase
{
    // ///////////////////////////////////////////////////
    // Tests concerning implementation registry
    // ///////////////////////////////////////////////////

    /**
     * @covers ::register()
     *
     * @uses \Sjorek\UnicodeNormalization\Utility\AutoloadUtility::getRootNamespace
     * @uses \Sjorek\UnicodeNormalization\Utility\AutoloadUtility::registerNormalizerImplementation
     * @uses \Sjorek\UnicodeNormalization\Utility\AutoloadUtility::registerStringValidatorImplementation
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     *
     * @see AutoloadUtility::register()
     */
    public function testRegister()
    {
        $this->assertTrue(
            AutoloadUtility::register(),
            'initial registration succeeds'
        );
        $this->assertFalse(
            AutoloadUtility::register(),
            'subsequent registration fails'
        );
    }

    /**
     * @covers ::registerNormalizerImplementation()
     *
     * @uses \Sjorek\UnicodeNormalization\Utility\AutoloadUtility::getRootNamespace
     * @uses \Sjorek\UnicodeNormalization\Utility\AutoloadUtility::isNfdMacCompatible
     * @uses \Sjorek\UnicodeNormalization\Utility\AutoloadUtility::isStrictImplementation
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     *
     * @see AutoloadUtility::registerNormalizerImplementation()
     */
    public function testRegisterNormalizerImplementation()
    {
        $this->assertTrue(
            AutoloadUtility::registerNormalizerImplementation(),
            'initial registration succeeds'
        );
        $this->assertFalse(
            AutoloadUtility::registerNormalizerImplementation(),
            'subsequent registration fails'
        );
        $className = \Sjorek\UnicodeNormalization\Normalizer::class;
        $this->assertSame(
            AutoloadUtility::isNfdMacCompatible(),
            is_a($className, \Sjorek\UnicodeNormalization\Implementation\MacNormalizer::class, true)
        );
        $this->assertSame(
            AutoloadUtility::isStrictImplementation(),
            // strict implementations should not inherit the strict-enforcing facade
            !is_a($className, StrictNormalizer::class, true)
        );
    }

    /**
     * @coversNothing
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testRegisterNormalizerImplementationSkipped()
    {
        $this->assertTrue(
            is_a(\Sjorek\UnicodeNormalization\Normalizer::class, InvalidNormalizer::class, true)
        );
        $this->assertTrue(
            is_a(\Sjorek\UnicodeNormalization\Implementation\NormalizerImpl::class, InvalidNormalizer::class, true)
        );
    }

    /**
     * @covers ::registerStringValidatorImplementation()
     *
     * @uses \Sjorek\UnicodeNormalization\Utility\AutoloadUtility::getRootNamespace
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     *
     * @see AutoloadUtility::registerStringValidatorImplementation()
     */
    public function testRegisterStringValidatorImplementation()
    {
        $this->assertTrue(
            AutoloadUtility::registerStringValidatorImplementation(),
            'initial registration succeeds');
        $this->assertFalse(
            AutoloadUtility::registerStringValidatorImplementation(),
            'subsequent registration fails'
        );
        $className = \Sjorek\UnicodeNormalization\Validation\StringValidator::class;
        $this->assertTrue(is_a($className, StringValidatorImpl::class, true));
        $this->assertSame(
            version_compare(PHP_VERSION, '7.0.11', '<'),
            // with buggy php the implementation should inherit the bugfix facade
            is_a($className, StringValidatorBugfix65732::class, true)
        );
    }

    /**
     * @covers ::registerStringValidatorImplementation()
     *
     * @uses \Sjorek\UnicodeNormalization\Utility\AutoloadUtility::getRootNamespace
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     *
     * @see AutoloadUtility::registerStringValidatorImplementation()
     */
    public function testRegisterStringValidatorImplementationBugfix65732()
    {
        require_once __DIR__ . '/../Fixtures/AutoloadUtilityTestFixture.php';
        $this->assertTrue(AutoloadUtility::registerStringValidatorImplementation());
        $this->assertTrue(
            is_a(
                \Sjorek\UnicodeNormalization\Validation\StringValidator::class,
                StringValidatorBugfix65732::class,
                true
            )
        );
    }

    // ///////////////////////////////////////////////////
    // Tests concerning implementation capabilities
    // ///////////////////////////////////////////////////

    /**
     * @covers ::isNfdMacCompatible()
     */
    public function testIsNfdMacCompatible()
    {
        $expected = extension_loaded('iconv');
        if ($expected) {
            $level = error_reporting();
            error_reporting(E_ALL);
            set_error_handler(
                function ($errno, $errstr, $errfile, $errline) use (&$expected) {
                    $message = 'iconv(): Wrong charset';
                    if ($message === substr($errstr, 0, strlen($message))) {
                        $expected = false;
                    }
                },
                E_ALL
            );
            iconv('utf-8', 'utf-8-mac', 'xxx');
            restore_error_handler();
            error_reporting($level);
        }
        $this->assertSame($expected, AutoloadUtility::isNfdMacCompatible());
    }

    /**
     * @covers ::isStrictImplementation()
     */
    public function testIsStrictImplementation()
    {
        $isStrict = AutoloadUtility::isStrictImplementation();
        if (ConfigurationHandler::isPolyfillImplementation()) {
            $this->assertFalse($isStrict);
        } else {
            $this->assertTrue($isStrict);
        }
    }
}
