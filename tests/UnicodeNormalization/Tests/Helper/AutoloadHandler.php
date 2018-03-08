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

namespace Sjorek\UnicodeNormalization\Tests\Helper;

/**
 * @author Stephan Jorek <stephan.jorek@gmail.com>
 */
class AutoloadHandler
{
    public static function run()
    {
        if (defined('PHP_EXTENSION_HANDLER_RUN_WITHOUT')) {
            PhpExtensionHandler::runWithout(explode(',', PHP_EXTENSION_HANDLER_RUN_WITHOUT));
        }
        if (false !== getenv('PHP_EXTENSION_HANDLER_RUN_WITHOUT')) {
            PhpExtensionHandler::runWithout(explode(',', getenv('PHP_EXTENSION_HANDLER_RUN_WITHOUT')));
        }
        if (false !== strpos($_SERVER['argv'][0], 'phpunit') && ConfigurationHandler::isPolyfillAvailable()) {
            PhpExtensionHandler::runWithout('intl');
        }
        if (false !== strpos($_SERVER['argv'][0], 'php-cs-fixer')) {
            PhpExtensionHandler::runWithout('xdebug');
        }
    }
}
