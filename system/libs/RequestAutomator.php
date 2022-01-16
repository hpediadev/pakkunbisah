<?php
/**
 * PHPMyLicense Development Platform.
 * User: giova
 * Date: 14/11/2018
 * Time: 20:24
 * Project: phpmylicense
 */

class RequestAutomator
{
    private $engine;

    public function get($endpoint)
    {
        $client = new \GuzzleHttp\Client();
        $res = $client->request('GET', $endpoint);
        return $res->getBody()->getContents();
    }

}