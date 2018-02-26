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

namespace Sjorek\UnicodeNormalization {
    if (false !== getenv('PHP_EXTENSION_HANDLER_RUN_WITHOUT')) {
        Tests\Utility\PhpExtensionHandler::runWithout(explode(',', getenv('PHP_EXTENSION_HANDLER_RUN_WITHOUT')));
    }
    if (false !== strpos($_SERVER['argv'][0], 'phpunit')) {
        if (class_exists(NormalizationUtility::IMPLEMENTATION_SYMFONY, true)) {
            Tests\Utility\PhpExtensionHandler::runWithout('intl');
        }
        if (class_exists(NormalizationUtility::IMPLEMENTATION_PATCHWORK, true)) {
            Tests\Utility\PhpExtensionHandler::runWithout('intl');
        }
    }
    if (false !== strpos($_SERVER['argv'][0], 'php-cs-fixer')) {
        Tests\Utility\PhpExtensionHandler::runWithout('xdebug');
    }
}

