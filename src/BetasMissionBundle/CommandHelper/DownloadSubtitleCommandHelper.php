<?php

namespace BetasMissionBundle\CommandHelper;

use BetasMissionBundle\Business\FileManagementBusiness;
use BetasMissionBundle\Helper\BetaseriesApiWrapper;
use stdClass;
use Symfony\Bridge\Monolog\Logger;

/**
 * Class DownloadSubtitleCommandHelper
 */
class DownloadSubtitleCommandHelper
{
    const SUBTITLE_EXTENSION = '.srt';

    /**
     * @var FileManagementBusiness
     */
    private $fileManagementBusiness;

    /**
     * @var BetaseriesApiWrapper
     */
    private $apiWrapper;

    /**
     * DownloadSubtitleCommandHelper constructor.
     *
     * @param Logger $logger
     */
    public function __construct(Logger $logger)
    {
        $this->logger                 = $logger;
        $this->fileManagementBusiness = new FileManagementBusiness($logger);
        $this->apiWrapper             = new BetaseriesApiWrapper();
    }

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

        foreach ($episodes as $i => $episode) {
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
            $episodeData = $this->apiWrapper->getEpisodeData($episode);
        }
        catch (\Exception $e) {
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
            $files = $this->fileManagementBusiness->scandir($episode);

            foreach ($files as $file) {
                if ($this->episodeHasSubtitle($episode.'/'.$file)) {
                    return true;
                };
            }

            return false;
        } else {
            if (!$this->fileManagementBusiness->isVideo($episode)) {
                $this->logger->info('No video file');

                return null;
            }

            if (file_exists($this->getSubtitleFileNameFromEpisode($episode))) {
                $this->logger->info('Episode already has a subtitle');

                return true;
            }

            return false;
        }
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
            return null;
        }

        $teamSubtitle = $this->getBestSubtitleByTeam($subtitles->subtitles, $episodeName);

        if ($teamSubtitle !== null) {
            return $teamSubtitle;
        }

        $bestQualitySubtitle = $this->getBestSubtitleByQuality($subtitles->subtitles);

        if ($bestQualitySubtitle !== null) {
            return $bestQualitySubtitle;
        }

        return null;
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
            $files = $this->fileManagementBusiness->scandir($episode);

            foreach ($files as $file) {
                if (!$this->fileManagementBusiness->isVideo($file)) {
                    continue;
                }

                copy($tempSubtitle, $this->getSubtitleFileNameFromEpisode($episode.'/'.$file));
                unlink($tempSubtitle);

                return true;
            }
        } else {
            copy($tempSubtitle, $this->getSubtitleFileNameFromEpisode($episode));
            unlink($tempSubtitle);
        }

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
        if (strpos($this->fileManagementBusiness->slugify(pathinfo($episode, PATHINFO_FILENAME)), 'vostfr') !== false) {
            $this->logger->info('VOSTFR Episode');

            return true;
        }

        return false;
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
            return null;
        }

        foreach ($subtitles as $subtitle) {
            // ToDo (ndreux - 2015-08-31) Manage zip
            if ($this->fileManagementBusiness->isZip($subtitle->file)) {
                continue;
            }

            if (strpos($this->fileManagementBusiness->slugify($subtitle->file), $team) !== false) {
                return $subtitle;
            }
        }

        return null;
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
        $explodedEpisodeName = explode('.', $this->fileManagementBusiness->slugify($episodeInfo['filename']));

        foreach ($explodedEpisodeName as $episodeNamePart) {
            if (in_array($episodeNamePart, self::getAvailableTeams())) {
                return $episodeNamePart;
            }
        }

        return null;
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
            if ($this->fileManagementBusiness->isZip($subtitle->file)) {
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
     * @return stdClass
     * @throws \Exception
     */
    public function getSubtitlesByEpisodeId($id)
    {
        $subtitles = $this->apiWrapper->getSubtitleByEpisodeId($id);
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
        return $this->fileManagementBusiness->scandir($showPath);
    }
}
