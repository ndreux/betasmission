<?php

namespace BetasMissionBundle\Command;

use BetasMissionBundle\CommandHelper\MoveCommandHelper;
use BetasMissionBundle\Helper\Context;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\LockHandler;

/**
 * Class MoveCommand.
 */
class MoveCommand extends AbstractCommand
{
    const CONTEXT = Context::CONTEXT_MOVE;

    /**
     * Configure
     */
    protected function configure()
    {
        $this->setName('betasmission:move')
            ->setDescription('Move downloaded episodes')
            ->addOption(
                'from',
                'f',
                InputOption::VALUE_REQUIRED,
                'TVShow root directory'
            )->addOption(
                'destination',
                'd',
                InputOption::VALUE_REQUIRED,
                'TVShow root directory'
            )->addOption(
                'default-destination',
                'dd',
                InputOption::VALUE_REQUIRED,
                'TVShow root directory'
            );
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
        $logger = $this->getContainer()->get('logger');
        $lockHandler = new LockHandler('move.lock');
        
        if (!$lockHandler->lock()) {
            $logger->info('Script locked');

            return 0;
        }
        
        $from               = $input->getOption('from');
        $destination        = $input->getOption('destination');
        $defaultDestination = $input->getOption('default-destination');

        $commandHelper = new MoveCommandHelper($logger);
        $commandHelper->organize($from, $destination, $defaultDestination);
        
        $lockHandler->release();
        
        return 0;
    }
}
