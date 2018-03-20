[![Build Status](https://travis-ci.org/songshenzong/siren-client.svg?branch=master)](https://travis-ci.org/songshenzong/siren-client)
[![Total Downloads](https://poser.pugx.org/songshenzong/siren-client/d/total.svg)](https://packagist.org/packages/songshenzong/siren-client)
[![Latest Stable Version](https://poser.pugx.org/songshenzong/siren-client/v/stable.svg)](https://packagist.org/packages/songshenzong/siren-client)
[![License](https://poser.pugx.org/songshenzong/siren-client/license.svg)](https://packagist.org/packages/songshenzong/siren-client)
[![PHP Version](https://img.shields.io/packagist/php-v/songshenzong/siren-client.svg)](https://packagist.org/packages/songshenzong/siren-client)


## Installation

Require this package with composer:

```shell
composer require songshenzong/siren-client
```



## Settings
```php

/**
* If Laravel, Just write these fields in the `env` file
*/
SIREN_TOKEN=YourToken
SIREN_PROTOCOL=udp
SIREN_UDP_HOST=127.0.0.1
SIREN_UDP_PORT=55656
SIREN_TCP_HOST=127.0.0.1
SIREN_TCP_PORT=55657
SIREN_HTTP_HOST=127.0.0.1
SIREN_HTTP_PORT=55658
 
  
/**
* If not Laravel, You Need to `setConfig`
* Requests will be discarded by the server when the token is incorrect
*/
Siren::setConfig([
                     'token'    => 'YourToken',
                     'protocol' => 'udp',
                     'servers'  => [
                         'udp'  => [
                             'host' => '127.0.0.1',
                             'port' => '55656'
                         ],
                         'tcp'  => [
                             'host' => '127.0.0.1',
                             'port' => '55657'
                         ],
                         'http' => [
                             'host' => '127.0.0.1',
                             'port' => '55658'
                         ],
                     ]
                 ]);
 
  
 
// If `SERVER` is multiple servers, the system will randomly choose one to reduce the pressure.

```


## Laravel

Publish configuration files. If not, They can not be serialized correctly when you execute the `config:cache` Artisan command.

```shell
php artisan vendor:publish --provider="Songshenzong\Siren\ServiceProvider"
```



## Report
```php
 
/**
* Module and submodule consumption time
*/
Siren::tick('moduleName', 'submoduleName');
 
  
   
/**
* Send success or failure
*/
if (YourClass::action()) {
  Siren::success('moduleName', 'submoduleName');
} else {
  Siren::error('moduleName', 'submoduleName', 'Something wrong');
}
 
 
 
/**
* Handle Exception
*/
try {
  throw new Exception('Message');
} catch (Exception $exception) {
  Siren::exception($exception);
}
 
```



## Documentation

Please refer to our extensive [Wiki documentation](https://github.com/songshenzong/siren-client/wiki) for more information.


## Support

For answers you may not find in the Wiki, avoid posting issues. Feel free to ask for support on Songshenzong.com


## License

This package is licensed under the [BSD 3-Clause license](http://opensource.org/licenses/BSD-3-Clause).
