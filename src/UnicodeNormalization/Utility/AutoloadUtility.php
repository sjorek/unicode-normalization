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
     */
    public static function register()
    {
        return !in_array(
            false,
            [
                static::registerNormalizer(),
                static::registerStringValidator(),
            ],
            true
        );
    }

    /**
     * Register the Normalizer implementation class. Takes several capabilities into account.
     *
     * @return bool
     */
    public static function registerNormalizer()
    {
        $namespace = static::getRootNamespace();
        $normalizerClass = $namespace . 'Normalizer';
        $implementationClass = $namespace . 'Implementation\\NormalizerImpl';
        $baseClass = $namespace . 'Implementation\\BaseNormalizer';

        // Use the autoloader here!
        if (!class_exists('Normalizer', true)) {
            return
                class_alias($namespace . 'Implementation\\MissingNormalizer', 'Normalizer', true) &&
                class_exists($normalizerClass, true);
        }
        // Do not use the autoloader here!
        if (class_exists($normalizerClass, false) || class_exists($implementationClass, false)) {
            return false;
        }
        if (!NormalizationUtility::isStrictImplementation()) {
            $baseClass = $namespace . 'Implementation\\StrictNormalizer';
        }
        // Use the autoloader here!
        if (!class_alias($baseClass, $implementationClass, true)) {
            return false;
        }
        if (NormalizationUtility::isNfdMacCompatible()) {
            $implementationClass = $namespace . 'Implementation\\MacNormalizer';
        }
        // Use the autoloader here!
        return class_alias($implementationClass, $normalizerClass, true);
    }

    /**
     * Register the Normalizer implementation class. Takes several capabilities into account.
     *
     * @return bool
     */
    public static function registerStringValidator()
    {
        $namespace = static::getRootNamespace() . 'Validation\\';
        $validatorClass = $namespace . 'StringValidator';
        $implementationClass = $namespace . 'Implementation\\StringValidatorImpl';

        // Do not use the autoloader here!
        if (class_exists($validatorClass, false)) {
            return false;
        }
        // Workaround for https://bugs.php.net/65732 - fixed for PHP 7.0.11 and above.
        if (version_compare(PHP_VERSION, '7.0.11', '<')) {
            $implementationClass = $namespace . 'Implementation\\StringValidatorBugfix65732';
        }
        // Use the autoloader here!
        return class_alias($implementationClass, $validatorClass, true);
    }

    /**
     * Return the root namespace of the 'sjorek/unicode-normalization' package.
     *
     * @return string
     */
    protected static function getRootNamespace()
    {
        return substr(__NAMESPACE__, 0, -7);
    }
}
