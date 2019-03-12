<?php

namespace Cordial;

use GuzzleHttp\Client;

class API
{
    const REPORT_MAX_INTERVAL = 15;

    /** @var string $apiKey */
    protected $apiKey = "";

    /** @var Client $client  */
    protected $client = null;

    /** @var string $baseURI */
    protected $baseURI = 'https://api.cordial.io/v1';

    /** @var array $recents */
    protected $recents = [];

    /** @var int $counter */
    protected $counter = 0;

    /**
     * API constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->apiKey = $this->findApiKey();
        $this->client = new Client([
            'auth' => [$this->apiKey, ''],
            'headers' => [
                'Content-Type' => 'application/json'
            ]
        ]);
    }

    /**
     * Send contactactivities POST request for RFID tag and Reader
     * @param $a
     * @param $rfid
     * @return bool
     */
    public function activity($a, $rfid)
    {
        $ats = time();
        if (!empty($this->recents[$rfid]) && $this->recents[$rfid] > $ats - self::REPORT_MAX_INTERVAL) {
                return false;
        }

        $this->recents[$rfid] = $ats;

        $body = [
            'a' => $a,
            'rfid' => $rfid
        ];

        echo ++$this->counter . ": " . json_encode($body) . "\n";

        try {
            return $this->client->post($this->baseURI . '/contactactivities', ['body' => json_encode($body)]);
        } catch (\Exception $e) {
            print_r($e->getMessage());
        }
    }

    /**
     * Search memory for API key
     * @return mixed
     * @throws \Exception
     */
    protected function findApiKey()
    {
        if (!empty($_ENV['CORDIAL_API_KEY'])) {
            return $_ENV['CORDIAL_API_KEY'];
        }

        if (!empty($_SERVER['CORDIAL_API_KEY'])) {
            return $_SERVER['CORDIAL_API_KEY'];
        }

        throw new \Exception('Cordial API key not specified.  Set environment variable CORDIAL_API_KEY before continuing.');
    }
}