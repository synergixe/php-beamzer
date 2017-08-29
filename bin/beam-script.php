#!/usr/bin/env php

<?php
/*
 * @file 
 *
 * @copyright Copyright (c) 2018 https://twitter.com/isocroft
 *
 * @created /01/2018
 *
 * @license http://github.com/isocroft/baeamzer/blob/master/LICENSE
 */

$included = false;
$files = array(
    __DIR__ . '/../../../autoload.php',
    __DIR__ . '/../../vendor/autoload.php',
    __DIR__ . '/../vendor/autoload.php'
);

while(!$included){

    $file = current($files);
    if(file_exists($file)){
        $included = include_once $file;
    }
    next($files);
}

if (! $included) {

    fwrite(STDOUT, 'You must set up the project dependencies, run the following commands:' . PHP_EOL
         . 'curl -sS https://getcomposer.org/installer | php' . PHP_EOL
         . 'php composer.phar install' . PHP_EOL);

    exit(1);
}

use Illuminate\Command\LaravelNotificationOptionsCommand;
use Illuminate\Command\LaravelNotificationLoadCommand;

use Symfony\Component\Console\Application;

$name    = 'Beamzer Notification Loader Script';
$version = '0.0.1';
$console = new Application($name, $version);

/* detect the Laravel environment if it is in place */

if(defined('LARAVEL_START') 
    or class_exists('Illuminate\Foundation\Application')){

    $console->addCommands(array(
        new LaravelNotificationOptionsCommand(),
        new LaravelNotificationLoadCommand()
    ));
}else{
    
    ;
}

$console->run();

?>