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

use Sjorek\UnicodeNormalization\NormalizationUtility;

/**
 * Testcase for Sjorek\UnicodeNormalization\Normalizer.
 *
 * @author Stephan Jorek <stephan.jorek@gmail.com>
 */
class NormalizerTest extends AbstractImplementationTestCase
{
    /**
     * @var string
     */
    const IMPLEMENTATION_CLASS = NormalizationUtility::IMPLEMENTATION_INTL;

    /**
     * @param string|false $same
     * @param string       $string
     * @param int|null     $form
     * @test
     * @dataProvider provideCheckNormalizeData
     * @covers \Sjorek\UnicodeNormalization\Normalizer::normalizeTo
     * @covers \Sjorek\UnicodeNormalization\Normalizer::normalize
     * @covers \Sjorek\UnicodeNormalization\Normalizer::isNormalized
     */
    public function checkNormalizeTo($same, $string, $form)
    {
        $this->markTestSkippedIfAppleIconvIsNotAvailable($form);
        if (false !== $same) {
            $this->assertSame($same, $this->subject->normalizeTo($string, $form));
        } else {
            $this->assertFalse($this->subject->normalizeTo($string, $form));
        }
    }

    /**
     * @param string|false $same
     * @param string       $string
     * @param int|null     $form
     * @test
     * @dataProvider provideCheckNormalizeData
     * @covers \Sjorek\UnicodeNormalization\Normalizer::normalizeTo
     * @covers \Sjorek\UnicodeNormalization\Normalizer::normalize
     * @covers \Sjorek\UnicodeNormalization\Normalizer::isNormalized
     */
    public function checkNormalizeStringTo($same, $string, $form)
    {
        $this->markTestSkippedIfAppleIconvIsNotAvailable($form);
        if (false !== $same) {
            $this->assertSame($same, $this->subject->normalizeStringTo($string, $form));
        } else {
            $this->assertFalse($this->subject->normalizeStringTo($string, $form));
        }
    }

    /**
     * @return bool
     */
    protected function implementationIsAvailable()
    {
        return extension_loaded('intl') && parent::implementationIsAvailable();
    }
}
