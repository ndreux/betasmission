<?php

namespace src\BetasMission\Tests\Helper;

use BetasMissionBundle\Helper\Locker;
use BetasMissionBundle\Helper\Mailer;
use BetasMissionBundle\MailType\OrphanLockMessage;
use DateTime;
use PHPUnit_Framework_TestCase;

/**
 * Class MailerTest
 */
class MailerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();
        mkdir('/tmp/betasmission', 0777, true);
    }

    /**
     * @return void
     */
    protected function tearDown()
    {
        parent::tearDown();
        rmdir('/tmp/betasmission');
    }

    public function testSendOrphanLockMessage()
    {
        $mailer = new Mailer();
        $result = $mailer->send((new OrphanLockMessage((new Locker())->getLockFile(), new DateTime()))->getMessage());

        $this->assertEquals(1, $result);
    }
}
