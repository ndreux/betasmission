<?php

namespace BetasMissionBundle\Tests\Helper;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TraktTvApiWrapperTest extends WebTestCase
{


    /**
     * @dataProvider testMarkAsDownloadedDataProvider
     * @return void
     */
    public function testMarkAsDownloaded($tvdbId)
    {
        static::bootKernel();
        $apiWrapper = static::$kernel->getContainer()->get('betasmission.api_wrapper.trakt_tv');
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
