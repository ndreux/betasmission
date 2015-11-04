<?php

namespace src\BetasMission\Tests\CommandHelper;

use BetasMission\Command\MoveCommand;
use BetasMission\CommandHelper\MoveCommandHelper;
use BetasMission\Helper\Context;
use BetasMission\Helper\Logger;

/**
 * Class MoveCommandHelperTest
 */
class MoveCommandHelperTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Logger
     */
    private $logger;

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
     * Variables initialisation
     */
    public function setUp()
    {
        $this->logger             = new Logger(Context::CONTEXT_MOVE);
        $this->from               = MoveCommand::FROM;
        $this->destination        = '/tmp/betasmission';
        $this->defaultDestination = '/tmp/betasmission';

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
     * @dataProvider testMoveFileDataProvider
     *
     * @param string $file
     */
    public function testMoveFile($file)
    {
        $commandHelper = new MoveCommandHelper($this->logger, $this->from, $this->destination, $this->defaultDestination);

        touch($this->from.'/'.$file);

        $this->assertFileNotExists($this->destination.'/'.$file);
        $this->assertFileExists($this->from.'/'.$file);

        $commandHelper->moveShow($file, $this->destination);

        $this->assertFileNotExists($this->from.'/'.$file);
        $this->assertFileExists($this->destination.'/'.$file);

        unlink($this->destination.'/'.$file);
    }

    /**
     * Data provider for testMoveFileDataProvider
     *
     * @return string[]
     */
    public function testMoveFileDataProvider()
    {
        return [
            ['Nashville.S04E05.720p.HDTV.x264-FLEET[rarbg].mp4'],
            ['Awkward.S05E08.720p.HDTV.x264-FLEET[rarbg].mkv'],
            ['Finding.Carter.S02E14.720p.HDTV.x264-KILLERS[rarbg].avi'],
        ];
    }

    /**
     * @dataProvider testMoveDirectoryDataProvider
     *
     * @param string $directory
     */
    public function testMoveDirectory($directory)
    {
        $commandHelper = new MoveCommandHelper($this->logger, $this->from, $this->destination, $this->defaultDestination);

        mkdir($this->from.'/'.$directory);
        touch($this->from.'/'.$directory.'/file1.mp4');
        touch($this->from.'/'.$directory.'/file2.nfo');
        touch($this->from.'/'.$directory.'/file3.srt');

        $this->assertFileNotExists($this->destination.'/'.$directory);
        $this->assertFileNotExists($this->destination.'/'.$directory.'/file1.mp4');
        $this->assertFileNotExists($this->destination.'/'.$directory.'/file2.nfo');
        $this->assertFileNotExists($this->destination.'/'.$directory.'/file3.srt');

        $this->assertFileExists($this->from.'/'.$directory);
        $this->assertFileExists($this->from.'/'.$directory.'/file1.mp4');
        $this->assertFileExists($this->from.'/'.$directory.'/file2.nfo');
        $this->assertFileExists($this->from.'/'.$directory.'/file3.srt');

        $commandHelper->moveShow($directory, $this->destination);

        $this->assertFileNotExists($this->from.'/'.$directory);
        $this->assertFileNotExists($this->from.'/'.$directory.'/file1.mp4');
        $this->assertFileNotExists($this->from.'/'.$directory.'/file2.nfo');
        $this->assertFileNotExists($this->from.'/'.$directory.'/file3.srt');

        $this->assertFileExists($this->destination.'/'.$directory);
        $this->assertFileExists($this->destination.'/'.$directory.'/file1.mp4');
        $this->assertFileExists($this->destination.'/'.$directory.'/file2.nfo');
        $this->assertFileExists($this->destination.'/'.$directory.'/file3.srt');

        unlink($this->destination.'/'.$directory.'/file1.mp4');
        unlink($this->destination.'/'.$directory.'/file2.nfo');
        unlink($this->destination.'/'.$directory.'/file3.srt');
        rmdir($this->destination.'/'.$directory);
    }

    /**
     * @return array
     */
    public function testMoveDirectoryDataProvider()
    {
        return [
            ['Nashville.S04E05.720p.HDTV.x264-FLEET[rarbg]'],
            ['Awkward.S05E08.720p.HDTV.x264-FLEET[rarbg]'],
            ['Finding.Carter.S02E14.720p.HDTV.x264-KILLERS[rarbg]'],
        ];
    }

    /**
     * @dataProvider testGetTVShowDestinationPathDataProvider
     *
     * @param string $showLabel TV Show label
     */
    public function testGetTVShowDestinationPath($showLabel)
    {
        $commandHelper = new MoveCommandHelper($this->logger, $this->from, $this->destination, $this->defaultDestination);

        if ((bool) rand(0, 1)) {
            mkdir($this->destination.'/'.$showLabel);
        }

        $path = $commandHelper->getTVShowDestinationPath($showLabel);

        $this->assertEquals($this->destination.'/'.$showLabel, $path);
        $this->assertFileExists($path);

        rmdir($path);
    }

    /**
     * @return string[]
     */
    public function testGetTVShowDestinationPathDataProvider()
    {
        return [
            ['Nashville (2012)'],
            ['Supernatural'],
            ['Suits'],
            ['Test'],
            ['Awkward.']
        ];
    }
}
