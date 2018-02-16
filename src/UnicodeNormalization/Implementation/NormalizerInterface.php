<?php declare(strict_types=1);

/*
 * This file is part of the Unicode Normalization project.
 *
 * (c) Stephan Jorek <stephan.jorek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sjorek\UnicodeNormalization\Implementation;


use Sjorek\UnicodeNormalization\InvalidNormalizationFormException;
use Sjorek\UnicodeNormalization\Utility;

/**
 * Interface for all unicode normalizer implementations.
 * Additionally required form constants are defined here.
 *
 * @author Stephan Jorek <stephan.jorek@gmail.com>
 */
interface NormalizerInterface
{

    /**
     * Ignore any decomposition/composition
     *
     * Ignoring Implementation decomposition/composition, means nothing is automatically normalized.
     * Manny Linux- and BSD-filesystems do not normalize paths and filenames, but treat them as binary data.
     * Apple™'s APFS filesystem treats paths and filenames as binary data.
     *
     * @var integer
     */
    const NONE = 1;

    /**
     * Canonical decomposition
     *
     *    “A normalization form that erases any canonical differences, and produces a
     *    decomposed result. For example, ä is converted to a + umlaut in this form.
     *    This form is most often used in internal processing, such as in collation.”
     *
     *    -- quoted from unicode glossary linked below
     *
     * @var integer $NFD
     * @var integer $FORM_D
     * @link http://www.unicode.org/glossary/#normalization_form_d
     * @link https://developer.apple.com/library/content/qa/qa1173/_index.html
     * @link https://developer.apple.com/library/content/qa/qa1235/_index.html
     */
    const NFD  = 2, FORM_D  = 2;

    /**
     * Compatibility decomposition
     *
     *    “A normalization form that erases both canonical and compatibility differences,
     *    and produces a decomposed result: for example, the single ǆ character is
     *    converted to d + z + caron in this form.”
     *
     *    -- quoted from unicode glossary linked below
     *
     * @var integer $NFKD
     * @var integer $FORM_KD
     * @link http://www.unicode.org/glossary/#normalization_form_kd
     */
    const NFKD = 3, FORM_KD = 3;

    /**
     * Canonical decomposition followed by canonical composition
     *
     *    “A normalization form that erases any canonical differences, and generally produces
     *    a composed result. For example, a + umlaut is converted to ä in this form. This form
     *    most closely matches legacy usage.”
     *
     *    -- quoted from unicode glossary linked below
     *
     * W3C recommends NFC for HTML5 output and requires NFC for HTML5-compliant parser implementations.
     *
     * @var integer $NFC
     * @var integer $FORM_C
     * @link http://www.unicode.org/glossary/#normalization_form_c
     */
    const NFC  = 4, FORM_C  = 4;

    /**
     * Compatibility Decomposition followed by Canonical Composition
     *
     *    “A normalization form that erases both canonical and compatibility differences,
     *    and generally produces a composed result: for example, the single ǆ character
     *    is converted to d + ž in this form. This form is commonly used in matching.”
     *
     *    -- quoted from unicode glossary linked below
     *
     * @var integer $NFKC
     * @var integer $FORM_KC
     * @link http://www.unicode.org/glossary/#normalization_form_kc
     */
    const NFKC = 5, FORM_KC = 5;

    /**
     * Apple™ Canonical decomposition for HFS Plus filesystems
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
     * @var integer $NFD_MAC
     * @var integer $FORM_D_MAC
     * @var integer $MAC
     * @see NormalizerInterface::NFD
     * @link https://developer.apple.com/library/content/qa/qa1173/_index.html
     * @link https://developer.apple.com/library/content/qa/qa1235/_index.html
     * @link http://dubeiko.com/development/FileSystems/HFSPLUS/tn1150.html#CanonicalDecomposition
     * @link https://opensource.apple.com/source/libiconv/libiconv-50/libiconv/lib/utf8mac.h.auto.html
     */
    const NFD_MAC = 32, FORM_D_MAC  = 32; // 0x2 & 0xF


    /**
     * Set the default normalization form to the given value.
     *
     * @param integer|string $form
     * @return void
     * @see Utility::parseNormalizationForm()
     * @throws InvalidNormalizationFormException
     */
    public function setForm($form = null);

    /**
     * Retrieve the current normalization-form constant.
     *
     * @return integer
     */
    public function getForm();

    /**
     * Return the unicode conformance level
     *
     * @return string
     */
    public static function getConformanceLevel();

    /**
     * Return an array of supported normalization form constants
     *
     * @return integer[]
     */
    public static function getNormalizationForms();

    /**
     * Returns true if this normalizer implementation behaves strict.
     *
     * The strict implementation uses a potentially expensive equality-check to check if
     * a string is normalized to NFD_MAC. As we primilarily want to use it for filenames,
     * this full check should be ok.
     *
     * @return boolean
     * @see NormalizerInterface::NFD_MAC
     */
    public function isStrictImplementation();

    /**
     * Normalizes the input provided and returns the normalized string.
     *
     * @param string $input     The input string to normalize.
     * @param int $form         [optional] One of the normalization forms.
     * @return string The normalized string or FALSE if an error occurred.
     * @throws InvalidNormalizationFormException
     */
    public function normalize($input, $form = null);

    /**
     * Checks if the provided string is already in the specified normalization form.
     *
     * @param string $input     The input string to normalize
     * @param int $form         [optional] One of the normalization forms.
     * @return bool TRUE if normalized, FALSE otherwise or if an error occurred.
     * @throws InvalidNormalizationFormException
     */
    public function isNormalized($input, $form = null);

    /**
     * Normalizes the $string provided to the given or default $form and returns the normalized string.
     *
     * Calls underlying implementation even if given $form is NONE, but finally it normalizes only if
     * there is something to normalize.
     *
     * @param string $input     The string to normalize.
     * @param integer $form     [optional] normalization form to use, overriding the default
     * @return string|null Normalized string or null if an error occurred
     * @throws InvalidNormalizationFormException
     * @link http://php.net/manual/en/normalizer.isnormalized.php
     * @link http://www.php.net/manual/en/normalizer.normalize.php
     */
    public function normalizeTo($input, $form = null);

    /**
     * Normalizes the $string provided to the given or default $normalization and returns the normalized string.
     *
     * Does not call underlying implementation if given normalization is NONE and normalizes only if needed.
     *
     * @param string $input     The string to normalize.
     * @param integer $form     [optional] normalization form to use, overriding the default
     * @return string|null Normalized string or null if an error occurred
     * @throws InvalidNormalizationFormException
     * @link http://www.php.net/manual/en/normalizer.normalize.php
     */
    public function normalizeStringTo($input, $form = null);
}
