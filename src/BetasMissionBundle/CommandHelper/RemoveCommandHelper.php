<?php

namespace BetasMissionBundle\CommandHelper;

use BetasMissionBundle\Business\FileManagementBusiness;
use BetasMissionBundle\Helper\BetaseriesApiWrapper;
use BetasMissionBundle\Helper\Logger;
use BetasMissionBundle\Helper\TraktTvApiWrapper;
use Exception;
use stdClass;

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
     * @var BetaseriesApiWrapper
     */
    private $betaseriesApiWrapper;

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
        $this->betaseriesApiWrapper   = new BetaseriesApiWrapper();

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

    /**
     * Return true if the show is while listed and must not be deleted
     *
     * @param string $showPath
     *
     * @return bool
     */
    public function isWhiteListed($showPath)
    {
        return file_exists($showPath.'/.do_not_remove.lock');
    }

    /**
     * Return the episode data from TraktTv matching the given file name
     *
     * @param string $fileName
     *
     * @return stdClass
     * @throws Exception
     */
    public function getEpisodeFromFileName($fileName)
    {
        $episodeData = $this->betaseriesApiWrapper->getEpisodeData($fileName);

        return $this->traktTvApiWrapper->searchEpisode($episodeData->episode->thetvdb_id);
    }

    public function hasEpisodeBeenSeen($traktTvId)
    {
        return $this->traktTvApiWrapper->hasEpisodeBeenSeen($traktTvId);
    }
    
    
    /**
     * @param string $file
     *
     * @return bool
     */
    public function isVideo($file)
    {
        return $this->fileManagementBusiness->isVideo($file);
    }
}
