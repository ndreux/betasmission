<?php

namespace src\BetasMission\Test\CommandHelper;

use BetasMission\CommandHelper\DownloadSubtitleCommandHelper;
use BetasMission\Helper\Context;
use BetasMission\Helper\Logger;
use PHPUnit_Framework_TestCase;

/**
 * Class DownloadSubtitleCommandHelperTest
 */
class DownloadSubtitleCommandHelperTest extends PHPUnit_Framework_TestCase
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
    public function testEpisodeHasSubtitleFile()
    {
        mkdir('/tmp/betasmission/Suits/', 0777, true);
        touch('/tmp/betasmission/Suits/Suit.S01E01.KILLERS.mp4');
        touch('/tmp/betasmission/Suits/Suit.S01E01.KILLERS.srt');

        $commandHelper = new DownloadSubtitleCommandHelper(new Logger(Context::CONTEXT_DOWNLOAD_SUBTITLE));
        $result        = $commandHelper->episodeHasSubtitle('/tmp/betasmission/Suits/Suit.S01E01.KILLERS.mp4');

        unlink('/tmp/betasmission/Suits/Suit.S01E01.KILLERS.mp4');
        unlink('/tmp/betasmission/Suits/Suit.S01E01.KILLERS.srt');
        rmdir('/tmp/betasmission/Suits');

        $this->assertTrue($result);
    }

    /**
     */
    public function testEpisodeHasNoSubtitleFile()
    {
        mkdir('/tmp/betasmission/Suits/', 0777, true);
        touch('/tmp/betasmission/Suits/Suit.S01E01.KILLERS.mp4');

        $commandHelper = new DownloadSubtitleCommandHelper(new Logger(Context::CONTEXT_DOWNLOAD_SUBTITLE));
        $result        = $commandHelper->episodeHasSubtitle('/tmp/betasmission/Suits/Suit.S01E01.KILLERS.mp4');

        unlink('/tmp/betasmission/Suits/Suit.S01E01.KILLERS.mp4');
        rmdir('/tmp/betasmission/Suits');

        $this->assertFalse($result);
    }

    /**
     */
    public function testEpisodeHasSubtitleNoVideoFile()
    {
        mkdir('/tmp/betasmission/Suits/', 0777, true);
        touch('/tmp/betasmission/Suits/Suit.S01E01.KILLERS.nfo');

        $commandHelper = new DownloadSubtitleCommandHelper(new Logger(Context::CONTEXT_DOWNLOAD_SUBTITLE));
        $result        = $commandHelper->episodeHasSubtitle('/tmp/betasmission/Suits/Suit.S01E01.KILLERS.nfo');

        unlink('/tmp/betasmission/Suits/Suit.S01E01.KILLERS.nfo');
        rmdir('/tmp/betasmission/Suits');

        $this->assertNull($result);
    }

    /**
     */
    public function testEpisodeHasSubtitleDirectory()
    {
        mkdir('/tmp/betasmission/Suits/Suits.S01E01.KILLERS', 0777, true);
        touch('/tmp/betasmission/Suits/Suits.S01E01.KILLERS/Suit.S01E01.KILLERS.mp4');
        touch('/tmp/betasmission/Suits/Suits.S01E01.KILLERS/Suit.S01E01.KILLERS.srt');

        $commandHelper = new DownloadSubtitleCommandHelper(new Logger(Context::CONTEXT_DOWNLOAD_SUBTITLE));
        $result        = $commandHelper->episodeHasSubtitle('/tmp/betasmission/Suits/Suits.S01E01.KILLERS');

        unlink('/tmp/betasmission/Suits/Suits.S01E01.KILLERS/Suit.S01E01.KILLERS.mp4');
        unlink('/tmp/betasmission/Suits/Suits.S01E01.KILLERS/Suit.S01E01.KILLERS.srt');
        rmdir('/tmp/betasmission/Suits/Suits.S01E01.KILLERS');
        rmdir('/tmp/betasmission/Suits');

        $this->assertTrue($result);
    }

    /**
     */
    public function testEpisodeHasNoSubtitleDirectory()
    {
        mkdir('/tmp/betasmission/Suits/Suits.S01E01.KILLERS', 0777, true);
        touch('/tmp/betasmission/Suits/Suits.S01E01.KILLERS/Suit.S01E01.KILLERS.mp4');

        $commandHelper = new DownloadSubtitleCommandHelper(new Logger(Context::CONTEXT_DOWNLOAD_SUBTITLE));
        $result        = $commandHelper->episodeHasSubtitle('/tmp/betasmission/Suits/Suits.S01E01.KILLERS');

        unlink('/tmp/betasmission/Suits/Suits.S01E01.KILLERS/Suit.S01E01.KILLERS.mp4');
        rmdir('/tmp/betasmission/Suits/Suits.S01E01.KILLERS');
        rmdir('/tmp/betasmission/Suits');

        $this->assertFalse($result);
    }

    /**
     * @dataProvider getBestSubtitleDataProvider
     *
     * @param string $jsonSubtitles
     * @param mixed  $expected
     */
    public function testGetBestSubtitle($jsonSubtitles, $expected)
    {
        $subtitles = json_decode($jsonSubtitles);

        $commandHelper = new DownloadSubtitleCommandHelper(new Logger(Context::CONTEXT_DOWNLOAD_SUBTITLE));
        $subtitle      = $commandHelper->getBestSubtitle($subtitles, '/tmp/betasmission/Suits/Suits.S01E01.KILLERS/Suit.S01E01.KILLERS.mp4');

        $this->assertEquals($expected, $subtitle->id);
    }

    /**
     *
     */
    public function testGetBestSubtitleWithNoSubtitle()
    {
        $subtitles = json_decode($this->getFakeSubtitleList4());

        $commandHelper = new DownloadSubtitleCommandHelper(new Logger(Context::CONTEXT_DOWNLOAD_SUBTITLE));
        $subtitle      = $commandHelper->getBestSubtitle($subtitles, '/tmp/betasmission/Suits/Suits.S01E01.KILLERS/Suit.S01E01.KILLERS.mp4');

        $this->assertEquals(null, $subtitle);
    }

    /**
     *
     */
    public function testGetBestSubtitleWithNoProcessableSubtitle()
    {
        $subtitles = json_decode($this->getFakeSubtitleList6());

        $commandHelper = new DownloadSubtitleCommandHelper(new Logger(Context::CONTEXT_DOWNLOAD_SUBTITLE));
        $subtitle      = $commandHelper->getBestSubtitle($subtitles, '/tmp/betasmission/Suits/Suits.S01E01.KILLERS/Suit.S01E01.KILLERS.mp4');

        $this->assertEquals(null, $subtitle);
    }

    /**
     * @return array
     */
    public function getBestSubtitleDataProvider()
    {
        return [
            [$this->getFakeSubtitleList1(), 449932],
            [$this->getFakeSubtitleList2(), 449931],
            [$this->getFakeSubtitleList3(), 449933],
            [$this->getFakeSubtitleList5(), 449935],
        ];
    }

    /**
     */
    public function testApplySubtitleOnFile()
    {
        mkdir('/tmp/betasmission/Suits/', 0777, true);
        touch('/tmp/betasmission/Suits/Suit.S01E01.KILLERS.mp4');

        $subtitle = json_decode($this->getFakeSubtitle());
        $episode  = '/tmp/betasmission/Suits/Suit.S01E01.KILLERS.mp4';

        $commandHelper = new DownloadSubtitleCommandHelper(new Logger(Context::CONTEXT_DOWNLOAD_SUBTITLE));
        $commandHelper->applySubTitle($episode, $subtitle);

        $this->assertFileExists('/tmp/betasmission/Suits/Suit.S01E01.KILLERS.srt');

        unlink('/tmp/betasmission/Suits/Suit.S01E01.KILLERS.mp4');
        unlink('/tmp/betasmission/Suits/Suit.S01E01.KILLERS.srt');
        rmdir('/tmp/betasmission/Suits');
    }

    /**
     */
    public function testApplySubtitleOnDirectory()
    {
        mkdir('/tmp/betasmission/Suits/Suits.S01E01.KILLERS', 0777, true);
        touch('/tmp/betasmission/Suits/Suits.S01E01.KILLERS/Suit.S01E01.KILLERS.mp4');
        touch('/tmp/betasmission/Suits/Suits.S01E01.KILLERS/Suit.S01E01.KILLERS.jpeg');

        $subtitle = json_decode($this->getFakeSubtitle());
        $episode  = '/tmp/betasmission/Suits/Suits.S01E01.KILLERS';

        $commandHelper = new DownloadSubtitleCommandHelper(new Logger(Context::CONTEXT_DOWNLOAD_SUBTITLE));
        $commandHelper->applySubTitle($episode, $subtitle);

        $this->assertFileExists('/tmp/betasmission/Suits/Suits.S01E01.KILLERS/Suit.S01E01.KILLERS.srt');

        unlink('/tmp/betasmission/Suits/Suits.S01E01.KILLERS/Suit.S01E01.KILLERS.mp4');
        unlink('/tmp/betasmission/Suits/Suits.S01E01.KILLERS/Suit.S01E01.KILLERS.srt');
        unlink('/tmp/betasmission/Suits/Suits.S01E01.KILLERS/Suit.S01E01.KILLERS.jpeg');
        rmdir('/tmp/betasmission/Suits/Suits.S01E01.KILLERS');
        rmdir('/tmp/betasmission/Suits');
    }

    /**
     */
    public function testApplySubtitleOnDirectoryWithNoVideoFiles()
    {
        mkdir('/tmp/betasmission/Suits/Suits.S01E02.KILLERS', 0777, true);
        touch('/tmp/betasmission/Suits/Suits.S01E02.KILLERS/Suit.S01E02.KILLERS.jpeg');

        $subtitle = json_decode($this->getFakeSubtitle());
        $episode  = '/tmp/betasmission/Suits/Suits.S01E02.KILLERS';

        $commandHelper = new DownloadSubtitleCommandHelper(new Logger(Context::CONTEXT_DOWNLOAD_SUBTITLE));
        $commandHelper->applySubTitle($episode, $subtitle);

        $this->assertFileNotExists('/tmp/betasmission/Suits/Suits.S01E02.KILLERS/Suit.S01E02.KILLERS.srt');

        unlink('/tmp/betasmission/Suits/Suits.S01E02.KILLERS/Suit.S01E02.KILLERS.jpeg');
        rmdir('/tmp/betasmission/Suits/Suits.S01E02.KILLERS');
        rmdir('/tmp/betasmission/Suits');
    }

    /**
     * @dataProvider testIsVOSTFREpisodeFileDataProvider
     *
     * @param string $episode
     * @param bool   $expected
     */
    public function testIsVOSTFREpisodeFile($episode, $expected)
    {
        $commandHelper = new DownloadSubtitleCommandHelper(new Logger(Context::CONTEXT_DOWNLOAD_SUBTITLE));
        $isVOSTFR      = $commandHelper->isVOSTFREpisode($episode);

        $this->assertEquals($expected, $isVOSTFR);
    }

    /**
     * @return array
     */
    public function testIsVOSTFREpisodeFileDataProvider()
    {
        return [
            ['/tmp/betasmission/Suits/Suits.S01E01.KILLERS/Suit.S01E01.KILLERS.mp4', false],
            ['/tmp/betasmission/Suits/Suits.S01E01.VOSTFR.KILLERS/Suit.S01E01.VOSTFR.KILLERS.mp4', true],
            ['/tmp/betasmission/Suits/Suits.S01E01.vostfr.KILLERS/Suit.S01E01.vostfr.KILLERS.mp4', true],
            ['/tmp/betasmission/Suits/Suits.S01E01.vOstFr.KILLERS/Suit.S01E01.vOstFr.KILLERS.mp4', true],
            ['/tmp/betasmission/Suits/Suits.S01E01.vOstFr.KILLERS', true],
            ['/tmp/betasmission/Suits/Suits.S01E01.KILLERS', false],
        ];
    }

    /**
     * @return string
     */
    private function getFakeSubtitle()
    {
        return '{
                    "id": 449932,
                    "language": "VO",
                    "source": "addic7ed",
                    "quality": 3,
                    "file": "Suits - 01x01 - Pilot.720p.WebDL-TB.English.HI.C.orig.Addic7ed.com.srt",
                    "content": [],
                    "url": "https:\/\/www.betaseries.com\/srt\/449932",
                    "episode": {
                        "show_id": 3286,
                        "episode_id": 203664,
                        "season": 1,
                        "episode": 1
                    },
                    "date": "2014-04-22 20:08:08"
                }';
    }

    /**
     * Return a fake api result for the TV Show Suits, Episode 01 from season1
     *
     * @return mixed
     */
    private function getFakeSubtitleList1()
    {
        return
            '{
            "subtitles": [
                {
                    "id": 449932,
                    "language": "VO",
                    "source": "addic7ed",
                    "quality": 3,
                    "file": "Suits - 01x01 - Pilot.720p.WebDL-TB.English.HI.C.orig.Addic7ed.com.srt",
                    "content": [],
                    "url": "https:\/\/www.betaseries.com\/srt\/449932",
                    "episode": {
                        "show_id": 3286,
                        "episode_id": 203664,
                        "season": 1,
                        "episode": 1
                    },
                    "date": "2014-04-22 20:08:08"
                },
                {
                    "id": 449931,
                    "language": "VO",
                    "source": "addic7ed",
                    "quality": 3,
                    "file": "Suits - 01x01 - Pilot.FQM.English.HI.C.orig.Addic7ed.com.srt",
                    "content": [],
                    "url": "https:\/\/www.betaseries.com\/srt\/449931",
                    "episode": {
                        "show_id": 3286,
                        "episode_id": 203664,
                        "season": 1,
                        "episode": 1
                    },
                    "date": "2014-04-22 20:08:08"
                },
                {
                    "id": 347033,
                    "language": "VO",
                    "source": "tvsubtitles",
                    "quality": 1,
                    "file": "Suits_1x01_720p.WEB-DL.en.zip",
                    "content": [
                        "Suits - 1x01 - Episode 1.720p.WEB-DL.en.srt"
                    ],
                    "url": "https:\/\/www.betaseries.com\/srt\/347033",
                    "episode": {
                        "show_id": 3286,
                        "episode_id": 203664,
                        "season": 1,
                        "episode": 1
                    },
                    "date": "2011-06-25 22:34:58"
                },
                {
                    "id": 449933,
                    "language": "VO",
                    "source": "addic7ed",
                    "quality": 3,
                    "file": "Suits - 01x01 - Pilot.FQM.English.C.orig.Addic7ed.com.srt",
                    "content": [],
                    "url": "https:\/\/www.betaseries.com\/srt\/449933",
                    "episode": {
                        "show_id": 3286,
                        "episode_id": 203664,
                        "season": 1,
                        "episode": 1
                    },
                    "date": "2014-04-22 20:08:08"
                },
                {
                    "id": 449935,
                    "language": "VO",
                    "source": "addic7ed",
                    "quality": 3,
                    "file": "Suits - 01x01 - Pilot.720p.WebDL-TB.English.C.orig.Addic7ed.com.srt",
                    "content": [],
                    "url": "https:\/\/www.betaseries.com\/srt\/449935",
                    "episode": {
                        "show_id": 3286,
                        "episode_id": 203664,
                        "season": 1,
                        "episode": 1
                    },
                    "date": "2014-04-22 20:08:08"
                }
            ],
            "errors": []
        }';
    }

    /**
     * @return string
     */
    private function getFakeSubtitleList2()
    {
        return
            '{
            "subtitles": [
                {
                    "id": 449932,
                    "language": "VO",
                    "source": "addic7ed",
                    "quality": 3,
                    "file": "Suits - 01x01 - Pilot.720p.WebDL-TB.English.HI.C.orig.Addic7ed.com.zip",
                    "content": [],
                    "url": "https:\/\/www.betaseries.com\/srt\/449932",
                    "episode": {
                        "show_id": 3286,
                        "episode_id": 203664,
                        "season": 1,
                        "episode": 1
                    },
                    "date": "2014-04-22 20:08:08"
                },
                {
                    "id": 449931,
                    "language": "VO",
                    "source": "addic7ed",
                    "quality": 3,
                    "file": "Suits - 01x01 - Pilot.FQM.English.HI.C.orig.Addic7ed.com.srt",
                    "content": [],
                    "url": "https:\/\/www.betaseries.com\/srt\/449931",
                    "episode": {
                        "show_id": 3286,
                        "episode_id": 203664,
                        "season": 1,
                        "episode": 1
                    },
                    "date": "2014-04-22 20:08:08"
                },
                {
                    "id": 347033,
                    "language": "VO",
                    "source": "tvsubtitles",
                    "quality": 1,
                    "file": "Suits_1x01_720p.WEB-DL.en.zip",
                    "content": [
                        "Suits - 1x01 - Episode 1.720p.WEB-DL.en.srt"
                    ],
                    "url": "https:\/\/www.betaseries.com\/srt\/347033",
                    "episode": {
                        "show_id": 3286,
                        "episode_id": 203664,
                        "season": 1,
                        "episode": 1
                    },
                    "date": "2011-06-25 22:34:58"
                },
                {
                    "id": 449933,
                    "language": "VO",
                    "source": "addic7ed",
                    "quality": 3,
                    "file": "Suits - 01x01 - Pilot.FQM.English.C.orig.Addic7ed.com.srt",
                    "content": [],
                    "url": "https:\/\/www.betaseries.com\/srt\/449933",
                    "episode": {
                        "show_id": 3286,
                        "episode_id": 203664,
                        "season": 1,
                        "episode": 1
                    },
                    "date": "2014-04-22 20:08:08"
                },
                {
                    "id": 449935,
                    "language": "VO",
                    "source": "addic7ed",
                    "quality": 3,
                    "file": "Suits - 01x01 - Pilot.720p.WebDL-TB.English.C.orig.Addic7ed.com.srt",
                    "content": [],
                    "url": "https:\/\/www.betaseries.com\/srt\/449935",
                    "episode": {
                        "show_id": 3286,
                        "episode_id": 203664,
                        "season": 1,
                        "episode": 1
                    },
                    "date": "2014-04-22 20:08:08"
                }
            ],
            "errors": []
        }';
    }

    /**
     * @return string
     */
    private function getFakeSubtitleList3()
    {
        return
            '{
            "subtitles": [
                {
                    "id": 449932,
                    "language": "VO",
                    "source": "addic7ed",
                    "quality": 3,
                    "file": "Suits - 01x01 - Pilot.720p.WebDL-TB.English.HI.C.orig.Addic7ed.com.zip",
                    "content": [],
                    "url": "https:\/\/www.betaseries.com\/srt\/449932",
                    "episode": {
                        "show_id": 3286,
                        "episode_id": 203664,
                        "season": 1,
                        "episode": 1
                    },
                    "date": "2014-04-22 20:08:08"
                },
                {
                    "id": 449931,
                    "language": "VO",
                    "source": "addic7ed",
                    "quality": 1,
                    "file": "Suits - 01x01 - Pilot.FQM.English.HI.C.orig.Addic7ed.com.srt",
                    "content": [],
                    "url": "https:\/\/www.betaseries.com\/srt\/449931",
                    "episode": {
                        "show_id": 3286,
                        "episode_id": 203664,
                        "season": 1,
                        "episode": 1
                    },
                    "date": "2014-04-22 20:08:08"
                },
                {
                    "id": 347033,
                    "language": "VO",
                    "source": "tvsubtitles",
                    "quality": 1,
                    "file": "Suits_1x01_720p.WEB-DL.en.zip",
                    "content": [
                        "Suits - 1x01 - Episode 1.720p.WEB-DL.en.srt"
                    ],
                    "url": "https:\/\/www.betaseries.com\/srt\/347033",
                    "episode": {
                        "show_id": 3286,
                        "episode_id": 203664,
                        "season": 1,
                        "episode": 1
                    },
                    "date": "2011-06-25 22:34:58"
                },
                {
                    "id": 449933,
                    "language": "VO",
                    "source": "addic7ed",
                    "quality": 3,
                    "file": "Suits - 01x01 - Pilot.FQM.English.C.orig.Addic7ed.com.srt",
                    "content": [],
                    "url": "https:\/\/www.betaseries.com\/srt\/449933",
                    "episode": {
                        "show_id": 3286,
                        "episode_id": 203664,
                        "season": 1,
                        "episode": 1
                    },
                    "date": "2014-04-22 20:08:08"
                },
                {
                    "id": 449935,
                    "language": "VO",
                    "source": "addic7ed",
                    "quality": 3,
                    "file": "Suits - 01x01 - Pilot.720p.WebDL-TB.English.C.orig.Addic7ed.com.srt",
                    "content": [],
                    "url": "https:\/\/www.betaseries.com\/srt\/449935",
                    "episode": {
                        "show_id": 3286,
                        "episode_id": 203664,
                        "season": 1,
                        "episode": 1
                    },
                    "date": "2014-04-22 20:08:08"
                }
            ],
            "errors": []
        }';
    }

    /**
     * @return string
     */
    private function getFakeSubtitleList5()
    {
        return
            '{
            "subtitles": [
                {
                    "id": 449932,
                    "language": "VO",
                    "source": "addic7ed",
                    "quality": 3,
                    "file": "Suits - 01x01 - Pilot.720p.WebDL-TB.English.KILLERS.HI.C.orig.Addic7ed.com.zip",
                    "content": [],
                    "url": "https:\/\/www.betaseries.com\/srt\/449932",
                    "episode": {
                        "show_id": 3286,
                        "episode_id": 203664,
                        "season": 1,
                        "episode": 1
                    },
                    "date": "2014-04-22 20:08:08"
                },
                {
                    "id": 449931,
                    "language": "VO",
                    "source": "addic7ed",
                    "quality": 1,
                    "file": "Suits - 01x01 - Pilot.FQM.English.HI.C.orig.Addic7ed.com.srt",
                    "content": [],
                    "url": "https:\/\/www.betaseries.com\/srt\/449931",
                    "episode": {
                        "show_id": 3286,
                        "episode_id": 203664,
                        "season": 1,
                        "episode": 1
                    },
                    "date": "2014-04-22 20:08:08"
                },
                {
                    "id": 347033,
                    "language": "VO",
                    "source": "tvsubtitles",
                    "quality": 1,
                    "file": "Suits_1x01_720p.WEB-DL.en.zip",
                    "content": [
                        "Suits - 1x01 - Episode 1.720p.WEB-DL.en.srt"
                    ],
                    "url": "https:\/\/www.betaseries.com\/srt\/347033",
                    "episode": {
                        "show_id": 3286,
                        "episode_id": 203664,
                        "season": 1,
                        "episode": 1
                    },
                    "date": "2011-06-25 22:34:58"
                },
                {
                    "id": 449933,
                    "language": "VO",
                    "source": "addic7ed",
                    "quality": 3,
                    "file": "Suits - 01x01 - Pilot.FQM.English.C.orig.Addic7ed.com.srt",
                    "content": [],
                    "url": "https:\/\/www.betaseries.com\/srt\/449933",
                    "episode": {
                        "show_id": 3286,
                        "episode_id": 203664,
                        "season": 1,
                        "episode": 1
                    },
                    "date": "2014-04-22 20:08:08"
                },
                {
                    "id": 449935,
                    "language": "VO",
                    "source": "addic7ed",
                    "quality": 3,
                    "file": "Suits - 01x01 - Pilot.720p.KILLERS.English.C.orig.Addic7ed.com.srt",
                    "content": [],
                    "url": "https:\/\/www.betaseries.com\/srt\/449935",
                    "episode": {
                        "show_id": 3286,
                        "episode_id": 203664,
                        "season": 1,
                        "episode": 1
                    },
                    "date": "2014-04-22 20:08:08"
                }
            ],
            "errors": []
        }';
    }

    /**
     * @return string
     */
    private function getFakeSubtitleList4()
    {
        return '{
            "subtitles": [],
            "errors": []
        }';
    }

    /**
     * @return string
     */
    private function getFakeSubtitleList6()
    {
        return '{
            "subtitles": [
                {
                    "id": 449935,
                    "language": "VO",
                    "source": "addic7ed",
                    "quality": 3,
                    "file": "Suits - 01x01 - Pilot.720p.KILLERS.English.C.orig.Addic7ed.com.zip",
                    "content": [],
                    "url": "https:\/\/www.betaseries.com\/srt\/449935",
                    "episode": {
                        "show_id": 3286,
                        "episode_id": 203664,
                        "season": 1,
                        "episode": 1
                    },
                    "date": "2014-04-22 20:08:08"
                }
            ],
            "errors": []
        }';
    }
}
