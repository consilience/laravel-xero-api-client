<?php

namespace Consilience\XeroApi\LaravelClient;

use Illuminate\Support\Facades\Facade;

class ClientServiceFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ClientServiceProvider::CONTAINER_KEY;
    }
}
