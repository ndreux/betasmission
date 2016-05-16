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
        $episode      = new \stdClass();
        $episode->ids = ['tvdb' => $thetvbdId];

        $this->query(self::HTTP_POST, $this->apiBasePath.'/sync/collection', ['body' => json_encode(['episodes' => [$episode]])]);
    }

    /**
     * @param int           $thetvbdId
     * @param DateTime|null $watchedDateTime
     */
    public function markAsWatched($thetvbdId, $watchedDateTime = null)
    {
        $watchedAt = ($watchedDateTime === null) ? new DateTime() : $watchedDateTime;

        $episode             = new \stdClass();
        $episode->ids        = ['tvdb' => $thetvbdId];
        $episode->watched_at = $watchedAt->format('Y-m-dTH:i:s');

        $this->query(self::HTTP_POST, $this->apiBasePath.'/sync/history', ['body' => json_encode(['episodes' => [$episode]])]);
    }

    /**
     * @param int           $thetvbdId
     * @param DateTime|null $watchedDateTime
     */
    public function removeFromCollection($thetvbdId)
    {
        $episode      = new \stdClass();
        $episode->ids = ['tvdb' => $thetvbdId];

        $this->query(self::HTTP_POST, $this->apiBasePath.'/sync/collection/remove', ['body' => json_encode(['episodes' => [$episode]])]);
    }

    /**
     * @return stdClass[]
     */
    public function getCollection()
    {
        if (!empty($this->collection)) {
            return $this->collection;
        }

        $this->collection = $this->query(self::HTTP_GET, $this->apiBasePath.'/sync/collection/shows');

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
        $response = $this->query(self::HTTP_GET, $this->apiBasePath.'/search', ['query' => ['id_type' => 'tvdb', 'id' => $tvdbId]]);

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
        return !empty($this->query(self::HTTP_GET, sprintf($this->apiBasePath.'/sync/history/episodes/%d', $traktTvId)));
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array  $options
     *
     * @throws \Exception
     *
     * @return mixed
     */
    protected function query($method, $uri, array $options = [])
    {
        $headers = [
            'Authorization'     => 'Bearer '.$this->accessToken,
            'trakt-api-key'     => $this->clientId,
            'Content-Type'      => 'application/json',
            'trakt-api-version' => 2,
        ];

        return parent::query($method, $uri, array_merge($options, ['headers' => $headers]));
    }

    /**
     * @return mixed
     */
    public function getArchivedShows()
    {
        $headers = [
            'Authorization'     => 'Bearer '.$this->accessToken,
            'trakt-api-key'     => $this->clientId,
            'Content-Type'      => 'application/json',
            'trakt-api-version' => 2,
        ];

        $client   = new Client();
        $response = $client->get($this->apiBasePath.'/users/ndreux/lists/ishows-archives/items/shows', ['headers' => $headers]);
        
        return json_decode($response->getBody()->getContents());
    }
}
