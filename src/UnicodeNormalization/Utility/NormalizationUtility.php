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

namespace Sjorek\UnicodeNormalization\Utility;

use Sjorek\UnicodeNormalization\Exception\InvalidNormalizationForm;
use Sjorek\UnicodeNormalization\Implementation\NormalizerInterface;

/**
 * Class to handle unicode specific functionality.
 *
 * @author Stephan Jorek <stephan.jorek@gmail.com>
 */
class NormalizationUtility
{
    /**
     * Convert the given value to a known normalization-form constant.
     *
     * Supported case-insensitive aliases:
     * <pre>
     * - Disable unicode-normalization     : 0,  false, null, empty
     * - Ignore/skip unicode-normalization : 1,  NONE, true, binary, default, validate
     * - Normalization form D              : 2,  NFD, FORM_D, D, form-d, decompose, collation
     * - Normalization form D (mac)        : 18, NFD_MAC, FORM_D_MAC, D_MAC, form-d-mac, d-mac, mac
     * - Normalization form KD             : 3,  NFKD, FORM_KD, KD, form-kd
     * - Normalization form C              : 4,  NFC, FORM_C, C, form-c, compose, recompose, legacy, html5
     * - Normalization form KC             : 5,  NFKC, FORM_KC, KC, form-kc, matching
     * </pre>
     *
     * Hints:
     * <pre>
     * - The W3C recommends NFC for HTML5 Output.
     * - Mac OS X's HFS+ filesystem uses a NFD variant to store paths. We provide one implementation for this
     *   special variant, but plain NFD works in most cases too. Even if you use something else than NFD or its
     *   variant HFS+ will always use decomposed NFD path-strings if needed.
     * </pre>
     *
     * @param null|bool|int|string $value
     *
     * @throws InvalidNormalizationForm
     */
    public static function parseForm($value)
    {
        $value = trim((string) $value);
        if (
            in_array(
                $value,
                [
                    '0',
                    (string) NormalizerInterface::NONE,
                    (string) NormalizerInterface::NFC,
                    (string) NormalizerInterface::NFD,
                    (string) NormalizerInterface::NFD_MAC,
                    (string) NormalizerInterface::NFKC,
                    (string) NormalizerInterface::NFKD,
                ],
                true
            )
        ) {
            return (int) $value;
        }

        $form = str_replace(['-', 'NF', 'FORM_'], ['_', '', ''], strtoupper($value));

        switch ($form) {
            case '':
            case 'NULL':
            case 'FALSE':
                return 0;
            case 'NONE':
            case 'TRUE':
            case 'BINARY':
            case 'DEFAULT':
            case 'VALIDATE':
                return NormalizerInterface::NONE;
            case 'D':
            case 'DECOMPOSE':
            case 'COLLATION':
                return NormalizerInterface::NFD;
            case 'KD':
                return NormalizerInterface::NFKD;
            case 'C':
            case 'COMPOSE':
            case 'RECOMPOSE':
            case 'LEGACY':
            case 'HTML5':
                return NormalizerInterface::NFC;
            case 'KC':
            case 'MATCHING':
                return NormalizerInterface::NFKC;
            case 'D_MAC':
            case 'MAC':
                return NormalizerInterface::NFD_MAC;
        }

        throw new InvalidNormalizationForm(
            sprintf('Invalid unicode normalization form value: %s', $value), 1398603947
        );
    }

    /**
     * Return true if the \Normalizer implementation is strict.
     * Strict implementations process every character in a string to determine if a string is normalized.
     *
     * @return bool
     *
     * @see \Symfony\Polyfill\Intl\Normalizer\Normalizer
     * @see https://github.com/symfony/polyfill-intl-normalizer/blob/master/Normalizer.php#L56
     * @see https://packagist.org/packages/symfony/polyfill-intl-normalizer
     * @see \Patchwork\PHP\Shim\Normalizer
     * @see https://github.com/tchwork/utf8/blob/master/src/Patchwork/PHP/Shim/Normalizer.php#L53
     * @see https://packagist.org/packages/patchwork/utf8
     */
    public static function isStrictImplementation()
    {
        // déjà 훈쇼™⒜你
        return \Normalizer::isNormalized(hex2bin('64c3a96ac3a020ed9b88ec87bce284a2e2929ce4bda0'));
    }

    /**
     * Return true if all dependencies of a special variant of an iconv-implementation is available.
     * This is usually the case on Darwin, OS X and MacOS.
     *
     * @return bool
     *
     * @see \Sjorek\UnicodeNormalization\Implementation\NormalizerInterface::NFD_MAC
     */
    public static function isNfdMacCompatible()
    {
        $nfc = hex2bin('64c3a96ac3a020ed9b88ec87bce284a2e2929ce4bda0');
        $mac = hex2bin('6465cc816a61cc8020e18492e185aee186abe18489e185ade284a2e2929ce4bda0');

        return
            extension_loaded('iconv') &&
            $mac === @iconv('utf-8', 'utf-8-mac', $nfc) &&
            $nfc === @iconv('utf-8-mac', 'utf-8', $mac)
        ;
    }

    /**
     * Get the supported unicode version level as version triple ("X.Y.Z").
     *
     * @throws \RuntimeException
     *
     * @return string
     */
    public static function detectUnicodeVersion()
    {
        if (class_exists('IntlChar', true) && method_exists('IntlChar', 'getUnicodeVersion')) {
            return implode('.', array_slice(\IntlChar::getUnicodeVersion(), 0, 3));
        }
        $icuVersion = null;
        if (defined('INTL_ICU_VERSION')) {
            $icuVersion = INTL_ICU_VERSION;
        } else {
            try {
                $reflector = new \ReflectionExtension('intl');
                ob_start();
                $reflector->info();
                $output = strip_tags(ob_get_clean());
                $matches = null;
                preg_match('/^ICU version (?:=>)?(.*)$/m', $output, $matches);
                $icuVersion = trim($matches[1]);
            } catch (\ReflectionException $e) {
                $icuVersion = null;
            }
        }
        if (null !== $icuVersion) {
            $icuVersion = array_shift(explode('.', $icuVersion));
            // taken from http://source.icu-project.org/repos/icu/trunk/icu4j/main/classes/core/src/com/ibm/icu/util/VersionInfo.java
            $icuToUnicodeVersionMap = [
                '49' => '6.1.0',
                '50' => '6.2.0',
                '52' => '6.3.0',
                '54' => '7.0.0',
                '56' => '8.0.0',
                '58' => '9.0.0',
                '60' => '10.0.0',
            ];
            if (isset($icuToUnicodeVersionMap[$icuVersion])) {
                return $icuToUnicodeVersionMap[$icuVersion];
            }
        }
        if (!extension_loaded('intl') && class_exists('Normalizer', true)) {
            // TODO replace hard-coded version with a real detection
            return '7.0.0';
        }

        throw new \RuntimeException('Could not determine unicode version.', 1519488534);
    }
}
