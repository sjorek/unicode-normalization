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

use Sjorek\UnicodeNormalization\Filesystem\Filesystem;
use Sjorek\UnicodeNormalization\Filesystem\FilesystemInterface;
use Sjorek\UnicodeNormalization\Implementation\NormalizerInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

/**
 * Class to detect unicode filesystem capabilities.
 *
 * @author Stephan Jorek <stephan.jorek@gmail.com>
 */
class FilesystemUtility
{
    /**
     * We need to use a sub-folder, as the operating might alter the given filenames.
     * A sub-folder is the only guaranteed chance to cleanup after detection.
     *
     * @var string
     */
    const DETECTION_FOLDER = '.utf8-filesystem-detection';

    /**
     * List of mapping unicode-normalization constants to filenames in corresponding unicode-normalizations.
     *
     * @var bool[]|string[]
     */
    const FILESYSTEM_MODES = [
        // Raw binary data (not normalized, and not even a mix different normalizations):
        //
        //  php > $fileName = "ÖéöĄĆŻĘĆćążęóΘЩשݐซဤ⒜あ겫你你♥︎☺︎.txt";
        //  php > echo bin2hex($fileName);
        //  php > echo bin2hex(
        //         "\u{00D6}" // Ö
        //       . "\u{00E9}" // é - reserved character in Apple™'s HFS+ (OS X Extended) filesystem
        //       . "\u{00F6}" // ö
        //       . "\u{0104}" // Ą
        //       . "\u{0106}" // Ć
        //       . "\u{017B}" // Ż
        //       . "\u{0118}" // Ę
        //       . "\u{0106}" // Ć
        //       . "\u{0107}" // ć
        //       . "\u{0105}" // ą
        //       . "\u{017C}" // ż
        //       . "\u{0119}" // ę
        //       . "\u{00F3}" // ó
        //       . "\u{0398}" // Θ
        //       . "\u{0429}" // Щ
        //       . "\u{05E9}" // ש
        //       . "\u{0750}" // ݐ
        //       . "\u{0E0B}" // ซ︎
        //       . "\u{1024}" // ဤ
        //       . "\u{249C}" // ⒜  - special treatment in Apple™'s filename NFD normalization
        //       . "\u{3042}" // あ
        //       . "\u{ACAB}" // 겫
        //       . "\u{4F60}" // 你 - same as below, but in NFC
        //       . "\u{2F804}" // 你 - neither C, D, KC or KD + special in Apple™'s filename NFD normalization
        //       . "\u{2665}\u{FE0E}" // ♥
        //       . "\u{263A}\u{FE0E}" // ☺
        //       . ".txt"
        //  );
        // Many zeros to align with stuff below … turns into a single 0
        0000000000000000000000000 => 'c396c3a9c3b6c484c486c5bbc498c486c487c485c5bcc499c3b3ce98d0a9d7a9dd90e0b88be180a4e2929ce38182eab2abe4bda0f0afa084e299a5efb88ee298baefb88e2e747874',

        // not normalized $fileName from above partially in NFC, partially in NFD and with special treatments
        // honestly, this filename is completely broken, so maybe this delivers some unexpected results
        //
        //  php > echo bin2hex(mb_substr($fileName, 0, 4) .
        //                     Normalizer::normalize(mb_substr($fileName, 4, 4), Normalizer::NFC).
        //                     Normalizer::normalize(mb_substr($fileName, 8, 4), Normalizer::NFD).
        //                     mb_substr($fileName, 12));
        //
        NormalizerInterface::NONE => 'c396c3a9c3b6c484c486c5bbc498c48663cc8161cca87acc8765cca8c3b3ce98d0a9d7a9dd90e0b88be180a4e2929ce38182eab2abe4bda0f0afa084e299a5efb88ee298baefb88e2e747874',

        // NFC-normalized variant of $fileName from above
        //  php > echo bin2hex(Normalizer::normalize($fileName, Normalizer::NFC));
        NormalizerInterface::NFC => 'c396c3a9c3b6c484c486c5bbc498c486c487c485c5bcc499c3b3ce98d0a9d7a9dd90e0b88be180a4e2929ce38182eab2abe4bda0e4bda0e299a5efb88ee298baefb88e2e747874',

        // NFD-normalized variant of $fileName from above
        //  php > echo bin2hex(Normalizer::normalize($fileName, Normalizer::NFD));
        NormalizerInterface::NFD => '4fcc8865cc816fcc8841cca843cc815acc8745cca843cc8163cc8161cca87acc8765cca86fcc81ce98d0a9d7a9dd90e0b88be180a4e2929ce38182e18480e185a7e186aae4bda0e4bda0e299a5efb88ee298baefb88e2e747874',
        // look right for difference to NFD_MAC =>                                                                                                                                 ^^^^^^

        // NFD_MAC-normalized variant of $fileName from above, differing from NFD in 3 bytes
        //  php > echo bin2hex(iconv('utf-8', 'utf-8-mac', $fileName));
        NormalizerInterface::NFD_MAC => '4fcc8865cc816fcc8841cca843cc815acc8745cca843cc8163cc8161cca87acc8765cca86fcc81ce98d0a9d7a9dd90e0b88be180a4e2929ce38182e18480e185a7e186aae4bda0efbfbde299a5efb88ee298baefb88e2e747874',
        // look right for difference to plain NFD =>                                                                                                                                   ^^^^^^

        // Not supported for file names
        NormalizerInterface::NFKD => false,
        NormalizerInterface::NFKC => false,
    ];

    /**
     * Detect utf8-capabilities for given absolute path.
     *
     * The result will look like one of the following examples.
     *
     * Example 1: Filesystem has no utf8-capabilities at all
     * <pre>
     * php > [
     * php >      'locale' => false,
     * php >      'shell' => false,
     * php >      'unicode' => false,
     * php > ]
     * </pre>
     *
     * Example 2: Filesystem has utf8-capabilities, but does not normalize anything (~ treats paths as binary)
     * <pre>
     * php > [
     * php >      'locale' => true,
     * php >      'shell' => true,
     * php >      'unicode' => false,
     * php > ]
     * </pre>
     *
     * Example 3: Filesystem has utf8-capabilities and normalizes everything
     * <pre>
     * php > [
     * php >      'locale' => true,
     * php >      'shell' => true,
     * php >      'unicode' => true,
     * php > ]
     * </pre>
     *
     * Example 4: Filesystem has utf8-capabilities and normalizes something
     * <pre>
     * php > [
     * php >      'locale' => true,
     * php >      'shell' => true,
     * php >      'unicode' => [
     * php >          NormalizerInterface::NONE => false,
     * php >          NormalizerInterface::NFC => true,
     * php >          NormalizerInterface::NFD => true,
     * php >          NormalizerInterface::NFKC => true,
     * php >          NormalizerInterface::NFKC => true,
     * php >          NormalizerInterface::NFD_MAC => false,
     * php >      ]
     * php > ]
     * </pre>
     *
     * Example 5: Filesystem has utf8-capabilities and normalizes sometimes on write, but not on read
     * <pre>
     * php > [
     * php >      'locale' => true,
     * php >      'shell' => true,
     * php >      'unicode' => [
     * php >          NormalizerInterface::NONE => false,
     * php >          NormalizerInterface::NFC => [
     * php >              'read' => false,
     * php >              'write' => true,
     * php >          ],
     * php >          NormalizerInterface::NFD => [
     * php >              'read' => false,
     * php >              'write' => true,
     * php >          ],
     * php >          NormalizerInterface::NFKC => [
     * php >              'read' => false,
     * php >              'write' => false,
     * php >          ],,
     * php >          NormalizerInterface::NFKC => [
     * php >              'read' => false,
     * php >              'write' => false,
     * php >          ],,
     * php >          NormalizerInterface::NFD_MAC => [
     * php >              'read' => false,
     * php >              'write' => true,
     * php >          ],
     * php >      ]
     * php > ]
     * </pre>
     *
     * @param string              $absolutePath
     * @param FilesystemInterface $fs
     *
     * @throws \InvalidArgumentException if given path is not an absolute path to a directory
     * @throws IOExceptionInterface      on filesystem error
     *
     * @return array[]|bool[]
     */
    public static function detectCapabilitiesForPath($absolutePath, FilesystemInterface $fs = null)
    {
        if (null === $fs) {
            $fs = new Filesystem();
        }

        $absolutePath = (string) $absolutePath;
        if (DIRECTORY_SEPARATOR !== $absolutePath) {
            $absolutePath = rtrim($absolutePath, DIRECTORY_SEPARATOR);
        }

        if ('' === $absolutePath || !$fs->isAbsolutePath($absolutePath) || !is_dir($absolutePath)) {
            throw new \InvalidArgumentException(
                sprintf('Invalid path given, which is either not absolute or does not exist: %s', $absolutePath),
                1518778464
            );
        }

        $fullPath = $absolutePath . DIRECTORY_SEPARATOR . self::DETECTION_FOLDER;
        if (file_exists($fullPath) || is_link($fullPath) || is_dir($fullPath)) {
            throw new IOException(
                sprintf('The detection folder already exists: %s', $fullPath),
                1519131257
            );
        }

        $capabilities = [
            'locale' => false,
            'shell' => false,
            'unicode' => false,
        ];

        $isWindows = static::isWindows();
        $currentLocale = setlocale(LC_CTYPE, 0);
        if (false !== strpos(strtolower(str_replace('-', '', $currentLocale)), '.utf8')) {
            $capabilities['locale'] = true;

        // On Windows an empty locale value uses the regional settings from the Control Panel, we assume to be ok
        // On Windows the codepage 65001 refers to UTF-8
        // @see https://docs.microsoft.com/en-us/cpp/c-runtime-library/locale-names-languages-and-country-region-strings
        // @see https://docs.microsoft.com/en-us/cpp/c-runtime-library/code-pages
        // @see https://docs.microsoft.com/en-us/cpp/c-runtime-library/reference/setlocale-wsetlocale
        // @see https://msdn.microsoft.com/library/windows/desktop/dd317756.aspx
        } elseif ($isWindows && ('' === $currentLocale || false !== strpos($currentLocale, '.65001'))) {
            $capabilities['locale'] = true;
        } else {
            return $capabilities;
        }

        $fileName = hex2bin(self::FILESYSTEM_MODES[NormalizerInterface::NFC]);
        $quote = $isWindows ? '"' : '\'';

        // Since PHP 5.6.0 escapeshellarg uses the 'default_charset' on platforms lacking a 'mblen'-implementation
        // @see http://www.php.net/manual/en/function.escapeshellarg.php#refsect1-function.escapeshellarg-changelog
        // @see https://github.com/php/php-src/blob/PHP-5.6.0/ext/standard/exec.c#L349
        // @see https://github.com/php/php-src/blob/PHP-5.6.0/ext/standard/php_string.h#L155
        // @see http://man7.org/linux/man-pages/man3/mblen.3.html
        // @see https://www.freebsd.org/cgi/man.cgi?query=mblen
        // @see http://man.openbsd.org/mblen.3
        // @see https://developer.apple.com/legacy/library/documentation/Darwin/Reference/ManPages/man3/mblen_l.3.html
        // @see https://developer.apple.com/library/content/documentation/General/Reference/APIDiffsMacOSX10_10SeedDiff/modules/Darwin.html
        // @see https://docs.microsoft.com/en-us/cpp/c-runtime-library/reference/mbclen-mblen-mblen-l
        if (escapeshellarg($fileName) === $quote . $fileName . $quote &&
            (
                version_compare(PHP_VERSION, '5.6.0', '<') ||
                'utf8' === strtolower(str_replace('-', '', (string) ini_get('default_charset')))
            )) {
            $capabilities['shell'] = true;
        } else {
            return $capabilities;
        }

        // TODO Check if we need to implement chdir() to circumvent (too) long paths under Windows
        $fs->mkdir($fullPath);

        $fileNames = [];
        $normalizations = array_map(function ($_) { return false; }, self::FILESYSTEM_MODES);

        foreach (self::FILESYSTEM_MODES as $normalization => $fileName) {
            if (false === $fileName) {
                continue;
            }
            $normalizations[$normalization] = [
                'read' => false,
                'write' => true,
            ];
            $fileName = $normalization . '-' . hex2bin($fileName);
            $fileNames[$normalization] = $fileName;
            $filePath = $fullPath . DIRECTORY_SEPARATOR . $fileName;
            try {
                // TODO chdir() above and use relative $fileName to circumvent (too) long paths under Windows?
                $fs->touch($filePath);
            } catch (IOExceptionInterface $e) {
                $normalizations[$normalization]['write'] = false;
            }
            // TODO verify the need of clearstatcache() here
            clearstatcache(true, $filePath);
        }

        $iteratorMode = \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::CURRENT_AS_PATHNAME;
        $iterator = new \FilesystemIterator($fullPath, $iteratorMode);

        foreach ($iterator as $filePath) {
            $fileName = basename($filePath);
            foreach ($fileNames as $normalization => $candidate) {
                if ($normalizations[$normalization]['read'] === true) {
                    continue;
                }
                if ($fileName === $candidate) {
                    // If all files exist then the filesystem does not normalize unicode. If
                    // some files are missing then the filesystem, either normalizes unicode
                    // or it denies access to not-normalized filepaths or it simply does not
                    // support unicode at all, at least not those normalization forms we test.
                    $normalizations[$normalization]['read'] = true;
                }
            }
            $fs->remove($fullPath . DIRECTORY_SEPARATOR . $fileName);
        }
        $fs->remove($fullPath);

        // Reduce the given array of normalization detection results
        $capabilities['unicode'] = array_map(
            function ($normalization) {
                if (false === $normalization) {
                    return $normalization;
                }
                if (!in_array(false, $normalization, true)) {
                    return true;
                }
                if (in_array(true, $normalization, true)) {
                    return $normalization;
                }

                return false;
            },
            $normalizations
        );

        return $capabilities;
    }

    /**
     * @return bool
     */
    protected static function isWindows()
    {
        return !stristr(PHP_OS, 'darwin') && !stristr(PHP_OS, 'cygwin') && stristr(PHP_OS, 'win');
    }
}
