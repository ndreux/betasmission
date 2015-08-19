<?php

require 'Helper/Autoloader.php';

Helper\Autoloader::register();

$removeCommand = new Command\RemoveWatchedCommand();
$removeCommand->execute();
