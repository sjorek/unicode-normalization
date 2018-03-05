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

namespace Sjorek\UnicodeNormalization\Tests\Validation;

use Sjorek\UnicodeNormalization\Tests\NormalizationTestCase;
use Sjorek\UnicodeNormalization\Utility\AutoloadUtility;

/**
 * @author Stephan Jorek <stephan.jorek@gmail.com>
 */
class ValidationTestCase extends NormalizationTestCase
{
    /**
     * This method is called before the first test of this test class is run.
     *
     * @beforeClass
     */
    public static function setUpValidationTestCase()
    {
        static::setUpNormalizationTestCase();
        if (!class_exists(__NAMESPACE__ . '\\StringValidator', false)) {
            AutoloadUtility::registerStringValidatorImplementation();
            class_alias(
                str_replace('\\Tests', '', __NAMESPACE__) . '\\StringValidator',
                __NAMESPACE__ . '\\StringValidator',
                true
            );
        }
    }
}
