
# THIS PACKAGED IS OUTDATED - BUILT FOR THE OAUTH 1.0A API

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

## Basic Use

These steps assume the user has authenticated your app, and the authentication
details have been stored by `MyStorageClass`.
The initial authorisation process is supported by package
`https://github.com/consilience/xero-api-client`, but you can take the user through
that process any way you like, so long as you collect the token details at the end.

Create an OAuth1 token object with the current saved authentication details.

```php
// $authDetails is an array of token details fetched from storage
// using key $persistenceKey

$tokenData = MyStorageClass::get($persistenceKey);

// "app1" is the app access details config to use.

$oauth1Token = \XeroClient::getOauthToken($authDetails, 'app1');
```

If accessing the *Partner* app (which is the only app type supported at present)
then create a function for persisting renewed tokens.

```php
$onPersist = function (\Consilience\XeroApi\Client\OauthTokenInterface $oauth1Token) use ($persistenceKey) {
    // The new token data to save.

    $tokenData = $oauth1Token->getTokenData();

    // Save it.

    MyStorageClass::put($persistenceKey, $tokenData);
};

$oauth1Token = $oauth1Token->withOnPersist($onPersist);
```

If accessing the *Partner* app, create a function to fetch the current
authenticartion token details.
This will be used just before a token renewal, to help mitigate multiple
processes renewing the token at the same time.

```php
$onReload = function (\Consilience\XeroApi\Client\OauthTokenInterface $oauth1Token) use ($persistenceKey) {
    return MyStorageClass::get($persistenceKey);
};

$oauth1Token = $oauth1Token->withOnReload($onReload);
```

You can also use this function for initialising the OAuth token object in the first place:

```php
// Instantiate with no token details.

$oauth1Token = \XeroClient::getOauthToken([], 'app1');

// Trigger a reload, to fetch stored token details.

$oauth1Token = $oauth1Token->reload();
```

Now create the PSR-18 Xero client.

```php
// This will give you an app client, with authentication and built-on token renewal
// as provided by https://github.com/consilience/xero-api-client

$applicationClient = \XeroClient::getClient($oauth1Token, 'app1');
```

The above with attempt to auto-discover a base PSR-18 HTTP client.
If you have one you would like to use instead, then pass it in as a third parameter:

```php
$applicationClient = \XeroClient::getClient($oauth1Token, 'app1', $myFavouritePsr18Client);
```

Now you can use this client to make requests to Xero, using PSR-7 messages.
A simple example is:

```php
// PSR-17 factory will allow us to create PSR-7 messages.

$requestFactory = Http\Discovery\Psr17FactoryDiscovery::findRequestFactory();

$response = $applicationClient->sendRequest(
    $requestFactory->createRequest(
        'GET',
        'https://api.xero.com/api.xro/2.0/organisation'
    )->withHeader('Accept', 'application/json')
);

$payload = json_decode((string)$response->getBody());
```

A separate package is in development to handle the requests.
It uses code and models generated from the [Xero OpenAPI specs](https://github.com/XeroAPI/Xero-OpenAPI)
to create the request messages, send the requests, and parse the response
into models.
That package would use the `$applicationClient` set up here to handle the communications.

The package can be found here https://github.com/consilience/xero-api-sdk and the equivalent to
the above manual "organisations" request is very simple:

```php
$configuration = new Consilience\Xero\AccountingSdk\Configuration([
    'syncClient' => $applicationClient,
]);
$accountingApi = new \Consilience\Xero\AccountingSdk\Api\AccountingApi($configuration);

$result = $accountingApi->getOrganisations();
```

`$result` will be an `Consilience\Xero\AccountingSdk\Model\Organisations` object, with
and single `Consilience\Xero\AccountingSdk\Model\Organisation` object and further embedded
objects as appropriate for multiple addresses (`Consilience\Xero\AccountingSdk\Model\Address`)
and payment terms (`Consilience\Xero\AccountingSdk\Model\PaymentTerm`) etc.

Note that the endpoint URL is *organisation* (singular) but the operation and hence
the method is *organisations* (plural).
The API actually returns an array containing a single organisation, so the plural
name is more correct and future-proof, and the OpenAPI spec deals with this name-mismatch
for us; we don't care about the URLs and just use the operation functions.

