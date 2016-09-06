<?php
declare(strict_types = 1);

namespace Sfynx\DddGeneratorBundle\Generator\Api\Generator;

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
    /** @var string */
    protected static $handlersFileName = 'application.yml';

    /** @var string */
    protected static $skeletonDir = 'Api/Application';

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

        try {
            $this->output->writeln('### COMMANDS GENERATION ###');
            $this->generateCommands();
            $this->output->writeln('### QUERIES GENERATION ###');
            $this->generateQueries();
            $this->output->writeln('### TESTS GENERATION ###');
            //TODO: work on the generation of the tests.
            //$this->generateTests();
        } catch (\InvalidArgumentException $e) {
            fwrite(STDERR, $e->getMessage());
            exit;
        }
    }

    /**
     * Generate the Command part in the "Application" layer.
     * @throws \InvalidArgumentException
     */
    protected function generateCommands()
    {
        foreach ($this->commandsQueriesList[self::COMMAND] as $data) {
            $this->parameters['actionName'] = ucfirst($data['action']);
            $this->parameters['entityName'] = ucfirst(strtolower($data['entity']));
            $this->parameters['entityFields'] = $this->entitiesToCreate[$data['entity']];
            $this->parameters['constructorArgs'] = $this->buildConstructorParamsString($data['entity']);

            $this->addHandlers(
                'commandHandler',
                'commandHandlerDecoratorHandler',
                'commandHandlerHandler',
                'commandSpecHandler',
                'commandValidationHandler'
            );
        }

        $this->generator->execute()->clear();
    }

    /**
     * Generate the Query part in the "Application" layer.
     * @throws \InvalidArgumentException
     */
    protected function generateQueries()
    {
        foreach ($this->commandsQueriesList[self::QUERY] as $data) {
            $this->parameters['actionName'] = ucfirst($data['action']);
            $this->parameters['entityName'] = ucfirst(strtolower($data['entity']));
            $this->parameters['entityFields'] = $this->entitiesToCreate[$data['entity']];

            $this->addHandlers('queryHandler', 'queryHandlerHandler');
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
            $this->parameters['entityFields'] = $this->entitiesToCreate[$data['entity']];

            $this->output->writeln(' - ' . $this->parameters['actionName'] . ' - ');

            foreach ($this->entitiesToCreate[$data['entity']] as $field) {
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
