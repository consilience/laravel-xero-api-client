# laravel-xero-api-client
Laravel/Lumen provider for consilience/xero-api-client

Provides, as a convenience:

* Service provider to generate clients.
* Facade for the service provider.
* Publishable configuration file.
* Multiple configurations, with a default.
* Example environment variables.
* Covers HTTP communications only; the endpoints and models are handled in other packages of generated code (possibly).
* Installation documentation.
* Usage examples.

Although only Xero Partner applications are supported at present, the configuration file
will separate settings by application type.

Treat the above as a TODO list until DONE.


## Installation

### Laravel

TODO

### Lumen

    $app->register(Consilience\XeroApi\LaravelClient\ClientServiceProvider::class);
    class_alias(Consilience\XeroApi\LaravelClient\ClientServiceFacade::class, 'XeroClient');
    $app->configure('xero-api-client');

and

    cp vendor/consilience/laravel-xero-api-client/config/xero-api-client.php config/

