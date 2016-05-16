<?php

namespace Tests\BetasMissionBundle\Features\Context;

use Behat\Gherkin\Node\TableNode;

class RemoveContext extends AbstractCommandContext
{

    /**
     * @When /^I create the following file$/
     * @param TableNode $table
     */
    public function iCreateTheFollowingFile(TableNode $table)
    {
        foreach ($table->getTable() as $row) {
            $file         = $row[0];
            $withLockFile = $row[1] === 'true';

            $this->createFile($file);
            $this->assertFileExists($file);

            $lockFileForShow = $this->getLockFileNameForShow($file);
            if ($withLockFile) {
                $this->createFile($lockFileForShow);
                $this->assertFileExists($lockFileForShow);
            } else {
                $this->assertFileNotExists($lockFileForShow);
            }
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
            $shouldExist = $row[2] === 'true';

            if ($shouldExist) {
                $this->assertFileExists($file);
            } else {
                $this->assertFileNotExists($file);
            }
        }
    }

    /**
     * @param string $file
     *
     * @return string string
     */
    private function getLockFileNameForShow($file)
    {
        return pathinfo($file, PATHINFO_DIRNAME).'/.do_not_remove.lock';
    }

    /**
     * @When /^I have a file named "([^"]*)"$/
     * @And /^I have a file named "([^"]*)"$/
     */
    public function iHaveAFileNamed($file)
    {
        $this->createFile($file);
        $this->assertFileExists($file);
    }
}