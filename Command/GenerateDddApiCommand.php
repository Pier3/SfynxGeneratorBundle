<?php
declare(strict_types = 1);

namespace Sfynx\DddGeneratorBundle\Command;

use InvalidArgumentException;
use Symfony\Component\Console\Exception\{
    InvalidArgumentException as SFConsoleInvalidArgumentException,
    LogicException as SFConsoleLogicException,
    RuntimeException as SFConsoleRuntimeException
};
use Sfynx\DddGeneratorBundle\Generator\Api\Generator\Test;
use Symfony\Component\Yaml\Exception\ParseException as SFYMLParseException;

use Symfony\Component\Yaml\Parser;
use Symfony\Component\Console\{
    Command\Command,
    Helper\QuestionHelper,
    Question\Question,
    Input\InputArgument,
    Input\InputInterface,
    Input\InputOption,
    Output\OutputInterface
};

use Sfynx\DddGeneratorBundle\Generator\Api\DddApiGenerator;
use Sfynx\DddGeneratorBundle\Generator\Api\Generator\{
    Application,
    Domain,
    Infrastructure,
    InfrastructureBundle,
    Presentation,
    PresentationBundle
};
use Sfynx\DddGeneratorBundle\Generator\Api\ValueObjects\{
    ElementsToCreateVO,
    GeneratorVO,
    LayerVO,
    PathsVO
};

/**
 * Class GenerateDddApiCommand.
 *
 * @category Command
 */
class GenerateDddApiCommand extends Command
{
    //Constants about the name of the environment variable that can be used in this class.
    const ENV_SWAGGER_PATH_NAME = 'SYMFONY_SFYNX_PATH_TO_SWAGGER_FILE';
    const ENV_DESTINATION_FILE_NAME = 'SYMFONY_SFYNX_PATH_TO_DEST_FILES';
    const ENV_CONTEXT_NAME = 'SYMFONY_SFYNX_CONTEXT_NAME';

    //Constants about the arguments names of this command.
    const ARG_SWAGGER_PATH_NAME = 'path-to-swagger-file';
    const ARG_DESTINATION_FILE_NAME = 'destination-path';
    const ARG_CONTEXT_NAME = 'context-name';
    const ARG_CREATE_ALL = 'create-all';

    /** @var DddApiGenerator */
    protected $generator;
    /** @var array */
    protected $config;

    /** @var array */
    protected $entities = [];
    /** @var array */
    protected $valueObjects = [];
    /** @var array */
    protected $paths = [];

    /** @var string */
    protected $rootDir;
    /** @var string */
    protected $destinationPath;
    /** @var string */
    protected $contextName;

    /**
     * Set the generator to use.
     *
     * @param DddApiGenerator $generator
     * @return GenerateDddApiCommand
     */
    public function setGenerator(DddApiGenerator $generator): self
    {
        $this->generator = $generator;
        return $this;
    }

    /**
     * Set the configuration file to use for the generator. This file must be a YML file, containing a Swagger compliant
     * snippet that can define a valid PHP array.
     *
     * @param array $config
     * @return GenerateDddApiCommand
     */
    public function setConfig(array $config): self
    {
        $this->config = $config;
        return $this;
    }

    /**
     * Set the root dir based upon the argument sent.
     *
     * @param string $rootDir
     * @return GenerateDddApiCommand
     */
    public function setRootDir(string $rootDir): self
    {
        $this->rootDir = str_replace(DIRECTORY_SEPARATOR . 'app', '', $rootDir);
        return $this;
    }

    /**
     * Configure the current command.
     *
     * @see Command::configure()
     * @throws InvalidArgumentException When the name is invalid
     */
    public function configure()
    {
        $this->setName('sfynx:api')
            ->setDescription('Generate a DDD Rest API.')
            ->addArgument(self::ARG_CONTEXT_NAME, InputArgument::REQUIRED, 'Context name.')
            ->addArgument(self::ARG_SWAGGER_PATH_NAME, InputArgument::OPTIONAL, 'Path to swagger yml file.')
            ->addArgument(self::ARG_DESTINATION_FILE_NAME, InputArgument::OPTIONAL, 'Destination path.', '/tmp')
            ->addOption(self::ARG_CREATE_ALL, null, InputOption::VALUE_NONE, 'Generate all items.')
            ->setHelp('Generate a DDD Rest API for an entity or a list of entities.');
    }

    /**
     * Interacts with the user.
     *
     * @see Command::interact()
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @throws SFConsoleInvalidArgumentException
     * @throws SFConsoleLogicException
     * @throws SFConsoleRuntimeException
     * @throws SFYMLParseException
     */
    public function interact(InputInterface $input, OutputInterface $output)
    {
        $this->setArgumentPathToSwaggerFile($input, $output)
            ->setArgumentDestinationPath($input, $output)
            ->setArgumentContextName($input, $output)
            ->parseSwaggerFile($input, $output);
    }

    /**
     * Main function, execute the generator.
     *
     * @see Command::execute()
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return int The error code of the execution. 0 if no error.
     * @throws SFConsoleInvalidArgumentException
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $projectDir = $input->getArgument(self::ARG_CONTEXT_NAME);
        $destinationPath = $input->getArgument(self::ARG_DESTINATION_FILE_NAME);

        $voElementsToCreate = new ElementsToCreateVO($this->entities, $this->valueObjects, $this->paths);
        $voPaths = new PathsVO($this->rootDir, $projectDir, $destinationPath);
        $voGenerator = new GeneratorVO($this->generator, $voElementsToCreate, $voPaths);

        $voLayer = new LayerVO($voGenerator, $output);

        //Generate Layers based on the inverse importance of the Layer.
        //Infrastructure : no dependency.
        //Domain has a dependency to Infrastructure.
        //Application has dependency to Domain.
        //Presentation has dependency to Application.
        (new Infrastructure($voLayer))->generate();
        (new Domain($voLayer))->generate();
        (new Application($voLayer))->generate();
        (new Presentation($voLayer))->generate();
        (new Test($voLayer))->generate();

        //Generate Layers linked to Symfony with the same pattern of generation upside.
        (new InfrastructureBundle($voLayer))->generate();
        (new PresentationBundle($voLayer))->generate();

        return 0;
    }

    /**
     * Parse the swaggerFile read entity, route and actions.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return GenerateDddApiCommand
     * @throws SFConsoleRuntimeException
     * @throws SFConsoleLogicException
     * @throws SFConsoleInvalidArgumentException
     * @throws SFYMLParseException
     */
    protected function parseSwaggerFile(InputInterface $input, OutputInterface $output): self
    {
        $this->config = (new Parser())->parse(file_get_contents($input->getArgument(self::ARG_SWAGGER_PATH_NAME)));

        $this->buildAllValueObjects($input, $output)
            ->buildAllEntities($input, $output)
            ->buildAllRoutes($input, $output);

        return $this;
    }

    /**
     * Parse route from a parsed Swagger File. Create a route with needed information
     *
     * @return array
     */
    protected function parseRoutes(): array
    {
        $results = [];
        foreach ($this->config['paths'] as $path => $data) {
            foreach ($data as $verb => $verbData) {
                $result = [
                    'verb' => $verb,
                    'action' => $verbData['operationId'],
                    'description' => $verbData['description'],
                    'controller' => $verbData['x-controller'],
                    'entity' => $verbData['x-entity'],
                ];
                $results[$path][$verb] = $result;
            }
        }

        return $results;
    }

    /**
     * Parse Value object from a swagger file
     *
     * @return array
     */
    protected function parseValueObjects(): array
    {
        if (!isset($this->config['x-valueObjects'])) {
            return [];
        }

        $results = [];
        foreach ($this->config['x-valueObjects'] as $name => $data) {
            $results[$name] = [
                'type' => $data['type'],
                'name' => $data['name'],
                'fields' => $data['x-fields'],
            ];
        }

        return $results;
    }

    /**
     * Parse entities from a swagger file
     *
     * @return array
     */
    protected function parseEntities(): array
    {
        if (!isset($this->config['x-entities'])) {
            return [];
        }

        $results = [];
        foreach ($this->config['x-entities'] as $name => $data) {
            foreach ($data['x-fields'] as $field) {
                $results[$name][$field['name']] = $field;
            }
        }

        return $results;
    }

    /**
     * Set the argument {self::ARG_SWAGGER_PATH_NAME}. Use environment variable to set it, or ask to end user if the
     * environment variable is not set.
     * Name of the environment variable to use is {self::ENV_SWAGGER_PATH_NAME}
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return GenerateDddApiCommand
     * @throws SFConsoleRuntimeException
     * @throws SFConsoleLogicException
     * @throws SFConsoleInvalidArgumentException
     */
    protected function setArgumentPathToSwaggerFile(InputInterface $input, OutputInterface $output): self
    {
        if (isset($_SERVER[self::ENV_SWAGGER_PATH_NAME])) {
            $input->setArgument(self::ARG_SWAGGER_PATH_NAME, $_SERVER[self::ENV_SWAGGER_PATH_NAME]);
            return $this;
        }

        $question = new Question('Path to swagger yml file: ');
        $pathToSwaggerEntityFile = $this->getQuestionHelper()->ask($input, $output, $question);

        while (!is_file($pathToSwaggerEntityFile)) {
            $output->writeln('This file does not exist.');
            $pathToSwaggerEntityFile = $this->getQuestionHelper()->ask($input, $output, $question);
        }

        $input->setArgument(self::ARG_SWAGGER_PATH_NAME, $pathToSwaggerEntityFile);
        return $this;
    }

    /**
     * Set the argument {self::ARG_DESTINATION_FILE_NAME}. Use environment variable to set it, or ask to end user if the
     * environment variable is not set.
     * Name of the environment variable to use is {self::ENV_DESTINATION_FILE_NAME}
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return GenerateDddApiCommand
     * @throws SFConsoleRuntimeException
     * @throws SFConsoleLogicException
     * @throws SFConsoleInvalidArgumentException
     */
    protected function setArgumentDestinationPath(InputInterface $input, OutputInterface $output): self
    {
        if (isset($_SERVER[self::ENV_DESTINATION_FILE_NAME])) {
            $input->setArgument(self::ARG_DESTINATION_FILE_NAME, $_SERVER[self::ENV_DESTINATION_FILE_NAME]);
            return $this;
        }

        $question = new Question('Destination path: ');
        $destinationPath = $this->getQuestionHelper()->ask($input, $output, $question);

        while (!is_dir($destinationPath) || !is_writable($destinationPath)) {
            $output->writeln('This directory does not exist or is not writable.');
            $destinationPath = $this->getQuestionHelper()->ask($input, $output, $question);
        }

        $input->setArgument(self::ARG_DESTINATION_FILE_NAME, $destinationPath);
        return $this;
    }

    /**
     * Set the argument {self::ARG_CONTEXT_NAME}. Use environment variable to set it, or ask to end user if the
     * environment variable is not set.
     * Name of the environment variable to use is {self::ENV_CONTEXT_NAME}
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return GenerateDddApiCommand
     * @throws SFConsoleRuntimeException
     * @throws SFConsoleLogicException
     * @throws SFConsoleInvalidArgumentException
     */
    protected function setArgumentContextName(InputInterface $input, OutputInterface $output): self
    {
        if (isset($_SERVER[self::ENV_CONTEXT_NAME])) {
            $input->setArgument(self::ARG_CONTEXT_NAME, $_SERVER[self::ENV_CONTEXT_NAME]);
            return $this;
        }
        $contextName = $this->getQuestionHelper()->ask($input, $output, new Question('Context name: '));
        $input->setArgument(self::ARG_CONTEXT_NAME, $contextName);

        return $this;
    }

    /**
     * Set property "valueObjects" after parsing the value objects defined in the YML file.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return GenerateDddApiCommand
     * @throws SFConsoleRuntimeException
     * @throws SFConsoleLogicException
     * @throws SFConsoleInvalidArgumentException
     */
    protected function buildAllValueObjects(InputInterface $input, OutputInterface $output): self
    {
        $valueObjects = $this->parseValueObjects();

        //If option "create-all" is set, we take all value objects.
        if ($input->getOption(self::ARG_CREATE_ALL)) {
            $this->valueObjects = $valueObjects;
            return $this;
        }

        //Otherwise, loop for each value object and ask to end user.
        $dialog = $this->getQuestionHelper();
        $questionSentence = 'Do you want to create the valueObject "%s" ? [Y/n]' . PHP_EOL;
        foreach ($valueObjects as $voName => $vo) {
            if ($dialog->ask($input, $output, new Question(sprintf($questionSentence, $voName)))) {
                $this->valueObjects[$voName] = $vo;
            }
        }

        return $this;
    }

    /**
     * Set property "entities" after parsing the entities defined in the YML file.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return GenerateDddApiCommand
     * @throws SFConsoleRuntimeException
     * @throws SFConsoleLogicException
     * @throws SFConsoleInvalidArgumentException
     */
    protected function buildAllEntities(InputInterface $input, OutputInterface $output): self
    {
        $entities = $this->parseEntities();

        //If option "create-all" is set, we take all entities.
        if ($input->getOption(self::ARG_CREATE_ALL)) {
            $this->entities = $entities;
            return $this;
        }

        //Otherwise, loop for each entity and ask to end user.
        $dialog = $this->getQuestionHelper();
        $questionSentence = 'Do you want to create the entity "%s" ? [Y/n]' . PHP_EOL;
        foreach ($entities as $entityName => $fields) {
            if ($dialog->ask($input, $output, new Question(sprintf($questionSentence, $entityName)))) {
                $this->entities[$entityName] = $fields;
            }
        }

        return $this;
    }

    /**
     * Set property "paths" after parsing the routes defined in the YML file.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return GenerateDddApiCommand
     * @throws SFConsoleRuntimeException
     * @throws SFConsoleLogicException
     * @throws SFConsoleInvalidArgumentException
     */
    protected function buildAllRoutes(InputInterface $input, OutputInterface $output): self
    {
        $paths = $this->parseRoutes();

        //If option "create-all" is set, we take all routes.
        if ($input->getOption(self::ARG_CREATE_ALL)) {
            $this->paths = $paths;
            return $this;
        }

        //Otherwise, loop for each route and ask to end user.
        $dialog = $this->getQuestionHelper();
        $questionSentence = 'Do you want create the %s action for route %s and verb %s? [Y/n]' . PHP_EOL;
        foreach ($paths as $path => $verbData) {
            foreach ($verbData as $verb => $data) {
                $question = new Question(sprintf($questionSentence, $data['action'], $path, $verb));
                if ($dialog->ask($input, $output, $question)) {
                    $this->paths[$path][$verb] = $data;
                }
            }
        }

        return $this;
    }

    /**
     * Retrieve the Symfony QuestionHelper object.
     *
     * @return QuestionHelper
     * @throws SFConsoleInvalidArgumentException
     * @throws SFConsoleLogicException
     */
    private function getQuestionHelper(): QuestionHelper
    {
        return $this->getHelper('question');
    }
}
