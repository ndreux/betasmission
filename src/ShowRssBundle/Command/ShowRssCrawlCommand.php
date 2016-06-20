<?php

namespace ShowRssBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

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
        $since = $input->getOption('since');
        $links = $this->getContainer()->getParameter('show_rss.crawler.links');

        $this->getContainer()->get('show_rss.command_helper.crawl')->crawl($links, $since);
    }
}
