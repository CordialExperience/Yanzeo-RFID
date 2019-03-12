<?php

namespace Yanzeo\Commands;

abstract class AbstractCommand
{
    protected $header = 0x7C;
    protected $readerAddressLSB = 0xFF;
    protected $readerAddressMSB = 0xFF;
    protected $cid1 = 0x00;
    protected $cid2 = 0x00;
    protected $length = 0;
    protected $data = "";

    public function __construct()
    {
        $this->data = pack('C*', $this->header, $this->readerAddressLSB, $this->readerAddressMSB, $this->cid1, $this->cid2);
    }

    public static function checksum($buffer)
    {
        $sum = 0;

        foreach ($buffer as $byte) {
            $sum += $byte;
        }
        $sum = (~$sum) + 1;

        return $sum & 0xFF;
    }

    protected function getDataLength()
    {
        return strlen($this->data);
    }

    public function getData()
    {
        return $this->data;
    }
}