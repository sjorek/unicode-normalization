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

namespace Sjorek\UnicodeNormalization;

// @codeCoverageIgnoreStart
if (!class_exists(__NAMESPACE__ . '\\Normalizer', false)) {
    /**
     * Normalizer implementation (for IDE only).
     *
     * @see Implementation\NormalizerImpl
     * @see Implementation\BaseNormalizer
     * @see Implementation\StrictNormalizer
     * @see Implementation\MacNormalizer
     *
     * @author Stephan Jorek <stephan.jorek@gmail.com>
     */
    class Normalizer extends Implementation\NormalizerImpl
    {
    }
}
// @codeCoverageIgnoreEnd
