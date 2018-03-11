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

use Sjorek\UnicodeNormalization\Implementation\Runtime\MissingNormalizer;

/**
 * Class to handle autoload functionality, especially for the composer bootstrap.
 *
 * @author Stephan Jorek <stephan.jorek@gmail.com>
 */
class AutoloadUtility
{
    /**
     * Registration method to be called by (an) bootstrap script like "src/UnicodeNormalization/bootstrap.php".
     *
     * @return bool
     *
     * @see AutoloadUtility::registerNormalizerImplementation()
     * @see AutoloadUtility::registerStringValidatorImplementation()
     */
    public static function register()
    {
        return !in_array(
            false,
            [
                static::registerNormalizerImplementation(),
                static::registerStringValidatorImplementation(),
            ],
            true
        );
    }

    /**
     * Register the Normalizer implementation class. Takes several capabilities into account.
     *
     * @return bool
     */
    public static function registerNormalizerImplementation()
    {
        $namespace = static::getRootNamespace();
        $normalizer = 'Normalizer';
        $facade = $namespace . 'Normalizer';
        $base = $namespace . 'Implementation\\Normalizer';
        $strict = $namespace . 'Implementation\\StrictNormalizer';
        $implementation = $namespace . 'Implementation\\NormalizerImpl';
        $mac = $namespace . 'Implementation\\MacNormalizer';

        // Use the autoloader here!
        if (!class_exists($normalizer, true)) {
            class_alias(MissingNormalizer::class, $normalizer);

            return false;
        }
        // Do not use the autoloader here!
        if (
            class_exists($facade, false) ||
            class_exists($implementation, false) ||
            is_a($normalizer, MissingNormalizer::class, true)
        ) {
            return false;
        }
        if (!self::isStrictImplementation()) {
            $base = $strict;
        }
        // Use the autoloader here!
        if (!class_alias($base, $implementation, true)) {
            return false;
        }
        if (self::isNfdMacCompatible()) {
            $implementation = $mac;
        }
        // Use the autoloader here!
        return class_alias($implementation, $facade, true);
    }

    /**
     * Register the Normalizer implementation class. Takes several capabilities into account.
     *
     * @return bool
     */
    public static function registerStringValidatorImplementation()
    {
        $namespace = static::getRootNamespace() . 'Validation\\';
        $validator = $namespace . 'StringValidator';
        $implementation = $namespace . 'Implementation\\StringValidatorImpl';

        // Do not use the autoloader here!
        if (class_exists($validator, false)) {
            return false;
        }
        // Workaround for https://bugs.php.net/65732 - fixed for PHP 7.0.11 and above.
        if (version_compare(PHP_VERSION, '7.0.11', '<')) {
            $implementation = $namespace . 'Implementation\\StringValidatorBugfix65732';
        }
        // Use the autoloader here!
        return class_alias($implementation, $validator, true);
    }

    /**
     * Return true if the Normalizer implementation is strict.
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
     * @see \Sjorek\UnicodeNormalization\Implementation\NormalizationForms::NFD_MAC
     */
    public static function isNfdMacCompatible()
    {
        // déjà 훈쇼™⒜你
        $nfc = hex2bin('64c3a96ac3a020ed9b88ec87bce284a2e2929ce4bda0');
        $mac = hex2bin('6465cc816a61cc8020e18492e185aee186abe18489e185ade284a2e2929ce4bda0');

        return
            extension_loaded('iconv') &&
            $mac === @iconv('UTF-8', 'UTF-8-MAC', $nfc) &&
            $nfc === @iconv('UTF-8-MAC', 'UTF-8', $mac)
        ;
    }

    /**
     * Return the root namespace of the 'sjorek/unicode-normalization' package.
     *
     * @codeCoverageIgnore
     *
     * @return string
     */
    protected static function getRootNamespace()
    {
        return substr(__NAMESPACE__, 0, -7);
    }
}
