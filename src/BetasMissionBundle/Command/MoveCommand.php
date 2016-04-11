<?php

namespace BetasMissionBundle\Command;

use BetasMissionBundle\CommandHelper\MoveCommandHelper;
use BetasMissionBundle\Helper\Context;
use Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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

        //$this->commandHelper = new MoveCommandHelper($this->logger, $this->from, $this->destination, $this->defaultDestination);
    }
    
    /**
         * Configure
         */
        protected function configure()
        {
            $this->setName('betasmission:move')
                ->setDescription('Check if scripts are not locked');
        }

    /**
     * MoveCommand Execute.
     *
     * @param InputInterface  $input
     * @param OutputInterface $outputInterface
     *
     * @return int|null|void
     */
    public function execute(InputInterface $input, OutputInterface $outputInterface)
    {
        $episodes = array_diff(scandir($this->from), ['..', '.']);

        foreach ($episodes as $episode) {

            $this->logger->log('File : '.$episode);

            try {
                $episodeData     = $this->apiWrapper->getEpisodeData($episode);
                $destinationPath = (!empty($episodeData->episode->show->title)) ? $this->commandHelper->getTVShowDestinationPath($episodeData->episode->show->title) : $this->defaultDestination;
            }
            catch (\Exception $e) {
                $this->logger->log('The episode has not been found.');
                $destinationPath = $this->defaultDestination;
            }

            if ($this->commandHelper->moveShow($episode, $destinationPath) && isset($episodeData)) {
                $this->commandHelper->markAsDownloaded($episodeData);
            }
        }
    }
}
