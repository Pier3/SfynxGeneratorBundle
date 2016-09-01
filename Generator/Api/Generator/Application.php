<?php
declare(strict_types=1);

namespace Sfynx\DddGeneratorBundle\Generator\Api\Generator;

//Commands
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Application\Command\{
    CommandHandler,
    Handler\CommandHandlerHandler,
    Handler\Decorator\CommandHandlerDecoratorHandler,
    Validation\SpecHandler\CommandSpecHandler,
    Validation\ValidationHandler\CommandValidationHandler
};

// Queries
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Application\Query\{
    QueryHandler,
    Handler\QueryHandlerHandler
};

// Tests
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Tests\{
    Application\Entity\Command\CommandTestHandler,
    Application\Entity\Command\Handler\CommandHandlerTestHandler,
    Application\Entity\Command\Handler\Decorator\CommandHandlerDecoratorTestHandler,
    Application\Entity\Command\Handler\DeleteCommandHandlerTestHandler,
    Application\Entity\Command\Handler\DeleteManyCommandHandlerTestHandler
};

/**
 * Class Application
 *
 * @category Generator
 * @package Api
 * @subpackage Generator
 */
class Application extends LayerAbstract
{
    /**
     * Entry point of the generation of the "Application" layer in DDD.
     * Call the generation of the Commands, the Queries and the Tests of the "Application" layer.
     */
    public function generate()
    {
        $this->output->writeln('');
        $this->output->writeln('##############################################');
        $this->output->writeln('#       GENERATE APPLICATION STRUCTURE       #');
        $this->output->writeln('##############################################');
        $this->output->writeln('');

        $this->output->writeln('### COMMANDS GENERATION ###');
        $this->generateCommands();
        $this->output->writeln('### QUERIES GENERATION ###');
        $this->generateQueries();
        $this->output->writeln('### TESTS GENERATION ###');
        $this->generateTests();
    }

    /**
     * Generate the Command part in the "Application" layer.
     */
    protected function generateCommands()
    {
        foreach ($this->commandsQueriesList[self::COMMAND] as $data) {
            $this->parameters['actionName'] = ucfirst($data['action']);
            $this->parameters['entityName'] = ucfirst(strtolower($data['entity']));
            $this->parameters['entityFields'] = $this->entities[$data['entity']];
            $this->parameters['constructorArgs'] = $this->buildConstructorParamsString($data['entity']);

            // Command
            $this->generator->addHandler(new CommandHandler($this->parameters), true);
            // Decorator
            $this->generator->addHandler(new CommandHandlerDecoratorHandler($this->parameters), true);
            // Handler
            $this->generator->addHandler(new CommandHandlerHandler($this->parameters), true);
            // SpecHandler
            $this->generator->addHandler(new CommandSpecHandler($this->parameters), true);
            // ValidationHandler
            $this->generator->addHandler(new CommandValidationHandler($this->parameters), true);
        }

        $this->generator->execute()->clear();
    }

    /**
     * Generate the Query part in the "Application" layer.
     */
    protected function generateQueries()
    {
        foreach ($this->commandsQueriesList[self::QUERY] as $data) {
            $this->parameters['actionName'] = ucfirst($data['action']);
            $this->parameters['entityName'] = ucfirst(strtolower($data['entity']));
            $this->parameters['entityFields'] = $this->entities[$data['entity']];

            $this->generator->addHandler(new QueryHandler($this->parameters), true);
            $this->generator->addHandler(new QueryHandlerHandler($this->parameters), true);
        }

        $this->generator->execute()->clear();
    }

    /**
     * Generate the tests for the whole "Application" layer.
     */
    protected function generateTests()
    {
        foreach ($this->commandsQueriesList[self::COMMAND] as $data) {
            $constructorParams = '';
            $managerArgs = '';

            $this->parameters['actionName'] = ucfirst($data['action']);
            $this->parameters['entityName'] = ucfirst(strtolower($data['entity']));
            $this->parameters['entityFields'] = $this->entities[$data['entity']];
            $this->parameters['fields'] = $this->entities[$data['entity']]; //todo: unify these entityFields and fields

            $this->output->writeln(' - ' . $this->parameters['actionName'] . ' - ');

            foreach ($this->entities[$data['entity']] as $field) {
                $constructorParams .= '$' . $field['name'] . ', ';

                if (('new' === $data['action'] && 'id' !== $field['type']) || ('new' !== $data['action'])) {
                    $managerArgs .= '$' . $field['name'] . ', ';
                }
            }

            $this->parameters['constructorArgs'] = trim($constructorParams, ', ');
            $this->parameters['managerArgs'] = trim($managerArgs, ', ');

            if ('Delete' !== $this->parameters['actionName']) {
                // Command
                $this->generator->addHandler(new CommandTestHandler($this->parameters));
                // Decorator
                $this->generator->addHandler(new CommandHandlerDecoratorTestHandler($this->parameters));
                // Handler
                $this->generator->addHandler(new CommandHandlerTestHandler($this->parameters));

                // Todo : create SpecHandler Test
                //$this->generator->addHandler(new CommandSpecTestHandler($this->parameters));

                // Todo : create ValidationHandler Test
                //$this->generator->addHandler(new CommandValidationTestHandler($this->parameters));
            } else {
                // Command
                $this->generator->addHandler(new DeleteCommandHandlerTestHandler($this->parameters));
                // Handler
                $this->generator->addHandler(new DeleteManyCommandHandlerTestHandler($this->parameters));
                $this->generator->addHandler(new DeleteCommandHandlerTestHandler($this->parameters));
            }

            $this->generator->execute();
            $this->generator->clear();
        }

        // TODO : do tests for queries, like commands
        //foreach ($this->commandsQueriesList[self::QUERY] as $data) {
        //}
    }
}
