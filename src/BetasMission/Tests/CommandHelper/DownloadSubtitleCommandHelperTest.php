<?php

namespace src\BetasMission\Test\CommandHelper;

use BetasMission\CommandHelper\DownloadSubtitleCommandHelper;
use PHPUnit_Framework_TestCase;

/**
 * Class DownloadSubtitleCommandHelperTest
 */
class DownloadSubtitleCommandHelperTest extends PHPUnit_Framework_TestCase
{
    /**
     */
    public function testEpisodeHasSubtitleFile()
    {
        mkdir('/tmp/betasmission/Suits/', 0777, true);
        touch('/tmp/betasmission/Suits/Suit.S01E01.KILLERS.mp4');
        touch('/tmp/betasmission/Suits/Suit.S01E01.KILLERS.srt');

        $commandHelper = new DownloadSubtitleCommandHelper();
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

        $commandHelper = new DownloadSubtitleCommandHelper();
        $result        = $commandHelper->episodeHasSubtitle('/tmp/betasmission/Suits/Suit.S01E01.KILLERS.mp4');

        unlink('/tmp/betasmission/Suits/Suit.S01E01.KILLERS.mp4');
        rmdir('/tmp/betasmission/Suits');

        $this->assertFalse($result);
    }

    /**
     */
    public function testEpisodeHasSubtitleDirectory()
    {
        mkdir('/tmp/betasmission/Suits/Suits.S01E01.KILLERS', 0777, true);
        touch('/tmp/betasmission/Suits/Suits.S01E01.KILLERS/Suit.S01E01.KILLERS.mp4');
        touch('/tmp/betasmission/Suits/Suits.S01E01.KILLERS/Suit.S01E01.KILLERS.srt');

        $commandHelper = new DownloadSubtitleCommandHelper();
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

        $commandHelper = new DownloadSubtitleCommandHelper();
        $result        = $commandHelper->episodeHasSubtitle('/tmp/betasmission/Suits/Suits.S01E01.KILLERS');

        unlink('/tmp/betasmission/Suits/Suits.S01E01.KILLERS/Suit.S01E01.KILLERS.mp4');
        rmdir('/tmp/betasmission/Suits/Suits.S01E01.KILLERS');
        rmdir('/tmp/betasmission/Suits');

        $this->assertFalse($result);
    }

    /**
     * @dataProvider getBestSubtitleDataProvider
     */
    public function testGetBestSubtitle($jsonSubtitles, $expected)
    {
        $subtitles = json_decode($jsonSubtitles);

        $commandHelper = new DownloadSubtitleCommandHelper();
        $subtitle      = $commandHelper->getBestSubtitle($subtitles);

        $this->assertEquals($expected, $subtitle->id);
    }

    /**
     * @dataProvider getBestSubtitleDataProvider
     */
    public function testGetBestSubtitleWithNoSubtitle()
    {
        $subtitles = json_decode($this->getFakeSubtitleList4());

        $commandHelper = new DownloadSubtitleCommandHelper();
        $subtitle      = $commandHelper->getBestSubtitle($subtitles);

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

        $commandHelper = new DownloadSubtitleCommandHelper();
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

        $subtitle = json_decode($this->getFakeSubtitle());
        $episode  = '/tmp/betasmission/Suits/Suits.S01E01.KILLERS';

        $commandHelper = new DownloadSubtitleCommandHelper();
        $commandHelper->applySubTitle($episode, $subtitle);

        $this->assertFileExists('/tmp/betasmission/Suits/Suits.S01E01.KILLERS/Suit.S01E01.KILLERS.srt');

        unlink('/tmp/betasmission/Suits/Suits.S01E01.KILLERS/Suit.S01E01.KILLERS.mp4');
        unlink('/tmp/betasmission/Suits/Suits.S01E01.KILLERS/Suit.S01E01.KILLERS.srt');
        rmdir('/tmp/betasmission/Suits/Suits.S01E01.KILLERS');
        rmdir('/tmp/betasmission/Suits');
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
    private function getFakeSubtitleList4()
    {
        return '{
            "subtitles": [],
            "errors": []
        }';
    }
}
