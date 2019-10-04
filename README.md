# Slam P7M Reader

[![Build Status](https://travis-ci.org/Slamdunk/php-p7m-reader.svg?branch=master)](https://travis-ci.org/Slamdunk/php-p7m-reader)
[![Code Coverage](https://scrutinizer-ci.com/g/Slamdunk/php-p7m-reader/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Slamdunk/php-p7m-reader/?branch=master)
[![Packagist](https://img.shields.io/packagist/v/slam/php-p7m-reader.svg)](https://packagist.org/packages/slam/php-p7m-reader)

## Installation

`composer require slam/php-php-p7m-reader`

## Requirements

1. PHP ^7.1
1. `ext-openssl`

## Usage

*WARNING*: the signature is not verified, this library only extracts the content and the certificate.

```php
$p7mReader = new \Slam\P7MReader\P7MReader(
    new \SplFileObject('/path/to/my.xml.p7m',
    __DIR__ . '/tmp' // Optional custom temporary directory, defaults to sys_get_temp_dir()
);

var_dump($p7mReader->getP7mFile()->getPathname());      // The original P7M
var_dump($p7mReader->getContentFile()->getPathname());  // The signed content
var_dump($p7mReader->getCertFile()->getPathname());     // The certificate
var_dump($p7mReader->getCertData());                    // Certificate data in openssl_x509_parse output format
```