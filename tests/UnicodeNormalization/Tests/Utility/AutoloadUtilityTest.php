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

use Sjorek\UnicodeNormalization\Implementation\MacNormalizer;
use Sjorek\UnicodeNormalization\Implementation\StrictNormalizer;
use Sjorek\UnicodeNormalization\Tests\AbstractTestCase;
use Sjorek\UnicodeNormalization\Utility\AutoloadUtility;
use Sjorek\UnicodeNormalization\Utility\NormalizationUtility;
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
     * @uses \Sjorek\UnicodeNormalization\Utility\NormalizationUtility::isNfdMacCompatible
     * @uses \Sjorek\UnicodeNormalization\Utility\NormalizationUtility::isStrictImplementation
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     *
     * @see AutoloadUtility::registerNormalizerImplementation()
     */
    public function testRegisterNormalizerImplementation()
    {
        // should already be registered by autoloader
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
            NormalizationUtility::isNfdMacCompatible(),
            is_a($className, MacNormalizer::class, true)
        );
        $this->assertSame(
            NormalizationUtility::isStrictImplementation(),
            // strict implementations should not inherit the strict-enforcing facade
            !is_a($className, StrictNormalizer::class, true)
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
        // should already be registered by autoloader
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
}
