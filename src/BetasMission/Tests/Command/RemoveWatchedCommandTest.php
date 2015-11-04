<?php

namespace BetasMission\Tests\Command;

use BetasMission\Command\RemoveWatchedCommand;
use PHPUnit_Framework_TestCase;

/**
 * Class RemoveWatchedCommandTest
 */
class RemoveWatchedCommandTest extends PHPUnit_Framework_TestCase
{

    /**
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        mkdir('/tmp/betasmission');
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
     * @return void
     */
    public function testExecute()
    {
        $from = '/tmp/betasmission/from';
        mkdir($from);

        mkdir($from.'/test2');
        touch($from.'/test2/KLQSDKLQSDQSD.mp4');

        mkdir($from.'/test3');
        touch($from.'/test3/Suits.S01E01.KILLERS.mp4');

        mkdir($from.'/test4');
        touch($from.'/test4/My.little.pony.S01E01.KILLERS.mp4');

        $this->assertFileExists($from.'/test2/KLQSDKLQSDQSD.mp4');
        $this->assertFileExists($from.'/test3/Suits.S01E01.KILLERS.mp4');
        $this->assertFileExists($from.'/test4/My.little.pony.S01E01.KILLERS.mp4');

        $removeCommand = new RemoveWatchedCommand($from);
        $removeCommand->execute();

        $this->assertFileExists($from.'/test2/KLQSDKLQSDQSD.mp4');
        $this->assertFileExists($from.'/test4/My.little.pony.S01E01.KILLERS.mp4');
        $this->assertFileNotExists($from.'/test3/Suits.S01E01.KILLERS.mp4');

        unlink($from.'/test2/KLQSDKLQSDQSD.mp4');
        rmdir($from.'/test2');

        rmdir($from.'/test3');

        unlink($from.'/test4/My.little.pony.S01E01.KILLERS.mp4');
        rmdir($from.'/test4');
        rmdir($from);
    }
}
