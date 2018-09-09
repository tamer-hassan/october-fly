<?php

namespace OctoberFly\Map\Bootstrap;

use OctoberFly\Map\Application;
use Illuminate\Support\Facades\Facade;

class CleanOnWorker
{
    public function bootstrap(Application $app)
    {
        $app->resetServiceProviders();

        Facade::clearResolvedInstance('request');

        //'url' has made? when? \Illuminate\Routing\RoutingServiceProvider
        Facade::clearResolvedInstance('url');
    }
}
