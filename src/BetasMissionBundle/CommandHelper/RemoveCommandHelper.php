<?php

namespace BetasMissionBundle\CommandHelper;

use BetasMissionBundle\ApiWrapper\BetaseriesApiWrapper;
use BetasMissionBundle\ApiWrapper\TraktTvApiWrapper;
use BetasMissionBundle\Business\FileManagementBusiness;
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
     * @var BetaseriesApiWrapper
     */
    private $betaseriesApiWrapper;

    /**
     * @var TraktTvApiWrapper
     */
    private $traktTvApiWrapper;

    /**
     * RemoveCommandHelper constructor.
     *
     * @param Logger                 $logger
     * @param FileManagementBusiness $fileManagementBusiness
     * @param BetaseriesApiWrapper   $betaseriesApiWrapper
     * @param TraktTvApiWrapper      $traktTvApiWrapper
     */
    public function __construct(Logger $logger, FileManagementBusiness $fileManagementBusiness, BetaseriesApiWrapper $betaseriesApiWrapper, TraktTvApiWrapper $traktTvApiWrapper)
    {
        $this->logger                 = $logger;
        $this->fileManagementBusiness = $fileManagementBusiness;
        $this->betaseriesApiWrapper   = $betaseriesApiWrapper;
        $this->traktTvApiWrapper      = $traktTvApiWrapper;
    }

    /**
     * Remove all the watched episode located in the given directory
     *
     * @param string $from
     */
    public function removeWatched($from)
    {
        $shows = $this->fileManagementBusiness->scandir($from);

        $this->logger->info(count($shows).' found');
        $archivedShows = $this->getArchivedShows();

        $processingShowId = null;

        foreach ($shows as $show) {
            $this->logger->info('Show : '.$show);

            if ($this->isWhiteListed($from.'/'.$show)) {
                $this->logger->info('Show white listed');
                continue;
            }

            if ($this->isArchivedShow($archivedShows, $processingShowId)) {
                $this->logger->info('Show archived');
                continue;
            }

            $episodes = $this->fileManagementBusiness->scandir($from.'/'.$show);

            foreach ($episodes as $i => $episode) {
                $episodeCount = count($episodes);
                $this->logger->info($episode);

                if (is_file($from.'/'.$show.'/'.$episode) && !$this->isVideo($episode)) {
                    $this->logger->info(sprintf('The file %s is not a video file. Continue.', $episode));
                    continue;
                }

                try {
                    $episodeData      = $this->getEpisodeFromFileName($episode);
                    $processingShowId = $episodeData->show->ids->trakt;
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

                    --$episodeCount;

                    if ($episodeCount === 0) {
                        $this->logger->info('No more show in Show directory. Remove '.$from.'/'.$show);
                        $this->remove($from.'/'.$show);
                    }
                }

                if ($i === count($episode)) {
                    $processingShowId = null;
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
        try {
            $this->traktTvApiWrapper->removeFromCollection($thetvdbId);
        }
        catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
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
     * @throws Exception
     *
     * @return stdClass
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
        try {
            return $this->traktTvApiWrapper->hasEpisodeBeenSeen($traktTvId);
        }
        catch (Exception $e) {
            $this->logger->error($e->getMessage());

            return false;
        }
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

    /**
     * @param array $archivedShows
     * @param int   $processingShowId
     *
     * @return bool
     */
    private function isArchivedShow($archivedShows, $processingShowId)
    {
        return in_array($processingShowId, array_keys($archivedShows));
    }


    /**
     * @return array
     */
    private function getArchivedShows()
    {
        $archivedShows = $this->traktTvApiWrapper->getArchivedShows();

        $organizedArchivedShows = [];
        foreach ($archivedShows as $archivedShow) {
            $organizedArchivedShows[$archivedShow->show->ids->trakt] = $archivedShow->show->title;
        }

        return $organizedArchivedShows;
    }
}
