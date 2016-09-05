<?php
declare(strict_types = 1);

namespace Sfynx\DddGeneratorBundle\Generator\Api\Generator;

use Sfynx\DddGeneratorBundle\Generator\Api\DddApiGenerator;
use Sfynx\DddGeneratorBundle\Generator\Api\ValueObjects\GeneratorVO;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Abstract class GeneratorAbstract
 *
 * @category Generator
 * @package Api
 * @subpackage Generator
 */
abstract class LayerAbstract
{
    const COMMANDS_LIST = ['update', 'new', 'delete', 'patch'];
    const QUERIES_LIST = ['get', 'getAll', 'searchBy', 'getByIds', 'findByName'];

    const COMMAND = 'Command';
    const QUERY = 'Query';

    /** @var DddApiGenerator */
    protected $generator;
    /** @var array[] */
    protected $entitiesToCreate = [];
    /** @var array[] */
    protected $valueObjectsToCreate = [];
    /** @var array[] */
    protected $pathsToCreate = [];
    /** @var string */
    protected $rootDir;
    /** @var string */
    protected $projectDir;
    /** @var string */
    protected $destinationPath;
    /** @var OutputInterface */
    protected $output;
    /** @var array[] */
    protected $parameters;
    /** @var array[] */
    protected $commandsQueriesList;
    /** @var array[] */
    protected $entitiesGroups;

    /**
     * Abstract constructor, used by all layers.
     *
     * @param GeneratorVO     $voGenerator
     * @param OutputInterface $output
     */
    public function __construct(GeneratorVO $voGenerator, OutputInterface $output)
    {
        $this->generator = $voGenerator->getGenerator();
        $this->entitiesToCreate = $voGenerator->getEntitiesToCreate();
        $this->valueObjectsToCreate = $voGenerator->getValueObjectsToCreate();
        $this->pathsToCreate = $voGenerator->getPathsToCreate();

        $this->rootDir = $voGenerator->getRootDir();
        $this->projectDir = $voGenerator->getProjectDir();
        $this->destinationPath = $voGenerator->getDestinationPath();

        $this->output = $output;

        $this->commandsQueriesList = $this->parseRoutes();
        $this->parameters = [
            'rootDir' => $this->rootDir . '/src',
            'projectDir' => $this->projectDir,
            'projectName' => str_replace('src/', '', $this->projectDir),
            'valueObjects' => $this->valueObjectsToCreate,
            'destinationPath' => $this->destinationPath,
        ];
    }

    /**
     * Entry point of the generation of the current concrete layer in DDD.
     * Must be declared in the concrete class to call all generation methods.
     */
    abstract public function generate();

    /**
     * Entry point of generation of the tests files
     */

    abstract protected function generateTests();


    /**
     * Parse all routes and define:
     * - entities for each group;
     * - groups for each entities.
     *
     * This create helpful properties to be used in the layer generations.
     *
     * @return array
     */
    public function parseRoutes(): array
    {
        $routes = [self::COMMAND => [], self::QUERY => []];

        foreach ($this->pathsToCreate as $route => $verbData) {
            foreach ($verbData as $verb => $data) {
                //Define the group
                $group = in_array($data['action'], self::COMMANDS_LIST) ? self::COMMAND : self::QUERY;

                //Init the elements
                $elements = $data;
                $elements['route'] = $route;
                $elements['verb'] = $verb;
                $elements['group'] = $group;

                //Sort by entities and by group (command/query)
                $this->entitiesGroups[$data['entity']][$group][] = $elements;
                //Sort by group
                $routes[$group][] = $elements;
            }
        }

        return $routes;
    }

    /**
     * Build a string which value is equal to the argument list of a constructor of any generated class.
     *
     * @param string $entityName Name of the entity to parse all attributes in order to build a valid constructor
     *                           argument list.
     * @return string
     */
    protected function buildConstructorParamsString(string $entityName): string
    {
        $constructorParamsString = '';
        foreach ($this->entitiesToCreate[$entityName] as $field) {
            $constructorParamsString .= '$' . $field['name'] . ', ';
        }

        return trim($constructorParamsString, ', ');
    }

    /**
     * Build a string which value is equal to the argument list of a manager of any generated class.
     *
     * @param string $entityName Name of the entity to parse all attributes in order to build a valid manager argument
     *                           list.
     * @param string $action     Name of the action the manager argument list will be used. If the action is 'new', the
     *                           field 'id' will not be part of the manager argument list.
     * @return string
     */
    protected function buildManagerParamsString(string $entityName, string $action): string
    {
        $managerParamsString = '';
        foreach ($this->entitiesToCreate[$entityName] as $field) {
            if (('new' === $action && 'id' !== $field['type']) || ('new' !== $action)) {
                $managerParamsString .= '$' . $field['name'] . ', ';
            }
        }

        return trim($managerParamsString, ', ');
    }
}
