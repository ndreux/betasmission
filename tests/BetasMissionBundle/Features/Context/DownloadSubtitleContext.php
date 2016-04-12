<?php

namespace tests\BetasMissionBundle\Features\Context;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Gherkin\Node\TableNode;
use Symfony\Bundle\FrameworkBundle\Client;

class DownloadSubtitleContext extends CommandTestCase implements Context, SnippetAcceptingContext
{

    /**
     * @var Client
     */
    private $client;

    /**
     * @Given /^I run the command "([^"]*)"$/
     */
    public function iRunTheCommand($arg1)
    {
        $this->client = self::createClient();
        $this->runCommand($this->client, "betasmission:subtitle /tmp");
    }

    /**
     * @When /^I create the following file$/
     */
    public function iCreateTheFollowingFile(TableNode $table)
    {

        throw new PendingException();
    }

    /**
     * @Then /^I should have the following results$/
     */
    public function iShouldHaveTheFollowingResults(TableNode $table)
    {
        throw new PendingException();
    }

}