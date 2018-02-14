<?php declare(strict_types=1);

/*
 * This file is part of the Unicode Normalization project.
 *
 * (c) Stephan Jorek <stephan.jorek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sjorek\UnicodeNormalization;

if (!in_array(getenv('UNICODE_NORMALIZER_IMPLEMENTATION'), array(false, 'Normalizer'), true)) {
    class_alias(getenv('UNICODE_NORMALIZER_IMPLEMENTATION'), __NAMESPACE__ . '\\Normalizer', true);
} elseif (class_exists('Normalizer', true)) {
    // class_alias only works for user defined classes
    class Normalizer extends \Normalizer
    {
    }
}
