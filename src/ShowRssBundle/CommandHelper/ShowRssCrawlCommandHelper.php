<?php

namespace ShowRssBundle\CommandHelper;


use DateTime;
use Exception;
use SimpleXMLElement;
use Symfony\Bridge\Monolog\Logger;
use Transmission\Model\Torrent;
use Transmission\Transmission;

class ShowRssCrawlCommandHelper
{
    
    const SINCE_DATE_FORMAT = 'Y-m-d-H:i:s';
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var Transmission
     */
    private $transmission;

    /**
     * @var string
     */
    private $logFileDirectory;

    /**
     * ShowRssCrawlCommandHelper constructor.
     *
     * @param Logger       $logger
     * @param Transmission $transmission
     * @param string       $logFileDirectory
     */
    public function __construct(Logger $logger, Transmission $transmission, $logFileDirectory)
    {
        $this->logger           = $logger;
        $this->transmission     = $transmission;
        $this->logFileDirectory = $logFileDirectory;
    }


    /**
     * @param string[] $links
     * @param string   $since
     */
    public function crawl($links, $since)
    {
        $sinceDate = DateTime::createFromFormat(self::SINCE_DATE_FORMAT, ($since !== null) ? $since : $this->getLastRunDateFromLogFile());
        $this->logger->info(sprintf('Crawling since : %s', $sinceDate->format('Y-m-d H:i:s')));
        
        foreach ($links as $link) {

            $this->logger->info(sprintf('Crawling link : %s', $link));
            $xml = $this->getXmlFormLink($link);

            foreach ($xml->channel->item as $episode) {

                $this->logger->info(sprintf('File : %s', (string)$episode->title));
                $episodeDate = DateTime::createFromFormat(DateTime::RFC2822, $episode->pubDate);

                if ($episodeDate > $sinceDate && !$this->isAlreadyDownloading($episode)) {
                    try {
                        $this->transmission->add((string)$episode->link);
                        $this->logger->info('File added.');
                    } catch (Exception $e) {
                        $this->logger->error(sprintf('Error : %s', $e->getMessage()));

                        return;
                    }
                }
            }

            $this->logProcess((new DateTime())->setTimezone(new \DateTimeZone('GMT'))->format(self::SINCE_DATE_FORMAT));
        }
    }

    /**
     * @param $link
     *
     * @return SimpleXMLElement
     */
    public function getXmlFormLink($link)
    {
        return new SimpleXMLElement(file_get_contents($link));
    }

    /**
     * @param SimpleXMLElement $episode
     *
     * @return bool
     */
    private function isAlreadyDownloading($episode)
    {
        /** @var Torrent[] $torrents */
        $torrents    = $this->transmission->all();
        $torrentHash = $this->getHasFromMagnetLink((string)$episode->link);

        foreach ($torrents as $torrent) {
            if ($torrent->getHash() === $torrentHash) {
                $this->logger->info('Already downloading');

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
        foreach (scandir($this->logFileDirectory) as $file) {
            if (preg_match("/^crawler\-.*$/", $file)) {
                return $this->logFileDirectory.$file;
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
        $logFile = $this->logFileDirectory.'crawler-'.$from;
        $this->logger->info(sprintf('Create log file : %s', $logFile));

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
            return (new DateTime())->setTimestamp(strtotime('5 days ago'))->setTimezone(new \DateTimeZone('GMT'))->format(self::SINCE_DATE_FORMAT);
        }

        $timeData = explode('-', $previousLogFile);

        return sprintf('%s-%s-%s-%s', $timeData[1], $timeData[2], $timeData[3], $timeData[4]);
    }
}