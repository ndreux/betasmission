<?php

namespace Command;

use Helper\Context;
use stdClass;

/**
 * Class DownloadSubtitleCommand
 */
class DownloadSubtitleCommand extends AbstractCommand implements CommandInterface
{
    const CONTEXT = Context::CONTEXT_DOWNLOAD_SUBTITLE;
    const FROM    = '/mnt/smb/Labox/Series/Actives/';

    public function execute()
    {
        if ($this->locker->isLocked()) {
            $this->logger->log('The script is locked.');

            return;
        }

        $this->logger->log('Lock');
        $this->locker->lock();

        $shows = array_diff(scandir(self::FROM), ['..', '.']);

        $this->logger->log(count($shows)." found");

        foreach ($shows as $show) {
            $this->logger->log('Show : '.$show);
            $episodes = array_diff(scandir(self::FROM.'/'.$show), ['..', '.']);

            foreach ($episodes as $i => $episode) {
                $this->logger->log($episode);

                if ($i % 30 == 0) {
                    $this->logger->log("Wait 20s");
                    sleep(20);
                }

                if ($this->episodeHasSubtitle(self::FROM.$show.'/'.$episode)) {
                    $this->logger->log('Episode already has a subtitle');
                    continue;
                }

                try {
                    $episodeData = $this->apiWrapper->getEpisodeData($episode);
                } catch (\Exception $e) {
                    $this->logger->log("Episode not found on BetaSeries");
                    continue;
                }

                $subtitle = $this->getBestSubtitle($episodeData->episode->id);

                if ($subtitle === null) {
                    $this->logger->log("Subtitles not found on BetaSeries");
                    continue;
                }

                $this->applySubTitle(self::FROM.$show.'/'.$episode, $subtitle);
            }
        }

        $this->logger->log('Unlock');
        $this->locker->unlock();

    }

    /**
     * @param int $episodeId
     *
     * @return null|stdClass
     * @throws \Exception
     */
    private function getBestSubtitle($episodeId)
    {
        $subtitles = $this->apiWrapper->getSubtitleByEpisodeId($episodeId);

        $this->logger->log(count($subtitles).' found');

        /** @var StdClass|null $bestSubtitle */
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
     * @param StdClass $subtitle
     *
     * @return bool
     */
    private function applySubTitle($episode, $subtitle)
    {

        $subtitleFilePath = $this->getSubtitleFilePath($subtitle->url, $subtitle->file);

        if (is_dir($episode)) {

            $files = array_diff(scandir($episode), ['..', '.']);

            foreach ($files as $file) {
                $filePathInfo = pathinfo($file);

                if (!in_array($filePathInfo['extension'], ['mp4', 'mkv', 'avi'])) {
                    continue;
                }

                $filePathInfo          = pathinfo($episode.'/'.$file);
                $destination           = $filePathInfo['dirname'];
                $episodeFileWithoutExt = $filePathInfo['filename'];

                copy($subtitleFilePath, $destination.'/'.$episodeFileWithoutExt.'.srt');
                unlink($subtitleFilePath);

                $this->logger->log('Subtitle applied');

                return true;

            }

        } else {
            $filePathInfo          = pathinfo($episode);
            $destination           = $filePathInfo['dirname'];
            $episodeFileWithoutExt = $filePathInfo['filename'];

            copy($subtitleFilePath, $destination.'/'.$episodeFileWithoutExt.'.srt');
            unlink($subtitleFilePath);

            $this->logger->log('Subtitle applied');
        }

        return true;
    }

    /**
     * @param string $subtitleUrl
     * @param string $subtitleLabel
     *
     * @return string
     */
    private function getSubtitleFilePath($subtitleUrl, $subtitleLabel)
    {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $subtitleUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($ch);
        curl_close($ch);

        file_put_contents('/tmp/'.$subtitleLabel, $data);

        $this->logger->log('Subtitle downloaded : '.$subtitleLabel);

        return '/tmp/'.$subtitleLabel;
    }

    /**
     * @param string $episode
     *
     * @return bool
     */
    private function episodeHasSubtitle($episode)
    {

        if (is_dir($episode)) {
            $files = array_diff(scandir($episode), ['..', '.']);

            foreach ($files as $file) {
                $filePathInfo = pathinfo($file);

                if (!in_array($filePathInfo['extension'], ['mp4', 'mkv', 'avi'])) {
                    continue;
                }

                $episodePathInfo       = pathinfo($file);
                $subtitleWithExtension = $episodePathInfo['filename'].'.srt';

                return file_exists($episode.'/'.$subtitleWithExtension);
            }
        } else {
            $episodePathInfo       = pathinfo($episode);
            $subtitleWithExtension = $episodePathInfo['filename'].'.srt';

            $episodeDirectory = $episodePathInfo['dirname'];

            if (!in_array($episodePathInfo['extension'], ['mp4', 'mkv', 'avi'])) {
                return true;
            }

            return file_exists($episodeDirectory.'/'.$subtitleWithExtension);
        }
    }
}
