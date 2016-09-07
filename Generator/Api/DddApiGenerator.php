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
     */
    public function addHandler(HandlerInterface $handler)
    {
        $this->handlers[] = $handler;
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
}
