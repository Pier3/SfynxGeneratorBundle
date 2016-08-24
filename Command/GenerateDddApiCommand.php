<?php
//todo à revoir
namespace Sfynx\DddGeneratorBundle\Command;

use Sfynx\DddGeneratorBundle\Generator\Api\Generator\Application;
use Sfynx\DddGeneratorBundle\Generator\Api\Generator\Domain;
use Sfynx\DddGeneratorBundle\Generator\Api\Generator\Infrastructure;
use Sfynx\DddGeneratorBundle\Generator\Api\Generator\InfrastructureBundle;
use Sfynx\DddGeneratorBundle\Generator\Api\Generator\Presentation;
use Sfynx\DddGeneratorBundle\Generator\Api\Generator\PresentationBundle;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Yaml\Parser;

class GenerateDddApiCommand extends Command
{
    protected $generator;
    protected $actionsToCreate;
    protected $rootDir;
    protected $destinationPath;
    protected $contextName;

    protected $config;
    protected $entities = [];
    protected $entitiesToCreate = [];
    protected $valueObjects = [];
    protected $valueObjectsToCreate = [];
    protected $paths = [];
    protected $pathsToCreate = [];

    public function setGenerator($generator)
    {
        $this->generator = $generator;
    }

    public function setConfig($config)
    {
        $this->config = $config;
    }

    public function setRootDir($rootDir)
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $rootDir = str_replace("\\app", "", $rootDir);
        } else {
            $rootDir = str_replace("/app", "", $rootDir);
        }
        $this->rootDir = $rootDir;
    }

    /**
     * @see Command
     * @throws \InvalidArgumentException When the name is invalid
     */
    public function configure()
    {
        $this
            ->setName('sfynx:api')
            ->setDescription('Generates a ddd api')
            ->addArgument('context-name', InputArgument::REQUIRED, 'Context name')
            ->addArgument('path-to-swagger-file', InputArgument::OPTIONAL, 'Path to swagger yml file.')
            ->addArgument('destination-path', InputArgument::OPTIONAL, 'Destination path', '/tmp')
            ->addOption('create-all', null, InputOption::VALUE_NONE, 'Generate all items.')
            ->setHelp("Generate a ddd rest API for an entity");
    }

    public function interact(InputInterface $input, OutputInterface $output)
    {
        $dialog = $this->getHelper('question');
        //set argument : path_to_swagger_file
        if (isset($_SERVER['SYMFONY_SFYNX_PATH_TO_SWAGGER_FILE'])) {
            $pathToSwaggerEntityFile = $_SERVER['SYMFONY_SFYNX_PATH_TO_SWAGGER_FILE'];
        } else {
            $pathToSwaggerEntityFile = $dialog->ask(
                $input,
                $output,
                new Question('Path to swagger yml file: ')
            );

            while (!is_file($pathToSwaggerEntityFile)) {
                //Set the entity name
                $output->writeln("This file doesn't exist");
                //Todo: check the existence of the entity-name
                $dialog = $this->getHelper('question');
                $pathToSwaggerEntityFile = $dialog->ask(
                    $input,
                    $output,
                    new Question('Path to swagger yml file: ')
                );
            }
        }
        $input->setArgument('path-to-swagger-file', $pathToSwaggerEntityFile);

        //set argument : destination_path
        if (isset($_SERVER['SYMFONY_SFYNX_PATH_TO_DEST_FILES'])) {
            $destPath = $_SERVER['SYMFONY_SFYNX_PATH_TO_DEST_FILES'];
        } else {
            $destPath = $dialog->ask(
                $input,
                $output,
                new Question('destination path: ')
            );

            while (!is_dir($destPath) || !is_writable($destPath)) {
                //Set the entity name
                $output->writeln("This directory doesn't exist or is not writable");
                $dialog = $this->getHelper('question');
                $destPath = $dialog->ask(
                    $input,
                    $output,
                    new Question('Path to swagger yml file: ')
                );
            }
        }
        $input->setArgument('destination-path', $destPath);


        //set argument : context-name
        if (isset($_SERVER['SYMFONY_SFYNX_CONTEXT_NAME'])) {
            $contextName = $_SERVER['SYMFONY_SFYNX_CONTEXT_NAME'];
        } else {
            $contextName = $dialog->ask(
                $input,
                $output,
                new Question('context name: ')
            );
        }
        $input->setArgument('context-name', $contextName);
        // Parse swagger File
        $this->parseSwaggerFile($input, $output);
    }

    /**
     *
     * Main function, execute the generator
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $projectDir = $input->getArgument('context-name');
        $destinationPath = $input->getArgument('destination-path');

        $applicationGenerator = new Application($this->generator, $this->entities, $this->entitiesToCreate, $this->valueObjects, $this->valueObjectsToCreate, $this->paths, $this->pathsToCreate, $this->rootDir, $projectDir, $destinationPath, $output);
        $domainGenerator = new Domain($this->generator, $this->entities, $this->entitiesToCreate, $this->valueObjects, $this->valueObjectsToCreate, $this->paths, $this->pathsToCreate, $this->rootDir, $projectDir, $destinationPath, $output);
        $presentationGenerator = new Presentation($this->generator, $this->entities, $this->entitiesToCreate, $this->valueObjects, $this->valueObjectsToCreate, $this->paths, $this->pathsToCreate, $this->rootDir, $projectDir, $destinationPath, $output);
        $presentationBundleGenerator = new PresentationBundle($this->generator, $this->entities, $this->entitiesToCreate, $this->valueObjects, $this->valueObjectsToCreate, $this->paths, $this->pathsToCreate, $this->rootDir, $projectDir, $destinationPath, $output);
        $infrastructureGenerator = new Infrastructure($this->generator, $this->entities, $this->entitiesToCreate, $this->valueObjects, $this->valueObjectsToCreate, $this->paths, $this->pathsToCreate, $this->rootDir, $projectDir, $destinationPath, $output);
        $infrastructureBundleGenerator = new InfrastructureBundle($this->generator, $this->entities, $this->entitiesToCreate, $this->valueObjects, $this->valueObjectsToCreate, $this->paths, $this->pathsToCreate, $this->rootDir, $projectDir, $destinationPath, $output);

        $applicationGenerator->generate();
        $domainGenerator->generate();
        $presentationGenerator->generate();
        $presentationBundleGenerator->generate();
        $infrastructureGenerator->generate();
        $infrastructureBundleGenerator->generate();
    }

    /**
     * Parse the swaggerFile
     * read entity, route and actions
     *
     * @param $input
     * @param $output
     */
    public function parseSwaggerFile($input, $output)
    {
        $dialog = $this->getHelper('question');
        $ymlParser = new Parser();
        $this->config = $ymlParser->parse(file_get_contents($input->getArgument('path-to-swagger-file')));
        $this->valueObjects = $this->parseValueObjects();
        $this->entities = $this->parseEntities();
        $this->paths = $this->parseRoutes();

        foreach ($this->valueObjects as $voName => $fields) {
            if ($input->getOption("create-all") || $dialog->ask(
                    $input,
                    $output,
                    new Question(sprintf('Do you want to create the valueObject "%s" ? [Y/n]' . PHP_EOL, $voName))
                )
            ) {
                $this->valueObjectsToCreate[] = $voName;
            }
        }

        foreach ($this->entities as $entityName => $fields) {
            if ($input->getOption("create-all") || $dialog->ask(
                    $input,
                    $output,
                    new Question(sprintf('Do you want to create the entity "%s" ? [Y/n]' . PHP_EOL, $entityName))
                )
            ) {
                $this->entitiesToCreate[$entityName][] = $fields;
            }
        }

        foreach ($this->paths as $path => $verbData) {
            foreach ($verbData as $verb => $data) {
                if ($input->getOption("create-all") || $dialog->ask(
                        $input,
                        $output,
                        new Question(sprintf('Do you want create the "' . $data['action'] . '" action for route "' . $path . '"" and verb "' . $data['verb'] . '" ? [Y/n]'))
                    )
                ) {
                    $this->pathsToCreate[$path][$data["verb"]] = $data;
                }
            }
        }
    }

    /**
     * Parse route from a parsed Swagger File
     *
     * Create a route with needed informations
     */
    protected function parseRoutes()
    {
        $results = [];
        foreach ($this->config["paths"] as $path => $data) {
            foreach ($data as $verb => $verbData) {
                $result = [];
                $result["verb"] = $verb;
                $result["action"] = $verbData["operationId"];
                $result["description"] = $verbData["description"];
                $result["controller"] = $verbData["x-controller"];
                $result["entity"] = $verbData["x-entity"];
                $results[$path][$verb] = $result;
            }
        }

        return $results;
    }

    /**
     * Parse Value object from a swagger file
     */
    protected function parseValueObjects()
    {
        $results = [];
        if (isset($this->config['x-valueObjects'])) {
            foreach ($this->config['x-valueObjects'] as $name => $data) {
                if (!isset($results[$name])) {
                    $results[$name] = [];
                }
                $results[$name]['type'] = $data['type'];
                $results[$name]['name'] = $data['name'];
                foreach ($data['x-fields'] as $field) {
                    $results[$name]['fields'][] = $field;
                }
            }
        }

        return $results;
    }

    /**
     * Parse entities from a swagger file
     */
    protected function parseEntities()
    {
        $results = [];
        if (isset($this->config['x-entities'])) {
            foreach ($this->config['x-entities'] as $name => $data) {
                foreach ($data['x-fields'] as $field) {
                    $results[$name][] = $field;
                }
            }
        }

        return $results;
    }
}
