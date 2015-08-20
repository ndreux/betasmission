<?php

namespace BetasMission\Command;

use BetasMission\Helper\BetaseriesApiWrapper;
use BetasMission\Helper\Locker;
use BetasMission\Helper\Logger;

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
     * @return bool
     */
    public function preExecute()
    {
        if ($this->locker->isLocked()) {
            $this->logger->log('The script is locked.');

            return false;
        }

        $this->logger->log('Lock');
        $this->locker->lock();

        return true;
    }

    /**
     * @return bool
     */
    public function postExecute()
    {
        $this->logger->log('Unlock');

        return $this->locker->unlock();
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
                    $this->logger->log('Remove : '.$src.'/'.$file);
                    unlink($src.'/'.$file);
                }
            }
        }
        $this->logger->log('Remove : '.$src);
        rmdir($src);
        closedir($dir);

        return true;
    }
}
