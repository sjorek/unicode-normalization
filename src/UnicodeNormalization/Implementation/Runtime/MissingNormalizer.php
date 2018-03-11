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

namespace Sjorek\UnicodeNormalization\Implementation\Runtime;

use Sjorek\UnicodeNormalization\Exception\InvalidRuntimeFailure;
use Sjorek\UnicodeNormalization\Implementation\NormalizationForms;

/**
 * Missing Normalizer implementation.
 *
 * @author Stephan Jorek <stephan.jorek@gmail.com>
 */
final class MissingNormalizer implements NormalizationForms
{
    /**
     * Throw a InvalidRuntimeFailure on every normalization attempt.
     *
     * @param string $input
     * @param int    $form
     *
     * @throws InvalidRuntimeFailure
     */
    public static function normalize($input, $form = null)
    {
        throw self::createException();
    }

    /**
     * Throw a InvalidRuntimeFailure on every normalization check.
     *
     * @param string $input
     * @param int    $form
     *
     * @throws InvalidRuntimeFailure
     */
    public static function isNormalized($input, $form = null)
    {
        throw self::createException();
    }

    /**
     * Creates an InvalidRuntimeFailure exception, stating that the normalizer-implementation is missing.
     *
     * @return InvalidRuntimeFailure
     */
    private static function createException()
    {
        return new InvalidRuntimeFailure(
            'A unicode normalizer implementation is missing. '
            . 'Please install the "intl"-extension or one of the suggested polyfills.',
            1519658533
        );
    }
}
