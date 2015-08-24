<?php

namespace src\BetasMission\Tests\Helper;

use BetasMission\Helper\Locker;
use BetasMission\Helper\Mailer;
use BetasMission\MailType\OrphanLockMessage;
use DateTime;
use PHPUnit_Framework_TestCase;

/**
 * Class MailerTest
 */
class MailerTest extends PHPUnit_Framework_TestCase
{
    public function testSendOrphanLockMessage()
    {
        $mailer = new Mailer();
        $result = $mailer->send((new OrphanLockMessage((new Locker())->getLockFile(), new DateTime()))->getMessage());

        $this->assertEquals(1, $result);
    }
}
