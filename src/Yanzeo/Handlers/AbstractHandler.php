<?php

namespace Yanzeo\Handlers;


abstract class AbstractHandler
{
    /** @var int $command */
    protected $command = 0;

    /**
     * Get the binary string representing the command
     * @return string
     */
    public function getCommand()
    {
        return pack('C', $this->command);
    }
}