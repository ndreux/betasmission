<?php

namespace BetasMissionBundle\Command;

use BetasMissionBundle\CommandHelper\DownloadSubtitleCommandHelper;
use BetasMissionBundle\Helper\Context;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DownloadSubtitleCommand
 */
class DownloadSubtitleCommand extends AbstractCommand
{
    const CONTEXT = Context::CONTEXT_DOWNLOAD_SUBTITLE;
    const FROM = '/mnt/smb/Labox/Series/Actives/';

    /**
     * @var string
     */
    private $from;

    /**
     * @var DownloadSubtitleCommandHelper
     */
    private $commandHelper;

    /**
     * @param string $from
     */
    public function __construct($from = self::FROM)
    {
        parent::__construct();
        $this->from = $from;
        $this->commandHelper = new DownloadSubtitleCommandHelper($this->logger);
    }

    /**
     * Configure
     */
    protected function configure()
    {
        $this->setName('betasmission:subtitle')
            ->setDescription('Check if scripts are not locked');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $outputInterface
     *
     * @return int|null|void
     * @throws \Exception
     */
    public function execute(InputInterface $input, OutputInterface $outputInterface)
    {
        $shows = array_diff(scandir($this->from), ['..', '.']);

        $this->logger->log(count($shows) . ' found');

        foreach ($shows as $show) {
            $this->logger->log('Show : ' . $show);
            $episodes = array_diff(scandir($this->from . '/' . $show), ['..', '.']);

            foreach ($episodes as $i => $episode) {
                $this->logger->log($episode);

                $isVOSTFR = $this->commandHelper->isVOSTFREpisode($this->from . $show . '/' . $episode);
                if ($isVOSTFR) {
                    $this->logger->log('VOSTFR Episode. Does not need subtitle');
                    continue;
                }

                $hasSubtitle = $this->commandHelper->episodeHasSubtitle($this->from . $show . '/' . $episode);
                if ($hasSubtitle === null || $hasSubtitle === true) {
                    $this->logger->log('Episode already has a subtitle');
                    continue;
                }

                try {
                    $episodeData = $this->apiWrapper->getEpisodeData($episode);
                } catch (\Exception $e) {
                    $this->logger->log('Episode not found on BetaSeries');
                    continue;
                }

                $subtitles = $this->apiWrapper->getSubtitleByEpisodeId($episodeData->episode->id);
                $this->logger->log(count($subtitles->subtitles) . ' found');

                $subtitle = $this->commandHelper->getBestSubtitle($subtitles, $episode);

                if ($subtitle === null) {
                    $this->logger->log('Subtitles not found on BetaSeries');
                    continue;
                }

                $this->commandHelper->applySubTitle($this->from . $show . '/' . $episode, $subtitle);
                $this->logger->log('Subtitle applied');
            }
        }
    }
}
