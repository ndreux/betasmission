<?php

namespace BetasMissionBundle\Command;

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
     * @throws \Exception
     *
     * @return int|null|void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = $this->getContainer()->get('logger');

        $lockHandler = new LockHandler('subtitle.lock');
        if (!$lockHandler->lock()) {
            $logger->info('Script locked');

            return 0;
        }

        $from = $input->getArgument('from');

        $commandHelper = $this->getContainer()->get('betasmission.command_helpers.subtitle');
        $commandHelper->downloadSubtitles($from);

        $lockHandler->release();

        return 0;
    }
}
