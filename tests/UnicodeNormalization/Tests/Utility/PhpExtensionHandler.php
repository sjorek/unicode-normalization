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
 * This implementation has been inspired by composer's XdebugHandler.
 *
 * @author John Stevenson <john-stevenson@blueyonder.co.uk>
 * @author Stephan Jorek <stephan.jorek@gmail.com>
 */
class PhpExtensionHandler
{
    const ENV_ALLOW = 'PHP_EXTENSION_HANDLER';
    const ENV_ORIGINAL = 'PHP_EXTENSION_HANDLER_INIS';
    const RESTART_ID = 'internal';

    private $extensions;
    private $loaded;
    private $envScanDir;
    private $tmpIni;

    public static function runWithout($extension, ...$extensions)
    {
        $handler = new static(array_merge([$extension], $extensions));
        $handler->check();
    }

    /**
     * Constructor
     * @param string[] $extensions
     */
    protected function __construct(array $extensions)
    {
        $this->extensions = $extensions;
        $this->loaded = in_array(true, array_map('extension_loaded', $extensions), true);
        $this->envScanDir = getenv('PHP_INI_SCAN_DIR');
    }

    /**
     * Checks if any extension is loaded and the process needs to be restarted
     *
     * If so, then a tmp ini is created with the extension's ini entry commented
     * out. If additional inis have been loaded, these are combined into the tmp
     * ini and PHP_INI_SCAN_DIR is set to an empty value. Current ini locations
     * are stored in PHP_EXTENSION_HANDLER_INIS, for use in the restarted process.
     *
     * This behaviour can be disabled by setting the PHP_EXTENSION_HANDLER
     * environment variable to 1. This variable is used internally so that the
     * restarted process is created only once and PHP_INI_SCAN_DIR can be
     * restored to its original value.
     */
    protected function check()
    {
        $args = explode('|', strval(getenv(self::ENV_ALLOW)), 2);

        if ($this->needsRestart($args[0])) {
            if ($this->prepareRestart()) {
                $command = $this->getCommand();
                $this->restart($command);
            }

            return;
        }

        // Restore environment variables if we are restarting
        if (self::RESTART_ID === $args[0]) {
            putenv(self::ENV_ALLOW);

            if (false !== $this->envScanDir) {
                // $args[1] contains the original value
                if (isset($args[1])) {
                    putenv('PHP_INI_SCAN_DIR='.$args[1]);
                } else {
                    putenv('PHP_INI_SCAN_DIR');
                }
            }
        }
    }

    /**
     * Executes the restarted command then deletes the tmp ini
     *
     * @param string $command
     */
    protected function restart($command)
    {
        $exitCode = 1;
        passthru($command, $exitCode);

        if (!empty($this->tmpIni)) {
            @unlink($this->tmpIni);
        }

        exit($exitCode);
    }

    /**
     * Returns true if a restart is needed
     *
     * @param string $allow Environment value
     *
     * @return bool
     */
    private function needsRestart($allow)
    {
        if (PHP_SAPI !== 'cli' || !defined('PHP_BINARY')) {
            return false;
        }

        return !empty($this->extensions) && empty($allow) && $this->loaded;
    }

    /**
     * Returns true if everything was written for the restart
     *
     * If any of the following fails (however unlikely) we must return false to
     * stop potential recursion:
     *   - tmp ini file creation
     *   - environment variable creation
     *
     * @return bool
     */
    private function prepareRestart()
    {
        $this->tmpIni = '';
        $iniPaths = $this->getAllPhpIniLocations();
        $additional = count($iniPaths) > 1;

        if ($this->writeTmpIni($iniPaths)) {
            return $this->setEnvironment($additional, $iniPaths);
        }

        return false;
    }

    /**
     * Returns true if the tmp ini file was written
     *
     * The filename is passed as the -c option when the process restarts.
     *
     * @param array $iniPaths Locations reported by the current process
     *
     * @return bool
     */
    private function writeTmpIni(array $iniPaths)
    {
        if (!$this->tmpIni = tempnam(sys_get_temp_dir(), '')) {
            return false;
        }

        // $iniPaths has at least one item and it may be empty
        if (empty($iniPaths[0])) {
            array_shift($iniPaths);
        }

        $content = '';
        $regex = implode('|', array_map('preg_quote', $this->extensions));
        $regex = '/^\s*(zend_)?extension\s*=.*(' . $regex . ').*$/mi';

        foreach ($iniPaths as $file) {
            if (false !== ($data = file_get_contents($file))) {
                $content .= preg_replace($regex, ';$0', $data) . PHP_EOL;
            }
        }

        $content .= 'allow_url_fopen='.ini_get('allow_url_fopen') . PHP_EOL;
        $content .= 'disable_functions="'.ini_get('disable_functions').'"' . PHP_EOL;
        $content .= 'memory_limit='.ini_get('memory_limit') . PHP_EOL;

        if (defined('PHP_WINDOWS_VERSION_BUILD')) {
            // Work-around for PHP windows bug, see issue #6052
            $content .= 'opcache.enable_cli=0' . PHP_EOL;
        }

        return @file_put_contents($this->tmpIni, $content);
    }

    /**
     * Returns the restart command line
     *
     * @return string
     */
    private function getCommand()
    {
        $phpArgs = array(PHP_BINARY, '-c', $this->tmpIni);
        $params = array_merge($phpArgs, $this->getScriptArgs($_SERVER['argv']));

        return implode(' ', array_map(array($this, 'escape'), $params));
    }

    /**
     * Returns true if the restart environment variables were set
     *
     * @param bool  $additional Whether there were additional inis
     * @param array $iniPaths   Locations reported by the current process
     *
     * @return bool
     */
    private function setEnvironment($additional, array $iniPaths)
    {
        // Set scan dir to an empty value if additional ini files were used
        if ($additional && !putenv('PHP_INI_SCAN_DIR=')) {
            return false;
        }

        // Make original inis available to restarted process
        if (!putenv(self::ENV_ORIGINAL.'='.implode(PATH_SEPARATOR, $iniPaths))) {
            return false;
        }

        // Flag restarted process and save env scan dir state
        $args = array(self::RESTART_ID);

        if (false !== $this->envScanDir) {
            // Save current PHP_INI_SCAN_DIR
            $args[] = $this->envScanDir;
        }

        return putenv(self::ENV_ALLOW.'='.implode('|', $args));
    }

    /**
     * Returns the restart script arguments, adding required options
     *
     * @param array $args The argv array
     *
     * @return array
     */
    private function getScriptArgs(array $args)
    {
        // if (in_array('--...', $args)) {
        //     return $args;
        // }
        // $offset = count($args) > 1 ? 2 : 1;
        // array_splice($args, $offset, 0, '--...');

        return $args;
    }

    /**
     * Returns an array of php.ini locations with at least one entry
     *
     * The equivalent of calling php_ini_loaded_file then php_ini_scanned_files.
     * The loaded ini location is the first entry and may be empty.
     *
     * @return string[]
     */
    private function getAllPhpIniLocations()
    {
        $env = getenv(self::ENV_ORIGINAL);

        if (false !== $env) {
            return explode(PATH_SEPARATOR, $env);
        }

        $paths = array(strval(php_ini_loaded_file()));

        if ($scanned = php_ini_scanned_files()) {
            $paths = array_merge($paths, array_map('trim', explode(',', $scanned)));
        }

        return $paths;
    }

    /**
     * Escapes a string to be used as a shell argument.
     *
     * From https://github.com/johnstevenson/winbox-args
     * MIT Licensed (c) John Stevenson <john-stevenson@blueyonder.co.uk>
     *
     * @param string $arg  The argument to be escaped
     * @param bool   $meta Additionally escape cmd.exe meta characters
     *
     * @return string The escaped argument
     */
    private function escape($arg, $meta = true)
    {
        if (!defined('PHP_WINDOWS_VERSION_BUILD')) {
            return escapeshellarg($arg);
        }

        $dquotes = 0;
        $quote = strpbrk($arg, " \t") !== false || $arg === '';
        $arg = preg_replace('/(\\\\*)"/', '$1$1\\"', $arg, -1, $dquotes);

        if ($meta) {
            $meta = $dquotes || preg_match('/%[^%]+%/', $arg);

            if (!$meta && !$quote) {
                $quote = strpbrk($arg, '^&|<>()') !== false;
            }
        }

        if ($quote) {
            $arg = preg_replace('/(\\\\*)$/', '$1$1', $arg);
            $arg = '"'.$arg.'"';
        }

        if ($meta) {
            $arg = preg_replace('/(["^&|<>()%])/', '^$1', $arg);
        }

        return $arg;
    }

}
