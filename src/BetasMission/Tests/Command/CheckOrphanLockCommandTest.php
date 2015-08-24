<?php

namespace BetasMission\Tests\Command;

use BetasMission\Command\CheckOrphanLockCommand;
use BetasMission\Helper\Context;
use BetasMission\Helper\Locker;
use DateTime;
use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;

/**
 * Class CheckOrphanLockCommandTest
 */
class CheckOrphanLockCommandTest extends PHPUnit_Framework_TestCase
{
    /**
     * @return void
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
     * @return void
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
}
