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

/**
 * Class for normalizing unicode, supporting a special normalization form NFD_MAC.
 *
 * @see NormalizationForms::NFD_MAC
 *
 * @author Stephan Jorek <stephan.jorek@gmail.com>
 */
class MacNormalizer extends NormalizerImpl
{
    /**
     * Array of supported normalization forms.
     *
     * @var array
     */
    const NORMALIZATION_FORMS = [
        self::NONE,
        self::NFD,
        self::NFKD,
        self::NFC,
        self::NFKC,
        self::NFD_MAC,
    ];

    /**
     * {@inheritdoc}
     *
     * @see Normalizer::callNormalize()
     */
    protected static function callNormalize($input, $form)
    {
        if (self::NFD_MAC !== $form) {
            return \Normalizer::normalize($input, $form);
        }
        $input = \Normalizer::normalize($input, self::NFD);

        return false !== $input ? iconv('utf-8', 'utf-8-mac', $input) : false;
    }

    /**
     * {@inheritdoc}
     *
     * @see Normalizer::callIsNormalized()
     */
    protected static function callIsNormalized($input, $form)
    {
        if (self::NFD_MAC !== $form) {
            return NormalizerImpl::callIsNormalized($input, $form);
        }
        // Having no cheap check here, forces us to do a full equality-check here.
        return self::NONE !== $form && $input === static::callNormalize($input, $form);
    }
}
