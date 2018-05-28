<?php

namespace BetasMissionBundle\Tests\ApiWrapper;

use BetasMissionBundle\ApiWrapper\BetaseriesApiWrapper;
use Exception;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class BetaseriesApiWrapperTest
 */
class BetaseriesApiWrapperTest extends WebTestCase
{
    /**
     * @var BetaseriesApiWrapper
     */
    private $apiWrapper;

    /**
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();

        static::bootKernel();
        $this->apiWrapper = static::$kernel->getContainer()->get('betasmission.api_wrapper.betaseries');

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
     */
    public function testGetEpisodeData()
    {
        $this->apiWrapper->setLogin('Dev034');
        $this->apiWrapper->setPasswordHash(md5('developer'));

        $fileName = 'A.Developers.Life.S01E01.mp4';
        $result   = $this->apiWrapper->getEpisodeData($fileName);

        $this->assertInstanceOf('stdClass', $result);
    }

    /**
     *
     * UPDATE: Do not expect exception anymore. The API has been updated.
     * Note : Expect exception because Dev034 has not subscribed to A developer's life TV Show
     */
    public function testMarkAsDownloaded()
    {
        $this->apiWrapper->setLogin('Dev034');
        $this->apiWrapper->setPasswordHash(md5('developer'));

        $episodeId = 348990;
        $this->apiWrapper->markAsDownloaded($episodeId);
    }

    /**
     * UPDATE: Do not expect exception anymore. The API has been updated.
     * Note : Expect exception because Dev034 has not subscribed to A developer's life TV Show
     */
    public function testMarkAsWatchedFail()
    {
        $this->apiWrapper->setLogin('Dev034');
        $this->apiWrapper->setPasswordHash(md5('developer'));

        $episodeId = 348990;
        $this->apiWrapper->markAsWatched($episodeId);
    }

    /**
     * @throws Exception
     */
    public function testMarkAsWatched()
    {
        $episodeId = 203664;
        $this->apiWrapper->markAsWatched($episodeId);
    }

    /**
     * @throws \Exception
     */
    public function testGetSubtitleByEpisodeId()
    {
        $this->apiWrapper->setLogin('Dev034');
        $this->apiWrapper->setPasswordHash(md5('developer'));

        $episodeId = 348990;
        $language  = 'all';
        $result    = $this->apiWrapper->getSubtitleByEpisodeId($episodeId, $language);

        $this->assertInstanceOf('stdClass', $result);
    }

    /**
     * @throws \Exception
     *
     * @expectedException Exception
     * Note : Expect to fail because the episode id is unknown
     */
    public function testGetSubtitleByEpisodeIdFail()
    {
        $this->apiWrapper->setLogin('Dev034');
        $this->apiWrapper->setPasswordHash(md5('developer'));

        $episodeId = 0;
        $language  = 'all';
        $this->apiWrapper->getSubtitleByEpisodeId($episodeId, $language);
    }

    /**
     * @throws \Exception
     *
     * @expectedException Exception
     * Note : Expect to fail because the login data are wrong
     */
    public function testFailAuthenticate()
    {
        $this->apiWrapper->setLogin('ndreux');
        $this->apiWrapper->setPasswordHash(md5('skldfhsdjklfhfjqlksfsdhf:qk'));

        $episodeId = 0;
        $language  = 'all';
        $this->apiWrapper->getSubtitleByEpisodeId($episodeId, $language);
    }
}
