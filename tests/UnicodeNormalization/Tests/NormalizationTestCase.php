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

// DO NOT USE HERE, TO PREVENT TOO EARLY AUTOLOADING
// use Sjorek\UnicodeNormalization\Normalizer;
use Sjorek\UnicodeNormalization\Implementation\NormalizationForms;
use Sjorek\UnicodeNormalization\Utility\AutoloadUtility;
use Sjorek\UnicodeNormalization\Utility\NormalizationUtility;

/**
 * Base test case class for all unit-tests.
 *
 * @author Stephan Jorek <stephan.jorek@gmail.com>
 */
class NormalizationTestCase extends AbstractTestCase
{
    /**
     * @var bool
     */
    protected static $isStrictImplementation = null;

    /**
     * @var bool
     */
    protected static $isNfdMacCompatible = null;

    /**
     * @var string
     */
    protected static $unicodeVersion = null;

    /**
     * @var int[]
     */
    protected static $normalizationForms = null;

    /**
     * This method is called before the first test of this test class is run.
     *
     * @beforeClass
     */
    public static function setUpNormalizationTestCase()
    {
        AutoloadUtility::registerNormalizerImplementation();
        if (null === static::$isStrictImplementation) {
            static::$isStrictImplementation = true;
        }
        if (null === static::$isNfdMacCompatible) {
            static::$isNfdMacCompatible = AutoloadUtility::isNfdMacCompatible();
        }
        if (null === static::$unicodeVersion) {
            static::$unicodeVersion = \Sjorek\UnicodeNormalization\Normalizer::getUnicodeVersion();
        }
        if (null === static::$normalizationForms) {
            static::$normalizationForms = \Sjorek\UnicodeNormalization\Normalizer::getNormalizationForms();
        }
    }

    /**
     * This method is called after the last test of this test class is run.
     *
     * @afterClass
     */
    public static function tearDownNormalizationTestCase()
    {
        static::$isStrictImplementation = null;
        static::$isNfdMacCompatible = null;
        static::$unicodeVersion = null;
        static::$normalizationForms = null;
    }

    /**
     * @var \Sjorek\UnicodeNormalization\Implementation\NormalizerInterface
     */
    protected $subject;

    /**
     * {@inheritdoc}
     *
     * @see \PHPUnit\Framework\TestCase::setUp()
     */
    protected function setUp()
    {
        $this->subject = new \Sjorek\UnicodeNormalization\Normalizer();
    }

    // ////////////////////////////////////////////////////////////////
    // utility methods
    // ////////////////////////////////////////////////////////////////

    /**
     * @param string $unicodeVersion
     */
    protected function markTestSkippedIfUnicodeConformanceLevelIsInsufficient($unicodeVersion)
    {
        if (version_compare($unicodeVersion, static::$unicodeVersion, '>')) {
            $this->markTestSkipped(
                sprintf(
                    'Skipped test as unicode version %s is higher than the supported unicode conformance level %s.',
                    $unicodeVersion,
                    static::$unicodeVersion
                )
            );
        }
    }

    /**
     * @param mixed $form
     */
    protected function markTestSkippedIfNfdMacIsNotSupported($form)
    {
        $form = NormalizationUtility::parseForm($form);
        if (NormalizationForms::NFD_MAC === $form) {
            if (!in_array($form, static::$normalizationForms, true)) {
                $this->markTestSkipped(
                    'Skipped test as the Normalizer-implementation does not support NFD_MAC.'
                );
            }
            if (!static::$isNfdMacCompatible) {
                $this->markTestSkipped(
                    'Skipped test as "iconv" extension is either not available '
                    . 'or not able to handle "utf-8-mac" charset.'
                );
            }
        }
    }
}
