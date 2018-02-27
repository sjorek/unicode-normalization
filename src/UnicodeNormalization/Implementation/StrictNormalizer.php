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
class StrictNormalizer extends BaseNormalizer
{
    /**
     * {@inheritdoc}
     *
     * @see \Sjorek\UnicodeNormalization\Implementation\BaseNormalizer::isNormalized()
     */
    public function isNormalized($input, $form = null)
    {
        $form = $this->getFormArgument($form);
        if (self::NONE === $form) {
            return false;
        }
        if (parent::isNormalized($input, $form)) {
            return true;
        }
        // Having no cheap check here, forces us to do a full equality-check here.
        // As we just want it to use for file names, this full check should be ok.
        $result = $this->normalize($input, $form);
        if (null === $result || false === $result) {
            return false;
        }

        return $result === $input;
    }
}
