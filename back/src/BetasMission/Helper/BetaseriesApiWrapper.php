<?php

namespace BetasMission\Helper;

use stdClass;

/**
 * Class BetaseriesApiWrapper.
 */
class BetaseriesApiWrapper
{
    const API_BASE_PATH = 'https://api.betaseries.com/';
    const LOGIN         = 'ndreux';
    const PASSWORD_HASH = '370328edae152a82a8bc0970c9bfb20e';
    const API_KEY       = 'c8bb2471b101';

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
    private $apiUrl;

    /**
     * @var string
     */
    private $token;

    /**
     * @param string $login
     * @param string $passwordHash
     * @param string $apiKey
     * @param string $apiUrl
     */
    public function __construct($login = self::LOGIN, $passwordHash = self::PASSWORD_HASH, $apiKey = self::API_KEY, $apiUrl = self::API_BASE_PATH)
    {
        $this->login        = $login;
        $this->apiKey       = $apiKey;
        $this->passwordHash = $passwordHash;
        $this->apiUrl       = $apiUrl;
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

        $parameters  = ['token' => $this->token, 'key' => self::API_KEY, 'v' => '2.4', 'file' => $episodeFileName];
        $searchQuery = $this->apiUrl.'episodes/scraper?'.http_build_query($parameters);

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

        $parameters  = ['token' => $this->token, 'key' => self::API_KEY, 'v' => '2.4', 'id' => $episodeId];
        $searchQuery = $this->apiUrl.'episodes/downloaded';

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

        $parameters   = ['token' => $this->token, 'key' => self::API_KEY, 'v' => '2.4', 'id' => $episodeId];
        $watchedQuery = $this->apiUrl.'episodes/watched';

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

        $parameters  = ['token' => $this->token, 'key' => self::API_KEY, 'v' => '2.4', 'id' => $episodeId, 'language' => $language];
        $searchQuery = $this->apiUrl.'subtitles/episode?'.http_build_query($parameters);

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
        $authenticateUrl = $this->apiUrl.'members/auth';
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
}
