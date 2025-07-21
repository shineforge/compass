# shineforge/compass

[![License](https://img.shields.io/packagist/l/shineforge/compass)](https://github.com/shineforge/compass/blob/main/LICENSE)
[![Latest Version](https://img.shields.io/packagist/v/shineforge/compass?label=latest)](https://packagist.org/packages/shineforge/compass/)
[![PHP Version](https://img.shields.io/packagist/dependency-v/shineforge/compass/php?label=php)](https://www.php.net/releases/index.php)
[![Main Status](https://img.shields.io/github/actions/workflow/status/shineforge/compass/verify.yml?branch=main&label=main)](https://github.com/shineforge/compass/actions/workflows/verify.yml?query=branch%3Amain)
[![Release Status](https://img.shields.io/github/actions/workflow/status/shineforge/compass/verify.yml?branch=release&label=release)](https://github.com/shineforge/compass/actions/workflows/verify.yml?query=branch%3Arelease)
[![Develop Status](https://img.shields.io/github/actions/workflow/status/shineforge/compass/verify.yml?branch=develop&label=develop)](https://github.com/shineforge/compass/actions/workflows/verify.yml?query=branch%3Adevelop)

## Description

Compass provides a powerful `URL` class that simplifies parsing, building, and manipulating URLs in your PHP applications. It offers an intuitive, immutable API for handling all parts of a URL, from the scheme to the fragment.

Key features include:
- **PSR-7 `UriInterface` Compatibility**: Use it as a drop-in replacement wherever a PSR-7 URI is needed.
- **Immutable API**: All modification methods (`with...`) return a new instance, ensuring predictable and safe state management.
- **Advanced Path Resolution**: Easily convert between absolute and relative URLs, and canonicalize paths (resolving `.` and `..` segments).
- **Robust Parsing**: Handles complex URLs gracefully.

## Installation

Install the library using Composer:

```bash
composer require shineforge/compass
```

## Usage

### Creating a URL

You can create a `URL` instance from a string.

```php
<?php

require 'vendor/autoload.php';

use Compass\URL;

// Create via constructor
$url = new URL('https://user:pass@example.com:8080/path/to/file?query=string#fragment');

// Or using the static factory method
$url = URL::create('https://example.com/some/path');
```

If the provided string is an invalid URL, an `InvalidArgumentException` will be thrown.

### Accessing URL Components

The `URL` class provides getter methods for all parts of a URL, compliant with `Psr\Http\Message\UriInterface`.

```php
<?php

$url = new Compass\URL('https://user:pass@example.com:8080/path/to/file?query=string#fragment');

echo $url->getScheme();   // "https"
echo $url->getAuthority(); // "user:pass@example.com:8080"
echo $url->getUserInfo();  // "user:pass"
echo $url->getHost();      // "example.com"
echo $url->getPort();      // 8080
echo $url->getPath();      // "/path/to/file"
echo $url->getQuery();     // "query=string"
echo $url->getFragment();  // "fragment"
```

### Modifying a URL (Immutability)

The `URL` object is immutable. All methods that modify the URL, such as `withPath()` or `withScheme()`, return a *new* `URL` instance with the change, leaving the original object untouched.

```php
<?php

$url = Compass\URL::create('https://example.com/path');

$newUrl = $url->withScheme('http')->withPort(8080);

echo $url;     // "https://example.com/path"
echo $newUrl;  // "http://example.com:8080/path"
```

### Absolute vs. Relative URLs

You can easily check if a URL is absolute or relative.

```php
<?php

$absolute = new Compass\URL('https://example.com/path');
$relative = new Compass\URL('/path/only');

var_dump($absolute->isAbsolute()); // bool(true)
var_dump($absolute->isRelative()); // bool(false)

var_dump($relative->isAbsolute()); // bool(false)
var_dump($relative->isRelative()); // bool(true)
```

### Path Resolution

Compass excels at handling complex path resolution tasks.

#### Making a URL Absolute

You can resolve a relative URL against a base URL to make it absolute.

```php
<?php

$base = Compass\URL::create('https://example.com/a/b/c');
$relativeUrl = Compass\URL::create('../../d/e');

$absoluteUrl = $relativeUrl->makeAbsolute($base);

echo $absoluteUrl; // "https://example.com/a/d/e"
```

#### Making a URL Relative

You can also compute a relative path from one absolute URL to another.

```php
<?php

$base = Compass\URL::create('https://example.com/a/b/c');
$target = Compass\URL::create('https://example.com/a/d/e');

$relativeUrl = $target->makeRelative($base);

echo $relativeUrl; // "../d/e"
```

You can also make a URL root-relative by passing `null` as the base. This will strip the scheme and authority, leaving a path that is absolute from the root of a domain.

```php
<?php

$url = Compass\URL::create('https://example.com/some/path');
$rootRelative = $url->makeRelative(null);

echo $rootRelative; // "/some/path"
```

### Converting to a String

The `URL` object can be easily cast to a string, which will return the full, canonicalized URL.

```php
<?php

$url = Compass\URL::create('https://example.com/a/b/../c/');

echo $url; // "https://example.com/a/c/"
```