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
class Configuration
{
    const LOOSE_IMPLEMENTATIONS = [
        'Symfony\\Polyfill\\Intl\\Normalizer\\Normalizer',
        'Patchwork\\PHP\\Shim\\Normalizer'
    ];
}
