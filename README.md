# [Unicode-Normalization](https://sjorek.github.io/unicode-normalization/)

A [composer](http://getcomposer.org)-package providing an enhanced facade to existing unicode-normalization
implementations.


## Installation

```bash
php composer.phar require sjorek/unicode-normalization
```


## Usage

### Unicode Normalization

```php
<?php

/**
 * Class for normalizing unicode.
 *
 *    “Normalization: A process of removing alternate representations of equivalent
 *    sequences from textual data, to convert the data into a form that can be
 *    binary-compared for equivalence. In the Unicode Standard, normalization refers
 *    specifically to processing to ensure that canonical-equivalent (and/or
 *    compatibility-equivalent) strings have unique representations.”
 *
 *     -- quoted from unicode glossary linked below
 *
 * @see http://www.unicode.org/glossary/#normalization
 * @see http://www.php.net/manual/en/class.normalizer.php
 * @see http://www.w3.org/wiki/I18N/CanonicalNormalization
 * @see http://www.w3.org/TR/charmod-norm/
 * @see http://blog.whatwg.org/tag/unicode
 * @see http://en.wikipedia.org/wiki/Unicode_equivalence
 * @see http://stackoverflow.com/questions/7931204/what-is-normalized-utf-8-all-about
 * @see http://php.net/manual/en/class.normalizer.php
 */
class Sjorek\UnicodeNormalization\Normalizer
    implements Sjorek\UnicodeNormalization\Implementation\NormalizerInterface
{

    /**
     * Constructor.
     *
     * @param null|bool|int|string $form (optional) Set normalization form, default: NFC
     *
     * Besides the normalization form class constants defined below,
     * the following case-insensitive aliases are supported:
     * <pre>
     * - Disable unicode-normalization     : 0,  false, null, empty
     * - Ignore/skip unicode-normalization : 1,  NONE, true, binary, default, validate
     * - Normalization form D              : 2,  NFD, FORM_D, D, form-d, decompose, collation
     * - Normalization form D (mac)        : 18, NFD_MAC, FORM_D_MAC, D_MAC, form-d-mac, d-mac, mac
     * - Normalization form KD             : 3,  NFKD, FORM_KD, KD, form-kd
     * - Normalization form C              : 4,  NFC, FORM_C, C, form-c, compose, recompose, legacy, html5
     * - Normalization form KC             : 5,  NFKC, FORM_KC, KC, form-kc, matching
     * </pre>
     *
     * Hints:
     * <pre>
     * - The W3C recommends NFC for HTML5 Output.
     * - Mac OS X's HFS+ filesystem uses a NFD variant to store paths. We provide one implementation for this
     *   special variant, but plain NFD works in most cases too. Even if you use something else than NFD or its
     *   variant HFS+ will always use decomposed NFD path-strings if needed.
     * </pre>
     */
    public function __construct($form = null);

    /**
     * Ignore any decomposition/composition.
     *
     * Ignoring Implementation decomposition/composition, means nothing is automatically normalized.
     * Many Linux- and BSD-filesystems do not normalize paths and filenames, but treat them as binary data.
     * Apple™'s APFS filesystem treats paths and filenames as binary data.
     *
     * @var int
     */
    const NONE = 1;

    /**
     * Canonical decomposition.
     *
     *    “A normalization form that erases any canonical differences, and produces a
     *    decomposed result. For example, ä is converted to a + umlaut in this form.
     *    This form is most often used in internal processing, such as in collation.”
     *
     *    -- quoted from unicode glossary linked below
     *
     * @var int
     *
     * @see http://www.unicode.org/glossary/#normalization_form_d
     * @see https://developer.apple.com/library/content/qa/qa1173/_index.html
     * @see https://developer.apple.com/library/content/qa/qa1235/_index.html
     */
    const NFD = 2;

    /**
     * Compatibility decomposition.
     *
     *    “A normalization form that erases both canonical and compatibility differences,
     *    and produces a decomposed result: for example, the single ǆ character is
     *    converted to d + z + caron in this form.”
     *
     *    -- quoted from unicode glossary linked below
     *
     * @var int
     *
     * @see http://www.unicode.org/glossary/#normalization_form_kd
     */
    const NFKD = 3;

    /**
     * Canonical decomposition followed by canonical composition.
     *
     *    “A normalization form that erases any canonical differences, and generally produces
     *    a composed result. For example, a + umlaut is converted to ä in this form. This form
     *    most closely matches legacy usage.”
     *
     *    -- quoted from unicode glossary linked below
     *
     * W3C recommends NFC for HTML5 output and requires NFC for HTML5-compliant parser implementations.
     *
     * @var int
     * @var int $FORM_C
     *
     * @see http://www.unicode.org/glossary/#normalization_form_c
     */
    const NFC = 4;

    /**
     * Compatibility Decomposition followed by Canonical Composition.
     *
     *    “A normalization form that erases both canonical and compatibility differences,
     *    and generally produces a composed result: for example, the single ǆ character
     *    is converted to d + ž in this form. This form is commonly used in matching.”
     *
     *    -- quoted from unicode glossary linked below
     *
     * @var int
     * @var int $FORM_KC
     *
     * @see http://www.unicode.org/glossary/#normalization_form_kc
     */
    const NFKC = 5;

    /**
     * Apple™ Canonical decomposition for HFS Plus filesystems.
     *
     *    “For example, HFS Plus (OS X Extended) uses a variant of Normal Form D in
     *    which U+2000 through U+2FFF, U+F900 through U+FAFF, and U+2F800 through U+2FAFF
     *    are not decomposed …”
     *
     *    -- quoted from Apple™'s Technical Q&A 1173 linked below
     *
     *    “The characters with codes in the range u+2000 through u+2FFF are punctuation,
     *    symbols, dingbats, arrows, box drawing, etc. The u+24xx block, for example, has
     *    single characters for things like u+249c "⒜". The characters in this range are
     *    not fully decomposed; they are left unchanged in HFS Plus strings. This allows
     *    strings in Mac OS encodings to be converted to Implementation and back without loss of
     *    information. This is not unnatural since a user would not necessarily expect a
     *    dingbat "⒜" to be equivalent to the three character sequence "(a)" in a file name.
     *
     *    The characters in the range u+F900 through u+FAFF are CJK compatibility ideographs,
     *    and are not decomposed in HFS Plus strings.
     *
     *    So, for the example given earlier, u+00E9 ("é") must be stored as the two Implementation
     *    characters u+0065 and u+0301 (in that order). The Implementation character u+00E9 ("é")
     *    may not appear in a Implementation string used as part of an HFS Plus B-tree key.”
     *
     *    -- quoted from Apple™'s Technical Q&A 1150 linked below
     *
     * @var int
     *
     * @see NormalizerInterface::NFD
     * @see https://developer.apple.com/library/content/qa/qa1173/_index.html
     * @see https://developer.apple.com/library/content/qa/qa1235/_index.html
     * @see http://dubeiko.com/development/FileSystems/HFSPLUS/tn1150.html#CanonicalDecomposition
     * @see https://opensource.apple.com/source/libiconv/libiconv-50/libiconv/lib/utf8mac.h.auto.html
     */
    const NFD_MAC = 18; // 0x02 (NFD) | 0x10 = 0x12 (18)

    /**
     * Set the default normalization form to the given value.
     *
     * @param int|string $form
     *
     * @see \Sjorek\UnicodeNormalization\NormalizationUtility::parseForm()
     *
     * @throws \Sjorek\UnicodeNormalization\Exception\InvalidNormalizationForm
     */
    public function setForm($form);

    /**
     * Retrieve the current normalization-form constant.
     *
     * @return int
     */
    public function getForm();

    /**
     * Normalizes the input provided and returns the normalized string.
     *
     * @param string $input the input string to normalize
     * @param int    $form  (optional) One of the normalization forms
     *
     * @throws \Sjorek\UnicodeNormalization\Exception\InvalidNormalizationForm
     *
     * @return string the normalized string or FALSE if an error occurred
     *
     * @see http://php.net/manual/en/normalizer.normalize.php
     */
    public function normalize($input, $form = null);

    /**
     * Checks if the provided string is already in the specified normalization form.
     *
     * @param string $input The input string to normalize
     * @param int    $form  (optional) One of the normalization forms
     *
     * @throws \Sjorek\UnicodeNormalization\Exception\InvalidNormalizationForm
     *
     * @return bool TRUE if normalized, FALSE otherwise or if an error occurred
     *
     * @see http://php.net/manual/en/normalizer.isnormalized.php
     */
    public function isNormalized($input, $form = null);

    /**
     * Normalizes the $string provided to the given or default $form and returns the normalized string.
     *
     * Calls underlying implementation even if given $form is NONE, but finally it normalizes only if needed.
     *
     * @param string $input the string to normalize
     * @param int    $form  (optional) normalization form to use, overriding the default
     *
     * @throws \Sjorek\UnicodeNormalization\Exception\InvalidNormalizationForm
     *
     * @return null|string Normalized string or null if an error occurred
     */
    public function normalizeTo($input, $form = null);

    /**
     * Normalizes the $string provided to the given or default $form and returns the normalized string.
     *
     * Does not call underlying implementation if given normalization is NONE and normalizes only if needed.
     *
     * @param string $input the string to normalize
     * @param int    $form  (optional) normalization form to use, overriding the default
     *
     * @throws \Sjorek\UnicodeNormalization\Exception\InvalidNormalizationForm
     *
     * @return null|string Normalized string or null if an error occurred
     */
    public function normalizeStringTo($input, $form = null);

    /**
     * Get the supported unicode version level as version triple ("X.Y.Z").
     *
     * @return string
     */
    public static function getUnicodeVersion();

    /**
     * Get the supported unicode normalization forms as array.
     *
     * @return int[]
     */
    public static function getNormalizationForms();
}
```

### Stream filtering

```php
<?php

/**
 * @var $stream        resource    The stream to filter.
 * @var $form          string      The form to normalize unicode to.
 * @var $read_write    int         (optional) STREAM_FILTER_* constant to override the filter injection point
 * @var $params        string|int  (optional) A normalization-form alias or value
 *
 * @link http://php.net/manual/en/function.stream-filter-append.php
 * @link http://php.net/manual/en/function.stream-filter-prepend.php
 */
stream_filter_append($stream, "convert.unicode-normalization.$form"[, $read_write[, $params]]);
```

Note: Be careful when using on streams in `r+` or `w+` (or similar) modes; by default PHP will assign the
filter to both the reading and writing chain. This means it will attempt to convert the data twice - first when
reading from the stream, and once again when writing to it.


## Examples

### Unicode Normalization

```php
<?php

use Sjorek\UnicodeNormalization\Normalizer;

$string = 'äöü';

$normalizer = new Normalizer(Normalizer::NONE);
$nfc = new Normalizer();
$nfd = new Normalizer(Normalizer::NFD);
$nfkc = new Normalizer('matching');

var_dump(
    // yields false as form NONE is never normalized
    $normalizer->isNormalized($string),

    // yields true, as NFC is the default for utf8 in the web.
    $nfc->isNormalized($string),

    // yields false
    $nfd->isNormalized($string),

    // yields false
    $nfkc->isNormalized($string),

    // yields false
    $normalizer->isNormalized($string, Normalizer::NFKD),

    // yields true
    $normalizer->normalize($string) === $string,

    // yields true
    $nfc->normalize($string) === $string,

    // yields false
    $nfd->normalize($string) === $string,

    // yields true, as only combined characters (means two or more letters in one
    // character, like the single ǆ character) are decomposed (for faster matching).
    $nfkc->normalize($string) === $string,

    Normalizer::getUnicodeVersion(),
    Normalizer::getNormalizationForms()
);

```

### Stream filtering

```php
<?php

$in_file = fopen('utf8-file.txt', 'r');
$out_file = fopen('utf8-normalized-to-nfc-file.txt', 'w');

// It works as a read filter:
stream_filter_append($in_file, 'convert.unicode-normalization.NFC');

// Normalization form may be given as fourth parameter:
// stream_filter_append($in_file, 'convert.unicode-normalization', null, 'NFC');

// And it also works as a write filter:
// stream_filter_append($out_file, 'convert.unicode-normalization.NFC');

stream_copy_to_stream($in_file, $out_file);
```


## Contributing

Look at the [contribution guidelines](CONTRIBUTING.md)

## Links

### Status

[![Build Status](https://img.shields.io/travis/sjorek/unicode-normalization.svg)](https://travis-ci.org/sjorek/unicode-normalization)


### GitHub

[![GitHub Issues](https://img.shields.io/github/issues/sjorek/unicode-normalization.svg)](https://github.com/sjorek/unicode-normalization/issues)
[![GitHub Latest Tag](https://img.shields.io/github/tag/sjorek/unicode-normalization.svg)](https://github.com/sjorek/unicode-normalization/tags)
[![GitHub Total Downloads](https://img.shields.io/github/downloads/sjorek/unicode-normalization/total.svg)](https://github.com/sjorek/unicode-normalization/releases)


### Packagist

[![Packagist Latest Stable Version](https://poser.pugx.org/sjorek/unicode-normalization/version)](https://packagist.org/packages/sjorek/unicode-normalization)
[![Packagist Total Downloads](https://poser.pugx.org/sjorek/unicode-normalization/downloads)](https://packagist.org/packages/sjorek/unicode-normalization)
[![Packagist Latest Unstable Version](https://poser.pugx.org/sjorek/unicode-normalization/v/unstable)](https://packagist.org/packages/sjorek/unicode-normalization)
[![Packagist License](https://poser.pugx.org/sjorek/unicode-normalization/license)](https://packagist.org/packages/sjorek/unicode-normalization)


### Social

[![GitHub Forks](https://img.shields.io/github/forks/sjorek/unicode-normalization.svg?style=social)](https://github.com/sjorek/unicode-normalization/network)
[![GitHub Stars](https://img.shields.io/github/stars/sjorek/unicode-normalization.svg?style=social)](https://github.com/sjorek/unicode-normalization/stargazers)
[![GitHub Watchers](https://img.shields.io/github/watchers/sjorek/unicode-normalization.svg?style=social)](https://github.com/sjorek/unicode-normalization/watchers)
[![Twitter](https://img.shields.io/twitter/url/https/github.com/sjorek/unicode-normalization.svg?style=social)](https://twitter.com/intent/tweet?url=https%3A%2F%2Fsjorek.github.io%2Funicode-normalization%2F)

