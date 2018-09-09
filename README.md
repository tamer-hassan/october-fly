# OctoberFly

OctoberFly aims to make OctoberCMS faster by using Swoole extension.

## Version Compatibility

- OctoberCMS (Laravel 5.5.* under the hood)
- Swoole >4.0

Based on: [LaravelFly](https://github.com/scil/LaravelFly)

## PHP Setup Requirements

1. Install swoole extension
```pecl install swoole```

Make sure swoole is included in php.ini file.
```extension=swoole.so```

Also Suggested:
```pecl install inotify```

2. `composer require "tamerhassan/october-fly":"0.0.3"`

## Quick Start

1. Add the following line to your 'providers' array in `config/app.php`
```
'LaravelFly\Providers\ServiceProvider',
```

2. Publish server config
```
php artisan vendor:publish --tag=fly-server
```

3. Publish app config
```
php artisan vendor:publish --tag=fly-app
```

4. Finally you can start the server:
```
php vendor/tamerhassan/october-fly/bin/fly start
```
