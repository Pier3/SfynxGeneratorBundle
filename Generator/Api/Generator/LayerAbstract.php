<?php
declare(strict_types = 1);

namespace Sfynx\DddGeneratorBundle\Generator\Api\Generator;

use InvalidArgumentException;
use Sfynx\DddGeneratorBundle\Bin\Generator;
use Sfynx\DddGeneratorBundle\Generator\Api\DddApiGenerator;
use Sfynx\DddGeneratorBundle\Generator\Api\ValueObjects\LayerVO;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\Handler;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Exception\ParseException;
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
    /** @var bool */
    public static $verbose = false;

    /** @var string */
    protected static $handlersFileName;

    /** @var string */
    protected static $skeletonDir;

    const COMMANDS_LIST = ['update', 'new', 'delete', 'patch'];
    const QUERIES_LIST = ['get', 'getAll', 'searchBy', 'getByIds', 'findByName'];

    const COMMAND = 'Command';
    const QUERY = 'Query';

    const PATTERN_MARKER = '!#MARKER#!';

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
    /** @var OutputInterface */
    protected $output;

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

        $handlersFileName = realpath(Generator::API_HANDLERS . '/' . static::$handlersFileName);
        try {
            $this->handlersConfig = (new Parser())->parse(file_get_contents($handlersFileName));
        } catch (ParseException $e) {
            $errorMessage = '# Error in layer "%s": configuration handler file "%s" does not exist.' . PHP_EOL
                . 'Error reported is: "%s".' . PHP_EOL;
            $errorMessage = sprintf($errorMessage, static::class, $handlersFileName, $e->getMessage());
            $this->writeln($errorMessage, OutputInterface::VERBOSITY_NORMAL);
            exit;
        }
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
        $this->generator->addHandler(new Handler($this->parameters, $this->output));

        return $this;
    }

    /**
     * Add all handlers defined in arguments.
     *
     * @param string[] ...$handlersNames
     * @throws InvalidArgumentException If the handler name is not defined in the configuration file.
     * @return LayerAbstract
     */
    public function addHandlers(string ...$handlersNames): self
    {
        //Execute $this->addHandler for all elements in $handlersNames.
        array_walk($handlersNames, [$this, 'addHandler']);
        return $this;
    }


    /**
     * Write in the console depending on the verbosity level. Only write if mode verbose is activated.
     *
     * @param string $message
     * @param int $options
     * @return $this
     */
    public function writeln(string $message, int $options = OutputInterface::VERBOSITY_VERBOSE)
    {
        $this->output->writeln($message, $options);
        return $this;
    }

    /**
     * Build a string which value is equal to the argument list of a value object definition of any generated class.
     *
     * @param array $fields List of fields used to build the argument list string.
     * @return string
     */
    protected function buildValueObjectParamsString(array $fields): string
    {
        $voParamsString = '';
        foreach ($fields as $field) {
            $voParamsString .= '$' . $field['name'] . ', ';
        }

        return trim($voParamsString, ', ');
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
     * Build a string which value is equal to the argument list of any generated class.
     *
     * @param string $entityName Name of the entity to parse all attributes in order to build a valid function
     *                           or make instance argument list.
     * @return string
     */
    protected function buildParamsString(string $entityName): string
    {
        $paramsString = '';
        foreach ($this->entitiesToCreate[$entityName] as $field) {
            $paramsString .= '$' . $field['name'] . ',' . self::PATTERN_MARKER;
        }

        return trim($paramsString, ',' . self::PATTERN_MARKER);
    }

    /**
     * Build a array which all actions in the const COMMANDS_LIST and  QUERIES_LIST for the use Repository.
     *
     * @return string
     */
    protected function buildActionName(): string
    {
        $paramsString = implode(
            'Repository,' . self::PATTERN_MARKER,
            array_map('ucfirst', array_merge(self::COMMANDS_LIST, self::QUERIES_LIST))
        );

        $paramsString .= 'Repository';

        return $paramsString;
    }
}
