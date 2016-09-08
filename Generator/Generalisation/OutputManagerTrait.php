<?php
declare(strict_types = 1);

namespace Sfynx\DddGeneratorBundle\Generator\Generalisation;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * Trait OutputManagerTrait.
 *
 * @category Generator
 * @package Generalisation
 */
trait OutputManagerTrait
{
    /** @var OutputInterface */
    protected $output;

    /**
     * Set the Output object in the manager.
     *
     * @param OutputInterface $output
     * @return $this
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
        return $this;
    }

    /**
     * Write in the console depending on the verbosity level. By default, only write if mode verbose is activated.
     *
     * @param string $message
     * @param int $options
     * @return $this
     */
    public function writeln(string $message, int $options = OutputInterface::VERBOSITY_VERBOSE)
    {
        $this->output->writeln($message, $options);
        return $this;
    }

    /**
     * Write in the console errors and exceptions. Access method for better readability.
     *
     * @param string $message
     * @param int $options
     * @return $this
     */
    public function errWriteln(string $message, int $options = OutputInterface::VERBOSITY_NORMAL)
    {
        $this->output->writeln($message, $options);
        return $this;
    }
}
