<?php

namespace BetasMissionBundle\CommandHelper;

use BetasMissionBundle\Business\FileManagementBusiness;
use BetasMissionBundle\Helper\BetaseriesApiWrapper;
use BetasMissionBundle\Helper\Logger;
use BetasMissionBundle\Helper\TraktTvApiWrapper;
use Exception;
use stdClass;

/**
 * Class MoveCommandHelper
 */
class MoveCommandHelper extends AbstractCommandHelper
{

    /**
     * @var string
     */
    private $from;

    /**
     * @var string
     */
    private $destination;

    /**
     * @var string
     */
    private $defaultDestination;

    /**
     * @var FileManagementBusiness
     */
    private $fileManagementBusiness;

    /**
     * @var BetaseriesApiWrapper
     */
    private $betaseriesApiWrapper;

    /**
     * @var TraktTvApiWrapper
     */
    private $traktTvApiWrapper;

    /**
     * MoveCommandHelper constructor.
     *
     * @param string $from
     * @param string $destination
     * @param string $defaultDestination
     */
    public function __construct(Logger $logger, $from, $destination, $defaultDestination)
    {
        parent::__construct($logger);
        $this->fileManagementBusiness = new FileManagementBusiness($logger);

        $this->betaseriesApiWrapper = new BetaseriesApiWrapper();
        $this->traktTvApiWrapper    = new TraktTvApiWrapper();

        $this->from               = $from;
        $this->destination        = $destination;
        $this->defaultDestination = $defaultDestination;
    }

    /**
     * Move the given episode to its destination
     *
     * @param string $episode         Episode to move
     * @param string $destinationPath Destination path
     *
     * @return bool
     */
    public function moveShow($episode, $destinationPath)
    {
        $from = $this->from.'/'.$episode;

        $this->fileManagementBusiness->copy($from, $destinationPath.'/'.$episode);
        $this->fileManagementBusiness->remove($from);

        return true;
    }

    /**
     * Return the destination path of the given TV Show
     *
     * @param string $showLabel
     *
     * @return string
     */
    public function getTVShowDestinationPath($showLabel)
    {
        if (!is_dir($this->destination.'/'.$showLabel)) {
            mkdir($this->destination.'/'.$showLabel, 0777, true);
        }

        return $this->destination.'/'.$showLabel;
    }

    /**
     * @param stdClass $episodeData
     *
     * @return void
     */
    public function markAsDownloaded($episodeData)
    {
        try {
            $this->betaseriesApiWrapper->markAsDownloaded($episodeData->episode->id);
            $this->logger->log('Marked the episode as downloaded');
        } catch (Exception $e) {
            $this->logger->log('The user does dot watch this show.');
        }

        try {
            $this->traktTvApiWrapper->markAsDownloaded($episodeData->episode->thetvdb_id);
            $this->logger->log('Marked the episode as downloaded');
        } catch (Exception $e) {
            $this->logger->log('The user does dot watch this show.');
        }
    }
}
