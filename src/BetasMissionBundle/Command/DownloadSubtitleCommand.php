<?php

namespace BetasMissionBundle\Command;

use BetasMissionBundle\CommandHelper\DownloadSubtitleCommandHelper;
use BetasMissionBundle\Helper\Context;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\LockHandler;

/**
 * Class DownloadSubtitleCommand
 */
class DownloadSubtitleCommand extends AbstractCommand
{
    const CONTEXT = Context::CONTEXT_DOWNLOAD_SUBTITLE;

    /**
     * Configure
     */
    protected function configure()
    {
        $this->setName('betasmission:subtitle')
            ->setDescription('Check if scripts are not locked')
            ->addArgument(
                'from',
                InputArgument::REQUIRED,
                'TVShow root directory'
            );

    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     * @throws \Exception
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = $this->getContainer()->get('logger');

        $lockHandler = new LockHandler('subtitle.lock');
        if (!$lockHandler->lock()) {
            $logger->info('Script locked');

            return 0;
        }
        
        $from          = $input->getArgument('from');
        $commandHelper = new DownloadSubtitleCommandHelper($logger);
        $commandHelper->downloadSubtitles($from);

        return 0;
    }
}
