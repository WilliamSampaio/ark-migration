<?php

$autoload = function () {
    $files = [
        __DIR__ . '/../vendor/autoload.php'
    ];
    foreach ($files as $file) {
        if (is_file($file)) {
            require_once $file;
            return true;
        }
    }

    return false;
};

if (!$autoload()) {
    die('Composer is required!');
}

use Symfony\Component\Console\Application;
use Williamsampaio\ArkMigration\Commands\Init;

$application = new Application();

$application->add(new Init());

$application->run();
