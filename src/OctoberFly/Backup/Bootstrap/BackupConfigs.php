<?php

namespace OctoberFly\Backup\Bootstrap;

use OctoberFly\Backup\Application;

class BackupConfigs
{

    public function bootstrap(Application $app)
    {
        if (empty(LARAVELFLY_SERVICES['config']))
            $app->setBackupedConfig();

    }
}
