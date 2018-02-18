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


use Sjorek\UnicodeNormalization\Utility;

if (class_exists(__NAMESPACE__ . '\\NormalizerImpl', false))
{
    // Nothing to do here …

} elseif (Utility::getNormalizerImplementation() === 'Normalizer') {

    /**
     * Unicode Normalizer Implementation
     *
     * @author Stephan Jorek <stephan.jorek@gmail.com>
     */
    class NormalizerImpl extends \Normalizer {}

} else {

    // Alias normalizer implementation.
    class_alias(Utility::getNormalizerImplementation(), __NAMESPACE__ . '\\NormalizerImpl', true);
}
