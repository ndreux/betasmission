<?php

namespace Tests\BetasMissionBundle\Features\Context;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\TableNode;

class DownloadSubtitleContext extends AbstractCommandContext implements Context, SnippetAcceptingContext
{
    /**
     * @When /^I create the following file$/
     * @param TableNode $table
     */
    public function iCreateTheFollowingFile(TableNode $table)
    {
        foreach ($table->getTable() as $row) {
            $file         = $row[0];
            $withSubtitle = $row[1] === 'true';
            $this->createFile($file);
            if ($withSubtitle) {
                $this->createFile($this->getSubtitleFromFile($file));
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
            $file               = $row[0];
            $shouldHaveSubtitle = $row[1] === 'true';

            $this->assertFileExists($file);
            if ($shouldHaveSubtitle) {
                $this->assertFileExists($this->getSubtitleFromFile($file));
            } else {
                $this->assertFileNotExists($this->getSubtitleFromFile($file));
            }
        }
    }

    /**
     * @param string $file
     *
     * @return string
     */
    private function getSubtitleFromFile($file)
    {
        $pathInfo = pathinfo($file);

        return $pathInfo['dirname'].'/'.$pathInfo['filename'].'.srt';
    }
}