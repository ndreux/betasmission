<?php

namespace BetasMissionBundle\CommandHelper;

use Exception;
use stdClass;

/**
 * Class RemoveCommandHelper
 */
class RemoveCommandHelper extends AbstractCommandHelper
{
    /**
     * Remove all the watched episode located in the given directory
     *
     * @param string $from
     */
    public function removeWatched($from)
    {
        $shows = $this->fileStreamBusiness->scandir($from);

        $this->logger->info(count($shows).' found');

        foreach ($shows as $show) {
            $this->logger->info('Show : '.$show);

            if ($this->isWhiteListed($from.'/'.$show)) {
                continue;
            }

            $showPath = $from.'/'.$show;
            $episodes = $this->fileStreamBusiness->scandir($showPath);

            foreach ($episodes as $episode) {

                $episodePath  = $showPath.'/'.$episode;
                $episodeCount = count($episodes);

                $this->logger->info($episode);

                if (!is_dir($episodePath) && !$this->fileStreamBusiness->isVideo($episode)) {
                    continue;
                }

                try {
                    $episodeData = $this->getEpisodeFromFileName($episode);
                }
                catch (\Exception $e) {
                    $this->logger->info('Episode not found on BetaSeries');
                    continue;
                }

                $hasBeenSeen = $this->hasEpisodeBeenSeen($episodeData->episode->ids->trakt);

                if ($hasBeenSeen) {
                    $this->removeFromCollection($episodeData->episode->ids->tvdb);
                    $this->remove($episodePath);

                    --$episodeCount;

                    if ($episodeCount === 0) {
                        $this->logger->info('No more show in Show directory. Remove '.$showPath);
                        $this->remove($showPath);
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
        $this->fileStreamBusiness->remove($toBeRemoved);
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
        $fileExists = file_exists($showPath.'/.do_not_remove.lock');
        ($fileExists) ? $this->logger->info('Show white listed') : null;

        return $fileExists;
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
            $hasBeenSeen = $this->traktTvApiWrapper->hasEpisodeBeenSeen($traktTvId);
        }
        catch (Exception $e) {
            $this->logger->error($e->getMessage());
            $hasBeenSeen = false;
        }

        $this->logger->info('Episode seen : '.($hasBeenSeen ? 'true' : 'false'));

        return $hasBeenSeen;
    }
}
