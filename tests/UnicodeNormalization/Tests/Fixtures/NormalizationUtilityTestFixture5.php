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
 * tweaked utility namespace
 */
function is_a($object, $class_name, $allow_string = null)
{
    if (is_string($object) && $object === \Normalizer::class && $class_name === MissingNormalizer::class) {
        return true;
    }

    return \is_a($object, $class_name, $allow_string);
}