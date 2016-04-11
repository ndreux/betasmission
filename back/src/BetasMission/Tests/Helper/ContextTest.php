<?php

namespace BetasMission\Tests\Helper;

use BetasMission\Helper\Context;
use PHPUnit_Framework_TestCase;

/**
 * Class ContextTest
 */
class ContextTest extends PHPUnit_Framework_TestCase
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
    public function testGetAvailableContexts()
    {
        $this->assertContains(Context::CONTEXT_DOWNLOAD_SUBTITLE, Context::getAvailableContexts());
        $this->assertContains(Context::CONTEXT_MOVE, Context::getAvailableContexts());
        $this->assertContains(Context::CONTEXT_REMOVE, Context::getAvailableContexts());
    }
}
