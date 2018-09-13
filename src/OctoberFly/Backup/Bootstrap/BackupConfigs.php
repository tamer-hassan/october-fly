<?php

namespace OctoberFly\Backup\Bootstrap;

use OctoberFly\Backup\Application;

class BackupConfigs
{

    public function bootstrap(Application $app)
    {
        $app->setBackupedConfig();
    }
}
