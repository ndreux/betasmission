<?php

namespace BetasMission\Tests\Command;

use BetasMission\Command\CheckOrphanLockCommand;
use BetasMission\Helper\Context;
use BetasMission\Helper\Locker;
use BetasMission\Helper\Mailer;
use DateTime;
use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;

/**
 * Class CheckOrphanLockCommandTest
 */
class CheckOrphanLockCommandTest extends PHPUnit_Framework_TestCase
{
    /**
     */
    public function testExecuteNoOrphanLock()
    {
        $locker = new Locker();
        $locker->lock();

        $result = (new CheckOrphanLockCommand())->execute();

        $this->assertFalse($result);

        $locker->unlock();
    }

    /**
     */
    public function testExecuteOrphanLock()
    {
        /** @var PHPUnit_Framework_MockObject_MockObject|CheckOrphanLockCommand $command */
        $command = $this->getMockBuilder('BetasMission\Command\CheckOrphanLockCommand')
            ->setMethods(['getTimeStampLock', 'sendAlert'])
            ->getMock();

        $command->expects($this->once())
            ->method('getTimeStampLock')
            ->willReturn((new DateTime('2015-01-01T20:00:00'))->getTimestamp());

        $command->expects($this->once())
            ->method('sendAlert');

        $locker = new Locker();
        $locker->lock();

        $command->execute();

        $locker->unlock();
    }

    /**
     * @dataProvider executeWithContextLockDataProvider
     *
     * @param $context
     */
    public function testExecuteOrphanContextLock($context)
    {
        /** @var PHPUnit_Framework_MockObject_MockObject|CheckOrphanLockCommand $command */
        $command = $this->getMockBuilder('BetasMission\Command\CheckOrphanLockCommand')
            ->setMethods(['getTimeStampLock', 'sendAlert'])
            ->getMock();

        $command->expects($this->once())
            ->method('getTimeStampLock')
            ->willReturn((new DateTime('2015-01-01T20:00:00'))->getTimestamp());

        $command->expects($this->once())
            ->method('sendAlert');

        $locker = new Locker($context);
        $locker->lock();

        $command->execute();

        $locker->unlock();
    }

    /**
     * @dataProvider executeWithContextLockDataProvider
     *
     * @param $context
     */
    public function testExecuteWithContextNoOrphanLock($context)
    {
        $locker = new Locker($context);
        $locker->lock();

        $result = (new CheckOrphanLockCommand())->execute();

        $this->assertFalse($result);

        $locker->unlock();
    }

    /**
     * @return string
     */
    public function executeWithContextLockDataProvider()
    {
        return [[Context::CONTEXT_MOVE], [Context::CONTEXT_REMOVE], [Context::CONTEXT_DOWNLOAD_SUBTITLE]];
    }

    /**
     * @return void
     */
    public function testSendAlert()
    {
        $lockFile = '/tmp/test.lock';
        $datetime = new DateTime();

        /** @var PHPUnit_Framework_MockObject_MockObject|Mailer $mailer */
        $mailer = $this->getMockBuilder('BetasMission\Helper\Mailer')
            ->getMock();

        $mailer->expects($this->once())
            ->method('send');

        /** @var PHPUnit_Framework_MockObject_MockObject|CheckOrphanLockCommand $command */
        $command = $this->getMockBuilder('BetasMission\Command\CheckOrphanLockCommand')
            ->setMethods(['getMailer'])
            ->getMock();

        $command->expects($this->once())
            ->method('getMailer')
            ->willReturn($mailer);

        $command->sendAlert($lockFile, $datetime);
    }

    /**
     * @return void
     */
    public function testGetMailer()
    {
        $command = new CheckOrphanLockCommand();
        $mailer  = $command->getMailer();

        $this->assertInstanceOf('BetasMission\Helper\Mailer', $mailer);
    }
}
