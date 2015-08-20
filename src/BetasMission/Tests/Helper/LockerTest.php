<?php

namespace BetasMission\Tests\Helper;

use BetasMission\Helper\Context;
use BetasMission\Helper\Locker;
use PHPUnit_Framework_TestCase;

/**
 * Class LockerTest
 */
class LockerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @return void
     */
    public function testLock()
    {
        $locker = new Locker();
        $result = $locker->lock();

        $this->assertNotFalse($result);
    }

    /**
     * @return void
     */
    public function testLockWithContext()
    {
        $locker = new Locker(Context::CONTEXT_MOVE);
        $result = $locker->lock();

        $this->assertNotFalse($result);
    }

    /**
     * @return void
     */
    public function testIsLocked()
    {
        $locker = new Locker();
        $locker->lock();

        $result = $locker->isLocked();

        $this->assertTrue($result);
    }

    /**
     * @return void
     */
    public function testIsLockedWithContext()
    {
        $locker = new Locker(Context::CONTEXT_MOVE);
        $locker->lock();

        $result = $locker->isLocked();

        $this->assertTrue($result);
    }

    /**
     * @return void
     */
    public function testIsUnLocked()
    {
        $locker = new Locker();
        $locker->unlock();
        $result = $locker->isLocked();

        $this->assertFalse($result);
    }

    /**
     * @return void
     */
    public function testIsUnLockedWithContext()
    {
        $locker = new Locker(Context::CONTEXT_MOVE);
        $locker->unlock();
        $result = $locker->isLocked();

        $this->assertFalse($result);
    }

    /**
     * @return void
     */
    public function testUnLock()
    {
        $locker = new Locker();
        $result = $locker->unlock();

        $this->assertTrue($result);

    }

    /**
     * @return void
     */
    public function testIsUnLockWithContext()
    {
        $locker = new Locker(Context::CONTEXT_MOVE);
        $result = $locker->unlock();

        $this->assertTrue($result);
    }

}
