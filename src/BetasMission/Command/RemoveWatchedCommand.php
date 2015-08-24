<?php

namespace BetasMission\Command;

use BetasMission\Helper\Context;

/**
 * Class RemoveWatchedCommand.
 */
class RemoveWatchedCommand extends AbstractCommand
{
    const FROM    = '/mnt/smb/Labox/Series/Actives';
    const CONTEXT = Context::CONTEXT_REMOVE;

    /**
     * Execute
     */
    public function execute()
    {
        $this->logger->log('Lock');
        $this->locker->lock();

        $shows = array_diff(scandir(self::FROM), ['..', '.']);

        $this->logger->log(count($shows).' found');

        foreach ($shows as $show) {
            $this->logger->log('Show : '.$show);
            $episodes = array_diff(scandir(self::FROM.'/'.$show), ['..', '.']);

            foreach ($episodes as $i => $episode) {
                $this->logger->log($episode);
                if ($i % 30 == 0) {
                    $this->logger->log('Wait 20s');
                    sleep(20);
                }

                try {
                    $episodeData = $this->apiWrapper->getEpisodeData($episode);
                } catch (\Exception $e) {
                    $this->logger->log('Episode not found on BetaSeries');
                    continue;
                }

                $this->logger->log('Episode seen : '.($episodeData->episode->user->seen ? 'true' : 'false'));
                if ($episodeData->episode->user->seen) {
                    if (!is_dir(self::FROM.'/'.$show.'/'.$episode)) {
                        $this->logger->log('Remove : '.self::FROM.'/'.$show.'/'.$episode);
                        unlink(self::FROM.'/'.$show.'/'.$episode);
                    } else {
                        $this->recurseRmdir(self::FROM.'/'.$show.'/'.$episode);
                    }
                }
            }
        }
    }
}
