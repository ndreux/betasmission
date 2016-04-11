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
    const FROM = '/mnt/smb/Labox/Series/Actives/';

    /**
     * @var string
     */
    private $from;

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
        $from = $input->getArgument('from');
        $logger = $this->getContainer()->get('logger');

        $commandHelper = new DownloadSubtitleCommandHelper($logger);

        $shows = array_diff(scandir($from), ['..', '.']);

        $logger->info(count($shows) . ' found');

        foreach ($shows as $show) {
            $logger->info('Show : ' . $show);
            $episodes = array_diff(scandir($from . '/' . $show), ['..', '.']);

            foreach ($episodes as $i => $episode) {
                $logger->info($episode);

                $isVOSTFR = $commandHelper->isVOSTFREpisode($from . $show . '/' . $episode);
                if ($isVOSTFR) {
                    $logger->info('VOSTFR Episode. Does not need subtitle');
                    continue;
                }

                $hasSubtitle = $commandHelper->episodeHasSubtitle($from . $show . '/' . $episode);
                if ($hasSubtitle === null || $hasSubtitle === true) {
                    $logger->info('Episode already has a subtitle');
                    continue;
                }

                try {
                    $episodeData = $commandHelper->getEpisodeData($episode);
                } catch (\Exception $e) {
                    $logger->info('Episode not found on BetaSeries');
                    continue;
                }

                $subtitles = $commandHelper->getSubtitleByEpisodeId($episodeData->episode->id);
                $logger->info(count($subtitles->subtitles) . ' found');

                $subtitle = $commandHelper->getBestSubtitle($subtitles, $episode);

                if ($subtitle === null) {
                    $logger->info('Subtitles not found on BetaSeries');
                    continue;
                }

                $commandHelper->applySubTitle($from . $show . '/' . $episode, $subtitle);
                $logger->info('Subtitle applied');
            }
        }
    }
}
