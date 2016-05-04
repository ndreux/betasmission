<?php

namespace BetasMissionBundle\Command;

use BetasMissionBundle\CommandHelper\DownloadSubtitleCommandHelper;
use BetasMissionBundle\Helper\Context;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DownloadSubtitleCommand
 */
class DownloadSubtitleCommand extends ContainerAwareCommand
{
    const CONTEXT = Context::CONTEXT_DOWNLOAD_SUBTITLE;
    const FROM    = '/mnt/smb/Labox/Series/Actives/';

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
        $from   = $input->getArgument('from');
        $logger = $this->getContainer()->get('logger');

        $commandHelper = new DownloadSubtitleCommandHelper($logger);
        $commandHelper->downloadSubtitles($from);
    }
}
