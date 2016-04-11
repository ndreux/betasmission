<?php

namespace BetasMissionBundle\CommandHelper;

use BetasMissionBundle\Business\FileManagementBusiness;
use BetasMissionBundle\Helper\BetaseriesApiWrapper;
use stdClass;
use Symfony\Bridge\Monolog\Logger;

/**
 * Class DownloadSubtitleCommandHelper
 */
class DownloadSubtitleCommandHelper extends AbstractCommandHelper
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
        $this->logger = $logger;
        $this->fileManagementBusiness = new FileManagementBusiness($logger);
        $this->apiWrapper = new BetaseriesApiWrapper();
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
     * @param string $episode
     *
     * @return bool
     */
    public function episodeHasSubtitle($episode)
    {
        if (is_dir($episode)) {
            $files = array_diff(scandir($episode), ['..', '.']);

            foreach ($files as $file) {
                if ($this->episodeHasSubtitle($episode . '/' . $file)) {
                    return true;
                };
            }

            return false;
        } else {
            if (!$this->fileManagementBusiness->isVideo($episode)) {
                return;
            }

            return file_exists($this->getSubtitleFileNameFromEpisode($episode));
        }
    }

    /**
     * @param stdClass
     *
     * @throws \Exception
     *
     * @return null|stdClass
     */
    public function getBestSubtitle($subtitles, $episodeName)
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
            $files = array_diff(scandir($episode), ['..', '.']);

            foreach ($files as $file) {
                if (!$this->fileManagementBusiness->isVideo($file)) {
                    continue;
                }

                copy($tempSubtitle, $this->getSubtitleFileNameFromEpisode($episode . '/' . $file));
                unlink($tempSubtitle);

                return true;
            }
        } else {
            copy($tempSubtitle, $this->getSubtitleFileNameFromEpisode($episode));
            unlink($tempSubtitle);
        }

        return true;
    }

    /**
     * @param $episode
     *
     * @return bool|int
     */
    public function isVOSTFREpisode($episode)
    {
        $episodeInfo = pathinfo($episode);

        return strpos($this->slugify($episodeInfo['filename']), 'vostfr') !== false;
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

        file_put_contents('/tmp/' . $subtitleLabel, $data);

        return '/tmp/' . $subtitleLabel;
    }

    /**
     * @param string $file
     *
     * @return bool
     */
    private function isZip($file)
    {
        $filePathInfo = pathinfo($file);

        return in_array($filePathInfo['extension'], ['zip']);
    }

    /**
     * @param string $episode
     *
     * @return string
     */
    private function getSubtitleFileNameFromEpisode($episode)
    {
        $episodePathInfo = pathinfo($episode);

        return $episodePathInfo['dirname'] . '/' . $episodePathInfo['filename'] . self::SUBTITLE_EXTENSION;
    }

    /**
     * @param $text
     *
     * @return mixed|string
     */
    private function slugify($text)
    {
        $text = preg_replace('~[^\\pL\d]+~u', '-', $text);
        $text = trim($text, '-');
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = strtolower($text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = str_replace('-', '.', $text);

        return (empty($text)) ? null : $text;
    }

    /**
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
            if ($this->isZip($subtitle->file)) {
                continue;
            }

            if (strpos($this->slugify($subtitle->file), $team) !== false) {
                return $subtitle;
            }
        }

        return;
    }

    /**
     * @param string $episodeName
     *
     * @return null|string
     */
    private function getEpisodeTeam($episodeName)
    {
        $episodeInfo = pathinfo($episodeName);
        $explodedEpisodeName = explode('.', $this->slugify($episodeInfo['filename']));

        foreach ($explodedEpisodeName as $episodeNamePart) {
            if (in_array($episodeNamePart, self::getAvailableTeams())) {
                return $episodeNamePart;
            }
        }

        return;
    }

    /**
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
            if ($this->isZip($subtitle->file)) {
                continue;
            }

            if ($bestSubtitle === null || $bestSubtitle->quality < $subtitle->quality) {
                $bestSubtitle = $subtitle;
            }
        }

        return $bestSubtitle;
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

    public function getEpisodeData($episode)
    {
        return $this->apiWrapper->getEpisodeData($episode);
    }

    public function getSubtitleByEpisodeId($id)
    {
        return $this->apiWrapper->getSubtitleByEpisodeId($id);
    }
}
