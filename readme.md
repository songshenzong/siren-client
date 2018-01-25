# SirenClient

Siren Client

## Installation

Require this package with composer:

```shell

     composer require songshenzong/siren-client
```



## Common Settings
```php

     /**
     * If Laravel, Please write these fields in the `env` file
     * ServiceProvider will run the `setHost` and `setToken` automatically
     */
     
     SIREN_HOST=127.0.0.1
     SIREN_PORT=55656
     SIREN_TOKEN=TOKEN
 
  
     /**
     * Set the host and port, if not, the default value is 127.0.0.1:55656
     */
     
     SirenClient::setHost('127.0.0.2', 55656);
     // sirenSetHost('127.0.0.2', 55656);
 
  
     /**
     * Requests will be discarded by the server if the token is incorrect
     */
     
     SirenClient::setToken('Your Token');
     // sirenSetToken('Your Token');
    
```

## Report
```php
                       
      /**
      * Module and submodule consumption time sirens
      */
      SirenClient::tick('moduleName', 'submoduleName');
      // sirenTick('moduleName', 'submoduleName');
 
  
   
      /**
      * Send success or failure
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


## Documentation

Please refer to our extensive [Wiki documentation](https://github.com/songshenzong/siren-client/wiki) for more information.


## Support

For answers you may not find in the Wiki, avoid posting issues. Feel free to ask for support on Songshenzong.com


## License

This package is licensed under the [BSD 3-Clause license](http://opensource.org/licenses/BSD-3-Clause).
