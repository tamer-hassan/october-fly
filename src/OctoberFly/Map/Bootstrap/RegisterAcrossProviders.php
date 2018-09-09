<?php

namespace OctoberFly\Map\Bootstrap;

use OctoberFly\Map\Application;
class RegisterAcrossProviders
{
    public function bootstrap(Application $app)
    {
        $app->registerAcrossProviders();
    }
}
