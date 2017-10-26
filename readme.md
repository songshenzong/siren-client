# StatisticClient

Statistic Client

## Installation

Require this package with composer:

```shell
composer require songshenzong/statistic-client
```

After updating composer, add the ServiceProvider to the providers array in `config/app.php`

### Laravel 5.x:

```php
Songshenzong\StatisticClient\ServiceProvider::class,
```
## Use
```php
  StatisticClient::setAddress('127.0.0.1', 55656);
  
  StatisticClient::tick('TestModule', 'TestApi');
  
  StatisticClient::success();
  
  StatisticClient:: error(200, 'Parameter1', 'Parameter2');
  
  try {
          throw new Exception('Exception Message');
  } catch (Exception $exception) {
          StatisticClient:: exception($exception);
  }
```

## Documentation

Please refer to our extensive [Wiki documentation](https://github.com/songshenzong/statistic-client/wiki) for more information.


## Support

For answers you may not find in the Wiki, avoid posting issues. Feel free to ask for support on Songshenzong.com


## License

This package is licensed under the [BSD 3-Clause license](http://opensource.org/licenses/BSD-3-Clause).
