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
     * @var array
     */
    protected static $unicodeVersion = null;

    /**
     * @var \Sjorek\UnicodeNormalization\Implementation\NormalizerInterface
     */
    protected $subject;

    /**
     * This method is called before the first test of this test class is run.
     * @beforeClass
     */
    public static function setUpNormalizationTestCase()
    {
        if (!class_exists(__NAMESPACE__ . '\\Normalizer', false)) {
            AutoloadUtility::registerNormalizerImplementation();
            class_alias(
                str_replace('\\Tests', '', __NAMESPACE__) . '\\Normalizer',
                __NAMESPACE__ . '\\Normalizer',
                true
            );
            self::$unicodeVersion = Normalizer::getUnicodeVersion();
        }
    }

    /**
     * {@inheritDoc}
     * @see \PHPUnit\Framework\TestCase::setUp()
     */
    protected function setUp()
    {
        $this->subject = new Normalizer();
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
        if (Normalizer::NFD_MAC === $form && !in_array($form, Normalizer::getNormalizationForms(), true)) {
            $this->markTestSkipped(
                'Skipped test as "iconv" extension is either not available '
                . 'or not able to handle "utf-8-mac" charset.'
            );
        }
    }

    /**
     * @return boolean
     */
    protected function isStrictImplementation()
    {
        return true;
    }

    /**
     * @return boolean
     */
    protected function getNormalizationForms()
    {
        return Normalizer::getNormalizationForms();
    }
}