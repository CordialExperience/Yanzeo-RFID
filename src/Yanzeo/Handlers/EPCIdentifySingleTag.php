<?php

namespace Yanzeo\Handlers;

use GuzzleHttp\Exception\RequestException;

class EPCIdentifySingleTag extends AbstractHandler
{
    /** @var int $command */
    protected $command = 0x10;

    /** @var string $readerName */
    protected $readerName;

    /** @var \Cordial\API $client */
    protected static $client = null;

    /**
     * EPCIdentifySingleTag constructor.
     * @param string $readerName
     */
    public function __construct($readerName='default')
    {
        $this->readerName = $readerName;
        static::$client = new \Cordial\API();
    }

    /**
     * Binary handler
     * @param $data
     */
    public function handle($data)
    {
        /* Sample Data

        Structure:    [Header] [Reader Addr] [CMD1] [CMD2] [Length] [Antenna]
        Byte Number:  1        2  3          4      5      6        7
        Data Sample:  cc       ff ff         10     32     0d       01

        Structure:    [EPC Data]                          [Checksum]
        Byte Number:  8  9  10 11 12 13 14 15 16 17 18 19 20
        Data Sample:  e2 80 11 60 60 00 02 09 97 58 7a 7c c3

        */

        $id = substr($data, 7, 12);

        $rfid = bin2hex($id);

        try {
            static::$client->activity($this->readerName, $rfid);
        } catch (RequestException $e) {

        }
    }
}