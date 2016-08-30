<?php
namespace Sfynx\DddGeneratorBundle\Generator\Api\Generator;

use Sfynx\DddGeneratorBundle\Generator\Api\DddApiGenerator;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Adapter\Command\AdapterHandler as AdapterCommandHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Adapter\Query\AdapterHandler as AdapterQueryHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Adapter\DeleteAdapterHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Adapter\DeleteManyAdapterHandler;

use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Presentation\Coordination\ControllerHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Request\RequestHandler;

//use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Tests\Presentation\Adapter\Entity\Command\DeleteCommandAdapterTestHandler;
//use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Tests\Presentation\Adapter\Entity\Command\NewCommandAdapterTestHandler;
//use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Tests\Presentation\Adapter\Entity\Command\PatchCommandAdapterTestHandler;
//use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Tests\Presentation\Adapter\Entity\Command\UpdateCommandAdapterTestHandler;
//use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Tests\Presentation\Coordination\Entity\Command\ControllerCommandTestHandler;
//use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Tests\Presentation\Coordination\Entity\Query\ControllerQueryTestHandler;
//use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Tests\Presentation\Request\Entity\Command\DeleteRequestTestHandler;
//use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Tests\Presentation\Request\Entity\Command\NewRequestTestHandler;
//use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Tests\Presentation\Request\Entity\Command\PatchRequestTestHandler;
//use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Tests\Presentation\Request\Entity\Command\UpdateRequestTestHandler;
//use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Tests\Presentation\Request\Entity\Query\GetAllRequestTestHandler;
//use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Tests\Presentation\Request\Entity\Query\GetRequestTestHandler;
//use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Tests\Presentation\Request\Entity\Query\SearchByRequestTestHandler;
//use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Tests\TraitVerifyResolverHandler;
use Symfony\Component\Console\Output\OutputInterface;

class Presentation
{
    const COMMANDS_LIST = ['update', 'new', 'delete', 'patch'];
    const QUERIES_LIST = ['get', 'getAll', 'searchBy', 'getByIds', 'findByName'];

    const COMMAND = 'Command';
    const QUERY = 'Query';

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
    /** @var array */
    protected $entitiesGroups;

    /**
     * Domain constructor.
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
        $this->output->writeln('#      GENERATE PRESENTATION STRUCTURE       #');
        $this->output->writeln('##############################################');
        $this->output->writeln('');

        $this->output->writeln('### COMMAND ADAPTERS GENERATION ###');
        $this->generateCommandsAdapter();
        $this->output->writeln('### QUERY ADAPTERS GENERATION ###');
        $this->generateQueriesAdapter();
        $this->output->writeln('### COORDINATION CONTROLLERS GENERATION ###');
        $this->generateCoordinationControllers();
        $this->output->writeln('### REQUESTS GENERATION ###');
        $this->generateRequest();
        $this->output->writeln('### TESTS GENERATION ###');
        $this->generateTests();
    }

    public function parseRoutes()
    {
        $routes = ['commands' => [], 'queries' => []];

        foreach ($this->pathsToCreate as $route => $verbData) {
            foreach ($verbData as $verb => $data) {
                $elements = $data;
                $elements['route'] = $route;
                $elements['verb'] = $verb;

                //Sort by entities and by group (command/query)
                $group = (in_array($data['action'], self::COMMANDS_LIST)) ? self::COMMAND : self::QUERY;
                $this->entitiesGroups[$data['entity']][$group][] = $elements;

                //Sort by group
                if (in_array($data['action'], self::COMMANDS_LIST)) {
                    $elements['group'] = self::COMMAND;
                    $routes['commands'][] = $elements;
                } else {
                    $elements['group'] = self::QUERY;
                    $routes['queries'][] = $elements;
                }
            }
        }

        return $routes;
    }

    public function generateCommandsAdapter()
    {
        foreach ($this->commandsQueriesList['commands'] as $data) {
            $this->parameters['actionName'] = ucfirst($data['action']);
            $this->parameters['entityName'] = ucfirst($data['entity']);
            $this->parameters['entityFields'] = $this->entities[$data['entity']];
            $this->parameters['constructorArgs'] = $this->buildConstructorParamsString($data['entity']);

            $this->generator->addHandler(new AdapterCommandHandler($this->parameters), true);
        }

        $this->generator->addHandler(new DeleteAdapterHandler($this->parameters), true);
        $this->generator->addHandler(new DeleteManyAdapterHandler($this->parameters), true);

        $this->generator->execute();
        $this->generator->clear();
    }

    public function generateQueriesAdapter()
    {
        foreach ($this->commandsQueriesList['queries'] as $data) {
            $this->parameters['actionName'] = ucfirst($data['action']);
            $this->parameters['entityName'] = ucfirst($data['entity']);
            $this->parameters['entityFields'] = $this->entities[$data['entity']];
            $this->parameters['constructorArgs'] = $this->buildConstructorParamsString($data['entity']);

            $this->generator->addHandler(new AdapterQueryHandler($this->parameters), true);
        }
        $this->generator->execute();
        $this->generator->clear();
    }

    public function generateCoordinationControllers()
    {
        foreach ($this->entitiesGroups as $entityName => $entityGroups) {
            $this->parameters['entityName'] = $entityName;

            //Command part
            $this->addCQRSCoordinationToGenerator($entityGroups, self::COMMAND);

            //Query part
            $this->addCQRSCoordinationToGenerator($entityGroups, self::QUERY);
        }

        $this->generator->execute();
        $this->generator->clear();
    }

    public function generateRequest()
    {
        foreach ($this->entitiesGroups as $entityName => $entityGroups) {
            $this->parameters['entityName'] = $entityName;
            $this->parameters['constructorArgs'] = $this->buildConstructorParamsString($entityName);
            $this->parameters['entityFields'] = $this->entities[$entityName];

            //Command Part
            $this->addCQRSRequestToGenerator($entityGroups, self::COMMAND);

            //Query Part
            $this->addCQRSRequestToGenerator($entityGroups, self::QUERY);
        }

        $this->generator->execute();
        $this->generator->clear();
    }

    public function generateTests()
    {
        //TODO: Big todo => to review for refactoring.
        /*
        foreach ($this->pathsToCreate as $route => $verbData) {
            foreach ($verbData as $verb => $data) {
                $controllers[$data['controller']][] = [
                    'action' => $data['action'], 'path' => $route, 'method' => $verb, 'entityName' => $data['entity']
                ];

                $this->parameters = [
                    'rootDir' => $this->rootDir . '/src',
                    'projectDir' => $this->projectDir,
                    'projectName' => str_replace('src/', '', $this->projectDir),
                    'actionName' => ucfirst($data['action']),
                    'entityName' => ucfirst($data['entity']),
                    'entityFields' => $this->entities[$data['entity']],
                    'destinationPath' => $this->destinationPath,
                ];

                $this->generator->addHandler(new UpdateCommandAdapterTestHandler($this->parameters));
                $this->generator->addHandler(new PatchCommandAdapterTestHandler($this->parameters));
                $this->generator->addHandler(new NewCommandAdapterTestHandler($this->parameters));
                $this->generator->addHandler(new DeleteCommandAdapterTestHandler($this->parameters));

                $this->generator->execute();
                $this->generator->clear();

                foreach ($controllers as $controller => $data) {


                    $this->parametersQuery = [
                        'rootDir' => $this->rootDir . '/src',
                        'projectDir' => $this->projectDir,
                        'projectName' => str_replace('src/', '', $this->projectDir),
                        'controllerName' => $controller,
                        'group' => self::QUERY,
                        'destinationPath' => $this->destinationPath,
                    ];

                    $this->parametersCommand = [
                        'rootDir' => $this->rootDir . '/src',
                        'projectDir' => $this->projectDir,
                        'projectName' => str_replace('src/', '', $this->projectDir),
                        'controllerName' => $controller,
                        'group' => self::COMMAND,
                        'destinationPath' => $this->destinationPath,
                    ];


                    foreach ($data as $action) {

                        if (in_array($action['action'], ['put', 'delete', 'update', 'new', 'patch'])) {
                            $this->parametersCommand['controllerData'][] = $action;
                            $this->parametersCommand['entityName'] = $action['entityName'];
                            $this->parametersCommand['destinationPath'] = $this->destinationPath;

                            $this->generator->addHandler(new ControllerCommandTestHandler($this->parametersCommand));
                            $this->generator->execute();
                            $this->generator->clear();

                        } else {
                            $this->parametersQuery['controllerData'][] = $action;
                            $this->parametersQuery['entityName'] = $action['entityName'];
                            $this->parametersQuery['destinationPath'] = $this->destinationPath;

                            $this->generator->addHandler(new ControllerQueryTestHandler($this->parametersQuery));
                            $this->generator->execute();
                            $this->generator->clear();

                        }
                        $controllerToCreate[$controller][$action['entityName']] = true;
                    }
                    $controllersToCreate[] = $controllerToCreate;
                }

                $this->generator->addHandler(new UpdateRequestTestHandler($this->parameters));
                $this->generator->addHandler(new NewRequestTestHandler($this->parameters));
                $this->generator->addHandler(new DeleteRequestTestHandler($this->parameters));
                //$this->generator->addHandler(new GetAllRequestTestHandler($this->parameters));
                $this->generator->addHandler(new SearchByRequestTestHandler($this->parameters));
                $this->generator->addHandler(new GetRequestTestHandler($this->parameters));
                $this->generator->addHandler(new PatchRequestTestHandler($this->parameters));

                $this->generator->execute();
                $this->generator->clear();
            }
        }

        $this->generator->addHandler(New TraitEntityNameHandler($this->parameters));
        $this->generator->addHandler(New TraitVerifyResolverHandler($this->parameters));
        $this->generator->execute();
        $this->generator->clear();
        */
    }

    /**
     * @param string $entityName Name of the entity to parse all attributes in order to build a valid constructor
     *                           signature.
     * @return string
     */
    private function buildConstructorParamsString($entityName)
    {
        $constructorParamsString = '';
        foreach ($this->entities[$entityName] as $field) {
            $constructorParamsString .= '$' . $field['name'] . ', ';
        }

        return trim($constructorParamsString, ', ');
    }

    /**
     * @param array $entityGroups
     * @param string $group
     */
    private function addCQRSCoordinationToGenerator($entityGroups, $group)
    {
        //Set the parameter $group to its good value (might be a reset)
        $this->parameters['group'] = $group;

        //Reset the controllerData list
        $this->parameters['controllerData'] = [];

        //Fetch all controllerData for the given group (Command or Query)
        foreach ($entityGroups[$group] as $entityCommandData) {
            $this->parameters['controllerData'][] = $entityCommandData;
        }

        //Add the Handler, then execute it for generate the file, and finally, clear the handlers generator's stack.
        $this->generator->addHandler(new ControllerHandler($this->parameters), true);
    }

    /**
     * @param array $entityGroups
     * @param string $group
     */
    private function addCQRSRequestToGenerator($entityGroups, $group)
    {
        //Set the parameter $group to its good value (might be a reset)
        $this->parameters['group'] = $group;

        //Fetch all actionName and add the handler for this actionName
        foreach ($entityGroups[$group] as $data) {
            $this->parameters['actionName'] = ucfirst($data['action']);
            $this->generator->addHandler(new RequestHandler($this->parameters), true);
        }
    }
}
