<?php
declare(strict_types = 1);

namespace Sfynx\DddGeneratorBundle\Generator\Api\ValueObjects;

use Sfynx\DddGeneratorBundle\Generator\Api\DddApiGenerator;
use Sfynx\DddGeneratorBundle\Generator\Api\Generator\LayerAbstract;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class LayerVO.
 *
 * @category Generator
 * @package Api
 * @subpackage ValueObjects
 */
final class LayerVO
{
    /** @var DddApiGenerator */
    public $generator;
    /** @var array[] */
    public $entitiesToCreate = [];
    /** @var array[] */
    public $valueObjectsToCreate = [];
    /** @var array[] */
    public $pathsToCreate = [];
    /** @var string */
    public $rootDir;
    /** @var string */
    public $projectDir;
    /** @var string */
    public $destinationPath;
    /** @var array[] */
    public $parameters;
    /** @var array[] */
    public $commandsQueriesList;
    /** @var array[] */
    public $entitiesGroups;
    /** @var OutputInterface */
    public $output;

    /**
     * Layer VO constructor, used by all layers.
     *
     * @param GeneratorVO $voGenerator
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
        $routes = [LayerAbstract::COMMAND => [], LayerAbstract::QUERY => []];

        foreach ($this->pathsToCreate as $route => $verbData) {
            foreach ($verbData as $verb => $data) {
                //Define the group
                $group = in_array($data['action'], LayerAbstract::COMMANDS_LIST)
                    ? LayerAbstract::COMMAND
                    : LayerAbstract::QUERY;

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
}
