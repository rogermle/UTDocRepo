# UT DocRepositoryPHP

[![Latest Version on Packagist][ico-version]][link-packagist]
[![License](https://img.shields.io/badge/License-BSD%203--Clause-blue.svg)](LICENSE.md)
[![Build Status](https://travis-ci.org/rogermle/docrepo.svg?branch=master)](https://travis-ci.org/rogermle/docrepo)
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

The Document Repository PHP client was created to provide an object-oriented 
PHP class for interacting with the ITS Document Repository, which is a "secure, 
generalized library for storing binary documents and the metadata that
describes them."

The external API of the PHP client was written to mimic the behavior and 
syntax of the DocRepository Java API. Internally, it uses PHP's cURL library 
to interact with the Document Repository's REST API.

## Structure

If any of the following are applicable to your project, then the directory structure should follow industry best practises by being named the following.

```
build/
src/
tests/
```


## Install

Via Composer

``` bash
$ composer require utexas/docrepo
```

## Usage

``` php
$client = new Utexas\DocRepo\Client();
$client->get('1234);
```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Acknowledgement
The code in this project is based heavily on work done by 
[Paul Grotevant](https://github.com/gravelpot) of ITS Applications and
by Geoff Boyd in Liberal Arts ITS. It would not have been possible without the assistance of the 
Document Repository developer team, especially Chris Pittman and Dory Weiss.

## Security

If you discover any security related issues, please email roger.le@austin.utexas.edu instead of using the issue tracker.

## Credits

- [Roger Le](https://github.com/rogermle)

## License

The BSD 3 Clause License (BSD3). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/utexas/docrepo.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/utexas/docrepo/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/utexas/docrepo.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/utexas/docrepo.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/utexas/docrepo.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/utexas/docrepo
[link-travis]: https://travis-ci.org/utexas/docrepo
[link-scrutinizer]: https://scrutinizer-ci.com/g/utexas/docrepo/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/utexas/docrepo
[link-downloads]: https://packagist.org/packages/utexas/docrepo
[link-author]: https://github.com/rogermle
[link-contributors]: ../../contributors