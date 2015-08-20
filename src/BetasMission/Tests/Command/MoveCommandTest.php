<?php

namespace BetasMission\Tests\Command;

use BetasMission\Command\MoveCommand;
use BetasMission\Helper\Context;
use BetasMission\Helper\Locker;
use PHPUnit_Framework_TestCase;

/**
 * Class MoveCommandTest
 */
class MoveCommandTest extends PHPUnit_Framework_TestCase
{
    /**
     */
    public function testPreExecuteAlreadyLocked()
    {
        $command = new MoveCommand();

        $locker = new Locker(Context::CONTEXT_MOVE);
        $locker->lock();

        $return = $command->preExecute();

        $this->assertFalse($return);
    }

    /**
     */
    public function testPreExecute()
    {
        $command = new MoveCommand();

        $locker = new Locker(Context::CONTEXT_MOVE);
        $locker->unlock();

        $return = $command->preExecute();

        $this->assertTrue($return);
    }

    /**
     */
    public function testPostExecute()
    {
        $command = new MoveCommand();

        $locker = new Locker(Context::CONTEXT_MOVE);
        $locker->unlock();

        $return = $command->postExecute();

        $this->assertTrue($return);
    }
}
