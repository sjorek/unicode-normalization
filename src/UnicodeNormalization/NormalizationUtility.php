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

namespace Sjorek\UnicodeNormalization;

use Sjorek\UnicodeNormalization\Exception\InvalidNormalizationForm;
use Sjorek\UnicodeNormalization\Exception\InvalidNormalizerImplementation;
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
     * - Normalization form D (mac)        : 32, NFD_MAC, FORM_D_MAC, D_MAC, form-d-mac, d-mac, mac
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
     * @var string
     *
     * @see \Normalizer
     * @see http://.php.net/manual/en/class.normalizer.php
     */
    const IMPLEMENTATION_INTL = 'Normalizer';

    /**
     * @var string
     *
     * @see \Symfony\Polyfill\Intl\Normalizer\Normalizer
     * @see https://packagist.org/packages/symfony/polyfill-intl-normalizer
     * @see https://github.com/symfony/polyfill-intl-normalizer
     * @see https://github.com/symfony/polyfill/tree/master/src/Intl/Normalizer
     */
    const IMPLEMENTATION_SYMFONY = 'Symfony\\Polyfill\\Intl\\Normalizer\\Normalizer';

    /**
     * @var string
     *
     * @see \Patchwork\\PHP\Shim\Normalizer
     * @see https://packagist.org/packages/patchwork/utf8
     * @see https://github.com/tchwork/utf8
     */
    const IMPLEMENTATION_PATCHWORK = 'Patchwork\\PHP\\Shim\\Normalizer';

    /**
     * @var string
     *
     * @see \Sjorek\UnicodeNormalization\Implementation\StubNormalizer
     */
    const IMPLEMENTATION_STUB = __NAMESPACE__ . '\\Implementation\\StubNormalizer';

    /**
     * The normalizer implementation class.
     *
     * @var string
     */
    protected static $implementation = null;

    /**
     * Get the normalizer implementation's class-name.
     *
     * @throws InvalidNormalizerImplementation if all known implementations are missing
     *
     * @return string
     *
     * @see NormalizationUtility::registerImplementation()
     */
    public static function getImplementation()
    {
        if (null === self::$implementation) {
            $candidates = [
                self::IMPLEMENTATION_INTL,
                self::IMPLEMENTATION_SYMFONY,
                self::IMPLEMENTATION_PATCHWORK,
                self::IMPLEMENTATION_STUB,
            ];
            foreach ($candidates as $candidate) {
                try {
                    if (self::registerImplementation($candidate)) {
                        return self::$implementation;
                    }
                } catch (InvalidNormalizerImplementation $e) {
                }
            }
            // We should not get here, as the stub implementation is always available
            throw new InvalidNormalizerImplementation('Missing normalizer implementation.', 1519042942);
        }

        return self::$implementation;
    }

    /**
     * Register the normalizer implementation's class-name. The registration works once only.
     *
     * @param string $className The implementation class-name
     *
     * @throws InvalidNormalizerImplementation given implementation does not exist
     *
     * @return bool Returns true on success and false on subsequent calls
     *
     * @see NormalizationUtility::IMPLEMENTATION_INTL
     * @see NormalizationUtility::IMPLEMENTATION_PATCHWORK
     * @see NormalizationUtility::IMPLEMENTATION_SYMFONY
     * @see NormalizationUtility::IMPLEMENTATION_STUB
     */
    public static function registerImplementation($className)
    {
        if (null === self::$implementation) {
            if (class_exists((string) $className, true)) {
                self::$implementation = (string) $className;

                return true;
            }
            throw new InvalidNormalizerImplementation(
                    sprintf('The given normalizer implementation does not exist: %s', $className),
                    1519042943
                );
        }

        return false;
    }

    /**
     * Detect unicode conformance level, supported normalization forms and if the implementation is strict.
     *
     * The result might look like:
     * <pre>
     * php > [
     * php >      'level' => '9.0.0.0',
     * php >      'forms' => [
     * php >          NormalizerInterface::NONE,
     * php >          NormalizerInterface::NFC,
     * php >          NormalizerInterface::NFD,
     * php >          NormalizerInterface::NFKC,
     * php >          NormalizerInterface::NFKC,
     * php >          NormalizerInterface::NFD_MAC,
     * php >      ],
     * php >      'strict' => true,
     * php > ]
     * </pre>
     *
     *
     * @see \IntlChar::getUnicodeVersion()
     *
     * @param null|mixed $implementation
     *
     * @return array
     */
    public static function detectCapabilities($implementation = null)
    {
        if (null === $implementation) {
            $implementation = self::getImplementation();
        }

        if (class_exists($implementation, true) && method_exists($implementation, 'getCapabilities')) {
            return call_user_func($implementation . '::getCapabilities');
        }

        $conformanceLevel = '0.0.0.0';
        $normalizationForms = [];
        $strictImplementation = true;

        switch ($implementation) {
            case self::IMPLEMENTATION_INTL:
                if (extension_loaded('intl')) {
                    $conformanceLevel = implode('.', \IntlChar::getUnicodeVersion());
                    $normalizationForms = [
                        NormalizerInterface::NONE,
                        NormalizerInterface::NFC,
                        NormalizerInterface::NFD,
                        NormalizerInterface::NFKC,
                        NormalizerInterface::NFKD,
                    ];
                    if (self::appleIconvIsAvailable()) {
                        $normalizationForms[] = NormalizerInterface::NFD_MAC;
                    }
                    $strictImplementation = true;
                    break;
                }
                if (
                    !(
                        is_a($implementation, self::IMPLEMENTATION_SYMFONY) ||
                        is_a($implementation, self::IMPLEMENTATION_PATCHWORK)
                    )) {
                    throw new InvalidNormalizerImplementation(
                        sprintf(
                            'The normalizer implementation "%s" should either run with "intl"-extension loaded '
                            . 'or implement a "%s::getCapabilities()" method.',
                            $implementation,
                            $implementation
                        ),
                        1519042943
                    );
                }
                // no break here, to fall through to one of the polyfills
            case self::IMPLEMENTATION_SYMFONY:
            case self::IMPLEMENTATION_PATCHWORK:
                // Beware: Normalizer-implementations from the 'patchwork/utf8' and
                // 'symfony/polyfill-intl-normalizer' packages may have a higher conformance
                // level than the native 'intl'-extension's implementation, as the latter
                // depends on the underling ICU implementation.
                // TODO replace hard-coded unicode-conformance-levels with a real detection or something better.
                $conformanceLevel = '7.0.0.0';
                $normalizationForms = [
                    NormalizerInterface::NONE,
                    NormalizerInterface::NFC,
                    NormalizerInterface::NFD,
                    NormalizerInterface::NFKC,
                    NormalizerInterface::NFKD,
                ];
                if (self::appleIconvIsAvailable()) {
                    $normalizationForms[] = NormalizerInterface::NFD_MAC;
                }
                $strictImplementation = false;
                break;
            default:
                throw new InvalidNormalizerImplementation(
                    sprintf(
                        'Missing "%s::getCapabilities()" method-implementation.',
                        $implementation
                    ),
                    1519042944
                );
        }

        return [
            // 'class' => $implementation,
            'level' => $conformanceLevel,
            'forms' => $normalizationForms,
            'strict' => $strictImplementation,
        ];
    }

    /**
     * Return true if all dependencies of a special variant of an iconv-implementation is available.
     * This is usually the case on Darwin, OS X and MacOS.
     *
     * @return bool
     *
     * @see Normalizer::NFD_MAC
     */
    public static function appleIconvIsAvailable()
    {
        $nfc = hex2bin('64c3a96ac3a020ed9b88ec87bce284a2e2929ce4bda0');
        $mac = hex2bin('6465cc816a61cc8020e18492e185aee186abe18489e185ade284a2e2929ce4bda0');

        return
            extension_loaded('iconv') &&
            $mac === @iconv('utf-8', 'utf-8-mac', $nfc) &&
            $nfc === @iconv('utf-8-mac', 'utf-8', $mac)
        ;
    }
}
