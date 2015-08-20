<?php

namespace BetasMission\Tests\Helper;

use BetasMission\Helper\Context;
use BetasMission\Helper\Logger;
use PHPUnit_Framework_TestCase;

/**
 * Class LoggerTest
 */
class LoggerTest extends PHPUnit_Framework_TestCase
{
    /**
     */
    public function testLog()
    {
        $logger = new Logger();
        $result = $logger->log('toto');

        $this->assertNotFalse($result);
    }

    /**
     */
    public function testLogWithContext()
    {
        $logger = new Logger(Context::CONTEXT_MOVE);
        $result = $logger->log('toto');

        $this->assertNotFalse($result);
    }
}
