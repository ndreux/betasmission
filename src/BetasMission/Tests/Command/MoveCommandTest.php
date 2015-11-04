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
     * @return void
     */
    protected function setUp()
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
        mkdir(MoveCommand::FROM.'/Suits.S05E10.HDTV.x264-ASAP[rarbg]');
        touch(MoveCommand::FROM.'/Suits.S05E10.HDTV.x264-ASAP[rarbg]/Suits.S05E10.HDTV.x264-ASAP[rarbg].mp4');
        touch(MoveCommand::FROM.'/Awkward.S05E07.720p.HDTV.x264-FLEET[rarbg].mp4');
        touch(MoveCommand::FROM.'/Test.mkv');

        $this->assertFileExists(MoveCommand::FROM.'/Suits.S05E10.HDTV.x264-ASAP[rarbg]/Suits.S05E10.HDTV.x264-ASAP[rarbg].mp4');
        $this->assertFileExists(MoveCommand::FROM.'/Suits.S05E10.HDTV.x264-ASAP[rarbg]');
        $this->assertFileExists(MoveCommand::FROM.'/Awkward.S05E07.720p.HDTV.x264-FLEET[rarbg].mp4');
        $this->assertFileExists(MoveCommand::FROM.'/Test.mkv');

        $this->assertFileNotExists(MoveCommand::DESTINATION.'/Suits.S05E10.HDTV.x264-ASAP[rarbg]/Suits.S05E10.HDTV.x264-ASAP[rarbg].mp4');
        $this->assertFileNotExists(MoveCommand::DESTINATION.'/Suits.S05E10.HDTV.x264-ASAP[rarbg]');
        $this->assertFileNotExists(MoveCommand::DESTINATION.'/Awkward.S05E07.720p.HDTV.x264-FLEET[rarbg].mp4');
        $this->assertFileNotExists(MoveCommand::DESTINATION.'/Test.mkv');
        $this->assertFileNotExists(MoveCommand::DEFAULT_DESTINATION.'/Suits.S05E10.HDTV.x264-ASAP[rarbg]/Suits.S05E10.HDTV.x264-ASAP[rarbg].mp4');
        $this->assertFileNotExists(MoveCommand::DEFAULT_DESTINATION.'/Suits.S05E10.HDTV.x264-ASAP[rarbg]');
        $this->assertFileNotExists(MoveCommand::DEFAULT_DESTINATION.'/Awkward.S05E07.720p.HDTV.x264-FLEET[rarbg].mp4');
        $this->assertFileNotExists(MoveCommand::DEFAULT_DESTINATION.'/Test.mkv');

        $command = new MoveCommand();
        $command->execute();

        unlink(MoveCommand::DEFAULT_DESTINATION.'/Test.mkv');
        unlink(MoveCommand::DESTINATION.'/Awkward./Awkward.S05E07.720p.HDTV.x264-FLEET[rarbg].mp4');
        unlink(MoveCommand::DESTINATION.'/Suits/Suits.S05E10.HDTV.x264-ASAP[rarbg]/Suits.S05E10.HDTV.x264-ASAP[rarbg].mp4');
        rmdir(MoveCommand::DESTINATION.'/Suits/Suits.S05E10.HDTV.x264-ASAP[rarbg]');
    }
}
