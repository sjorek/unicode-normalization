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

use Sjorek\UnicodeNormalization\Exception\InvalidNormalizerImplementation;

/**
 * Missing Normalizer implementation.
 *
 * @author Stephan Jorek <stephan.jorek@gmail.com>
 */
final class MissingNormalizer
{
    const NONE = -1;
    const FORM_D = self::NONE;
    const NFD = self::NONE;
    const FORM_KD = self::NONE;
    const NFKD = self::NONE;
    const FORM_C = self::NONE;
    const NFC = self::NONE;
    const FORM_KC = self::NONE;
    const NFKC = self::NONE;

    /**
     * Throw a InvalidNormalizerImplementation on every normalization attempt.
     *
     * @param string $input
     * @param int    $form
     *
     * @throws InvalidNormalizerImplementation
     */
    public static function normalize($input, $form = null)
    {
        throw new InvalidNormalizerImplementation(
            'A unicode normalizer implementation is missing. '
            . 'Please install the "intl"-extension or one of the suggested polyfills.',
            1519658533
        );
    }

    /**
     * Throw a InvalidNormalizerImplementation on every normalization check.
     *
     * @param string $input
     * @param int    $form
     *
     * @throws InvalidNormalizerImplementation
     */
    public static function isNormalized($input, $form = null)
    {
        throw new InvalidNormalizerImplementation(
            'A unicode normalizer implementation is missing. '
            . 'Please install the "intl"-extension or one of the suggested polyfills.',
            1519658533
        );
    }
}
