<?php

namespace BetasMissionBundle\Command;

use BetasMissionBundle\CommandHelper\RemoveCommandHelper;
use BetasMissionBundle\Helper\Context;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class RemoveWatchedCommand.
 */
class RemoveWatchedCommand extends AbstractCommand
{
    const CONTEXT = Context::CONTEXT_REMOVE;

    /**
     * Configure
     */
    protected function configure()
    {
        $this->setName('betasmission:remove')
            ->setDescription('Check if scripts are not locked')
            ->addArgument(
                'from',
                InputArgument::REQUIRED,
                'TVShow root directory'
            );;
    }

    /**
     * Execute
     *
     * @param InputInterface  $input
     * @param OutputInterface $outputInterface
     *
     * @return int|null|void
     */
    public function execute(InputInterface $input, OutputInterface $outputInterface)
    {
        $from = $input->getArgument('from');
        
        $commandHelper = new RemoveCommandHelper($this->getContainer()->get('logger'));
        $commandHelper->removeWatched($from);
    }
}
