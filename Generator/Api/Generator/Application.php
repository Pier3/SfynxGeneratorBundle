<?php
namespace Sfynx\DddGeneratorBundle\Generator\Api\Generator;

use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Adapter\SearchByAdapterHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Application\Command\DeleteCommandHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Application\Command\Handler\Decorator\NewCommandHandlerDecoratorHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Application\Command\Handler\Decorator\PatchCommandHandlerDecoratorHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Application\Command\Handler\Decorator\UpdateCommandHandlerDecoratorHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Application\Command\Handler\DeleteCommandHandlerHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Application\Command\Handler\DeleteManyCommandHandlerHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Application\Command\Handler\NewCommandHandlerHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Application\Command\Handler\PatchCommandHandlerHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Application\Command\Handler\UpdateCommandHandlerHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Application\Command\NewCommandHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Application\Command\PatchCommandHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Application\Command\UpdateCommandHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Application\Command\Validation\SpecHandler\NewCommandSpecHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Application\Command\Validation\SpecHandler\PatchCommandSpecHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Application\Command\Validation\SpecHandler\UpdateCommandSpecHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Application\Command\Validation\ValidationHandler\NewCommandValidationHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Application\Command\Validation\ValidationHandler\PatchCommandValidationHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Application\Command\Validation\ValidationHandler\UpdateCommandValidationHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Application\Query\CustomQueryHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Application\Query\GetAllQueryHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Application\Query\GetByIdsHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Application\Query\GetQueryHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Application\Query\Handler\GetAllQueryHandlerHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Application\Query\Handler\GetByIdsHandlerHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Application\Query\Handler\GetQueryHandlerHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Application\Query\Handler\SearchByQueryHandlerHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Application\Query\SearchByQueryHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Tests\Application\Entity\Command\Handler\Decorator\NewCommandHandlerDecoratorTestHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Tests\Application\Entity\Command\Handler\Decorator\PatchCommandHandlerDecoratorTestHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Tests\Application\Entity\Command\Handler\Decorator\UpdateCommandHandlerDecoratorTestHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Tests\Application\Entity\Command\Handler\DeleteCommandHandlerTestHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Tests\Application\Entity\Command\DeleteCommandTestHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Tests\Application\Entity\Command\Handler\DeleteManyCommandHandlerTestHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Tests\Application\Entity\Command\Handler\NewCommandHandlerTestHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Tests\Application\Entity\Command\Handler\PatchCommandHandlerTestHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Tests\Application\Entity\Command\Handler\UpdateCommandHandlerTestHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Tests\Application\Entity\Command\NewCommandTestHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Tests\Application\Entity\Command\UpdateCommandTestHandler;
use Tests\Application\Country\Application\Country\Command\Validation\NewCommandValidationHandlerTest;

class Application
{
    protected $generator;
    protected $entities = [];
    protected $entitiesToCreate = [];
    protected $valueObjects = [];
    protected $valueObjectsToCreate = [];
    protected $paths = [];
    protected $pathsToCreate = [];
    protected $projectDir;
    protected $destinationPath;

    public function __construct($generator, $entities, $entitiesToCreate, $valueObjects, $valueObjectsToCreate, $paths, $pathsToCreate, $rootDir, $projectDir, $destinationPath, $output)
    {
        $this->generator = $generator;
        $this->destinationPath = $destinationPath;
        $this->output = $output;
        $this->entities = $entities;
        $this->entitiesToCreate = $entitiesToCreate;
        $this->valueObjects = $valueObjects;
        $this->valueObjectsToCreate = $valueObjectsToCreate;
        $this->paths = $paths;
        $this->pathsToCreate = $pathsToCreate;
        $this->projectDir = $projectDir;
        $this->rootDir = $rootDir;
    }

    public function generate()
    {
        $this->output->writeln("#############################################");
        $this->output->writeln("# GENERATE APPLICATION STRUCTURE            #");
        $this->output->writeln("#############################################");

        $this->generateCommands();
        $this->generateQueries();
        $this->generateTests();
    }

    public function generateCommands()
    {
        foreach ($this->pathsToCreate as $route => $verbData) {
            foreach ($verbData as $verb => $data) {
                if (in_array($data["action"], ["update", "new", "delete", "patch"])) {
                    $constructorParams = $managerArgs = "";
                    foreach ($this->entities[$data["entity"]] as $field) {
                        if ($data["action"] == "new") {
                            $constructorParams .= "$" . $field['name'] . ",";
                            if ($field["type"] != "id") {
                                $managerArgs .= "$" . $field['name'] . ",";
                            }
                        } else {
                            $constructorParams .= "$" . $field['name'] . ",";
                            $managerArgs .= "$" . $field['name'] . ",";
                        }
                    }

                    $parameters = [
                        'rootDir' => $this->rootDir . "/src",
                        'projectDir' => $this->projectDir,
                        'projectName' => str_replace('src/', '', $this->projectDir),
                        'actionName' => ucfirst(strtolower($data['action'])),
                        'entityName' => ucfirst(strtolower($data['entity'])),
                        'entityFields' => $this->entities[$data['entity']],
                        'managerArgs' => trim($managerArgs, ', '),
                        'fields' => $this->entities[$data['entity']],
                        'valueObjects' => $this->valueObjects,
                        'constructorArgs' => trim($constructorParams, ', '),
                        'destinationPath' => $this->destinationPath,
                    ];

                    // Command
                    $this->generator->addHandler(new UpdateCommandHandler($parameters));
                    // Decorator
                    $this->generator->addHandler(new UpdateCommandHandlerDecoratorHandler($parameters));
                    // Handler
                    $this->generator->addHandler(new UpdateCommandHandlerHandler($parameters));
                    // SpecHandler
                    $this->generator->addHandler(new UpdateCommandSpecHandler($parameters));
                    // ValidationHandler
                    $this->generator->addHandler(new UpdateCommandValidationHandler($parameters));

                    // Command
                    $this->generator->addHandler(new NewCommandHandler($parameters));
                    // Decorator
                    $this->generator->addHandler(new NewCommandHandlerDecoratorHandler($parameters));
                    // Handler
                    $this->generator->addHandler(new NewCommandHandlerHandler($parameters));
                    // SpecHandler
                    $this->generator->addHandler(new NewCommandSpecHandler($parameters));
                    // ValidationHandler
                    $this->generator->addHandler(new NewCommandValidationHandler($parameters));

                    // Command
                    $this->generator->addHandler(new DeleteCommandHandler($parameters));
                    // Handler
                    $this->generator->addHandler(new DeleteManyCommandHandlerHandler($parameters));
                    $this->generator->addHandler(new DeleteCommandHandlerHandler($parameters));

                    // Command
                    $this->generator->addHandler(new PatchCommandHandler($parameters));
                    // Decorator
                    $this->generator->addHandler(new PatchCommandHandlerDecoratorHandler($parameters));
                    // Handler
                    $this->generator->addHandler(new PatchCommandHandlerHandler($parameters));
                    // SpecHandler
                    $this->generator->addHandler(new PatchCommandSpecHandler($parameters));
                    // ValidationHandler
                    $this->generator->addHandler(new PatchCommandValidationHandler($parameters));

                    $this->generator->execute();
                    $this->generator->clear();
                }
            }
        }
    }

    public function generateQueries()
    {
        foreach ($this->pathsToCreate as $route => $verbData) {
            foreach ($verbData as $verb => $data) {

                if (in_array($data["action"], ["get", "getAll", "searchBy", "getByIds"])) {
                    $constructorParams = $managerArgs = "";
                    foreach ($this->entities[$data["entity"]] as $field) {
                        $constructorParams .= "$" . $field['name'] . ",";
                        $managerArgs .= "$" . $field['name'] . ",";
                    }

                    $parameters = [
                        'rootDir' => $this->rootDir . "/src",
                        'projectDir' => $this->projectDir,
                        'projectName' => str_replace('src/', '', $this->projectDir),
                        'actionName' => ucfirst(strtolower($data['action'])),
                        'entityName' => ucfirst(strtolower($data['entity'])),
                        'entityFields' => $this->entities[$data['entity']],
                        'managerArgs' => trim($managerArgs, ', '),
                        'fields' => $this->entities[$data['entity']],
                        'valueObjects' => $this->valueObjects,
                        'constructorArgs' => trim($constructorParams, ', '),
                        'destinationPath' => $this->destinationPath,
                    ];

                    $this->generator->addHandler(new GetAllQueryHandler($parameters));
                    $this->generator->addHandler(new GetAllQueryHandlerHandler($parameters));


                    $this->generator->addHandler(new GetQueryHandler($parameters));
                    $this->generator->addHandler(new GetQueryHandlerHandler($parameters));

                    $this->generator->addHandler(new GetByIdsHandler($parameters));
                    $this->generator->addHandler(new GetByIdsHandlerHandler($parameters));


                    $this->generator->addHandler(new SearchByQueryHandler($parameters));
                    $this->generator->addHandler(new SearchByQueryHandlerHandler($parameters));

                    $this->generator->execute();
                    $this->generator->clear();
                }
            }
        }
    }

    public function generateTests() {
        foreach ($this->pathsToCreate as $route => $verbData) {
            foreach ($verbData as $verb => $data) {
                if (in_array($data["action"], ["update", "new", "delete", "patch"])) {
                    $constructorParams = $managerArgs = "";
                    foreach ($this->entities[$data["entity"]] as $field) {
                        if ($data["action"] == "new") {
                            $constructorParams .= "$" . $field['name'] . ",";
                            if ($field["type"] != "id") {
                                $managerArgs .= "$" . $field['name'] . ",";
                            }
                        } else {
                            $constructorParams .= "$" . $field['name'] . ",";
                            $managerArgs .= "$" . $field['name'] . ",";
                        }
                    }

                    $parameters = [
                        'rootDir' => $this->rootDir . "/src",
                        'projectDir' => $this->projectDir,
                        'projectName' => str_replace('src/', '', $this->projectDir),
                        'actionName' => ucfirst(strtolower($data['action'])),
                        'entityName' => ucfirst(strtolower($data['entity'])),
                        'entityFields' => $this->entities[$data['entity']],
                        'managerArgs' => trim($managerArgs, ', '),
                        'fields' => $this->entities[$data['entity']],
                        'valueObjects' => $this->valueObjects,
                        'constructorArgs' => trim($constructorParams, ', '),
                        'destinationPath' => $this->destinationPath,

                    ];

                    // Command
                    $this->generator->addHandler(new UpdateCommandTestHandler($parameters));
                    // Decorator
                    $this->generator->addHandler(new UpdateCommandHandlerDecoratorTestHandler($parameters));
                    // Handler
                    $this->generator->addHandler(new UpdateCommandHandlerTestHandler($parameters));

                    // Command
                    $this->generator->addHandler(new NewCommandTestHandler($parameters));
                    // Decorator
                    $this->generator->addHandler(new NewCommandHandlerDecoratorTestHandler($parameters));
                    // Handler
                    $this->generator->addHandler(new NewCommandHandlerTestHandler($parameters));

                    // Command
                    $this->generator->addHandler(new DeleteCommandTestHandler($parameters));
                    // Handler
                    $this->generator->addHandler(new DeleteManyCommandHandlerTestHandler($parameters));
                    $this->generator->addHandler(new DeleteCommandHandlerTestHandler($parameters));


                    // Command
                    $this->generator->addHandler(new PatchCommandHandlerTestHandler($parameters));
                    // Decorator
                    $this->generator->addHandler(new PatchCommandHandlerDecoratorTestHandler($parameters));
                    // Handler
                    $this->generator->addHandler(new PatchCommandHandlerTestHandler($parameters));
                    // SpecHandler

                    /*
                    $this->generator->addHandler(new GetAllQueryHandler($parameters));
                    $this->generator->addHandler(new GetAllQueryHandlerHandler($parameters));


                    $this->generator->addHandler(new GetQueryHandler($parameters));
                    $this->generator->addHandler(new GetQueryHandlerHandler($parameters));

                    $this->generator->addHandler(new GetByIdsHandler($parameters));
                    $this->generator->addHandler(new GetByIdsHandlerHandler($parameters));


                    $this->generator->addHandler(new SearchByQueryHandler($parameters));
                    $this->generator->addHandler(new SearchByQueryHandlerHandler($parameters));
                    */

                    $this->generator->execute();
                    $this->generator->clear();
                }
            }
        }
    }
}
