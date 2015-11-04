<?php

namespace src\BetasMission\Tests\Command;

use BetasMission\Command\DownloadSubtitleCommand;
use PHPUnit_Framework_TestCase;

/**
 * Class DownloadSubtitleCommandTest
 */
class DownloadSubtitleCommandTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $from;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->from = '/tmp/betasmission/';
        mkdir($this->from);
    }

    /**
     * @return void
     */
    protected function tearDown()
    {
        rmdir($this->from);
    }

    /**
     * @return void
     */
    public function testExecute()
    {
        mkdir($this->from.'Suits');
        touch($this->from.'Suits/Suits.S01E01.KILLERS.mp4');

        $this->assertFileExists($this->from.'Suits/Suits.S01E01.KILLERS.mp4');
        $this->assertFileNotExists($this->from.'Suits/Suits.S01E01.KILLERS.srt');

        touch($this->from.'Suits/Suits.S01E02.KILLERS.VOSTFR.mp4');

        $this->assertFileExists($this->from.'Suits/Suits.S01E02.KILLERS.VOSTFR.mp4');
        $this->assertFileNotExists($this->from.'Suits/Suits.S01E02.KILLERS.VOSTFR.srt');

        touch($this->from.'Suits/Suits.S01E03.KILLERS.mp4');
        touch($this->from.'Suits/Suits.S01E03.KILLERS.srt');

        $this->assertFileExists($this->from.'Suits/Suits.S01E03.KILLERS.mp4');
        $this->assertFileExists($this->from.'Suits/Suits.S01E03.KILLERS.srt');

        mkdir($this->from.'UnknownEpisode');
        touch($this->from.'UnknownEpisode/KLQSDJKLQSJDHQSDKQS.mp4');

        $this->assertFileExists($this->from.'UnknownEpisode/KLQSDJKLQSJDHQSDKQS.mp4');
        $this->assertFileNotExists($this->from.'UnknownEpisode/KLQSDJKLQSJDHQSDKQS.srt');

        mkdir($this->from.'A Developer\'s Life');
        touch($this->from.'A Developer\'s Life/A.developer\'s.Life.S01E01.mp4');

        $this->assertFileExists($this->from.'A Developer\'s Life/A.developer\'s.Life.S01E01.mp4');
        $this->assertFileNotExists($this->from.'A Developer\'s Life/A.developer\'s.Life.S01E01.srt');

        (new DownloadSubtitleCommand($this->from))->execute();

        $this->assertFileExists($this->from.'Suits/Suits.S01E01.KILLERS.mp4');
        $this->assertFileExists($this->from.'Suits/Suits.S01E01.KILLERS.srt');

        $this->assertFileExists($this->from.'Suits/Suits.S01E02.KILLERS.VOSTFR.mp4');
        $this->assertFileNotExists($this->from.'Suits/Suits.S01E02.KILLERS.VOSTFR.srt');

        $this->assertFileExists($this->from.'Suits/Suits.S01E03.KILLERS.mp4');
        $this->assertFileExists($this->from.'Suits/Suits.S01E03.KILLERS.srt');

        $this->assertFileExists($this->from.'UnknownEpisode/KLQSDJKLQSJDHQSDKQS.mp4');
        $this->assertFileNotExists($this->from.'UnknownEpisode/KLQSDJKLQSJDHQSDKQS.srt');

        $this->assertFileExists($this->from.'A Developer\'s Life/A.developer\'s.Life.S01E01.mp4');
        $this->assertFileNotExists($this->from.'A Developer\'s Life/A.developer\'s.Life.S01E01.srt');

        unlink($this->from.'Suits/Suits.S01E01.KILLERS.mp4');
        unlink($this->from.'Suits/Suits.S01E01.KILLERS.srt');
        unlink($this->from.'Suits/Suits.S01E03.KILLERS.mp4');
        unlink($this->from.'Suits/Suits.S01E03.KILLERS.srt');
        unlink($this->from.'Suits/Suits.S01E02.KILLERS.VOSTFR.mp4');
        rmdir($this->from.'Suits');
        unlink($this->from.'UnknownEpisode/KLQSDJKLQSJDHQSDKQS.mp4');
        rmdir($this->from.'UnknownEpisode');
        unlink($this->from.'A Developer\'s Life/A.developer\'s.Life.S01E01.mp4');
        rmdir($this->from.'A Developer\'s Life');
    }
}
