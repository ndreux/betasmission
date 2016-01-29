<?php

namespace BetasMission\Helper;

use DateTime;
use GuzzleHttp\Client;
use stdClass;

/**
 * Class TraktTvApiWrapper
 */
class TraktTvApiWrapper
{

    const CLIENT_ID       = 'db4e4c24f7d4bb762e1c0c5858aa6c148fad2c608853c1c46db7a4bca7129259';
    const CLIENT_SECRET   = 'a97533b54ae9f9be98084888dbc3291b2ecc45088ca6e478d446d32c5fc24794';
    const APPLICATION_PIN = 'FEEFF3F3';

    const API_URL = 'https://api-v2launch.trakt.tv';

    const ACCESS_TOKEN  = '9add6f3dbb02c54ddea33b6b606cd0c41ba5ca82554a145554c1f0d31810fc23';
    const REFRESH_TOKEN = '0898a02446efc4970d63cf58aeaf493b7129f06c20f8154fe1ce0c34c0c82cf3';

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

        echo $response->getBody()->getContents();

        return null;
    }

    /**
     * @param string $thetvbdId
     */
    public function markAsDownloaded($thetvbdId)
    {
        $headers = [
            'Authorization'     => 'Bearer '.self::ACCESS_TOKEN,
            'trakt-api-key'     => self::CLIENT_ID,
            'Content-Type'      => 'application/json',
            'trakt-api-version' => 2
        ];

        $episode      = new \stdClass();
        $episode->ids = ['tvdb' => $thetvbdId];

        $client = new Client();
        $client->post(
            self::API_URL.'/sync/collection',
            [
                'headers' => $headers,
                'body'    => json_encode(['episodes' => [$episode]])
            ]
        );
    }

    /**
     * @param int           $thetvbdId
     * @param DateTime|null $watchedDateTime
     */
    public function markAsWatched($thetvbdId, $watchedDateTime = null)
    {
        $headers = [
            'Authorization'     => 'Bearer '.self::ACCESS_TOKEN,
            'trakt-api-key'     => self::CLIENT_ID,
            'Content-Type'      => 'application/json',
            'trakt-api-version' => 2
        ];

        $watchedAt = ($watchedDateTime === null) ? new DateTime() : $watchedDateTime;

        $episode             = new \stdClass();
        $episode->ids        = ['tvdb' => $thetvbdId];
        $episode->watched_at = $watchedAt->format('Y-m-dTH:i:s');

        $client = new Client();
        $client->post(
            self::API_URL.'/sync/history',
            [
                'headers' => $headers,
                'body'    => json_encode(['episodes' => [$episode]])
            ]
        );
    }

    /**
     * @param int           $thetvbdId
     * @param DateTime|null $watchedDateTime
     */
    public function removeFromCollection($thetvbdId)
    {
        $headers = [
            'Authorization'     => 'Bearer '.self::ACCESS_TOKEN,
            'trakt-api-key'     => self::CLIENT_ID,
            'Content-Type'      => 'application/json',
            'trakt-api-version' => 2
        ];

        $episode      = new \stdClass();
        $episode->ids = ['tvdb' => $thetvbdId];

        $client = new Client();
        $client->post(
            self::API_URL.'/sync/collection/remove',
            [
                'headers' => $headers,
                'body'    => json_encode(['episodes' => [$episode]])
            ]
        );
    }

    /**
     * @return stdClass[]
     */
    public function getCollection()
    {

        $headers = [
            'Authorization'     => 'Bearer '.self::ACCESS_TOKEN,
            'trakt-api-key'     => self::CLIENT_ID,
            'Content-Type'      => 'application/json',
            'trakt-api-version' => 2
        ];

        $client   = new Client();
        $response = $client->get(self::API_URL.'/sync/collection/shows', ['headers' => $headers]);

        return json_decode($response->getBody()->getContents());
    }

    /**
     * @param int $thetvdbId
     *
     * @return bool
     */
    public function isEpisodeInCollection($thetvdbId)
    {
        $episodeData = $this->searchEpisode($thetvdbId);
        $collection  = $this->getCollection();

        foreach ($collection as $show) {
            foreach ($show->seasons as $season) {

                if ($season->number != $episodeData->episode->season) {
                    continue;
                }

                foreach ($season->episodes as $episode) {
                    if ($episode->number == $episodeData->episode->number) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * @param int $tvdbId
     *
     * @return stdClass
     */
    public function searchEpisode($tvdbId)
    {
        $headers = [
            'trakt-api-key'     => self::CLIENT_ID,
            'Content-Type'      => 'application/json',
            'trakt-api-version' => 2
        ];

        $client   = new Client();
        $response = $client->get(
            self::API_URL.'/search',
            [
                'headers' => $headers,
                'query'   => ['id_type' => 'tvdb', 'id' => $tvdbId]
            ]
        );

        $response = json_decode($response->getBody()->getContents());

        return array_shift($response);
    }

    /**
     * Return true if an episode is marked as watched on trakt tv
     *
     * @param int $traktTvId
     *
     * @return bool
     */
    public function hasEpisodeBeenSeen($traktTvId)
    {
        $headers = [
            'Authorization'     => 'Bearer '.self::ACCESS_TOKEN,
            'trakt-api-key'     => self::CLIENT_ID,
            'Content-Type'      => 'application/json',
            'trakt-api-version' => 2
        ];

        $client   = new Client();
        $response = $client->get(sprintf(self::API_URL.'/sync/history/episodes/%d', $traktTvId), ['headers' => $headers]);

        return !empty(json_decode($response->getBody()->getContents()));
    }
}
