<?php
namespace Sfynx\DddGeneratorBundle\Generator\Api\Generator;

use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Tests\Application\Entity\Command\CommandTestHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Tests\Application\Entity\Command\Handler\CommandHandlerTestHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Tests\Application\Entity\Command\Handler\Decorator\{
    CommandHandlerDecoratorTestHandler
};

use Symfony\Component\Console\Output\OutputInterface;

//Commands
use Sfynx\DddGeneratorBundle\Generator\Api\DddApiGenerator;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Application\Command\CommandHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Application\Command\DeleteCommandHandler;
//Command Handlers
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Application\Command\Handler\CommandHandlerHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Application\Command\Handler\DeleteCommandHandlerHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Application\Command\Handler\DeleteManyCommandHandlerHandler;
//Command Handler Decorators
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Application\Command\Handler\Decorator\CommandHandlerDecoratorHandler;
//Command Validation SpecHandlers
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Application\Command\Validation\SpecHandler\CommandSpecHandler;
//Command Validation ValidationHandlers
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Application\Command\Validation\ValidationHandler\{
    CommandValidationHandler
};

// Use for the Query and QueryHandler
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Application\Query\Handler\QueryHandlerHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Application\Query\QueryHandler;

// Tests
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Tests\Application\Entity\Command\Handler\{
    DeleteCommandHandlerTestHandler
};

use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Tests\Application\Entity\Command\Handler\{
    DeleteManyCommandHandlerTestHandler
};

class Application
{
    const COMMANDS_LIST = ['update', 'new', 'delete', 'patch'];
    const QUERIES_LIST = ['get', 'getAll', 'searchBy', 'getByIds'];

    /** @var DddApiGenerator  */
    protected $generator;
    /** @var array  */
    protected $entities = [];
    /** @var array  */
    protected $entitiesToCreate = [];
    /** @var array  */
    protected $valueObjects = [];
    /** @var array  */
    protected $valueObjectsToCreate = [];
    /** @var array  */
    protected $paths = [];
    /** @var array  */
    protected $pathsToCreate = [];
    /** @var string  */
    protected $rootDir;
    /** @var string  */
    protected $projectDir;
    /** @var string */
    protected $destinationPath;
    /** @var OutputInterface  */
    protected $output;
    /** @var array  */
    protected $parameters;
    /** @var array  */
    protected $commandsQueriesList;

    /**
     * Application constructor.
     * @param DddApiGenerator $generator
     * @param $entities
     * @param $entitiesToCreate
     * @param $valueObjects
     * @param $valueObjectsToCreate
     * @param $paths
     * @param $pathsToCreate
     * @param $rootDir
     * @param $projectDir
     * @param $destinationPath
     * @param OutputInterface $output
     */
    public function __construct(
        DddApiGenerator $generator,
        $entities,
        $entitiesToCreate,
        $valueObjects,
        $valueObjectsToCreate,
        $paths,
        $pathsToCreate,
        $rootDir,
        $projectDir,
        $destinationPath,
        OutputInterface $output
    ) {
        $this->generator = $generator;
        $this->destinationPath = $destinationPath;
        $this->output = $output;
        $this->entities = $entities;
        $this->entitiesToCreate = $entitiesToCreate;
        $this->valueObjects = $valueObjects;
        $this->valueObjectsToCreate = $valueObjectsToCreate;
        $this->paths = $paths;
        $this->pathsToCreate = $pathsToCreate;
        $this->commandsQueriesList = $this->parseRoutes();
        $this->projectDir = $projectDir;
        $this->rootDir = $rootDir;

        $this->parameters = [
            'rootDir' => $this->rootDir . '/src',
            'projectDir' => $this->projectDir,
            'projectName' => str_replace('src/', '', $this->projectDir),
            'valueObjects' => $this->valueObjects,
            'destinationPath' => $this->destinationPath,
        ];
    }

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

    public function parseRoutes()
    {
        $routes = ['commands' => [], 'queries' => []];

        foreach ($this->pathsToCreate as $route => $verbData) {
            foreach ($verbData as $verb => $data) {
                in_array($data['action'], self::COMMANDS_LIST)
                    ? $routes['commands'][] = $data
                    : $routes['queries'][] = $data;
            }
        }

        return $routes;
    }

    public function generateCommands()
    {
        foreach ($this->commandsQueriesList['commands'] as $data) {
            $constructorParams = '';
            $managerArgs = '';

            //$this->parameters['actionName'] = ucfirst(strtolower($data['action']));
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
                $this->generator->addHandler(new CommandHandler($this->parameters));
                // Decorator
                $this->generator->addHandler(new CommandHandlerDecoratorHandler($this->parameters));
                // Handler
                $this->generator->addHandler(new CommandHandlerHandler($this->parameters));
                // SpecHandler
                $this->generator->addHandler(new CommandSpecHandler($this->parameters));
                // ValidationHandler
                $this->generator->addHandler(new CommandValidationHandler($this->parameters));
            } else {
                // Command
                $this->generator->addHandler(new DeleteCommandHandler($this->parameters));
                // Handler
                $this->generator->addHandler(new DeleteManyCommandHandlerHandler($this->parameters));
                $this->generator->addHandler(new DeleteCommandHandlerHandler($this->parameters));
            }

            $this->generator->execute();
            $this->generator->clear();
        }
    }

    public function generateQueries()
    {
        foreach ($this->commandsQueriesList['queries'] as $data) {
            $templateStringParameters = '';

            //$this->parameters['actionName'] = ucfirst(strtolower($data['action']));
            $this->parameters['actionName'] = ucfirst($data['action']);
            $this->parameters['entityName'] = ucfirst(strtolower($data['entity']));
            $this->parameters['entityFields'] = $this->entities[$data['entity']];
            $this->parameters['fields'] = $this->entities[$data['entity']]; //todo: unify these entityFields and fields

            $this->output->writeln(' - ' . $this->parameters['actionName'] . ' - ');

            if (in_array($data['action'], self::QUERIES_LIST)) {
                foreach ($this->entities[$data['entity']] as $field) {
                    $templateStringParameters .= '$' . $field['name'] . ', ';
                }
            }

            $this->parameters['constructorArgs'] = trim($templateStringParameters, ', ');
            $this->parameters['managerArgs'] = trim($templateStringParameters, ', ');

            $this->generator->addHandler(new QueryHandler($this->parameters));
            $this->generator->addHandler(new QueryHandlerHandler($this->parameters));

            $this->generator->execute();
            $this->generator->clear();
        }
    }

    public function generateTests()
    {
        foreach ($this->commandsQueriesList['commands'] as $data) {
            $constructorParams = '';
            $managerArgs = '';

            //$this->parameters['actionName'] = ucfirst(strtolower($data['action']));
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
        //foreach ($this->commandsQueriesList['queries'] as $data) {
        //}
    }
}
