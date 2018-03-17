[![Songshenzong](https://cdn.songshenzong.com/images/logo.png)](https://songshenzong.com)

[![Build Status](https://travis-ci.org/songshenzong/siren-client.svg?branch=master)](https://travis-ci.org/songshenzong/siren-client)
[![Total Downloads](https://poser.pugx.org/songshenzong/siren-client/d/total.svg)](https://packagist.org/packages/songshenzong/siren-client)
[![Latest Stable Version](https://poser.pugx.org/songshenzong/siren-client/v/stable.svg)](https://packagist.org/packages/songshenzong/siren-client)
[![License](https://poser.pugx.org/songshenzong/siren-client/license.svg)](https://packagist.org/packages/songshenzong/siren-client)
[![PHP Version](https://img.shields.io/packagist/php-v/songshenzong/siren-client.svg)](https://packagist.org/packages/songshenzong/siren-client)


## 安装 Installation

Require this package with composer:

```shell
composer require songshenzong/siren-client
```

##  配置 Laravel

Publish configuration files. If not, They can not be serialized correctly when you execute the `config:cache` Artisan command.

```shell
php artisan vendor:publish --provider="Songshenzong\SirenClient\ServiceProvider"
```


## 设置 Settings
```php

/**
* If Laravel, Please write these fields in the `env` file
* ServiceProvider will run the `setHost` and `setToken` automatically
*
* 如果使用 Laravel，需要添加以下字段和值，服务提供者会自动设置
*
*/

SIREN_HOST=127.0.0.1
SIREN_PORT=55656
SIREN_TOKEN=TOKEN


/**
* If not Laravel, Set the `host` and `port`, the default value is 127.0.0.1:55656
* Requests will be discarded by the server if the token is incorrect
*
* 如果没用 Laravel，需要设置主机、端口、TOKEN，如果 TOKEN 错误服务器会抛弃数据
*
*/

SirenClient::setHost('127.0.0.2', 55656);
// sirenSetHost('127.0.0.2', 55656);

SirenClient::setToken('Your Token');
// sirenSetToken('Your Token');

```

## 上报 Report
```php

/**
* Module and submodule consumption time
*
* tick 模块和子模块可以精确统计时间
*/

SirenClient::tick('moduleName', 'submoduleName');
// sirenTick('moduleName', 'submoduleName');



/**
* Send success or failure
*
* 发送成功或失败消息
*
*/

if (YourClass::action()) {
  SirenClient::success('moduleName', 'submoduleName');
  // sirenSuccess('moduleName', 'submoduleName');
} else {
  SirenClient::error('moduleName', 'submoduleName', 500, 'Something wrong');
  // sirenError('moduleName', 'submoduleName', 500, 'Something wrong');
}



/**
* Handle Exception
*
* 上传异常（失败类型）
*/

try {
  throw new Exception('Message');
} catch (Exception $exception) {
  SirenClient::exception($exception);
  // sirenException($exception);
}

```





## 文档 Documentation

Please refer to our extensive [Wiki documentation](https://github.com/songshenzong/siren-client/wiki) for more information.


## 支持 Support

For answers you may not find in the Wiki, avoid posting issues. Feel free to ask for support on Songshenzong.com


## 证书 License

This package is licensed under the [BSD 3-Clause license](http://opensource.org/licenses/BSD-3-Clause).
