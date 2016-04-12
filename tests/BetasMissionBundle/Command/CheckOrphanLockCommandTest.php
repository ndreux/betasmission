<?php

namespace BetasMissionBundle\Tests\Command;

use BetasMissionBundle\Command\CheckOrphanLockCommand;
use BetasMissionBundle\Helper\Context;
use BetasMissionBundle\Helper\Locker;
use DateTime;
use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Process\Process;

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

        $process = new Process('php bin/console betasmission:check-orphan-lock');
        $result = $process->run();
        
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
}
