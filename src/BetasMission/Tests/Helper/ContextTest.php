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
    public function testGetAvailableContexts()
    {
        $this->assertContains(Context::CONTEXT_DOWNLOAD_SUBTITLE, Context::getAvailableContexts());
        $this->assertContains(Context::CONTEXT_MOVE, Context::getAvailableContexts());
        $this->assertContains(Context::CONTEXT_REMOVE, Context::getAvailableContexts());
    }
}
