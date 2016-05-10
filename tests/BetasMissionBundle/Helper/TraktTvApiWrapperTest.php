<?php

namespace BetasMissionBundle\Tests\Helper;

use BetasMissionBundle\Helper\TraktTvApiWrapper;

class TraktTvApiWrapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider testMarkAsDownloadedDataProvider
     * @return void
     */
    public function testMarkAsDownloaded($tvdbId)
    {
        $apiWrapper = new TraktTvApiWrapper();
        $apiWrapper->markAsDownloaded($tvdbId);

        $isInCollection = $apiWrapper->isEpisodeInCollection($tvdbId);

        $this->assertTrue($isInCollection);
    }

    public function testMarkAsDownloadedDataProvider()
    {
        return [
            [4077754]
        ];
    }
}
