<?php

namespace Tests\BetasMissionBundle\Features\Context;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\TableNode;

abstract class AbstractCommandContext extends CommandTestCase implements Context, SnippetAcceptingContext
{
    /**
     * @Given /^I run the command "([^"]*)"$/
     */
    public function iRunTheCommand($arg1)
    {
        $this->client = $this->getClient();
        $this->runCommand($this->client, $arg1);
    }

    /**
     * @Given /^I run the command "([^"]*)" with the parameters "([^"]*)"$/
     */
    public function iRunTheCommandWithTheParameters($command, $parameters)
    {
        $this->client           = $this->getClient();
        static::$createdFiles[] = $parameters;
        $this->runCommand($this->client, $command.' '.$parameters);
    }

    /**
     * @Given /^I run the command "([^"]*)" with the following parameters$/
     */
    public function iRunTheCommandWithTheFollowingParameters($command, TableNode $table)
    {
        foreach ($table->getTable() as $row) {
            $parameter = $row[0];
            $value     = $row[1];

            $command .= sprintf(' --%s %s', $parameter, $value);
            static::$createdFiles[] = $value;
        }

        $this->client = $this->getClient();
        $this->runCommand($this->client, $command);
    }

    /**
     * Init client if not existing
     *
     * @return \Symfony\Bundle\FrameworkBundle\Client
     */
    protected function getClient()
    {
        if ($this->client === null) {
            $this->client = self::createClient();
        }

        return $this->client;
    }
}