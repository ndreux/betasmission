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
     * @var string
     */
    private $from;

    /**
     * @var string
     */
    private $destination;

    /**
     * @var string
     */
    private $defaultDestination;

    /**
     * @var MoveCommandHelper
     */
    private $commandHelper;

    /**
     * MoveCommand constructor.
     *
     * @param string $from
     * @param string $destination
     * @param string $defaultDestination
     */
    public function __construct($from = self::FROM, $destination = self::DESTINATION, $defaultDestination = self::DEFAULT_DESTINATION)
    {
        parent::__construct();
        $this->from               = $from;
        $this->destination        = $destination;
        $this->defaultDestination = $defaultDestination;

        $this->commandHelper = new MoveCommandHelper($this->logger, $this->from, $this->destination, $this->defaultDestination);
    }

    /**
     * MoveCommand Execute.
     */
    public function execute()
    {
        $episodes = array_diff(scandir($this->from), ['..', '.']);

        foreach ($episodes as $episode) {

            $this->logger->log('File : '.$episode);

            try {
                $episodeData     = $this->apiWrapper->getEpisodeData($episode);
                $destinationPath = $this->commandHelper->getTVShowDestinationPath($episodeData->episode->show->title);
            } catch (\Exception $e) {
                $this->logger->log('The episode has not been found.');
                $destinationPath = $this->defaultDestination;
            }

            if ($this->commandHelper->moveShow($episode, $destinationPath) && isset($episodeData)) {
                $this->commandHelper->markAsDownloaded($episodeData);
            }
        }
    }
}
