<?php

namespace Tests\BetasMissionBundle\Features\Context;

use Behat\Gherkin\Node\TableNode;

class MoveContext extends AbstractCommandContext
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
}