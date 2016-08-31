<?php

namespace Sfynx\DddGeneratorBundle\Generator\Api\Generator;

use Sfynx\DddGeneratorBundle\Generator\Api\DddApiGenerator;

use Sfynx\DddGeneratorBundle\Generator\Api\Handler\PresentationBundle\DependencyInjection\PresentationBundleExtensionHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\PresentationBundle\PresentationBundleHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\PresentationBundle\DependencyInjection\ConfigurationHandler as PBConfigurationHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\PresentationBundle\DependencyInjection\Compiler\ResettingListenersPassHandler as PBResettingListenersPass;

use Sfynx\DddGeneratorBundle\Generator\Api\Handler\PresentationBundle\Resources\Controller\ControllerMultiTenantHandler;

use Sfynx\DddGeneratorBundle\Generator\Api\Handler\PresentationBundle\Resources\Controller\ControllerSwaggerHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\PresentationBundle\Resources\File\MultiTenant\MultiTenantHandler;

use Sfynx\DddGeneratorBundle\Generator\Api\Handler\PresentationBundle\Resources\Application\ApplicationHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\PresentationBundle\Resources\Controller\ControllerHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\PresentationBundle\Resources\Route\RouteHandler;

use Sfynx\DddGeneratorBundle\Generator\Api\Handler\PresentationBundle\Resources\Route\RouteMultiTenantHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\PresentationBundle\Resources\Route\RouteSwaggerHandler;
use Symfony\Component\Console\Output\OutputInterface;

class PresentationBundle
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
        $this->output->writeln('######################################################');
        $this->output->writeln('#       GENERATE PRESENTATION BUNDLE STRUCTURE       #');
        $this->output->writeln('######################################################');
        $this->output->writeln('');

//        $this->generateBundle();
        $this->generateResourcesConfiguration();
    }

    // COPY PASTE OF PRESENTATION.PHP PARSEROUTES
    // NEEED TO BEEEE MERGEEEEEE
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

    public function generateBundle()
    {
        $this->parameters['projectName'] = str_replace('src/', '', $this->projectDir);
        $this->parameters['entities'] = $this->entities;

        $this->generator->addHandler(new PresentationBundleHandler($this->parameters));
        $this->generator->addHandler(new PresentationBundleExtensionHandler($this->parameters));
        $this->generator->addHandler(new PBConfigurationHandler($this->parameters));
        $this->generator->addHandler(new PBResettingListenersPass($this->parameters));

        $this->generator->execute();
        $this->generator->clear();
    }


    public function generateResourcesConfiguration()
    {
        foreach ($this->entitiesGroups as $entityName => $entityGroups) {
            $this->parameters['entityName'] = strtolower($entityName);

            //Command part
            $this->addCQRSApplicationHandlerServiceToGenerator($entityGroups, self::COMMAND);

            $this->addCQRSControllerHandlerServiceToGenerator($entityGroups, self::COMMAND);
            $this->addCQRSRouteHandlerServiceToGenerator($entityGroups, self::COMMAND);

            //Query Part
            $this->addCQRSApplicationHandlerServiceToGenerator($entityGroups, self::QUERY);

            $this->addCQRSControllerHandlerServiceToGenerator($entityGroups, self::QUERY);
            $this->addCQRSRouteHandlerServiceToGenerator($entityGroups, self::QUERY);
        }

        $this->generator->addHandler(new ControllerMultiTenantHandler($this->parameters));
        $this->generator->addHandler(new ControllerSwaggerHandler($this->parameters));

        $this->generator->execute();
        $this->generator->clear();
    }

    /**
     * @param array $entityGroups
     * @param string $group
     */
    private function addCQRSApplicationHandlerServiceToGenerator($entityGroups, $group)
    {
        //Set the parameter $group to its good value (might be a reset)
        $this->parameters['group'] = $group;

        //Reset the controllerData list
        $this->parameters['controllerData'] = [];

        //Fetch all controllerData for the given group (Command or Query)
        foreach ($entityGroups[$group] as $entityCommandData) {
            $this->parameters['controllerData'][] = $entityCommandData;
        }

        $this->generator->addHandler(new ApplicationHandler($this->parameters), true);
    }

    /**
     * @param array $entityGroups
     * @param string $group
     */
    private function addCQRSControllerHandlerServiceToGenerator($entityGroups, $group)
    {
        //Set the parameter $group to its good value (might be a reset)
        $this->parameters['group'] = $group;

        //Reset the controllerData list
        $this->parameters['controllerData'] = [];

        //Fetch all controllerData for the given group (Command or Query)
        foreach ($entityGroups[$group] as $entityCommandData) {
            $this->parameters['controllerData'][] = $entityCommandData;
        }

        $this->generator->addHandler(new ControllerHandler($this->parameters), true);
    }

    /**
     * @param array $entityGroups
     * @param string $group
     */
    private function addCQRSRouteHandlerServiceToGenerator($entityGroups, $group)
    {
        //Set the parameter $group to its good value (might be a reset)
        $this->parameters['group'] = $group;

        //Reset the controllerData list
        $this->parameters['controllerData'] = [];

        //Fetch all controllerData for the given group (Command or Query)
        foreach ($entityGroups[$group] as $entityCommandData) {
            $this->parameters['controllerData'][] = $entityCommandData;
        }

        $this->generator->addHandler(new RouteHandler($this->parameters), true);
    }

}
