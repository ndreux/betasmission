<?php

namespace BetasMission\CommandHelper;

use stdClass;

/**
 * Class DownloadSubtitleCommandHelper
 */
class DownloadSubtitleCommandHelper
{
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
                if ($this->episodeHasSubtitle($episode.'/'.$file)) {
                    return true;
                };
            }

            return false;
        } else {
            if (!$this->isFileAVideo($episode)) {
                return false;
            }

            return file_exists($this->getFileWithoutExtension($episode, true).'.srt');
        }
    }

    /**
     * @param stdClass
     *
     * @throws \Exception
     *
     * @return null|stdClass
     */
    public function getBestSubtitle($subtitles)
    {
        if ($subtitles->subtitles === null) {
            return;
        }

        /** @var stdClass|null $bestSubtitle */
        $bestSubtitle = null;

        foreach ($subtitles->subtitles as $subtitle) {
            $filePathInfo = pathinfo($subtitle->file);

            if ($filePathInfo['extension'] == 'zip') {
                continue;
            }

            if ($bestSubtitle === null || $bestSubtitle->quality < $subtitle->quality) {
                $bestSubtitle = $subtitle;
            }
        }

        return $bestSubtitle;
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
                if (!$this->isFileAVideo($file)) {
                    continue;
                }

                copy($tempSubtitle, $this->getFileWithoutExtension($episode.'/'.$file).'.srt');
                unlink($tempSubtitle);

                return true;
            }
        } else {
            copy($tempSubtitle, $this->getFileWithoutExtension($episode).'.srt');
            unlink($tempSubtitle);
        }

        return true;
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
     * @param string $file
     *
     * @return bool
     */
    private function isFileAVideo($file)
    {
        $filePathInfo = pathinfo($file);

        return in_array($filePathInfo['extension'], ['mp4', 'mkv', 'avi']);
    }

    /**
     * @param string $file
     *
     * @return string
     */
    private function getFileWithoutExtension($file)
    {
        $episodePathInfo = pathinfo($file);

        return $episodePathInfo['dirname'].'/'.$episodePathInfo['filename'];
    }
}
