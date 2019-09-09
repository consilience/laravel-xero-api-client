<?php

namespace Consilience\XeroApi\LaravelClient;

/**
 *
 */

use Consilience\XeroApi\Client\OauthTokenInterface;
use Psr\Http\Client\ClientInterface;
use Consilience\XeroApi\Client\App\Partner;
use Consilience\XeroApi\Client\App\AppPrivate;
use Consilience\XeroApi\Client\Oauth1\Token as Oauth1Token;

class ClientService
{
    /**
     * List of API clients that have been generated.
     */

    protected $clients = [];

    protected function defaultAppKey(string $appKey = null)
    {
        if ($appKey === null) {
            return config(ClientServiceProvider::CONFIG_FILE . '.default');
        }

        return $appKey;
    }

    /**
     * Get the configuration data for an application.
     *
     * @param string|null $appKey The application key in the config file.
     * @return array
     */
    public function getAppConfig(string $appKey = null): array
    {
        $appKey = $this->defaultAppKey($appKey);

        $config = config(ClientServiceProvider::CONFIG_FILE . '.apps.' . $appKey, []);

        // Any non-absolute pathname (not starting with a '/') is treated
        // as relative to the app base directory.
        // TODO: look at array mapping for more elegant solution.

        foreach (['private_key_file'] as $pathname) {
            if (isset($config[$pathname]) && substr($config[$pathname], 0, 1) !== '/') {
                $config[$pathname] = base_path($config[$pathname]);
            }
        }

        return $config;
    }

    /**
     * Return a PSR-18 application client, handling OAuth and token renewals.
     */
    public function getClient(
        OauthTokenInterface $oauthToken,
        string $appKey = null,
        ?ClientInterface $baseClient = null
    ): ClientInterface
    {
        $appKey = $this->defaultAppKey($appKey);

        if (array_key_exists($appKey, $this->clients)) {
            return $this->clients[$appKey];
        }

        $config = $this->getAppConfig($appKey);

        if ($config['type'] === ClientServiceProvider::APP_TYPE_PARTNER) {
            return $this->clients[$appKey] = new Partner(
                $oauthToken,
                $config,
                $baseClient
            );
        }

        if ($config['type'] === ClientServiceProvider::APP_TYPE_PRIVATE) {
            return $this->clients[$appKey] = new AppPrivate(
                $oauthToken,
                $config,
                $baseClient
            );
        }

        // Config not supported yet.
    }

    /**
     * Return an OAuth token object for an application key.
     * Initial credentials can be added here, or added to the token object later.
     */
    public function getOauthToken(array $authDetails = [], string $appKey = null): OauthTokenInterface
    {
        $config = $this->getAppConfig($appKey);

        if ($config['auth_type'] === ClientServiceProvider::AUTH_TYPE_OAUTH1
            && $config['type'] === ClientServiceProvider::APP_TYPE_PARTNER
        ) {
            $token = new Oauth1Token($authDetails);

            $guardTimeSeconds = config(ClientServiceProvider::CONFIG_FILE . '.guard_time_seconds');

            if ($guardTimeSeconds) {
                $token = $token->withGuardTimeSeconds();
            }

            return $token;
        }

        if ($config['auth_type'] === ClientServiceProvider::AUTH_TYPE_OAUTH1
            && $config['type'] === ClientServiceProvider::APP_TYPE_PRIVATE
        ) {
            $token = new Oauth1Token([
                'oauth_token' => $config['consumer_key'],
            ]);

            return $token;
        }

        // Config not supported yet.
    }
}
