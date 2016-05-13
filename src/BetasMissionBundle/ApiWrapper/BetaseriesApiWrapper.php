<?php

namespace BetasMissionBundle\ApiWrapper;

use stdClass;

/**
 * Class BetaseriesApiWrapper.
 */
class BetaseriesApiWrapper extends AbstractApiWrapper
{
    /**
     * @var string
     */
    private $token;

    /**
     * @param string      $login
     * @param string      $passwordHash
     * @param string|null $apiKey
     * @param string|null $apiBasePath
     */
    public function __construct($login, $passwordHash, $apiKey, $apiBasePath)
    {
        $this->login        = $login;
        $this->passwordHash = $passwordHash;
        $this->apiKey       = $apiKey;
        $this->apiBasePath  = $apiBasePath;

        $this->authenticate();
    }

    /**
     * @param $episodeFileName
     *
     * @throws \Exception
     *
     * @return StdClass
     */
    public function getEpisodeData($episodeFileName)
    {
        $parameters  = ['token' => $this->token, 'key' => $this->apiKey, 'v' => '2.4', 'file' => $episodeFileName];
        $searchQuery = $this->apiBasePath.'episodes/scraper?'.http_build_query($parameters);

        return $this->query(self::HTTP_GET, $searchQuery);
    }

    /**
     * @param int $episodeId
     *
     * @throws \Exception
     *
     * @return StdClass
     */
    public function markAsDownloaded($episodeId)
    {
        $parameters      = ['token' => $this->token, 'key' => $this->apiKey, 'v' => '2.4', 'id' => $episodeId];
        $downloadedQuery = $this->apiBasePath.'episodes/downloaded';

        return $this->query(self::HTTP_POST, $downloadedQuery, ['form_params' => $parameters]);

    }

    /**
     * @param int $episodeId
     *
     * @throws \Exception
     *
     * @return StdClass
     */
    public function markAsWatched($episodeId)
    {
        $parameters   = ['token' => $this->token, 'key' => $this->apiKey, 'v' => '2.4', 'id' => $episodeId];
        $watchedQuery = $this->apiBasePath.'episodes/watched';

        return $this->query(self::HTTP_POST, $watchedQuery, ['form_params' => $parameters]);
    }

    /**
     * @param int    $episodeId
     * @param string $language
     *
     * @throws \Exception
     *
     * @return StdClass
     */
    public function getSubtitleByEpisodeId($episodeId, $language = 'vo')
    {
        $parameters    = ['token' => $this->token, 'key' => $this->apiKey, 'v' => '2.4', 'id' => $episodeId, 'language' => $language];
        $subtitleQuery = $this->apiBasePath.'subtitles/episode?'.http_build_query($parameters);

        return $this->query(self::HTTP_GET, $subtitleQuery);
    }

    /**
     * @throws \Exception
     *
     * @return bool
     */
    private function authenticate()
    {
        $authenticateQuery = $this->apiBasePath.'members/auth';
        $parameters        = ['login' => $this->login, 'password' => $this->passwordHash, 'key' => $this->apiKey, 'v' => '2.4'];

        $response    = $this->query(self::HTTP_POST, $authenticateQuery, ['form_params' => $parameters]);
        $this->token = $response->token;

        return true;
    }

    /**
     * @param string $login
     */
    public function setLogin($login)
    {
        $this->login = $login;
    }

    /**
     * @param string $passwordHash
     */
    public function setPasswordHash($passwordHash)
    {
        $this->passwordHash = $passwordHash;
    }
}
