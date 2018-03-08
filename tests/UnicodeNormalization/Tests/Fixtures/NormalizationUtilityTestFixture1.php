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
 * tweaked utility namespace
 */
function class_exists($class_name, $autoload=null)
{
    return 'IntlChar' === $class_name ? false : \class_exists($class_name, $autoload);
}
