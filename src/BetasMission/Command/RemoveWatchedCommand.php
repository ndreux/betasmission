<?php

namespace BetasMission\Command;

use BetasMission\CommandHelper\RemoveCommandHelper;
use BetasMission\Helper\Context;

/**
 * Class RemoveWatchedCommand.
 */
class RemoveWatchedCommand extends AbstractCommand
{
    const FROM    = '/mnt/smb/Labox/Series/Actives';
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

        $this->from                = $from;
        $this->commandActionHelper = new RemoveCommandHelper($this->logger);
    }

    /**
     * Execute
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

                try {
                    $episodeData = $this->apiWrapper->getEpisodeData($episode);
                } catch (\Exception $e) {
                    $this->logger->log('Episode not found on BetaSeries');
                    continue;
                }

                $this->logger->log('Episode seen : '.($episodeData->episode->user->seen ? 'true' : 'false'));
                if ($episodeData->episode->user->seen) {
                    $this->commandActionHelper->remove($this->from.'/'.$show.'/'.$episode);
                }
            }
        }
    }
}
