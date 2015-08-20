<?php

namespace Command;

use Helper\Context;

/**
 * Class MoveCommand.
 */
class MoveCommand extends AbstractCommand implements CommandInterface
{
    const FROM                = '/home/pi/Downloads/Complete';
    const DESTINATION         = '/mnt/smb/Labox/Series/Actives';
    const DEFAULT_DESTINATION = '/mnt/smb/Labox/Download';

    const CONTEXT = Context::CONTEXT_MOVE;

    /**
     */
    public function execute()
    {
        $episodes = array_diff(scandir(self::FROM), ['..', '.']);

        foreach ($episodes as $episode) {
            $this->logger->log('File : '.$episode);
            try {
                $episodeData     = $this->apiWrapper->getEpisodeData($episode);
                $destinationPath = $this->computeDestinationPath($episodeData->episode->show->title);
            } catch (\Exception $e) {
                $this->logger->log('The episode has not been found.');
                $destinationPath = self::DEFAULT_DESTINATION;
            }

            if ($this->moveShow($episode, $destinationPath) && isset($episodeData)) {
                $this->apiWrapper->markAsDownloaded($episodeData->episode->id);
                $this->logger->log('Marked the episode has downloaded');
            }
        }
    }

    /**
     * @param string $showLabel
     *
     * @return string
     */
    private function computeDestinationPath($showLabel)
    {
        if (!is_dir(self::DESTINATION.'/'.$showLabel)) {
            mkdir(self::DESTINATION.'/'.$showLabel, 0777, true);
        }

        return self::DESTINATION.'/'.$showLabel;
    }

    /**
     * @param string $episode
     * @param string $destinationPath
     *
     * @return bool
     */
    private function moveShow($episode, $destinationPath)
    {
        $from = self::FROM.'/'.$episode;

        if (is_file($from)) {
            $this->logger->log('Moving '.$from.' to '.$destinationPath.'/'.$episode);
            if (copy($from, $destinationPath.'/'.$episode)) {
                $this->logger->log('Remove : '.$from);
                unlink($from);
            }
        } else {
            $this->recurseCopy($from, $destinationPath.'/'.$episode);
            $this->recurseRmdir($from);
        }

        return true;
    }

    /**
     * @param string $src
     * @param string $dst
     *
     * @return bool
     */
    private function recurseCopy($src, $dst)
    {
        $dir = opendir($src);
        mkdir($dst);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src.'/'.$file)) {
                    $this->recurseCopy($src.'/'.$file, $dst.'/'.$file);
                } else {
                    $this->logger->log('Copy : '.$src.'/'.$file.' to '.$dst.'/'.$file);
                    copy($src.'/'.$file, $dst.'/'.$file);
                }
            }
        }
        closedir($dir);
    }
}
