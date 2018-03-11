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
 * Class for normalizing unicode, strictly determining if a string is normalized.
 *
 * @author Stephan Jorek <stephan.jorek@gmail.com>
 *
 * @see https://github.com/symfony/polyfill/blob/master/src/Intl/Normalizer/Normalizer.php#L56
 * @see https://github.com/tchwork/utf8/blob/master/src/Patchwork/PHP/Shim/Normalizer.php#L53
 */
class StrictNormalizer extends Normalizer
{
    /**
     * {@inheritdoc}
     *
     * @see Normalizer::callIsNormalized()
     */
    protected static function callIsNormalized($input, $form)
    {
        return
            self::NONE !== $form &&
            // Having no cheap check here, forces us to do a full equality-check here.
            (\Normalizer::isNormalized($input, $form) || $input === \Normalizer::normalize($input, $form))
        ;
    }
}
