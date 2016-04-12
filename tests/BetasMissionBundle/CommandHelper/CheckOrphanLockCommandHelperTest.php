<?php
/**
 * Created by PhpStorm.
 * User: nicolas.dreux
 * Date: 11/04/2016
 * Time: 16:35
 */

namespace tests\BetasMissionBundle\CommandHelper;


use BetasMissionBundle\Command\CheckOrphanLockCommand;
use BetasMissionBundle\CommandHelper\CheckOrphanLockCommandHelper;
use BetasMissionBundle\Helper\Logger;
use BetasMissionBundle\Helper\Mailer;
use DateTime;
use PHPUnit_Framework_MockObject_MockObject;

class CheckOrphanLockCommandHelperTest extends \PHPUnit_Framework_TestCase
{

    private $mailer;
    private $logger;

    public function setUp()
    {
        parent::setUp();

        $this->mailer = new Mailer();
        $this->logger = new Logger();
    }

    /**
     * @dataProvider testTetTimeStampLockDataProvider
     *
     * @param string $lockFile
     * @param bool   $exists
     * @param int    $expectedTimeStamp
     */
    public function testTetTimeStampLock($lockFile, $exists)
    {
        $commandHelper = new CheckOrphanLockCommandHelper($this->logger, $this->mailer);

        if ($exists) {
            touch($lockFile);
        }
        
        $expectedTimestamp = file_exists($lockFile) ? filectime($lockFile) : 0;
        $timestamp         = $commandHelper->getTimeStampLock($lockFile);

        $this->assertEquals($expectedTimestamp, $timestamp);

        if ($exists) {
            unlink($lockFile);
        }
        
    }

    public function testTetTimeStampLockDataProvider()
    {
        return [
            ['/tmp/test01.lock', true],
            ['/tmp/test02.lock', false],
        ];
    }
}
