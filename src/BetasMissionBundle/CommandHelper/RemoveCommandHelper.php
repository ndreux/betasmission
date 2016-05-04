<?php

namespace BetasMissionBundle\CommandHelper;

use BetasMissionBundle\Business\FileManagementBusiness;
use BetasMissionBundle\Helper\BetaseriesApiWrapper;
use BetasMissionBundle\Helper\TraktTvApiWrapper;
use Exception;
use stdClass;
use Symfony\Bridge\Monolog\Logger;

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
     * Remove all the watched episode located in the given directory
     *
     * @param string $from
     */
    public function removeWatched($from)
    {
        $shows = array_diff(scandir($from), ['..', '.']);

        $this->logger->info(count($shows).' found');

        foreach ($shows as $show) {
            $this->logger->info('Show : '.$show);

            if ($this->isWhiteListed($from.'/'.$show)) {
                $this->logger->info('Show white listed');
                continue;
            }

            $episodes = array_diff(scandir($from.'/'.$show), ['..', '.']);

            foreach ($episodes as $i => $episode) {
                $episodeCount = count($episodes);
                $this->logger->info($episode);

                if (is_file($from.'/'.$show.'/'.$episode) && !$this->isVideo($episode)) {
                    $this->logger->info(sprintf('The file %s is not a video file. Continue.', $episode));
                }

                try {
                    $episodeData = $this->getEpisodeFromFileName($episode);
                }
                catch (\Exception $e) {
                    $this->logger->info('Episode not found on BetaSeries');
                    continue;
                }

                $hasBeenSeen = $this->hasEpisodeBeenSeen($episodeData->episode->ids->trakt);
                $this->logger->info('Episode seen : '.($hasBeenSeen ? 'true' : 'false'));

                if ($hasBeenSeen) {

                    $this->removeFromCollection($episodeData->episode->ids->tvdb);
                    $this->remove($from.'/'.$show.'/'.$episode);

                    $episodeCount--;

                    if ($episodeCount === 0) {
                        $this->logger->info('No more show in Show directory. Remove '.$from.'/'.$show);
                        $this->remove($from.'/'.$show);
                    }
                }
            }
        }
    }

    /**
     * Remove the file
     *
     * @param string $toBeRemoved
     */
    private function remove($toBeRemoved)
    {
        $this->fileManagementBusiness->remove($toBeRemoved);
    }

    /**
     * @param int $thetvdbId
     */
    private function removeFromCollection($thetvdbId)
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
    private function isWhiteListed($showPath)
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
    private function getEpisodeFromFileName($fileName)
    {
        $episodeData = $this->betaseriesApiWrapper->getEpisodeData($fileName);

        return $this->traktTvApiWrapper->searchEpisode($episodeData->episode->thetvdb_id);
    }

    /**
     * Check if the episode has been seen on trakt tv
     *
     * @param string $traktTvId
     *
     * @return bool
     */
    private function hasEpisodeBeenSeen($traktTvId)
    {
        return $this->traktTvApiWrapper->hasEpisodeBeenSeen($traktTvId);
    }


    /**
     * @param string $file
     *
     * @return bool
     */
    private function isVideo($file)
    {
        return $this->fileManagementBusiness->isVideo($file);
    }
}
