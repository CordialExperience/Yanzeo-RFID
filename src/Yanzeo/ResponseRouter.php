<?php

namespace Yanzeo;

class ResponseRouter
{
    /** @var array $handlers */
    protected $handlers = [];

    /**
     * Default handler that deciphers command and routes response to correct handler
     * @param $data
     */
    public function route($data)
    {
        $cmd1 = bin2hex(substr($data, 3, 1));

        if (!isset($this->handlers[$cmd1])) {
            return;
        }

        $this->handlers[$cmd1]->handle($data);
    }

    /**
     * Register a handler based on CMD1
     * @param $handler
     */
    public function registerHandler($handler)
    {
        $this->handlers[bin2hex($handler->getCommand())] = $handler;
    }
}