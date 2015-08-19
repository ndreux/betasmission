<?php

namespace Command;

use Helper\BetaseriesApiWrapper;
use Helper\Locker;
use Helper\Logger;

/**
 * Class AbstractCommand.
 */
class AbstractCommand
{

    const CONTEXT = null;

    /** @var  BetaseriesApiWrapper */
    protected $apiWrapper;

    /** @var Locker */
    protected $locker;

    /** @var  Logger */
    protected $logger;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->apiWrapper = new BetaseriesApiWrapper();
        $this->locker     = new Locker(static::CONTEXT);
        $this->logger     = new Logger(static::CONTEXT);
    }

    /**
     * @param string $src
     *
     * @return bool
     */
    protected function recurseRmdir($src)
    {
        $dir = opendir($src);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src.'/'.$file)) {
                    $this->recurseRmdir($src.'/'.$file);
                } else {
                    $this->logger->log("Remove : ".$src."/".$file);
                    unlink($src.'/'.$file);
                }
            }
        }
        $this->logger->log("Remove : ".$src);
        rmdir($src);
        closedir($dir);

        return true;
    }
}
