<?php

namespace LaravelFly\FpmLike;

class Application  extends \October\Rain\Foundation\Application
{
    use \LaravelFly\ApplicationTrait\InConsole;
    use \LaravelFly\ApplicationTrait\Server;
}
