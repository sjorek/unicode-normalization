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

    const INI_HEADER =
        '; extension(s): %u (%s)' . PHP_EOL .
        '; loaded      : %u (%s)' . PHP_EOL .
        '; found       : %u (%s)' . PHP_EOL . PHP_EOL
    ;

    private $extensions;
    private $loaded;
    private $envScanDir;
    private $tmpIni;

    /**
     * @param array|string $extension
     * @param string[]     ...$extensions
     */
    public static function runWithout($extension = [], ...$extensions)
    {
        $handler = new static(array_merge(is_array($extension) ? $extension : [$extension], $extensions));
        $handler->run();
    }

    /**
     * @param array|string $extension
     * @param string[]     ...$extensions
     */
    public static function renderWithout($extension = [], ...$extensions)
    {
        $handler = new static(array_merge(is_array($extension) ? $extension : [$extension], $extensions));

        return $handler->render();
    }

    /**
     * Constructor.
     *
     * @param string[] $extensions
     */
    protected function __construct(array $extensions)
    {
        $this->extensions = array_filter($extensions);
        $this->loaded = array_filter($this->extensions, function ($ext) { return extension_loaded($ext); });
        $this->envScanDir = getenv('PHP_INI_SCAN_DIR');
        sort($this->extensions);
        sort($this->loaded);
    }

    /**
     * Checks if any extension is loaded and the process needs to be restarted.
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
    protected function run()
    {
        $args = explode('|', (string) (getenv(self::ENV_ALLOW)), 2);

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
                    putenv('PHP_INI_SCAN_DIR=' . $args[1]);
                } else {
                    putenv('PHP_INI_SCAN_DIR');
                }
            }
        }
    }

    /**
     * Returns the rendered php ini.
     *
     * @return string
     */
    protected function render()
    {
        return $this->renderIni($this->getAllPhpIniLocations());
    }

    /**
     * Executes the restarted command then deletes the tmp ini.
     *
     * @param string $command
     */
    private function restart($command)
    {
        $exitCode = 1;
        passthru($command, $exitCode);

        if (!empty($this->tmpIni)) {
            @unlink($this->tmpIni);
        }

        exit($exitCode);
    }

    /**
     * Returns true if a restart is needed.
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

        return !empty($this->extensions) && empty($allow) && !empty($this->loaded);
    }

    /**
     * Returns true if everything was written for the restart.
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
     * Returns true if the tmp ini file was written.
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
        $found = 0;
        $disabled = [];
        $content = $this->renderIni($iniPaths, $found, $disabled);
        if (0 < $found && empty(array_diff($this->loaded, $disabled))) {
            return @file_put_contents($this->tmpIni, $content);
        }

        return false;
    }

    /**
     * Returns the rendered php.ini.
     *
     * @param array $iniPaths Locations reported by the current process
     *
     * @return string
     */
    private function renderIni(array $iniPaths, int &$found = 0, array &$disabled = [])
    {
        // $iniPaths has at least one item and it may be empty
        if (empty($iniPaths[0])) {
            array_shift($iniPaths);
        }

        $content = '';
        $pattern = implode('|', array_map('preg_quote', $this->extensions));
        $pattern = '/^\s*(?:zend_)?extension\s*=.*(' . $pattern . ').*$/mi';
        foreach ($iniPaths as $file) {
            $matches = null;
            if (empty($data = file_get_contents($file) ?: '') ||
                empty($this->extensions) ||
                !($count = preg_match_all($pattern, $data, $matches, PREG_PATTERN_ORDER))
            ) {
                $content .= $data . PHP_EOL;
                continue;
            }
            $content .= (preg_replace($pattern, ';$0', $data) ?: '') . PHP_EOL;
            $disabled = array_merge($disabled, $matches[1]);
            $found += $count;
        }
        sort($disabled);

        $content .= 'allow_url_fopen=' . ini_get('allow_url_fopen') . PHP_EOL;
        $content .= 'disable_functions="' . ini_get('disable_functions') . '"' . PHP_EOL;
        $content .= 'memory_limit=' . ini_get('memory_limit') . PHP_EOL;

        if (defined('PHP_WINDOWS_VERSION_BUILD')) {
            // Work-around for PHP windows bug, see issue #6052
            $content .= 'opcache.enable_cli=0' . PHP_EOL;
        }

        return
            sprintf(
                static::INI_HEADER,
                count($this->extensions), implode(', ', $this->extensions) ?: '-',
                count($this->loaded), implode(', ', $this->loaded) ?: '-',
                $found, implode(', ', $disabled) ?: '-'
            )
            . $content
        ;
    }

    /**
     * Returns the restart command line.
     *
     * @return string
     */
    private function getCommand()
    {
        $phpArgs = [PHP_BINARY, '-c', $this->tmpIni];
        $params = array_merge($phpArgs, $this->getScriptArgs($_SERVER['argv']));

        return implode(' ', array_map([$this, 'escape'], $params));
    }

    /**
     * Returns true if the restart environment variables were set.
     *
     * @param bool  $additional Whether there were additional inis
     * @param array $iniPaths   Locations reported by the current process
     *
     * @return bool
     */
    protected function setEnvironment($additional, array $iniPaths)
    {
        // Set scan dir to an empty value if additional ini files were used
        if ($additional && !putenv('PHP_INI_SCAN_DIR=')) {
            return false;
        }

        // Make original inis available to restarted process
        if (!putenv(self::ENV_ORIGINAL . '=' . implode(PATH_SEPARATOR, $iniPaths))) {
            return false;
        }

        // Flag restarted process and save env scan dir state
        $args = [self::RESTART_ID];

        if (false !== $this->envScanDir) {
            // Save current PHP_INI_SCAN_DIR
            $args[] = $this->envScanDir;
        }

        return putenv(self::ENV_ALLOW . '=' . implode('|', $args));
    }

    /**
     * Returns the restart script arguments, adding required options.
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
     * Returns an array of php.ini locations with at least one entry.
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

        $paths = [(string) (php_ini_loaded_file())];

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
        $quote = false !== strpbrk($arg, " \t") || '' === $arg;
        $arg = preg_replace('/(\\\\*)"/', '$1$1\\"', $arg, -1, $dquotes);

        if ($meta) {
            $meta = $dquotes || preg_match('/%[^%]+%/', $arg);

            if (!$meta && !$quote) {
                $quote = false !== strpbrk($arg, '^&|<>()');
            }
        }

        if ($quote) {
            $arg = preg_replace('/(\\\\*)$/', '$1$1', $arg);
            $arg = '"' . $arg . '"';
        }

        if ($meta) {
            $arg = preg_replace('/(["^&|<>()%])/', '^$1', $arg);
        }

        return $arg;
    }
}
