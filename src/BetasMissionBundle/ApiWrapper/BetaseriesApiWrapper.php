<?php

namespace BetasMissionBundle\ApiWrapper;

use stdClass;

/**
 * Class BetaseriesApiWrapper.
 */
class BetaseriesApiWrapper
{
    /**
     * @var string
     */
    private $login;

    /**
     * @var string
     */
    private $passwordHash;

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var string
     */
    private $apiBasePath;

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
        if ($this->token === null) {
            $this->authenticate();
        }

        $parameters  = ['token' => $this->token, 'key' => $this->apiKey, 'v' => '2.4', 'file' => $episodeFileName];
        $searchQuery = $this->apiBasePath.'episodes/scraper?'.http_build_query($parameters);

        $curlResource = curl_init();

        curl_setopt($curlResource, CURLOPT_URL, $searchQuery);
        curl_setopt($curlResource, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($curlResource);

        if (curl_getinfo($curlResource, CURLINFO_HTTP_CODE) !== 200) {
            throw new \Exception('API call did not return 200');
        }
        curl_close($curlResource);

        return json_decode($result);
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
        if ($this->token === null) {
            $this->authenticate();
        }

        $parameters  = ['token' => $this->token, 'key' => $this->apiKey, 'v' => '2.4', 'id' => $episodeId];
        $searchQuery = $this->apiBasePath.'episodes/downloaded';

        $curlResource = curl_init();

        curl_setopt($curlResource, CURLOPT_URL, $searchQuery);
        curl_setopt($curlResource, CURLOPT_POST, true);
        curl_setopt($curlResource, CURLOPT_POSTFIELDS, $parameters);
        curl_setopt($curlResource, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($curlResource);

        if (curl_getinfo($curlResource, CURLINFO_HTTP_CODE) !== 200) {
            throw new \Exception('API call did not return 200');
        }
        curl_close($curlResource);

        return json_decode($result);
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
        if ($this->token === null) {
            $this->authenticate();
        }

        $parameters   = ['token' => $this->token, 'key' => $this->apiKey, 'v' => '2.4', 'id' => $episodeId];
        $watchedQuery = $this->apiBasePath.'episodes/watched';

        $curlResource = curl_init();

        curl_setopt($curlResource, CURLOPT_URL, $watchedQuery);
        curl_setopt($curlResource, CURLOPT_POST, true);
        curl_setopt($curlResource, CURLOPT_POSTFIELDS, $parameters);
        curl_setopt($curlResource, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($curlResource);

        if (curl_getinfo($curlResource, CURLINFO_HTTP_CODE) !== 200) {
            throw new \Exception('API call did not return 200');
        }
        curl_close($curlResource);

        return json_decode($result);
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
        if ($this->token === null) {
            $this->authenticate();
        }

        $parameters  = ['token' => $this->token, 'key' => $this->apiKey, 'v' => '2.4', 'id' => $episodeId, 'language' => $language];
        $searchQuery = $this->apiBasePath.'subtitles/episode?'.http_build_query($parameters);

        $curlResource = curl_init();

        curl_setopt($curlResource, CURLOPT_URL, $searchQuery);
        curl_setopt($curlResource, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($curlResource);

        if (curl_getinfo($curlResource, CURLINFO_HTTP_CODE) !== 200) {
            throw new \Exception('API call did not return 200');
        }
        curl_close($curlResource);

        return json_decode($result);
    }

    /**
     * @throws \Exception
     *
     * @return bool
     */
    private function authenticate()
    {
        $authenticateUrl = $this->apiBasePath.'members/auth';
        $parameters      = ['login' => $this->login, 'password' => $this->passwordHash, 'key' => $this->apiKey, 'v' => '2.4'];

        $curlResource = curl_init();

        curl_setopt($curlResource, CURLOPT_URL, $authenticateUrl);
        curl_setopt($curlResource, CURLOPT_POST, true);
        curl_setopt($curlResource, CURLOPT_POSTFIELDS, $parameters);
        curl_setopt($curlResource, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($curlResource);

        if (curl_getinfo($curlResource, CURLINFO_HTTP_CODE) !== 200) {
            throw new \Exception('API call did not return 200');
        }
        curl_close($curlResource);

        $apiReturn = json_decode($result);

        $this->token = $apiReturn->token;

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
