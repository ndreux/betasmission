<?php

namespace BetasMission\Command;

use BetasMission\CommandHelper\DownloadSubtitleCommandHelper;
use BetasMission\Helper\Context;

/**
 * Class DownloadSubtitleCommand
 */
class DownloadSubtitleCommand extends AbstractCommand
{
    const CONTEXT = Context::CONTEXT_DOWNLOAD_SUBTITLE;
    const FROM    = '/mnt/smb/Labox/Series/Actives/';

    /**
     * @var string
     */
    private $from;

    /**
     * @var DownloadSubtitleCommandHelper
     */
    private $commandHelper;

    /**
     * @param string $from
     */
    public function __construct($from = self::FROM)
    {
        parent::__construct();
        $this->from          = $from;
        $this->commandHelper = new DownloadSubtitleCommandHelper();
    }

    /**
     */
    public function execute()
    {
        $shows = array_diff(scandir($this->from), ['..', '.']);

        $this->logger->log(count($shows).' found');

        foreach ($shows as $show) {
            $this->logger->log('Show : '.$show);
            $episodes = array_diff(scandir($this->from.'/'.$show), ['..', '.']);

            foreach ($episodes as $i => $episode) {
                $this->logger->log($episode);

                if ($i % 30 == 0) {
                    $this->logger->log('Wait 20s');
                    sleep(20);
                }

                $hasSubtitle = $this->commandHelper->episodeHasSubtitle($this->from.$show.'/'.$episode);
                if ($hasSubtitle === null || $hasSubtitle === true) {
                    $this->logger->log('Episode already has a subtitle');
                    continue;
                }

                try {
                    $episodeData = $this->apiWrapper->getEpisodeData($episode);
                } catch (\Exception $e) {
                    $this->logger->log('Episode not found on BetaSeries');
                    continue;
                }

                $subtitles = $this->apiWrapper->getSubtitleByEpisodeId($episodeData->episode->id);
                $this->logger->log(count($subtitles->subtitles).' found');

                $subtitle = $this->commandHelper->getBestSubtitle($subtitles, $episode);

                if ($subtitle === null) {
                    $this->logger->log('Subtitles not found on BetaSeries');
                    continue;
                }

                $this->commandHelper->applySubTitle($this->from.$show.'/'.$episode, $subtitle);
                $this->logger->log('Subtitle applied');
            }
        }
    }
}
