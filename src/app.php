<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Console\Application;
use Symfony\Component\Finder\Finder;

$app = new Application();

/**
 * This could be done with scandir() instead but this is much cleaner to read.
 */
$finder = new Finder();
$finder->files()->in(__DIR__ . '/Commands');

foreach ($finder as $cmd) {
    var_dump($cmd->getRealPath(), $cmd->getFilenameWithoutExtension());
    require_once $cmd->getRealPath();
    $command = "App\Commands\\" . $cmd->getFilenameWithoutExtension();

    $app->add(new $command());
}

$app->run();
?>