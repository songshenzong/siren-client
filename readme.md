# StatisticClient

Statistic Client

## Installation

Require this package with composer:

```shell
composer require songshenzong/statistic-client
```


## Use
```php
    // Set the server and port, if not, the default value is 127.0.0.1:55656
    StatisticClient::setAddress('127.0.0.1', 55656);
    
    // System will store log into your project
    StatisticClient::setToken('Your Token');
        
        
    // Module and interface consumption time statistics
    StatisticClient::tick('User', 'destroyToken');


    // If action failed
    if (User::destroyToken(1)) {
        StatisticClient::success('User', 'destroyToken');
    } else {
        StatisticClient::error('User', 'destroyToken', 200, 'User 1 token destroy failed');
    }


    // Handle Exception
    try {
        throw new Exception('Message');
    } catch (Exception $exception) {
        StatisticClient:: exception('System', 'Exception', $exception);
    }
```



## Laravel 5.x
After updating composer, add the ServiceProvider to the providers array in `config/app.php`

```php
Songshenzong\StatisticClient\ServiceProvider::class,
```


## Documentation

Please refer to our extensive [Wiki documentation](https://github.com/songshenzong/statistic-client/wiki) for more information.


## Support

For answers you may not find in the Wiki, avoid posting issues. Feel free to ask for support on Songshenzong.com


## License

This package is licensed under the [BSD 3-Clause license](http://opensource.org/licenses/BSD-3-Clause).
