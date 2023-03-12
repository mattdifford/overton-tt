<?php

use GuzzleHttp\Client;

class Scraper
{
    public $client;
    public $base_uri;
    public $delay;

    public function __construct($data)
    {
        $this->base_uri = $data['base'];
        $this->delay = $data['delay'];
        $this->client = new Client([
            'base_uri' => $data['base'],
            'connect_timeout' => $data['connect_timeout'],
            'timeout' => $data['timeout'],
            'http_errors' => false,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/96.0.4664.110 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
            ]
        ]);
    }
}