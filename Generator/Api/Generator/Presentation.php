<?php
namespace Sfynx\DddGeneratorBundle\Generator\Api\Generator;

use Sfynx\DddGeneratorBundle\Generator\Api\DddApiGenerator;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Adapter\Command\AdapterHandler as AdapterCommandHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Adapter\Query\AdapterHandler as AdapterQueryHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Adapter\DeleteAdapterHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Adapter\DeleteManyAdapterHandler;

use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Infrastructure\Persistence\TraitEntityNameHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Presentation\Coordination\ControllerHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\PresentationBundle\Resources\ControllersHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Request\DeleteManyRequestHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Request\DeleteRequestHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Request\GetAllRequestHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Request\GetByIdsRequestHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Request\GetRequestHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Request\NewRequestHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Request\PatchRequestHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Request\SearchByRequestHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Request\UpdateRequestHandler;


use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Tests\Presentation\Adapter\Entity\Command\DeleteCommandAdapterTestHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Tests\Presentation\Adapter\Entity\Command\NewCommandAdapterTestHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Tests\Presentation\Adapter\Entity\Command\PatchCommandAdapterTestHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Tests\Presentation\Adapter\Entity\Command\UpdateCommandAdapterTestHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Tests\Presentation\Coordination\Entity\Command\ControllerCommandTestHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Tests\Presentation\Coordination\Entity\Query\ControllerQueryTestHandler;

use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Tests\Presentation\Request\Entity\Command\DeleteRequestTestHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Tests\Presentation\Request\Entity\Command\NewRequestTestHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Tests\Presentation\Request\Entity\Command\PatchRequestTestHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Tests\Presentation\Request\Entity\Command\UpdateRequestTestHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Tests\Presentation\Request\Entity\Query\GetAllRequestTestHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Tests\Presentation\Request\Entity\Query\GetRequestTestHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Tests\Presentation\Request\Entity\Query\SearchByRequestTestHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Tests\TraitVerifyResolverHandler;
use Symfony\Component\Console\Output\OutputInterface;

class Presentation
{
    const COMMANDS_LIST = ['update', 'new', 'delete', 'patch'];
    const QUERIES_LIST = ['get', 'getAll', 'searchBy', 'getByIds', 'findByName'];

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
        //$this->generateCommandsAdapter();
        $this->output->writeln('### QUERY ADAPTERS GENERATION ###');
        //$this->generateQueriesAdapter();
        $this->output->writeln('### COORDINATION GENERATION ###');
        $this->generateCoordination();
        exit;
        $this->output->writeln('### ENTITIES & REPOSITORIES INTERFACES GENERATION ###');
        $this->generateRequest();
        $this->output->writeln('### ENTITIES & REPOSITORIES INTERFACES GENERATION ###');
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

                if (in_array($data['action'], self::COMMANDS_LIST)) {
                    $elements['group'] = 'Command';
                    $routes['commands'][] = $elements;
                } else {
                    $elements['group'] = 'Query';
                    $routes['queries'][] = $elements;
                }
            }
        }

        return $routes;
    }

    public function generateCommandsAdapter()
    {
        foreach ($this->commandsQueriesList['commands'] as $data) {
            $constructorParams = '';

            foreach ($this->entities[$data['entity']] as $field) {
                $constructorParams .= '$' . $field['name'] . ', ';
            }

            $this->parameters['actionName'] = ucfirst($data['action']);
            $this->parameters['entityName'] = ucfirst($data['entity']);
            $this->parameters['entityFields'] = $this->entities[$data['entity']];

            $this->parameters['constructorArgs'] = trim($constructorParams, ', ');

            $this->generator->addHandler(new AdapterCommandHandler($this->parameters));

            $this->generator->execute();
            $this->generator->clear();
        }

        $this->generator->addHandler(new DeleteAdapterHandler($this->parameters));
        $this->generator->addHandler(new DeleteManyAdapterHandler($this->parameters));

        $this->generator->execute();
        $this->generator->clear();
    }

    public function generateQueriesAdapter()
    {
        foreach ($this->commandsQueriesList['queries'] as $data) {
            $constructorParams = '';

            foreach ($this->entities[$data['entity']] as $field) {
                $constructorParams .= '$' . $field['name'] . ', ';
            }

            $this->parameters['actionName'] = ucfirst($data['action']);
            $this->parameters['entityName'] = ucfirst($data['entity']);
            $this->parameters['entityFields'] = $this->entities[$data['entity']];

            $this->parameters['constructorArgs'] = trim($constructorParams, ', ');

            $this->generator->addHandler(new AdapterQueryHandler($this->parameters));

            $this->generator->execute();
            $this->generator->clear();
        }
    }

        /*
    public function generateCoordination()
    {
        foreach ($this->pathsToCreate as $route => $verbData) {
            foreach ($verbData as $verb => $data) {
                $controllers[$data['controller']][] = ['action' => $data['action'], 'path' => $route, 'method' => $verb, 'entityName' => $data['entity']];
            }
        }

        foreach ($controllers as $controller => $data) {
            $parametersQuery = [
                'rootDir' => $this->rootDir . '/src',
                'projectDir' => $this->projectDir,
                'projectName' => str_replace('src/', '', $this->projectDir),
                'controllerName' => $controller,
                'group' => 'Query',
                'destinationPath' => $this->destinationPath,
            ];

            $parametersCommand = [
                'rootDir' => $this->rootDir . '/src',
                'projectDir' => $this->projectDir,
                'projectName' => str_replace('src/', '', $this->projectDir),
                'controllerName' => $controller,
                'group' => 'Command',
                'destinationPath' => $this->destinationPath,
            ];

            foreach ($data as $action) {

                if (in_array($action['action'], ['put', 'delete', 'update', 'new', 'patch'])) {
                    $parametersCommand['controllerData'][] = $action;
                    $parametersCommand['entityName'] = $action['entityName'];

                    $this->generator->addHandler(new ControllerHandler($parametersCommand));
                    $this->generator->execute();
                    $this->generator->clear();

                } else {
                    $parametersQuery['controllerData'][] = $action;
                    $parametersQuery['entityName'] = $action['entityName'];

                    $this->generator->addHandler(new ControllerHandler($parametersQuery));
                    $this->generator->execute();
                    $this->generator->clear();

                }

                $controllerToCreate[$controller][$action['entityName']] = true;
            }

            $controllersToCreate[] = $controllerToCreate;
        }


        foreach ($controllersToCreate as $controller => $entities) {
            $parameters = [
                'rootDir' => $this->rootDir . '/src',
                'projectDir' => $this->projectDir,
                'projectName' => str_replace('src/', '', $this->projectDir),
                'controllers' => $controllersToCreate[$controller],
                'destinationPath' => $this->destinationPath,
            ];

            $this->generator->addHandler(new ControllersHandler($parameters));

            $this->generator->execute();
            $this->generator->clear();
        }
    }*/


    public function generateCoordination()
    {
        //TODO: WIP: Fix this

        $controllerData = [];
        foreach ($this->commandsQueriesList['commands'] as $data) {
            $controllerData[] = [
                'action' => $data['action'],
                'path' => $data['route'],
                'method' => $data['verb'],
                'entityName' => $data['entity']
            ];
        }

        foreach ($this->commandsQueriesList['commands'] as $data) {
            $this->parameters['controllerName'] = $data['controller'];
            $this->parameters['controllerData'] = $controllerData;
            $this->parameters['entityName'] = $data['entity'];
            $this->parameters['group'] = $data['group'];
            $this->parameters['actionName'] = $data['action'];

            $this->generator->addHandler(new ControllerHandler($this->parameters));
            $this->generator->execute();
            $this->generator->clear();
            $controllerToCreate[$data['controller']][$data['entity']] = true;
        }

        die(var_dump($controllerToCreate));

        /*
        $controllersToCreate[] = $controllerToCreate;


        //Generate controllers.yml
        foreach ($controllersToCreate as $controller => $entities) {
            $this->parameters = [
                'rootDir' => $this->rootDir . '/src',
                'projectDir' => $this->projectDir,
                'projectName' => str_replace('src/', '', $this->projectDir),
                'controllers' => $controllersToCreate[$controller],
                'destinationPath' => $this->destinationPath,
            ];

            $this->generator->addHandler(new ControllersHandler($this->parameters));

            $this->generator->execute();
            $this->generator->clear();
        }
        */
    }

    public function generateRequest()
    {
        foreach ($this->pathsToCreate as $route => $verbData) {
            foreach ($verbData as $verb => $data) {
                $constructorParams = '';
                foreach ($this->entities[$data['entity']] as $field) {
                    $constructorParams .= "$" . $field['name'] . ',';
                }

                $this->parameters = [
                    'rootDir' => $this->rootDir . '/src',
                    'projectDir' => $this->projectDir,
                    'projectName' => str_replace('src/', '', $this->projectDir),
                    'actionName' => ucfirst($data['action']),
                    'entityName' => ucfirst($data['entity']),
                    'entityFields' => $this->entities[$data['entity']],
                    'destinationPath' => $this->destinationPath,
                ];
                $this->parameters['constructorArgs'] = trim($constructorParams, ',');
            }

            $this->generator->addHandler(new UpdateRequestHandler($this->parameters));
            $this->generator->addHandler(new NewRequestHandler($this->parameters));
            $this->generator->addHandler(new DeleteRequestHandler($this->parameters));
            $this->generator->addHandler(new DeleteManyRequestHandler($this->parameters));
            $this->generator->addHandler(new GetAllRequestHandler($this->parameters));
            $this->generator->addHandler(new SearchByRequestHandler($this->parameters));
            $this->generator->addHandler(new GetByIdsRequestHandler($this->parameters));
            $this->generator->addHandler(new GetRequestHandler($this->parameters));
            $this->generator->addHandler(new PatchRequestHandler($this->parameters));

            $this->generator->execute();
            $this->generator->clear();
        }
    }

    public function generateTests()
    {
        foreach ($this->pathsToCreate as $route => $verbData) {
            foreach ($verbData as $verb => $data) {
                $controllers[$data['controller']][] = ['action' => $data['action'], 'path' => $route, 'method' => $verb, 'entityName' => $data['entity']];

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
                        'group' => 'Query',
                        'destinationPath' => $this->destinationPath,
                    ];

                    $this->parametersCommand = [
                        'rootDir' => $this->rootDir . '/src',
                        'projectDir' => $this->projectDir,
                        'projectName' => str_replace('src/', '', $this->projectDir),
                        'controllerName' => $controller,
                        'group' => 'Command',
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
    }
}
