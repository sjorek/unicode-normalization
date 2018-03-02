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

namespace Sjorek\UnicodeNormalization\Validation;

// @codeCoverageIgnoreStart
if (!class_exists(__NAMESPACE__ . '\\StringValidator', false)) {
    /**
     * StringValidator implementation (for IDE only).
     *
     * @see Implementation\StringValidatorImpl
     * @see Implementation\StringValidatorBugfix65732
     *
     * @author Stephan Jorek <stephan.jorek@gmail.com>
     */
    class StringValidator extends Implementation\StringValidatorImpl
    {
    }
}
// @codeCoverageIgnoreEnd
