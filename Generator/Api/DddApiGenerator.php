<?php
declare(strict_types = 1);

namespace Sfynx\DddGeneratorBundle\Generator\Api;

use Sfynx\DddGeneratorBundle\Generator\Generalisation\HandlerInterface;

/**
 * Class DddApiGenerator
 *
 * @category Generator
 * @package Api
 */
class DddApiGenerator
{
    /** @var HandlerInterface[] */
    protected $handlers = [];

    /**
     * Add handler to $handlers if it is not included. Else do nothing.
     *
     * @param HandlerInterface $handler
     * @param bool $force
     */
    public function addHandler(HandlerInterface $handler, bool $force = true)
    {
        if (!$this->hasHandler($handler) || true === $force) {
            $this->handlers[] = $handler;
        }
    }

    /**
     * Call execute method for all handlers.
     * @return DddApiGenerator
     */
    public function execute(): self
    {
        foreach ($this->handlers as $handler) {
            $handler->execute();
            $this->shiftHandler();
        }

        return $this;
    }

    /**
     * Clear the handlers list.
     * @return DddApiGenerator
     */
    public function clear(): self
    {
        $this->handlers = [];
        return $this;
    }

    /**
     * Check if the handlers list is empty.
     * @return bool
     */
    public function isCleared(): bool
    {
        return ($this->handlers === []);
    }

    /**
     * Remove the first handler of the current handlers list.
     * @return DddApiGenerator
     */
    public function shiftHandler(): self
    {
        if (!$this->isCleared()) {
            array_shift($this->handlers);
        }

        return $this;
    }

    /**
     * Return true if $handler is already in $handlers. False otherwise.
     *
     * @param HandlerInterface $handler
     * @return bool
     */
    protected function hasHandler(HandlerInterface $handler)
    {
        $class = get_class($handler);
        foreach ($this->handlers as $handler) {
            $classIncluded = get_class($handler);
            if ($classIncluded === $class) {
                return true;
            }
        }

        return false;
    }
}
