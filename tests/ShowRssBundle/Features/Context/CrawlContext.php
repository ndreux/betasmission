<?php

namespace Tests\ShowRssBundle\Features\Context;

use Behat\Gherkin\Node\TableNode;
use PHPUnit_Framework_MockObject_MockObject;
use ShowRssBundle\CommandHelper\ShowRssCrawlCommandHelper;
use Transmission\Transmission;

class CrawlContext extends AbstractCommandContext
{

    /**
     * @When /^I create the following file$/
     * @param TableNode $table
     */
    public function iCreateTheFollowingFile(TableNode $table)
    {
        foreach ($table->getTable() as $row) {
            $file = $row[0];
            $this->createFile($file);
        }
    }

    /**
     * @Then /^I should have the following results$/
     * @param TableNode $table
     */
    public function iShouldHaveTheFollowingResults(TableNode $table)
    {
        foreach ($table->getTable() as $row) {
            $file        = $row[0];
            $destination = $row[1];

            $this->assertFileNotExists($file);
            $this->assertFileExists($destination);
        }
    }

    /**
     * @Given /^I run the command "([^"]*)" with the following parameters (.*) (.*) and (.*)$/
     */
    public function iRunTheCommandWithTheFollowingParametersAnd($command, $f, $d, $dd)
    {
        $command .= sprintf(' --from=%s --destination=%s --default-destination=%s', $f, $d, $dd);

        static::$createdFiles[] = '/tmp/betasmission';

        $this->client = self::createClient();
        $this->runCommand($this->client, $command);
    }

    /**
     * @When /^I load the xml file "([^"]*)"$/
     */
    public function iLoadTheXmlFile($xmlFile)
    {
        $this->client  = $this->getClient();
        /** @var ShowRssCrawlCommandHelper|PHPUnit_Framework_MockObject_MockObject $commandHelper */
        $commandHelper = $this->getMockBuilder('ShowRssBundle\CommandHelper\ShowRssCrawlCommandHelper')
            ->enableOriginalConstructor()
            ->setConstructorArgs([
                $this->client->getContainer()->get('logger'),
                $this->client->getContainer()->get('transmission'),
                $this->client->getKernel()->getRootDir() . '/../var/',
            ])
            ->setMethods(['getXmlFromLink'])
            ->getMock();

        $xml = file_get_contents(__DIR__.'/../../'.$xmlFile);

        $commandHelper->expects($this->any())->method('getXmlFromLink')->willReturn($xml);
        $this->client->getContainer()->set('show_rss.command_helper.crawl', $commandHelper);
    }

    /**
     * @Given /^I don't have any episode currently downloading$/
     */
    public function iDonTHaveAnyEpisodeCurrentlyDownloading()
    {
        $this->client  = $this->getClient();

        /** @var Transmission|PHPUnit_Framework_MockObject_MockObject $transmission */
        $transmission = $this->getMockBuilder('Transmission\Transmission')
            ->disableOriginalConstructor()
            ->getMock();

        $transmission->expects($this->exactly(1))
            ->method('add');
        
        $transmission->expects($this->any())
            ->method('all')->willReturn([]);
        
        $this->client->getContainer()->set('transmission', $transmission);
    }

    /**
     * @Then /^I should have "([^"]*)" torrent added$/
     */
    public function iShouldHaveTorrentAdded($torrentCount)
    {
        /** @var Transmission|PHPUnit_Framework_MockObject_MockObject $transmission */
        $transmission = $this->client->getContainer()->get('transmission');
        $transmission->expects($this->exactly($torrentCount))
            ->method('add');
        $this->client->getContainer()->set('transmission', $transmission);
    }
}