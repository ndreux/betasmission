<?php

namespace BetasMissionBundle\CommandHelper;

use stdClass;

/**
 * Class DownloadSubtitleCommandHelper
 */
class DownloadSubtitleCommandHelper extends AbstractCommandHelper
{
    const SUBTITLE_EXTENSION = '.srt';

    /**
     * @param string $from
     */
    public function downloadSubtitles($from)
    {
        $shows = $this->getList($from);
        $this->logger->info(count($shows).' found');

        foreach ($shows as $show) {
            $this->downloadSubtitleForShow($show, $from);
        }
    }

    /**
     * Download the subtitles for the given show directory
     *
     * @param string $show Show directory
     * @param string $from Root directory
     */
    private function downloadSubtitleForShow($show, $from)
    {
        $this->logger->info('Show : '.$show);

        $showPath = $from.'/'.$show;
        $episodes = $this->getList($showPath);

        foreach ($episodes as $episode) {
            $this->downloadSubtitleForEpisode($episode, $showPath);
        }
    }

    /**
     * Download the subtitles for the given episode
     *
     * @param string $episode  Episode file / directory
     * @param string $showPath Show directory (parent directory)
     */
    public function downloadSubtitleForEpisode($episode, $showPath)
    {
        $this->logger->info($episode);

        if (!$this->episodeNeedsSubtitle($episode, $showPath)) {
            return;
        }

        try {
            $episodeData = $this->betaseriesApiWrapper->getEpisodeData($episode);
        } catch (\Exception $e) {
            $this->logger->info('Episode not found on BetaSeries');

            return;
        }

        $subtitles = $this->getSubtitlesByEpisodeId($episodeData->episode->id);
        $subtitle  = $this->getBestSubtitle($subtitles, $episode);

        if ($subtitle === null) {
            $this->logger->info('Subtitles not found on BetaSeries');

            return;
        }

        $this->applySubTitle($showPath.'/'.$episode, $subtitle);
    }

    /**
     * Return true if the given episode needs a subtitle
     *
     * @param string $episode
     * @param string $showPath
     *
     * @return bool
     */
    private function episodeNeedsSubtitle($episode, $showPath)
    {
        $isVOSTFR    = $this->isVOSTFREpisode($showPath.'/'.$episode);
        $hasSubtitle = $this->episodeHasSubtitle($showPath.'/'.$episode);

        if ($isVOSTFR || $hasSubtitle === true) {
            $this->logger->info('Does not need subtitle');

            return false;
        }

        return true;
    }

    /**
     * @param string $episode
     *
     * @return bool|null
     */
    private function episodeHasSubtitle($episode)
    {
        if (is_dir($episode)) {
            $files = $this->fileStreamBusiness->scandir($episode);

            foreach ($files as $file) {
                if ($this->episodeHasSubtitle($episode.'/'.$file)) {
                    return true;
                };
            }

            return false;
        }

        if (!$this->fileStreamBusiness->isVideo($episode)) {
            $this->logger->info('No video file');

            return;
        }

        if (file_exists($this->getSubtitleFileNameFromEpisode($episode))) {
            $this->logger->info('Episode already has a subtitle');

            return true;
        }

        return false;
    }

    /**
     * @param stdClass $subtitles
     * @param string   $episodeName
     *
     * @throws \Exception
     *
     * @return null|stdClass
     */
    private function getBestSubtitle($subtitles, $episodeName)
    {
        if (empty($subtitles->subtitles)) {
            return;
        }

        $teamSubtitle = $this->getBestSubtitleByTeam($subtitles->subtitles, $episodeName);

        if ($teamSubtitle !== null) {
            return $teamSubtitle;
        }

        $bestQualitySubtitle = $this->getBestSubtitleByQuality($subtitles->subtitles);

        if ($bestQualitySubtitle !== null) {
            return $bestQualitySubtitle;
        }

        return;
    }

    /**
     * @param string   $episode
     * @param stdClass $subtitle
     *
     * @return bool
     */
    public function applySubTitle($episode, $subtitle)
    {
        $tempSubtitle = $this->downloadTemporarySubtitle($subtitle->url, $subtitle->file);

        if (is_dir($episode)) {
            $files = $this->fileStreamBusiness->scandir($episode);

            foreach ($files as $file) {
                if (!$this->fileStreamBusiness->isVideo($file)) {
                    continue;
                }

                $this->fileStreamBusiness->copy($tempSubtitle, $this->getSubtitleFileNameFromEpisode($episode.'/'.$file));
                $this->fileStreamBusiness->remove($tempSubtitle);

                return true;
            }

            return false;
        }

        $this->fileStreamBusiness->copy($tempSubtitle, $this->getSubtitleFileNameFromEpisode($episode));
        $this->fileStreamBusiness->remove($tempSubtitle);

        $this->logger->info('Subtitle applied');

        return true;
    }

    /**
     * @param $episode
     *
     * @return bool|int
     */
    public function isVOSTFREpisode($episode)
    {
        $isVOSTFREpisode = strpos($this->fileStreamBusiness->slugify(pathinfo($episode, PATHINFO_FILENAME)), 'vostfr') !== false;
        ($isVOSTFREpisode) ? $this->logger->info('VOSTFR Episode') : null;

        return $isVOSTFREpisode;
    }

    /**
     * @param string $subtitleUrl
     * @param string $subtitleLabel
     *
     * @return string
     */
    private function downloadTemporarySubtitle($subtitleUrl, $subtitleLabel)
    {
        $curlSession = curl_init();
        curl_setopt($curlSession, CURLOPT_URL, $subtitleUrl);
        curl_setopt($curlSession, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($curlSession);
        curl_close($curlSession);

        file_put_contents('/tmp/'.$subtitleLabel, $data);

        return '/tmp/'.$subtitleLabel;
    }

    /**
     * @param string $episode
     *
     * @return string
     */
    private function getSubtitleFileNameFromEpisode($episode)
    {
        $episodePathInfo = pathinfo($episode);

        return $episodePathInfo['dirname'].'/'.$episodePathInfo['filename'].self::SUBTITLE_EXTENSION;
    }

    /**
     * Return the best subtitle based on its team
     *
     * @param stdClass[] $subtitles
     * @param string     $episodeName
     *
     * @return null|stdClass
     */
    private function getBestSubtitleByTeam($subtitles, $episodeName)
    {
        $team = $this->getEpisodeTeam($episodeName);

        if ($team === null) {
            return;
        }

        foreach ($subtitles as $subtitle) {
            // ToDo (ndreux - 2015-08-31) Manage zip
            if ($this->fileStreamBusiness->isZip($subtitle->file)) {
                continue;
            }

            if (strpos($this->fileStreamBusiness->slugify($subtitle->file), $team) !== false) {
                return $subtitle;
            }
        }

        return;
    }

    /**
     * @return array
     */
    private static function getAvailableTeams()
    {
        return array_map(
            'strtolower',
            [
                'KILLERS', 'ASAP', 'LOL', 'FoV', 'YIFY', 'IMMERSE', '0-sec', 'DIMENSION', 'fastsub', 'RIVER', 'tla',
                'BATV', '2HD', 'FGT', 'QCF', 'DEFiNE', 'TASTETV', 'FQM', 'FEVER', '0TV', 'EVOLVE', 'SNEAkY',
            ]
        );
    }

    /**
     * @param string $episodeName
     *
     * @return null|string
     */
    private function getEpisodeTeam($episodeName)
    {
        $episodeInfo         = pathinfo($episodeName);
        $explodedEpisodeName = explode('.', $this->fileStreamBusiness->slugify($episodeInfo['filename']));

        foreach ($explodedEpisodeName as $episodeNamePart) {
            if (in_array($episodeNamePart, self::getAvailableTeams())) {
                return $episodeNamePart;
            }
        }

        return;
    }

    /**
     * Return the best subtitle based on its quality
     *
     * @param stdClass[] $subtitles
     *
     * @return null|stdClass
     */
    private function getBestSubtitleByQuality($subtitles)
    {
        /** @var stdClass|null $bestSubtitle */
        $bestSubtitle = null;
        foreach ($subtitles as $subtitle) {

            // ToDo (ndreux - 2015-08-31) Manage zip
            if ($this->fileStreamBusiness->isZip($subtitle->file)) {
                continue;
            }

            if ($bestSubtitle === null || $bestSubtitle->quality < $subtitle->quality) {
                $bestSubtitle = $subtitle;
            }
        }

        return $bestSubtitle;
    }

    /**
     * Return the subtitles for the episode identified by the given id
     *
     * @param int $id Episode id
     *
     * @throws \Exception
     *
     * @return stdClass
     */
    public function getSubtitlesByEpisodeId($id)
    {
        $subtitles = $this->betaseriesApiWrapper->getSubtitleByEpisodeId($id);
        $this->logger->info(count($subtitles->subtitles).' found');

        return $subtitles;
    }

    /**
     * Return a list of show contained in the given $showPath
     *
     * @param string $showPath
     *
     * @return string[]
     */
    public function getList($showPath)
    {
        return $this->fileStreamBusiness->scandir($showPath);
    }
}
