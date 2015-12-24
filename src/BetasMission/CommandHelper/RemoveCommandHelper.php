<?php

namespace BetasMission\CommandHelper;

use BetasMission\Business\FileManagementBusiness;
use BetasMission\Helper\Logger;
use BetasMission\Helper\TraktTvApiWrapper;

/**
 * Class RemoveCommandHelper
 */
class RemoveCommandHelper
{

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var FileManagementBusiness
     */
    private $fileManagementBusiness;

    /**
     * @var TraktTvApiWrapper
     */
    private $traktTvApiWrapper;

    /**
     * RemoveCommandHelper constructor.
     *
     * @param Logger $logger
     */
    public function __construct(Logger $logger)
    {
        $this->logger                 = $logger;
        $this->fileManagementBusiness = new FileManagementBusiness($this->logger);
        $this->traktTvApiWrapper      = new TraktTvApiWrapper();

    }

    /**
     * @param string $toBeRemoved
     */
    public function remove($toBeRemoved)
    {
        $this->fileManagementBusiness->remove($toBeRemoved);
    }

    /**
     * @param int $thetvdbId
     */
    public function removeFromCollection($thetvdbId)
    {
        $this->traktTvApiWrapper->removeFromCollection($thetvdbId);
    }
}
