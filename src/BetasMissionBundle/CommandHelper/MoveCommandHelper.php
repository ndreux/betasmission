<?php

namespace BetasMissionBundle\CommandHelper;

use BetasMissionBundle\Business\FileManagementBusiness;
use BetasMissionBundle\Helper\BetaseriesApiWrapper;
use BetasMissionBundle\Helper\TraktTvApiWrapper;
use Exception;
use stdClass;
use Symfony\Bridge\Monolog\Logger;

/**
 * Class MoveCommandHelper
 */
class MoveCommandHelper
{
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
     */
    public function __construct(Logger $logger)
    {
        $this->logger                 = $logger;
        $this->fileManagementBusiness = new FileManagementBusiness($logger);

        $this->betaseriesApiWrapper = new BetaseriesApiWrapper();
        $this->traktTvApiWrapper    = new TraktTvApiWrapper();
    }

    public function organize($from, $destination, $defaultDestination)
    {
        $episodes = array_diff(scandir($from), ['..', '.']);

        foreach ($episodes as $episode) {

            $this->logger->info('File : '.$episode);

            try {
                $episodeData     = $this->betaseriesApiWrapper->getEpisodeData($episode);
                $destinationPath = $this->getTVShowDestinationPath($destination, $defaultDestination, $episodeData);
            }
            catch (\Exception $e) {
                $this->logger->info('The episode has not been found.');
                $destinationPath = $defaultDestination;
            }

            if ($this->moveShow($from, $episode, $destinationPath) && isset($episodeData)) {
                $this->markAsDownloaded($episodeData);
            }
        }
    }

    /**
     * Move the given episode to its destination
     *
     * @param string $episode         Episode to move
     * @param string $destinationPath Destination path
     *
     * @return bool
     */
    private function moveShow($from, $episode, $destinationPath)
    {
        $from .= '/'.$episode;

        $this->fileManagementBusiness->copy($from, $destinationPath.'/'.$episode);
        $this->fileManagementBusiness->remove($from);

        return true;
    }


    /**
     * Return the destination path of the given TV Show
     *
     * @param string   $destination
     * @param string   $defaultDestination
     * @param stdClass $episodeData
     *
     * @return string
     */
    private function getTVShowDestinationPath($destination, $defaultDestination, $episodeData)
    {
        if (empty($episodeData->episode->show->title)) {
            return $defaultDestination;
        }

        $showLabel = $episodeData->episode->show->title;
        if (!is_dir($destination.'/'.$showLabel)) {
            mkdir($destination.'/'.$showLabel, 0777, true);
        }

        return $destination.'/'.$showLabel;
    }

    /**
     * @param stdClass $episodeData
     *
     * @return void
     */
    private function markAsDownloaded($episodeData)
    {
        try {
            $this->betaseriesApiWrapper->markAsDownloaded($episodeData->episode->id);
            $this->logger->info('Marked the episode as downloaded');
        }
        catch (Exception $e) {
            $this->logger->info('The user does dot watch this show.');
        }

        try {
            $this->traktTvApiWrapper->markAsDownloaded($episodeData->episode->thetvdb_id);
            $this->logger->info('Marked the episode as downloaded');
        }
        catch (Exception $e) {
            $this->logger->info('The user does dot watch this show.');
        }
    }
}
