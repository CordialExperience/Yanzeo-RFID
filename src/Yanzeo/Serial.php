<?php

namespace Yanzeo;


class Serial
{
    const RESPONSE_PREFIX = 0xCC;
    const NO_DATA_FLUSH_INTERVAL = 1;

    /** @var resource $serial  */
    protected $serial = null;

    /** @var string $device */
    protected $device = null;

    /** @var string $raw */
    protected $raw = null;

    /** @var int $readerAddress */
    protected $readerAddress;

    /** @var string $responseHeader */
    protected $responseHeader;

    /** @var int $lastData */
    protected $lastData = 0;

    /**
     * Serial constructor.
     * @param string $device
     * @param int $readerAddress
     */
    public function __construct($device, $readerAddress = 0xFFFF)
    {
        $this->device = $device;
        $this->readerAddress = $readerAddress;
        $this->responseHeader = pack('CS', self::RESPONSE_PREFIX, $readerAddress);
    }

    /**
     * Read data.  Must be called in a loop repeatedly to read everything.
     * @param $handler
     * @return int
     */
    public function read($handler)
    {
        usleep(100000);

        $startLen = strlen($this->raw);
        while(true) {
            $byte = dio_read($this->serial(), 1);
            if (is_null($byte)) {
                break;
            }

            // Store the timestamp of the last time we received data so we can flush it later
            $this->lastData = microtime(true);
            $this->raw .= $byte;
        }

        if (strlen($this->raw) == $startLen && !$this->shouldFlush()) {
            return 0;
        }

        return $this->parseResponses($handler);
    }

    /**
     * Decides whether we should flush the binary we've received to the handler
     * @return bool
     */
    protected function shouldFlush()
    {
        if (empty($this->lastData)) {
            return false;
        }

        return ($this->lastData < microtime(true) - self::NO_DATA_FLUSH_INTERVAL);
    }

    /**
     * Return the next complete, valid response from the binary stream
     * @return bool|string
     */
    protected function getNextResponse()
    {
        $responseStartPos = strpos($this->raw, $this->responseHeader);

        if (strlen($this->raw) <= $responseStartPos + strlen($this->responseHeader)) {
            return false;
        }

        $nextResponsePos = strpos($this->raw, $this->responseHeader, $responseStartPos + 3);

        if (false !== $nextResponsePos) {
            $responseData = substr($this->raw, 0, $nextResponsePos);
            $this->raw = substr($this->raw, $nextResponsePos);
        } elseif ($this->shouldFlush()) {
            $responseData = $this->raw;
            $this->raw = "";
        } else {
            return false;
        }

        $data = substr($responseData, 0, strlen($responseData)-1);
        $checksum = substr($responseData,-1);

        /* Some debug code
        echo "checksum: " . bin2hex($checksum) . "\n";
        echo "calc checksum: " . bin2hex(self::checksum($data)) . "\n";
        echo "data: " . bin2hex($data) . "\n";
        */
        if (self::checksum($data) != $checksum) {
            return false;
        }

        return $responseData;

    }

    /**
     * Send each response to the handler
     * @param $handler
     * @return int
     */
    protected function parseResponses($handler)
    {
        $responses = 0;

        while ($response = $this->getNextResponse()) {
            $handler($response);
        }

        return $responses;
    }

    /**
     * Send a specific command to the reader
     * @param Command $command
     * @param int $delay
     * @return mixed
     */
    public function send(Command $command, $delay = 1000000)
    {
        $bytes = dio_write($this->serial(), $command->getCommandData());
        usleep($delay);
        return $bytes;
    }

    /**
     * Calculate a checksum based on the buffer
     * @param $buffer
     * @return string
     */
    public static function checksum($buffer)
    {
        $sum = array_sum(unpack('C*', $buffer));
        $sum = (~$sum) + 1;

        $checksum = $sum & 0xFF;

        return pack('C', $checksum);
    }

    /**
     * initialize the serial port and keep track of it
     * @return resource
     */
    public function serial()
    {
        if (empty($this->serial)) {
            exec('stty -F ' . $this->device . ' 9600 raw -crtscts min 6 time 50');
            $this->serial = dio_open($this->device, O_RDWR | O_NOCTTY | O_NDELAY);
        }

        return $this->serial;
    }
}