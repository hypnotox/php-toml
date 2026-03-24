# hypnotox/toml

[![CI Status](https://github.com/hypnotox/php-toml/actions/workflows/ci.yml/badge.svg)](https://github.com/hypnotox/php-toml)
[![Code Coverage](https://codecov.io/gh/hypnotox/php-toml/branch/main/graph/badge.svg?token=FrsdlOIbRo)](https://codecov.io/gh/hypnotox/php-toml)
[![Packagist Version](https://badgen.net/packagist/v/hypnotox/toml)](https://packagist.org/packages/hypnotox/toml)
[![Packagist PHP Version Support](https://badgen.net/packagist/php/hypnotox/toml)](https://packagist.org/packages/hypnotox/toml)
[![GitHub](https://badgen.net/packagist/license/hypnotox/toml)](/LICENSE.md)

A PHP package for encoding and decoding TOML with immutable object representation.

Fully supports the [TOML v1.0.0 specification](https://toml.io/en/v1.0.0) and passes the [toml-test](https://github.com/toml-lang/toml-test) conformance suite (678 decoder tests + 205 encoder tests).

Requires PHP 8.3+.

## Installation

```bash
composer require hypnotox/toml
```

## Usage

### Decoding TOML

```php
use HypnoTox\Toml\TomlFactory;

$factory = new TomlFactory();
$toml = $factory->fromString('
[server]
host = "localhost"
port = 8080
enabled = true
');

$toml->get('server.host');    // "localhost"
$toml->get('server.port');    // 8080
$toml->get('server.enabled'); // true
$toml->toArray();             // ['server' => ['host' => 'localhost', 'port' => 8080, 'enabled' => true]]
```

### Encoding to TOML

```php
use HypnoTox\Toml\Toml;

$toml = Toml::fromArray([
    'title' => 'My App',
    'database' => [
        'host' => '127.0.0.1',
        'port' => 5432,
        'enabled' => true,
    ],
]);

echo $toml->toString();
// title = "My App"
//
// [database]
// host = "127.0.0.1"
// port = 5432
// enabled = true
```

### Working with Toml objects

```php
// Immutable set (returns a new instance)
$updated = $toml->set('server.host', '0.0.0.0');

// Get as PHP array
$array = $toml->toArray();

// Encode back to TOML string
$string = $toml->toString();
```

## Features

- Full TOML v1.0.0 specification support
  - Tables, inline tables, array of tables
  - All key types (bare, quoted, dotted)
  - All data types: string (basic, literal, multiline), integer (decimal, hex, octal, binary), float (including inf/nan), boolean, offset date-time, local date-time, local date, local time, array
- Bidirectional: decode TOML to PHP arrays and encode PHP arrays to TOML
- Immutable `Toml` data objects
- Strict validation with detailed error messages including line/column numbers

## License

[MIT](/LICENSE.md)
