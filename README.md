# aerospike-cache-provider [![Latest Stable Version](https://img.shields.io/packagist/v/redcode/aerospike-cache-provider.svg?style=flat)](https://packagist.org/packages/redcode/aerospike-cache-provider) [![Total Downloads](https://img.shields.io/packagist/dt/redcode/aerospike-cache-provider.svg?style=flat)](https://packagist.org/packages/redcode/aerospike-cache-provider)
[![Build Status](https://img.shields.io/travis/maZahaca/aerospike-cache-provider.svg?style=flat)](https://travis-ci.org/maZahaca/aerospike-cache-provider)

This is an implementation of Doctrine\Common\Cache\CacheProvider for [Aerospike](http://www.aerospike.com/).

It allows you to use aerospike connection in projects which are based on the [doctrine/cache](https://github.com/doctrine/cache)

## Installation
The easiest way to install this library is with [Composer](https://getcomposer.org/) using the following command:
```
$ composer require redcode/aerospike-cache-provider
```

## How does it work?

```php
// pre-configured Aerospike connection
$aerospike = new \Aerospike();
$cache = new AerospikeCache($aerospike);
$cache->save('test', 'value', 60);
$value = $cache->fetch('test');
```
