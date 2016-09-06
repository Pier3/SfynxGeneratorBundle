<?php
declare(strict_types = 1);

namespace Sfynx\DddGeneratorBundle\Generator\Api\Generator;

use InvalidArgumentException;
use Sfynx\DddGeneratorBundle\Bin\Generator;
use Sfynx\DddGeneratorBundle\Generator\Api\DddApiGenerator;
use Sfynx\DddGeneratorBundle\Generator\Api\ValueObjects\LayerVO;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\Handler;
use Symfony\Component\Yaml\Parser;

/**
 * Abstract class LayerAbstract
 *
 * @category Generator
 * @package Api
 * @subpackage Generator
 */
abstract class LayerAbstract
{
    /** @var string */
    protected static $handlersFileName;

    /** @var string */
    protected static $skeletonDir;

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
    /** @var array[] */
    protected $parameters;
    /** @var array[] */
    protected $commandsQueriesList;
    /** @var array[] */
    protected $entitiesGroups;
    /** @var array */
    protected $handlersConfig;

    /**
     * Abstract constructor, used by all layers.
     *
     * @param LayerVO $layerVO
     */
    public function __construct(LayerVO $layerVO)
    {
        $this->generator = $layerVO->generator;
        $this->entitiesToCreate = $layerVO->entitiesToCreate;
        $this->valueObjectsToCreate = $layerVO->valueObjectsToCreate;
        $this->pathsToCreate = $layerVO->pathsToCreate;
        $this->entitiesGroups = $layerVO->entitiesGroups;

        $this->rootDir = $layerVO->rootDir;
        $this->projectDir = $layerVO->projectDir;
        $this->destinationPath = $layerVO->destinationPath;

        $this->output = $layerVO->output;

        $this->commandsQueriesList = $layerVO->commandsQueriesList;
        $this->parameters = $layerVO->parameters;
        $this->parameters['skeletonDir'] = static::$skeletonDir;

        $handlersFileName = Generator::API_HANDLERS . '/' . static::$handlersFileName;
        $this->handlersConfig = (new Parser())->parse(file_get_contents($handlersFileName));
    }

    /**
     * Entry point of the generation of the current concrete layer in DDD.
     * Must be declared in the concrete class to call all generation methods.
     */
    abstract public function generate();

    /**
     * Add the given handler using configuration file to set the right parameters.
     *
     * @param string $handlerName
     * @throws InvalidArgumentException If the handler name is not defined in the configuration file.
     * @return LayerAbstract
     */
    public function addHandler(string $handlerName): self
    {
        if (!isset($this->handlersConfig[$handlerName])) {
            $msgException = 'The handler "%s" is not defined in the handler file "%s".';
            $handlersFileName = realpath(Generator::API_HANDLERS . '/' . static::$handlersFileName);
            throw new InvalidArgumentException(sprintf($msgException, $handlerName, $handlersFileName));
        }

        //Merge the parameters with the ones defined in the configuration file for the given handler name.
        $this->parameters = array_merge($this->parameters, $this->handlersConfig[$handlerName]);

        //Add the name of the handler for debugging in case of emergency.
        $this->parameters['handlerName'] = $handlerName;

        //Add the handler with the configured parameters.
        $this->generator->addHandler(new Handler($this->parameters));

        return $this;
    }

    /**
     * Add all handlers defined in arguments.
     *
     * @param string[] ...$handlersNames
     * @return LayerAbstract
     */
    public function addHandlers(string ...$handlersNames): self
    {
        //Execute $this->addHandler for all elements in $handlersNames.
        array_walk($handlersNames, [$this, 'addHandler']);
        return $this;
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
