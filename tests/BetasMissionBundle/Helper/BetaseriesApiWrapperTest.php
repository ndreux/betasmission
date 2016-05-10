<?php

namespace BetasMissionBundle\Tests\Helper;

use BetasMissionBundle\Helper\BetaseriesApiWrapper;
use Exception;
use PHPUnit_Framework_TestCase;

/**
 * Class BetaseriesApiWrapperTest
 */
class BetaseriesApiWrapperTest extends PHPUnit_Framework_TestCase
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
     * @throws \Exception
     *
     * @return mixed
     */
    public function testGetEpisodeData()
    {
        $apiWrapper = new BetaseriesApiWrapper('Dev034', md5('developer'));

        $fileName = 'A.Developers.Life.S01E01.mp4';
        $result   = $apiWrapper->getEpisodeData($fileName);

        $this->assertInstanceOf('stdClass', $result);
    }

    /**
     * @throws \Exception
     *
     * @return mixed
     *
     * Note : Expect exception because Dev034 has not subscribed to A developer's life TV Show
     * @expectedException Exception
     */
    public function testMarkAsDownloaded()
    {
        $apiWrapper = new BetaseriesApiWrapper('Dev034', md5('developer'));

        $episodeId = 348990;
        $apiWrapper->markAsDownloaded($episodeId);
    }

    /**
     * @throws \Exception
     *
     * @return mixed
     *
     * Note : Expect exception because Dev034 has not subscribed to A developer's life TV Show
     * @expectedException Exception
     */
    public function testMarkAsWatchedFail()
    {
        $apiWrapper = new BetaseriesApiWrapper('Dev034', md5('developer'));

        $episodeId = 348990;
        $apiWrapper->markAsWatched($episodeId);
    }

    /**
     * @throws Exception
     */
    public function testMarkAsWatched()
    {
        $apiWrapper = new BetaseriesApiWrapper(BetaseriesApiWrapper::LOGIN, BetaseriesApiWrapper::PASSWORD_HASH);

        $episodeId = 203664;
        $apiWrapper->markAsWatched($episodeId);
    }

    /**
     * @throws \Exception
     *
     * @return mixed
     */
    public function testGetSubtitleByEpisodeId()
    {
        $apiWrapper = new BetaseriesApiWrapper('Dev034', md5('developer'));

        $episodeId = 348990;
        $language  = 'all';
        $result    = $apiWrapper->getSubtitleByEpisodeId($episodeId, $language);

        $this->assertInstanceOf('stdClass', $result);
    }

    /**
     * @throws \Exception
     *
     * @expectedException Exception
     * Note : Expect to fail because the episode id is unknown
     * @return mixed
     */
    public function testGetSubtitleByEpisodeIdFail()
    {
        $apiWrapper = new BetaseriesApiWrapper('Dev034', md5('developer'));

        $episodeId = 0;
        $language  = 'all';
        $apiWrapper->getSubtitleByEpisodeId($episodeId, $language);
    }

    /**
     * @throws \Exception
     *
     * @expectedException Exception
     * Note : Expect to fail because the login data are wrong
     * @return mixed
     */
    public function testFailAuthenticate()
    {
        $apiWrapper = new BetaseriesApiWrapper('ndreux', md5('skldfhsdjklfhfjqlksfsdhf:qk'));

        $episodeId = 0;
        $language  = 'all';
        $apiWrapper->getSubtitleByEpisodeId($episodeId, $language);
    }
}
