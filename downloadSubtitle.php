<?php

require 'Helper/Autoloader.php';

Helper\Autoloader::register();

$downloadSubtitleCommand = new Command\DownloadSubtitleCommand();
$downloadSubtitleCommand->execute();