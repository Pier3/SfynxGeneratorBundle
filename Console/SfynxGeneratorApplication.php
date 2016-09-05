<?php

namespace Sfynx\DddGeneratorBundle\Console;

use Sfynx\DddGeneratorBundle\Command\GenerateDddApiCommand;
use Sfynx\DddGeneratorBundle\Command\GenerateDddStructureCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

class SfynxGeneratorApplication extends Application
{
    /**
     * Gets the name of the command based on input.
     *
     * @param InputInterface $input The input interface
     *
     * @return string The command name
     */
    protected function getCommandName(InputInterface $input)
    {
        //sfynx:generate:ddd:api

        $available = ['sfynx:api'];
        $arg = $input->getFirstArgument();
        if (!in_array($arg, $available) ||'sfynx:api' === $arg) {
            // default argument : we don't want to provide the name of the command by default
            $inputDefinition = $this->getDefinition();
            $inputDefinition->setArguments();
            $this->setDefinition($inputDefinition);
            return 'sfynx:api';
        }
        return $arg;
    }

    /**
     * Gets the default commands that should always be available.
     *
     * @return Command[] An array of default Command instances
     */
    protected function getDefaultCommands()
    {
        // Keep the core default commands to have the HelpCommand
        // which is used when using the --help option
        $defaultCommands = parent::getDefaultCommands();

        $defaultCommands[] = new GenerateDddApiCommand();

        return $defaultCommands;
    }
}
