<?php

namespace BetasMissionBundle\ApiWrapper;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;

abstract class AbstractApiWrapper
{
    const HTTP_GET  = 'get';
    const HTTP_POST = 'post';

    /**
     * @var string
     */
    protected $clientId;
    /**
     * @var string
     */
    protected $clientSecret;
    /**
     * @var string
     */
    protected $apiBasePath;
    /**
     * @var string
     */
    protected $accessToken;
    /**
     * @var string
     */
    protected $refreshToken;
    /**
     * @var string
     */
    protected $applicationPin;

    /**
     * @var string
     */
    protected $login;

    /**
     * @var string
     */
    protected $passwordHash;

    /**
     * @var string
     */
    protected $apiKey;

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
        if (!in_array($method, ['post', 'get'])) {
            throw new \Exception('Bad method');
        }

        /** @var Response $response */
        $response = (new Client())->{$method}($uri, $options);

        if (!in_array($response->getStatusCode(), [200, 201])) {
            throw new \Exception('API call did not return a valid response');
        }

        return json_decode($response->getBody()->getContents());
    }
}
