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

namespace Sjorek\UnicodeNormalization\Implementation;

// @codeCoverageIgnoreStart
if (false) {
    /**
     * Normalizer implementation (for IDE only).
     *
     * @see \Sjorek\UnicodeNormalization\Utility\AutoloadUtility::registerNormalizerImplementation()
     * @see Implementation\Normalizer
     * @see Implementation\StrictNormalizer
     * @see Implementation\MacNormalizer
     *
     * @author Stephan Jorek <stephan.jorek@gmail.com>
     */
    class NormalizerImpl extends Normalizer
    {
    }
}

if (!class_exists(__NAMESPACE__ . '\\NormalizerImpl', false)) {
    class_alias(InvalidNormalizer::class, __NAMESPACE__ . '\\NormalizerImpl', true);
}
// @codeCoverageIgnoreEnd
