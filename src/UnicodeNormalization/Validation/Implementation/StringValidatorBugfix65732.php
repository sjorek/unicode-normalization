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

namespace Sjorek\UnicodeNormalization\Validation\Implementation;

/**
 * This StringValidator implementation provides a workaround for
 * https://bugs.php.net/65732, which has been fixed for PHP ≥7.0.11.
 *
 * @author Stephan Jorek <stephan.jorek@gmail.com>
 */
class StringValidatorBugfix65732 extends StringValidatorImpl
{
    /**
     * {@inheritDoc}
     *
     * @see StringValidatorImpl::filter()
     */
    public function filter($input, $form = null, $charset = null)
    {
        if (false === strpos($input, "\r")) {
            return parent::filter($input, $form, $charset);
        }

        $input = array_map(
            function ($string) use ($form, $charset) {
                $this->filter($string, $form, $charset);
            },
            explode("\r", $input)
        );

        return in_array(false, $input, true) ? false : implode("\r", $input);
    }
}
