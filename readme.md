# SirenClient

Siren Client

## Installation 安装

Require this package with composer:

```shell

     composer require songshenzong/siren-client
```



## Settings 用前设置
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
     * If not Laravel, Set the `host` and `port`, if not, the default value is 127.0.0.1:55656
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

## Report
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
          SirenClient::error('moduleName', 'submoduleName', 500, 'Someting wrong');
          // sirenError('moduleName', 'submoduleName', 500, 'Someting wrong');
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



##  Before Laravel 5.5
After updating composer, add the ServiceProvider to the providers array in `config/app.php`

```php

      Songshenzong\SirenClient\ServiceProvider::class,
      
```


## Documentation 文档

Please refer to our extensive [Wiki documentation](https://github.com/songshenzong/siren-client/wiki) for more information.


## Support 支持

For answers you may not find in the Wiki, avoid posting issues. Feel free to ask for support on Songshenzong.com


## License 证书

This package is licensed under the [BSD 3-Clause license](http://opensource.org/licenses/BSD-3-Clause).
