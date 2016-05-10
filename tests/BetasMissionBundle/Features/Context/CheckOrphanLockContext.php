<?php

namespace Tests\BetasMissionBundle\Features\Context;

use AppKernel;

class CheckOrphanLockContext extends AbstractCommandContext
{
    /**
     * @When /^I run the command "(.*)" with remaining lock files$/
     */
    public function iRunTheCommandWithRemainingLockFiles($arg1)
    {
        static::bootKernel();

        $this->client = $this->getClient();

        touch('/tmp/betasmission-move.lock', time() - 4000);

        $this->mockMailer();
        $this->client->getContainer()->get('betasmission.mailer')->expects($this->exactly(1))->method('send');
        
        $this->iRunTheCommand($arg1);
        unlink('/tmp/betasmission-move.lock');
    }

    /**
     * @When /^I run the command "(.*)" without remaining lock files$/
     */
    public function iRunTheCommandWithoutRemainingLockFiles($arg1)
    {
        static::bootKernel();

        $this->client = static::createClient();

        $this->mockMailer();
        $this->client->getContainer()->get('betasmission.mailer')->expects($this->exactly(0))->method('send');

        $this->iRunTheCommand($arg1);
    }

    /**
     * @Then /^An email should be sent$/
     */
    public function anEmailShouldBeSent()
    {
    }

    /**
     * @param array $options
     *
     * @return AppKernel
     */
    protected static function createKernel(array $options = [])
    {
        return new AppKernel('test', false);
    }

    /**
     * Mock Mailer
     */
    private function mockMailer()
    {
        $mailer = $this->getMockBuilder('BetasMissionBundle\Helper\Mailer')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $this->client->getContainer()->set('betasmission.mailer', $mailer);
    }

    /**
     * @Then /^No email should be sent$/
     */
    public function noEmailShouldBeSent()
    {
    }
}