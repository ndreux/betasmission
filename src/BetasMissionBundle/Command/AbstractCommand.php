<?php

namespace BetasMissionBundle\Command;

use BetasMissionBundle\Helper\BetaseriesApiWrapper;
use BetasMissionBundle\Helper\Locker;
use BetasMissionBundle\Helper\Logger;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

/**
 * Class AbstractCommand.
 */
abstract class AbstractCommand extends ContainerAwareCommand
{
    const CONTEXT = null;

    /**
     * @var BetaseriesApiWrapper
     */
    protected $apiWrapper;

    /**
     * @var Locker
     */
    protected $locker;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        
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
}
