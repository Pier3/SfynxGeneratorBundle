<?php
declare(strict_types = 1);

namespace Sfynx\DddGeneratorBundle\Generator\Generalisation;

/**
 * Interface HandlerInterface
 *
 * @category Generator
 * @package Generalisation
 */
interface HandlerInterface
{
    /**
     * Execute the handler to render a template with parameters into a target file.
     */
    public function execute();
}
