<?php

namespace OctoberFly\Backup\Bootstrap;

use OctoberFly\Backup\Application;

class BackupAttributes
{

    public function bootstrap(Application $app)
    {

        $app->backUpOnWorker();

    }
}
