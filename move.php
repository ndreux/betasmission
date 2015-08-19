<?php

require 'Helper/Autoloader.php';

Helper\Autoloader::register();

$moveCommand = new Command\MoveCommand();
$moveCommand->execute();
