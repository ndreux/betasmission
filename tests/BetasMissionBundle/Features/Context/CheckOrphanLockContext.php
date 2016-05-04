<?php

namespace Tests\BetasMissionBundle\Features\Context;

use AppKernel;
use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Process\Process;

class CheckOrphanLockContext extends WebTestCase implements Context, SnippetAcceptingContext
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @When /^I run the command "(.*)" with remaining lock files$/
     */
    public function iRunTheCommandWithRemainingLockFiles($arg1)
    {
        static::bootKernel();

        $this->client = static::createClient();
        

        touch('/tmp/betasmission-move.lock', time() - 4000);

        $this->mockMailer();
        $this->client->getContainer()->get('betasmission.mailer')->expects($this->exactly(1))->method('send');

        $process = new Process('php app/console '.$arg1.' --env='.static::$kernel->getEnvironment());
        $process->run();
    }

    /**
     * @Then /^An email should be sent$/
     */
    public function anEmailShouldBeSent()
    {
    }

    protected static function createKernel(array $options = [])
    {
        return new AppKernel('test', false);
    }

    private function mockMailer()
    {
        $mailer = $this->getMockBuilder('BetasMissionBundle\Helper\Mailer')
            ->setMethods(null)
            ->getMock();

        $this->client->getContainer()->set('betasmission.mailer', $mailer);
    }
}