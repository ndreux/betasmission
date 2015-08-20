<?php

namespace BetasMission\Tests\Helper;

use BetasMission\Helper\BetaseriesApiWrapper;
use Exception;
use PHPUnit_Framework_TestCase;

/**
 * Class BetaseriesApiWrapperTest
 */
class BetaseriesApiWrapperTest extends PHPUnit_Framework_TestCase
{
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
    public function testMarkAsWatched()
    {
        $apiWrapper = new BetaseriesApiWrapper('Dev034', md5('developer'));

        $episodeId = 348990;
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
}
