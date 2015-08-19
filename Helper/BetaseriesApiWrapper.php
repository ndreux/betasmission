<?php

namespace Helper;

/**
 * Class BetaseriesApiWrapper.
 */
class BetaseriesApiWrapper
{
    const API_BASE_PATH = 'https://api.betaseries.com/';
    const LOGIN = 'ndreux';
    const PASSWORD_HASH = '370328edae152a82a8bc0970c9bfb20e';
    const API_KEY = 'c8bb2471b101';

    private $token;

    /**
     * @param $episodeFileName
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function getEpisodeData($episodeFileName)
    {
        if ($this->token === null) {
            $this->authenticate();
        }

        $parameters = ['token' => $this->token, 'key' => self::API_KEY, 'v' => '2.4', 'file' => $episodeFileName];
        $searchQuery = self::API_BASE_PATH.'episodes/scraper?'.http_build_query($parameters);

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
     * @param $episodeId
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function markAsDownloaded($episodeId)
    {
        if ($this->token === null) {
            $this->authenticate();
        }

        $parameters = ['token' => $this->token, 'key' => self::API_KEY, 'v' => '2.4', 'id' => $episodeId];
        $searchQuery = self::API_BASE_PATH.'episodes/downloaded';

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
     * @return mixed
     *
     * @throws \Exception
     */
    public function markAsWatched($episodeId)
    {
        if ($this->token === null) {
            $this->authenticate();
        }

        $parameters = ['token' => $this->token, 'key' => self::API_KEY, 'v' => '2.4', 'id' => $episodeId];
        $watchedQuery = self::API_BASE_PATH.'episodes/watched';

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
     * @param int $episodeId
     *
     * @return mixed
     * @throws \Exception
     */
    public function getSubtitleByEpisodeId($episodeId, $language = 'vo')
    {
        if ($this->token === null) {
            $this->authenticate();
        }

        $parameters = ['token' => $this->token, 'key' => self::API_KEY, 'v' => '2.4', 'id' => $episodeId, 'language' => $language];
        $searchQuery = self::API_BASE_PATH.'subtitles/episode?'.http_build_query($parameters);

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
     * @return bool
     *
     * @throws \Exception
     */
    private function authenticate()
    {
        $authenticateUrl = self::API_BASE_PATH.'members/auth';
        $parameters = ['login' => self::LOGIN, 'password' => self::PASSWORD_HASH, 'key' => self::API_KEY, 'v' => '2.4'];

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
