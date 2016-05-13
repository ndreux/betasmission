<?php

namespace BetasMissionBundle\ApiWrapper;

use DateTime;
use GuzzleHttp\Client;
use stdClass;

/**
 * Class TraktTvApiWrapper
 */
class TraktTvApiWrapper extends AbstractApiWrapper
{
    /**
     * @var stdClass[]
     */
    private $collection;

    /**
     * TraktTvApiWrapper constructor.
     *
     * @param string $clientId
     * @param string $clientSecret
     * @param string $apiBasePath
     * @param string $accessToken
     * @param string $refreshToken
     * @param string $applicationPin
     */
    public function __construct($clientId, $clientSecret, $apiBasePath, $accessToken, $refreshToken, $applicationPin)
    {
        $this->clientId       = $clientId;
        $this->clientSecret   = $clientSecret;
        $this->apiBasePath    = $apiBasePath;
        $this->accessToken    = $accessToken;
        $this->refreshToken   = $refreshToken;
        $this->applicationPin = $applicationPin;
    }

    /**
     */
    public function authenticate()
    {
        $client   = new Client();
        $response = $client->post(
            $this->apiBasePath.'/oauth/token',
            [
                'form_params' => [
                    'code'          => $this->applicationPin,
                    'client_id'     => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'redirect_uri'  => 'urn:ietf:wg:oauth:2.0:oob',
                    'grant_type'    => 'authorization_code',
                ],
            ]
        );

        echo $response->getBody()->getContents();

        return;
    }

    /**
     * @param string $thetvbdId
     */
    public function markAsDownloaded($thetvbdId)
    {
        $headers = [
            'Authorization'     => 'Bearer '.$this->accessToken,
            'trakt-api-key'     => $this->clientId,
            'Content-Type'      => 'application/json',
            'trakt-api-version' => 2,
        ];

        $episode      = new \stdClass();
        $episode->ids = ['tvdb' => $thetvbdId];

        $this->query(
            self::HTTP_POST,
            $this->apiBasePath.'/sync/collection',
            [
                'headers' => $headers,
                'body'    => json_encode(['episodes' => [$episode]]),
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
            'Authorization'     => 'Bearer '.$this->accessToken,
            'trakt-api-key'     => $this->clientId,
            'Content-Type'      => 'application/json',
            'trakt-api-version' => 2,
        ];

        $watchedAt = ($watchedDateTime === null) ? new DateTime() : $watchedDateTime;

        $episode             = new \stdClass();
        $episode->ids        = ['tvdb' => $thetvbdId];
        $episode->watched_at = $watchedAt->format('Y-m-dTH:i:s');

        $this->query(
            self::HTTP_POST,
            $this->apiBasePath.'/sync/history',
            [
                'headers' => $headers,
                'body'    => json_encode(['episodes' => [$episode]]),
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
            'Authorization'     => 'Bearer '.$this->accessToken,
            'trakt-api-key'     => $this->clientId,
            'Content-Type'      => 'application/json',
            'trakt-api-version' => 2,
        ];

        $episode      = new \stdClass();
        $episode->ids = ['tvdb' => $thetvbdId];

        $this->query(self::HTTP_POST,
            $this->apiBasePath.'/sync/collection/remove',
            [
                'headers' => $headers,
                'body'    => json_encode(['episodes' => [$episode]]),
            ]
        );
    }

    /**
     * @return stdClass[]
     */
    public function getCollection()
    {
        if (!empty($this->collection)) {
            return $this->collection;
        }

        $headers = [
            'Authorization'     => 'Bearer '.$this->accessToken,
            'trakt-api-key'     => $this->clientId,
            'Content-Type'      => 'application/json',
            'trakt-api-version' => 2,
        ];

        $this->collection = $this->query(self::HTTP_GET, $this->apiBasePath.'/sync/collection/shows', ['headers' => $headers]);

        return $this->collection;
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
            'trakt-api-key'     => $this->clientId,
            'Content-Type'      => 'application/json',
            'trakt-api-version' => 2,
        ];

        $options = [
            'headers' => $headers,
            'query'   => ['id_type' => 'tvdb', 'id' => $tvdbId],
        ];

        $response = $this->query(self::HTTP_GET, $this->apiBasePath.'/search', $options);

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
            'Authorization'     => 'Bearer '.$this->accessToken,
            'trakt-api-key'     => $this->clientId,
            'Content-Type'      => 'application/json',
            'trakt-api-version' => 2,
        ];

        return !empty($this->query(self::HTTP_GET, sprintf($this->apiBasePath.'/sync/history/episodes/%d', $traktTvId), ['headers' => $headers]));
    }
}
