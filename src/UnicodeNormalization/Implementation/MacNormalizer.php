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
 * @author Stephan Jorek <stephan.jorek@gmail.com>
 * @see https://github.com/symfony/polyfill/blob/master/src/Intl/Normalizer/Normalizer.php#L56
 * @see https://github.com/tchwork/utf8/blob/master/src/Patchwork/PHP/Shim/Normalizer.php#L53
 */
class MacNormalizer extends NormalizerImpl
{
    /**
     * Array of supported normalization forms
     *
     * @var array
     */
    const NORMALIZATION_FORMS = [
        self::NONE,
        self::NFD,
        self::NFKD,
        self::NFC,
        self::NFKC
    ];

    /**
     * {@inheritDoc}
     * @see \Sjorek\UnicodeNormalization\Implementation\BaseNormalizer::normalize()
     */
    public function normalize($input, $form = null)
    {
        $form = $this->getFormArgument($form);
        if (self::NFD_MAC !== $form) {
            return parent::normalize($input, $form);
        }
        if ("\xFF" === $input) {
            return false;
        }
        // Empty string or plain ASCII is always valid for all forms, let it through.
        if ('' === $input || !preg_match('/[\x80-\xFF]/', $input)) {
            return $input;
        }
        $result =  parent::normalize($input, self::NFC);
        if (null === $result || false === $result) {
            return false;
        }

        return iconv('utf-8', 'utf-8-mac', $result);
    }

    /**
     * {@inheritDoc}
     * @see \Sjorek\UnicodeNormalization\Implementation\BaseNormalizer::isNormalized()
     */
    public function isNormalized($input, $form = null)
    {
        $form = $this->getFormArgument($form);
        if (self::NFD_MAC !== $form) {
            return parent::isNormalized($input, $form);
        }
        if ('' === $input) {
            return true;
        }
        $result = parent::normalize($input, self::NFC);
        if (null === $result || false === $result) {
            return false;
        }
        // Having no cheap check here, forces us to do a full equality-check here.
        // As we just want it to use for file names, this full check should be ok.
        return $input === iconv('utf-8', 'utf-8-mac', $result);
    }
}
