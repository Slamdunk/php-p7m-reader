# Slam P7M Reader

[![Latest Stable Version](https://img.shields.io/packagist/v/slam/php-p7m-reader.svg)](https://packagist.org/packages/slam/php-p7m-reader)
[![Downloads](https://img.shields.io/packagist/dt/slam/php-p7m-reader.svg)](https://packagist.org/packages/slam/php-p7m-reader)
[![Integrate](https://github.com/Slamdunk/php-p7m-reader/workflows/Integrate/badge.svg?branch=master)](https://github.com/Slamdunk/php-p7m-reader/actions)
[![Code Coverage](https://codecov.io/gh/Slamdunk/php-p7m-reader/coverage.svg?branch=master)](https://codecov.io/gh/Slamdunk/php-p7m-reader?branch=master)

## Installation

`composer require slam/php-p7m-reader`

## Requirements

1. PHP ^7.4
1. `openssl` binary
1. `ext-openssl`

## Usage

*WARNING*: the signature is verified, but the validity of the certificate it is not!

```php
$p7mReader = \Slam\P7MReader\P7MReader::decodeFromFile(
    new \SplFileObject('/path/to/my.xml.p7m'),
    __DIR__ . '/tmp'    // Optional custom temporary directory, defaults to sys_get_temp_dir()
);
// OR
$p7mReader = \Slam\P7MReader\P7MReader::decodeFromBase64(
    'Abc==',            // base64 encoded content file
    __DIR__ . '/tmp'    // Optional custom temporary directory, defaults to sys_get_temp_dir()
);

var_dump($p7mReader->getP7mFile());     // string:        The original P7M file
var_dump($p7mReader->getContentFile()); // SplFileObject: The signed content
var_dump($p7mReader->getCertFile());    // SplFileObject: The certificate
var_dump($p7mReader->getCertData());    // array:         Certificate data in openssl_x509_parse output format
```
