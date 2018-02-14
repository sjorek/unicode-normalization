# [Unicode-Normalization](https://sjorek.github.io/unicode-normalization/)

A [composer](http://getcomposer.org)-package providing an enhanced facade to existing unicode-normalization
implementations.


## Installation

```bash
php composer.phar require sjorek/unicode-normalization
```


## Example

```php
<?php
\Sjorek\UnicodeNormalization\StreamFilter::register();

$in_file = fopen('utf8-file.txt', 'r');
$out_file = fopen('utf8-normalized-to-nfc-file.txt', 'w');

// It works as a read filter:
stream_filter_append($in_file, 'convert.unicode-normalization.NFC');
// And it also works as a write filter:
// stream_filter_append($out_file, 'convert.unicode-normalization.NFC');

stream_copy_to_stream($in_file, $out_file);
```


## Usage

```php
<?php
/**
 * @var $stream        resource   The stream to filter.
 * @var $form          string     The form to normalize unicode to.
 * @var $read_write    int        STREAM_FILTER_* constant to override the filter injection point
 *
 * @link http://php.net/manual/en/function.stream-filter-append.php
 * @link http://php.net/manual/en/function.stream-filter-prepend.php
 */
stream_filter_append($stream, "convert.unicode-normalization.$form", $read_write);
```

Note: Be careful when using on streams in `r+` or `w+` (or similar) modes; by default PHP will assign the
filter to both the reading and writing chain. This means it will attempt to convert the data twice - first when
reading from the stream, and once again when writing to it.


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

