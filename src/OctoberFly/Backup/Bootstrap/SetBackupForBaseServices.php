<?php

namespace OctoberFly\Backup\Bootstrap;

use OctoberFly\Backup\Application;

class SetBackupForBaseServices
{

    public function bootstrap(Application $app)
    {

        $appConfig = $app->make('config');

        $needBackup = [];

        foreach ($appConfig['laravelfly.BaseServices'] ?: [] as $name => $config) {
            if ($config) {
                $needBackup[$name] = $config;
            }
        }

        $app->addNeedBackupServiceAttributes($needBackup);


    }
}
