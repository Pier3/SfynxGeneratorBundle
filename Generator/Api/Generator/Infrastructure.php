<?php
namespace Sfynx\DddGeneratorBundle\Generator\Api\Generator;

use Symfony\Component\Console\Output\OutputInterface;
use Sfynx\DddGeneratorBundle\Generator\Api\DddApiGenerator;

use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Infrastructure\Persistence\TraitEntityNameHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Infrastructure\Persistence\Orm\RepositoryHandler as OrmRepositoryHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Infrastructure\Persistence\Odm\RepositoryHandler as OdmRepositoryHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Infrastructure\Persistence\CouchDb\RepositoryHandler as CouchDbRepositoryHandler;

class Infrastructure
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

    public function generate()
    {
        $this->output->writeln('');
        $this->output->writeln('##############################################');
        $this->output->writeln('#     GENERATE INFRASTRUCTURE STRUCTURE      #');
        $this->output->writeln('##############################################');
        $this->output->writeln('');

        $this->output->writeln('### PERSISTENCE GENERATION ###');
        $this->generatePersistence();
    }

    public function generatePersistence()
    {
        foreach ($this->entitiesGroups as $entityName => $entityGroups) {
            $this->parameters['entityName'] = $entityName;
            $this->parameters['constructorArgs'] = $this->buildConstructorParamsString($entityName);
            $this->parameters['entityFields'] = $this->entities[$entityName];

            //Command Part
            $this->addCQRSRepositoriesToGenerator($entityGroups, self::COMMAND);

            //Query Part
            $this->addCQRSRepositoriesToGenerator($entityGroups, self::QUERY);

            $this->generator->addHandler(new TraitEntityNameHandler($this->parameters), true);
        }

        do {
            try {
                $this->generator->execute();
                $this->generator->clear();
            } catch (\Exception $e) {
                fwrite(STDERR, 'Exception occurs: ' . $e->getMessage() . '.');
                $this->generator->shiftHandler();
            } catch (\Error $e) {
                fwrite(STDERR, 'Error occurs: ' . $e->getMessage() . '.');
                $this->generator->shiftHandler();
            }
        } while (!$this->generator->isCleared());
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
    private function addCQRSRepositoriesToGenerator($entityGroups, $group)
    {
        //Set the parameter $group to its good value (might be a reset)
        $this->parameters['group'] = $group;

        //Fetch all actionName and add the handler for this actionName
        foreach ($entityGroups[$group] as $data) {
            $this->parameters['actionName'] = ucfirst($data['action']);
            $this->generator->addHandler(new OrmRepositoryHandler($this->parameters), true);
            $this->generator->addHandler(new OdmRepositoryHandler($this->parameters), true);
            $this->generator->addHandler(new CouchDbRepositoryHandler($this->parameters), true);
        }
    }
}