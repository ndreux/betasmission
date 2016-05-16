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
        $archivedShows = $this->getArchivedShows();
        $shows         = $this->fileStreamBusiness->scandir($from);

        $this->logger->info(count($shows).' found');

        foreach ($shows as $show) {
            $this->logger->info('Show : '.$show);

            $showPath         = $from.'/'.$show;
            $processingShowId = null;

            if ($this->isWhiteListed($showPath)) {
                continue;
            }

            foreach ($this->fileStreamBusiness->scandir($showPath) as $episode) {
                $episodePath = $showPath.'/'.$episode;

                $this->logger->info($episode);

                if ((!is_dir($episodePath) && !$this->fileStreamBusiness->isVideo($episode)) || $this->isArchivedShow($archivedShows, $processingShowId)) {
                    continue;
                }

                try {
                    $episodeData      = $this->getEpisodeFromFileName($episode);
                    $processingShowId = $episodeData->show->ids->trakt;
                } catch (\Exception $e) {
                    $this->logger->info('Episode not found on BetaSeries');
                    continue;
                }

                if ($this->hasEpisodeBeenSeen($episodeData->episode->ids->trakt)) {
                    $this->removeFromCollection($episodeData->episode->ids->tvdb);
                    $this->fileStreamBusiness->remove($episodePath);

                    if (count($this->fileStreamBusiness->scandir($showPath)) === 0) {
                        $this->logger->info('No more show in Show directory. Remove '.$showPath);
                        $this->fileStreamBusiness->remove($showPath);
                    }
                }
            }
        }
    }

    /**
     * @param int $thetvdbId
     */
    private function removeFromCollection($thetvdbId)
    {
        try {
            $this->traktTvApiWrapper->removeFromCollection($thetvdbId);
        } catch (Exception $e) {
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
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            $hasBeenSeen = false;
        }

        $this->logger->info('Episode seen : '.($hasBeenSeen ? 'true' : 'false'));

        return $hasBeenSeen;
    }

    /**
     * @param array $archivedShows
     * @param int   $processingShowId
     *
     * @return bool
     */
    private function isArchivedShow($archivedShows, $processingShowId)
    {
        $archivedShow = in_array($processingShowId, array_keys($archivedShows));
        $archivedShow ? $this->logger->info('Show archived') : null;

        return $archivedShow;
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
