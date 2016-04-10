<?php

namespace BetasMission\Command;

use BetasMission\CommandHelper\RemoveCommandHelper;
use BetasMission\Helper\Context;

/**
 * Class RemoveWatchedCommand.
 */
class RemoveWatchedCommand extends AbstractCommand
{
    const FROM = '/mnt/smb/Labox/Series/Actives';
    const CONTEXT = Context::CONTEXT_REMOVE;

    /**
     * @var RemoveCommandHelper
     */
    private $commandActionHelper;

    /**
     * @var string
     */
    private $from;

    /**
     * RemoveWatchedCommand constructor.
     *
     * @param string $from
     */
    public function __construct($from = self::FROM)
    {
        parent::__construct();

        $this->from = $from;
        $this->commandActionHelper = new RemoveCommandHelper($this->logger);
    }

    /**
     * Execute
     */
    public function execute()
    {
        $shows = array_diff(scandir($this->from), ['..', '.']);

        $this->logger->log(count($shows) . ' found');

        foreach ($shows as $show) {
            $this->logger->log('Show : ' . $show);

            if ($this->commandActionHelper->isWhiteListed($this->from . '/' . $show)) {
                $this->logger->log('Show white listed');
                continue;
            }

            $episodes = array_diff(scandir($this->from . '/' . $show), ['..', '.']);

            foreach ($episodes as $i => $episode) {
                $episodeCount = count($episodes);
                $this->logger->log($episode);

                if (!$this->commandActionHelper->isVideo($episode)) {
                    $this->logger->log(sprintf('The file %s is not a video file. Continue.', $episode));
                }

                try {
                    $episodeData = $this->commandActionHelper->getEpisodeFromFileName($episode);
                } catch (\Exception $e) {
                    $this->logger->log('Episode not found on BetaSeries');
                    continue;
                }

                $hasBeenSeen = $this->commandActionHelper->hasEpisodeBeenSeen($episodeData->episode->ids->trakt);
                $this->logger->log('Episode seen : ' . ($hasBeenSeen ? 'true' : 'false'));

                if ($hasBeenSeen) {

                    $this->commandActionHelper->removeFromCollection($episodeData->episode->ids->tvdb);
                    $this->commandActionHelper->remove($this->from . '/' . $show . '/' . $episode);

                    $episodeCount--;

                    if ($episodeCount === 0) {
                        $this->logger->log('No more show in Show directory. Remove ' . $this->from . '/' . $show);
                        $this->commandActionHelper->remove($this->from . '/' . $show);
                    }
                }
            }
        }
    }
}
