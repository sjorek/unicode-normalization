<?php declare(strict_types=1);

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
 * A stub normalizer implementation, only working for normalization form NONE or ASCII strings.
 *
 * @author Stephan Jorek <stephan.jorek@gmail.com>
 */
class StubNormalizer
{
    /**
     * Normalizes the input provided and returns the normalized string.
     *
     * @param string $input     The input string to normalize.
     * @param int $form         [optional] One of the normalization forms.
     * @return string The normalized string or NULL if an error occurred.
     */
    public static function normalize($input, $form = null)
    {
        if ($form === null) {
            $form = NormalizerInterface::NFC;
        }
        if (NormalizerInterface::NONE === $form) {
            return $input;
        } elseif (NormalizerInterface::NFC === $form &&
            // Empty string or plain ASCII is always valid for all forms, let it through.
            ($input === '' || !preg_match( '/[\x80-\xff]/', $input) ||
            // A cheap NFC detection
            (preg_match('//u', $input) && !preg_match('/[^\x00-\x{2FF}]/u', $input))))
        {
            return $input;
        }
        return false;
    }

    /**
     * Checks if the provided string is already in the specified normalization form.
     *
     * @param string $input     The input string to normalize
     * @param int $form         [optional] One of the normalization forms.
     * @return bool TRUE if normalized, FALSE otherwise or if an error occurred.
     */
    public static function isNormalized($input, $form = null)
    {
        if ($form === null) {
            $form = NormalizerInterface::NFC;
        }
        if (NormalizerInterface::NFC === $form) {
            return (
                // Empty string or plain ASCII is always valid for all forms, let it through.
                $input === '' || !preg_match( '/[\x80-\xff]/', $input) ||
                // A cheap NFC detection
                (preg_match('//u', $input) && !preg_match('/[^\x00-\x{2FF}]/u', $input))
            );
        }
        return false;
    }
}
