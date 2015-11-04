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

    /**
     */
    public function testLock()
    {
        $locker = new Locker();
        $result = $locker->lock();

        $this->assertNotFalse($result);
    }

    /**
     */
    public function testLockWithContext()
    {
        $locker = new Locker(Context::CONTEXT_MOVE);
        $result = $locker->lock();

        $this->assertNotFalse($result);
    }

    /**
     */
    public function testIsLocked()
    {
        $locker = new Locker();
        $locker->lock();

        $result = $locker->isLocked();

        $this->assertTrue($result);
    }

    /**
     */
    public function testIsLockedWithContext()
    {
        $locker = new Locker(Context::CONTEXT_MOVE);
        $locker->lock();

        $result = $locker->isLocked();

        $this->assertTrue($result);
    }

    /**
     */
    public function testIsUnLocked()
    {
        $locker = new Locker();
        $locker->unlock();
        $result = $locker->isLocked();

        $this->assertFalse($result);
    }

    /**
     */
    public function testIsUnLockedWithContext()
    {
        $locker = new Locker(Context::CONTEXT_MOVE);
        $locker->unlock();
        $result = $locker->isLocked();

        $this->assertFalse($result);
    }

    /**
     */
    public function testUnLock()
    {
        $locker = new Locker();
        $result = $locker->unlock();

        $this->assertTrue($result);
    }

    /**
     */
    public function testIsUnLockWithContext()
    {
        $locker = new Locker(Context::CONTEXT_MOVE);
        $result = $locker->unlock();

        $this->assertTrue($result);
    }
}
