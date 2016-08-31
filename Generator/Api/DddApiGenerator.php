<?php

namespace Sfynx\DddGeneratorBundle\Generator\Api;

use Sfynx\DddGeneratorBundle\Generator\Generalisation\HandlerInterface;

class DddApiGenerator
{
    protected $handlers = [];

    /**
     * add handler to $handlers if it is not included. Else do nothing
     *
     * @param HandlerInterface $handler
     * @param bool $force
     */
    public function addHandler(HandlerInterface $handler, $force = false)
    {
        if (!$this->hasHandler($handler) || true === $force) {
            $this->handlers[] = $handler;
        }
    }

    /**
     * call execute method in all handlers
     */
    public function execute()
    {
        foreach ($this->handlers as $handler) {
            $handler->execute();
            $this->shiftHandler();
        }
    }


    public function clear()
    {
        $this->handlers = [];
    }

    public function isCleared()
    {
        return ($this->handlers === []);
    }

    public function shiftHandler()
    {
        if (!$this->isCleared()) {
            array_shift($this->handlers);
        }
    }

    /**
     * return true if $handler is already in $handlers. false if it's not
     *
     * @param HandlerInterface $handler
     * @return bool
     */
    protected function hasHandler(HandlerInterface $handler)
    {
        $class = get_class($handler);
        foreach ($this->handlers as $handler) {
            $classIncluded = get_class($handler);
            if ($classIncluded === $class){
                return true;
            }
        }

        return false;
    }
}
