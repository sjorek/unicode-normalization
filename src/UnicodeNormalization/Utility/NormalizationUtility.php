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

use Sjorek\UnicodeNormalization\Exception\FeatureDetectionFailure;
use Sjorek\UnicodeNormalization\Exception\InvalidFormFailure;
use Sjorek\UnicodeNormalization\Implementation\NormalizerInterface;
use Sjorek\UnicodeNormalization\Implementation\Runtime\MissingNormalizer;

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
     * @throws InvalidFormFailure
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

        throw new InvalidFormFailure(
            sprintf('Invalid unicode normalization form value: %s', $value), 1398603947
        );
    }

    /**
     * @var array
     *
     * @see http://source.icu-project.org/repos/icu/trunk/icu4j/main/classes/core/src/com/ibm/icu/util/VersionInfo.java
     */
    const MAP_ICU_TO_UNICODE_VERSION = [
        '49' => '6.1.0',
        '50' => '6.2.0',
        '52' => '6.3.0',
        '54' => '7.0.0',
        '56' => '8.0.0',
        '58' => '9.0.0',
        '60' => '10.0.0',
    ];

    /**
     * Get the supported unicode version level as version triple ("X.Y.Z").
     *
     * @throws FeatureDetectionFailure
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
                if (1 === preg_match('/^ICU version (?:=>)?(.*)$/m', $output, $matches)) {
                    $icuVersion = trim($matches[1]);
                }
            } catch (\ReflectionException $e) {
                $icuVersion = null;
            }
        }
        if (null !== $icuVersion) {
            if (
                1 === sscanf($icuVersion, '%u.', $icuVersion) &&
                isset(self::MAP_ICU_TO_UNICODE_VERSION[$icuVersion])
            ) {
                return self::MAP_ICU_TO_UNICODE_VERSION[$icuVersion];
            }

            throw new FeatureDetectionFailure('Could not determine unicode version from ICU version.', 1519488536);
        }

        if (is_a(\Normalizer::class, MissingNormalizer::class, true)) {
            throw new FeatureDetectionFailure('Could not determine unicode version.', 1519488534);
        }

        // TODO replace hard-coded version with a real detection
        return '7.0.0';
    }
}
