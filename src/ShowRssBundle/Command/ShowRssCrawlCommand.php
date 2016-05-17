<?php

namespace ShowRssBundle\Command;

use DateTime;
use Exception;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Transmission\Model\Torrent;

class ShowRssCrawlCommand extends ContainerAwareCommand
{
    const LOGFILE_DIRECTORY     = 'var/';
    const DEFAULT_DAYS_INTERVAL = 5;

    /**
     * Configures.
     */
    protected function configure()
    {
        $this
            ->setName('show-rss:crawl')
            ->setDescription('...')
            ->addOption('since', null, InputOption::VALUE_OPTIONAL, 'Date from when the crawler should start looking for new show');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $since = ($input->getOption('since') !== null) ? $input->getOption('since') : $this->getLastRunDateFromLogFile();
        $links = $this->getContainer()->getParameter('show_rss.crawler.links');

        $this->crawl($links, $since);
    }

    /**
     * @param string[] $links
     * @param string   $from
     */
    private function crawl($links, $from)
    {
        $sinceDate = DateTime::createFromFormat('Y-m-d-H:i:s', $from);
        $this->getContainer()->get('logger')->info(sprintf('Crawling since : %s', $sinceDate->format('Y-m-d H:i:s')));


        foreach ($links as $link) {

            $this->getContainer()->get('logger')->info(sprintf('Crawling link : %s', $link));
            $xml = new \SimpleXMLElement(file_get_contents($link));

            foreach ($xml->channel->item as $episode) {

                $this->getContainer()->get('logger')->info(sprintf('File : %s', (string)$episode->title));
                $episodeDate = DateTime::createFromFormat(DateTime::RFC2822, $episode->pubDate);

                if ($episodeDate > $sinceDate && !$this->isAlreadyDownloading($episode)) {
                    try {
                        $this->getContainer()->get('transmission')->add((string)$episode->link);
                        $this->getContainer()->get('logger')->info('File added.');
                    } catch (Exception $e) {
                        $this->getContainer()->get('logger')->error(sprintf('Error : %s', $e->getMessage()));

                        return;
                    }
                }
            }

            $this->logProcess((new DateTime())->setTimezone(new \DateTimeZone('GMT'))->format('Y-m-d-H:i:s'));
        }
    }

    /**
     * @param \SimpleXMLElement $episode
     *
     * @return bool
     */
    private function isAlreadyDownloading($episode)
    {
        /** @var Torrent[] $torrents */
        $torrents    = $this->getContainer()->get('transmission')->all();
        $torrentHash = $this->getHasFromMagnetLink((string)$episode->link);

        foreach ($torrents as $torrent) {
            if ($torrent->getHash() === $torrentHash) {
                $this->getContainer()->get('logger')->info('Already downloading');

                return true;
            }
        }

        return false;
    }

    /**
     * @param string $magnetLink
     *
     * @return string
     */
    private function getHasFromMagnetLink($magnetLink)
    {
        return strtolower(substr($magnetLink, 20, 40));
    }

    /**
     * @param string $from
     */
    private function logProcess($from)
    {
        $previousLogFile = $this->getPreviousLogFile();
        if ($previousLogFile) {
            unlink($previousLogFile);
        }

        $this->createLogFile($from);
    }

    /**
     * @return bool|string
     */
    private function getPreviousLogFile()
    {
        ;
        foreach (scandir($this->getContainer()->get('kernel')->getRootDir() . '/../'.self::LOGFILE_DIRECTORY) as $file) {
            if (preg_match("/^crawler\-.*$/", $file)) {
                return $this->getContainer()->get('kernel')->getRootDir() . '/../'.self::LOGFILE_DIRECTORY.$file;
            }
        }

        return false;
    }

    /**
     * @param string $from
     *
     * @return bool
     */
    private function createLogFile($from)
    {
        $logFile = $this->getContainer()->get('kernel')->getRootDir() . '/../'.self::LOGFILE_DIRECTORY.'crawler-'.$from;
        $this->getContainer()->get('logger')->info(sprintf('Create log file : %s', $logFile));

        return touch($logFile);
    }

    /**
     * Find
     *
     * @return DateTime
     */
    private function getLastRunDateFromLogFile()
    {
        $previousLogFile = $this->getPreviousLogFile();
        if (!$previousLogFile) {
            return (new DateTime())->setTimestamp(strtotime('5 days ago'))->setTimezone(new \DateTimeZone('GMT'))->format('Y-m-d-H:i:s');
        }

        $timeData = explode('-', $previousLogFile);

        return sprintf('%s-%s-%s-%s', $timeData[1], $timeData[2], $timeData[3], $timeData[4]);
    }
}
