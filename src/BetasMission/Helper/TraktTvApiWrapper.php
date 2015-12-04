<?php

namespace BetasMission\Helper;

use GuzzleHttp\Client;

/**
 * Class TraktTvApiWrapper
 */
class TraktTvApiWrapper
{

    const CLIENT_ID       = 'db4e4c24f7d4bb762e1c0c5858aa6c148fad2c608853c1c46db7a4bca7129259';
    const CLIENT_SECRET   = 'a97533b54ae9f9be98084888dbc3291b2ecc45088ca6e478d446d32c5fc24794';
    const APPLICATION_PIN = 'A33D1376';

    const API_URL = 'https://api-v2launch.trakt.tv';

    /**
     * @return null
     */
    public function authenticate()
    {
        $client   = new Client();
        $response = $client->post(
            self::API_URL.'/oauth/token',
            [
                'form_params' => [
                    'code'          => self::APPLICATION_PIN,
                    'client_id'     => self::CLIENT_ID,
                    'client_secret' => self::CLIENT_SECRET,
                    'redirect_uri'  => 'urn:ietf:wg:oauth:2.0:oob',
                    'grant_type'    => 'authorization_code'
                ]
            ]
        );

        echo $response->getBody();

        return null;
    }

}
