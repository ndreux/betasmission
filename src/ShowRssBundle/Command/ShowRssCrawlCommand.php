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

    private function crawl($links, $from)
    {
        $sinceDate = DateTime::createFromFormat('Y-m-d-H:i:s', $from);

        $transmission = $this->getContainer()->get('transmission');
        $torrents     = $transmission->all();

        foreach ($links as $link) {

            $xml = new \SimpleXMLElement(file_get_contents($link));

            foreach ($xml->channel->item as $episode) {
                $episodeDate = DateTime::createFromFormat(DateTime::RFC2822, $episode->pubDate);

                if ($episodeDate > $sinceDate && !$this->isAlreadyDownloading($episode, $torrents)) {
                    try {
                        $transmission->add((string)$episode->link);
                    } catch (Exception $e) {
                        return;
                    }
                }
            }

            $this->logProcess((new DateTime())->format('Y-m-d-H:i:s'));

        }
    }

    /**
     * @param \SimpleXMLElement $episode
     * @param Torrent[]         $torrents
     *
     * @return bool
     */
    private function isAlreadyDownloading($episode, $torrents)
    {
        $torrentHash = $this->getHasFromMagnetLink((string)$episode->link);

        foreach ($torrents as $torrent) {
            if ($torrent->getHash() === $torrentHash) {
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
        foreach (scandir(self::LOGFILE_DIRECTORY) as $file) {
            if (preg_match("/^crawler\-.*$/", $file)) {
                return self::LOGFILE_DIRECTORY.$file;
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
        return touch(self::LOGFILE_DIRECTORY.'crawler-'.$from);
    }

    /**
     * Find 
     * @return DateTime
     */
    private function getLastRunDateFromLogFile()
    {
        $previousLogFile = $this->getPreviousLogFile();
        if (!$previousLogFile) {
            $dateTime = DateTime::createFromFormat('U', strtotime('5 days ago'), new \DateTimeZone('GMT'));

            return $dateTime->format('Y-m-d-H:i:s');
        }

        $timeData = explode('-', $previousLogFile);

        return sprintf('%s-%s-%s-%s', $timeData[1], $timeData[2], $timeData[3], $timeData[4]);
    }
}
