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

namespace Sjorek\UnicodeNormalization\Tests\Utility;

/**
 * @author Stephan Jorek <stephan.jorek@gmail.com>
 */
class ConfigurationUtility
{
    /**
     * @var string[]
     */
    const KNOWN_UNICODE_VERSIONS = ['6.3.0', '7.0.0', '8.0.0', '9.0.0', '10.0.0'];

    /**
     * @var string
     */
    const SYMFONY_IMPLEMENTATION = 'Symfony\\Polyfill\\Intl\\Normalizer\\Normalizer';

    /**
     * @var string
     */
    const PATCHWORK_IMPLEMENTATION = 'Patchwork\\PHP\\Shim\\Normalizer';

    /**
     * @var string[]
     */
    const POLYFILL_IMPLEMENTATIONS = [self::SYMFONY_IMPLEMENTATION, self::PATCHWORK_IMPLEMENTATION];

    /**
     * @return bool
     */
    public static function isPolyfillImplementation()
    {
        if (self::isPolyfillAvailable()) {
            foreach (self::POLYFILL_IMPLEMENTATIONS as $implementation) {
                if (is_a('Normalizer', $implementation, true)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public static function isPolyfillAvailable()
    {
        foreach (self::POLYFILL_IMPLEMENTATIONS as $implementation) {
            if (class_exists($implementation, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return string[]
     */
    public static function getFixtureUnicodeVersions()
    {
        $versions = array_map(
            function ($filename) {
                return substr(basename($filename, '.txt.gz'), 18);
            },
            glob(__DIR__ . '/../Fixtures/NormalizationTest*.txt.gz')
        );
        usort($versions, 'version_compare');

        return $versions;
    }

    /**
     * @return string[]
     */
    public static function getKnownUnicodeVersions()
    {
        return self::getFixtureUnicodeVersions() ?: self::KNOWN_UNICODE_VERSIONS;
    }
}
