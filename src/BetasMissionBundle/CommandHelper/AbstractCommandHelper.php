<?php

namespace BetasMissionBundle\CommandHelper;

use BetasMissionBundle\ApiWrapper\BetaseriesApiWrapper;
use BetasMissionBundle\ApiWrapper\TraktTvApiWrapper;
use BetasMissionBundle\Business\FileManagementBusiness;
use Symfony\Bridge\Monolog\Logger;

/**
 * Class AbstractCommandHelper
 */
abstract class AbstractCommandHelper
{
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var null|FileManagementBusiness
     */
    protected $fileStreamBusiness;

    /**
     * @var null|BetaseriesApiWrapper
     */
    protected $betaseriesApiWrapper;

    /**
     * @var null|TraktTvApiWrapper
     */
    protected $traktTvApiWrapper;

    /**
     * RemoveCommandHelper constructor.
     *
     * @param Logger                      $logger
     * @param null|FileManagementBusiness $fileStreamBusiness
     * @param null|BetaseriesApiWrapper   $betaseriesApiWrapper
     * @param null|TraktTvApiWrapper      $traktTvApiWrapper
     */
    public function __construct(
        Logger $logger,
        FileManagementBusiness $fileStreamBusiness = null,
        BetaseriesApiWrapper $betaseriesApiWrapper = null,
        TraktTvApiWrapper $traktTvApiWrapper = null
    ) {
        $this->logger               = $logger;
        $this->fileStreamBusiness   = $fileStreamBusiness;
        $this->betaseriesApiWrapper = $betaseriesApiWrapper;
        $this->traktTvApiWrapper    = $traktTvApiWrapper;
    }
}
