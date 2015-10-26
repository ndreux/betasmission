<?php

namespace BetasMission\Command;

use BetasMission\CommandHelper\MoveCommandHelper;
use BetasMission\Helper\Context;
use Exception;

/**
 * Class MoveCommand.
 */
class MoveCommand extends AbstractCommand
{
    const FROM                = '/home/pi/Downloads/Complete';
    const DESTINATION         = '/mnt/smb/Labox/Series/Actives';
    const DEFAULT_DESTINATION = '/mnt/smb/Labox/Download';

    const CONTEXT = Context::CONTEXT_MOVE;

    /**
     * @var MoveCommandHelper
     */
    private $commandHelper;

    /**
     * MoveCommand constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->commandHelper = new MoveCommandHelper($this->logger, self::FROM, self::DESTINATION, self::DEFAULT_DESTINATION);
    }

    /**
     * MoveCommand Execute.
     */
    public function execute()
    {
        $episodes = array_diff(scandir(self::FROM), ['..', '.']);

        foreach ($episodes as $episode) {

            $this->logger->log('File : '.$episode);

            try {
                $episodeData     = $this->apiWrapper->getEpisodeData($episode);
                $destinationPath = $this->commandHelper->getTVShowDestinationPath($episodeData->episode->show->title);
            } catch (\Exception $e) {
                $this->logger->log('The episode has not been found.');
                $destinationPath = self::DEFAULT_DESTINATION;
            }

            if ($this->commandHelper->moveShow($episode, $destinationPath) && isset($episodeData)) {
                try {
                    $this->apiWrapper->markAsDownloaded($episodeData->episode->id);
                    $this->logger->log('Marked the episode has downloaded');
                } catch (Exception $e) {
                    $this->logger->log('The user does dot watch this show.');
                }
            }
        }
    }
}
