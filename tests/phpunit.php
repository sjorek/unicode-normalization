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

namespace Sjorek\UnicodeNormalization\Tests {
    foreach(Utility\Configuration::LOOSE_IMPLEMENTATIONS as $implementation) {
        if (class_exists($implementation, true)) {
            Utility\PhpExtensionHandler::runWithout('intl');
        }
    }
}

