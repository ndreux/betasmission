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
     * @var string
     */
    private $from;

    /**
     * @var string
     */
    private $destination;

    /**
     * @var string
     */
    private $defaultDestination;

    /**
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();
        $this->from               = '/tmp/betasmission/from';
        $this->destination        = '/tmp/betasmission/destination';
        $this->defaultDestination = '/tmp/betasmission/defaultDestination';

        mkdir($this->from, 0777, true);
        mkdir($this->destination, 0777, true);
        mkdir($this->defaultDestination, 0777, true);
    }

    /**
     * @return void
     */
    protected function tearDown()
    {
        parent::tearDown();

        rmdir($this->from);
        rmdir($this->destination);
        rmdir($this->defaultDestination);

        rmdir('/tmp/betasmission');
    }

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

    public function testExecute()
    {
        mkdir($this->from.'/Suits.S05E10.HDTV.x264-ASAP[rarbg]');
        touch($this->from.'/Suits.S05E10.HDTV.x264-ASAP[rarbg]/Suits.S05E10.HDTV.x264-ASAP[rarbg].mp4');
        touch($this->from.'/Awkward.S05E07.720p.HDTV.x264-FLEET[rarbg].mp4');
        touch($this->from.'/Test.mkv');

        $this->assertFileExists($this->from.'/Suits.S05E10.HDTV.x264-ASAP[rarbg]/Suits.S05E10.HDTV.x264-ASAP[rarbg].mp4');
        $this->assertFileExists($this->from.'/Suits.S05E10.HDTV.x264-ASAP[rarbg]');
        $this->assertFileExists($this->from.'/Awkward.S05E07.720p.HDTV.x264-FLEET[rarbg].mp4');
        $this->assertFileExists($this->from.'/Test.mkv');

        $this->assertFileNotExists($this->destination.'/Suits.S05E10.HDTV.x264-ASAP[rarbg]/Suits.S05E10.HDTV.x264-ASAP[rarbg].mp4');
        $this->assertFileNotExists($this->destination.'/Suits.S05E10.HDTV.x264-ASAP[rarbg]');
        $this->assertFileNotExists($this->destination.'/Awkward.S05E07.720p.HDTV.x264-FLEET[rarbg].mp4');
        $this->assertFileNotExists($this->destination.'/Test.mkv');
        $this->assertFileNotExists($this->defaultDestination.'/Suits.S05E10.HDTV.x264-ASAP[rarbg]/Suits.S05E10.HDTV.x264-ASAP[rarbg].mp4');
        $this->assertFileNotExists($this->defaultDestination.'/Suits.S05E10.HDTV.x264-ASAP[rarbg]');
        $this->assertFileNotExists($this->defaultDestination.'/Awkward.S05E07.720p.HDTV.x264-FLEET[rarbg].mp4');
        $this->assertFileNotExists($this->defaultDestination.'/Test.mkv');

        $command = new MoveCommand($this->from, $this->destination, $this->defaultDestination);
        $command->execute();

        $this->assertFileExists($this->defaultDestination.'/Test.mkv');
        $this->assertFileExists($this->destination.'/Suits/Suits.S05E10.HDTV.x264-ASAP[rarbg]/Suits.S05E10.HDTV.x264-ASAP[rarbg].mp4');
        $this->assertFileExists($this->destination.'/Awkward./Awkward.S05E07.720p.HDTV.x264-FLEET[rarbg].mp4');

        unlink($this->defaultDestination.'/Test.mkv');
        unlink($this->destination.'/Awkward./Awkward.S05E07.720p.HDTV.x264-FLEET[rarbg].mp4');
        rmdir($this->destination.'/Awkward.');
        unlink($this->destination.'/Suits/Suits.S05E10.HDTV.x264-ASAP[rarbg]/Suits.S05E10.HDTV.x264-ASAP[rarbg].mp4');
        rmdir($this->destination.'/Suits/Suits.S05E10.HDTV.x264-ASAP[rarbg]');
        rmdir($this->destination.'/Suits');
    }
}
