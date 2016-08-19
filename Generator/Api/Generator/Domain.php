<?php
namespace Sfynx\DddGeneratorBundle\Generator\Api\Generator;

use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Domain\Repository\EntityRepositoryInterfaceHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Domain\Service\Manager\ManagerHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Domain\Service\Processor\PostPersistProcessHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Domain\Service\Processor\PrePersistProcessHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Domain\Workflow\Handler\NewWFHandlerHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Domain\Workflow\Handler\PatchWFHandlerHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Domain\Workflow\Handler\UpdateWFHandlerHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Domain\Workflow\Listener\WFGenerateVOListenerHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Domain\Workflow\Listener\WFGetCurrencyHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Domain\Workflow\Listener\WFPublishEventHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Domain\Workflow\Listener\WFRetrieveEntityHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Domain\Workflow\Listener\WFSaveEntityHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Domain\Entity\EntityHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Domain\Service\Odm\RepositoryFactoryHandler as ODMRepositoryFactoryHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Domain\Service\Orm\RepositoryFactoryHandler as ORMRepositoryFactoryHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Domain\Service\CouchDB\RepositoryFactoryHandler as COUCHDBRepositoryFactoryHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Tests\Domain\Service\Entity\Factory\Orm\CountryManagerTestHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Tests\Domain\Service\Entity\Factory\Orm\RepositoryFactoryHandler as ORMRepositoryFactoryTestHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\ValueObject\ValueObjectCompositeHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\ValueObject\ValueObjectHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\ValueObject\ValueObjectTypeCouchDBHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\ValueObject\ValueObjectTypeODMHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\ValueObject\ValueObjectTypeORMHandler;

class Domain
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
        $this->output->writeln("# GENERATE DOMAIN  STRUCTURE                #");
        $this->output->writeln("#############################################");

        $this->generateEntitiesAndRepositoriesInterfaces();
        $this->generateServices();
        $this->generateWorkflow();
        $this->generateValueObject();
        $this->generateTests();
    }

    /**
     *
     * Generate :
     * /Domain/Entity/{entityName}.php
     * /Domain/Repository/{EntityName}RepositoryInterface.php
     */
    public function generateEntitiesAndRepositoriesInterfaces()
    {
        foreach ($this->pathsToCreate as $route => $verbData) {
            foreach ($verbData as $verb => $data) {

                $constructorParams = $managerArgs = '';
                foreach ($this->entities[$data['entity']] as $field) {
                    $constructorParams .= '$' . $field['name'] . ', ';
                    $managerArgs .= '$' . $field['name'] . ', ';
                }

                $parameters = [
                    'rootDir' => $this->rootDir . '/src',
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

                $this->generator->addHandler(new EntityHandler($parameters));
                $this->generator->addHandler(new EntityRepositoryInterfaceHandler($parameters));
                $this->generator->execute();
                $this->generator->clear();
            }
        }
    }

    /**
     *
     * Generate :
     * /Domain/Service/{entityName}/Manager/{entityName}Manager.php
     * /Domain/Service/{entityName}/Factory/CouchDB/RepositoryFactory.php
     * /Domain/Service/{entityName}/Factory/Odm/RepositoryFactory.php
     * /Domain/Service/{entityName}/Factory/Orm/RepositoryFactory.php
     * /Domain/Service/{entityName}/Processor/PostPersistProcess.php
     * /Domain/Service/{entityName}/Processor/PrePersistProcess.php
     */
    public function generateServices()
    {
        foreach ($this->pathsToCreate as $route => $verbData) {
            foreach ($verbData as $verb => $data) {

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

                $this->generator->addHandler(new COUCHDBRepositoryFactoryHandler($parameters));
                $this->generator->addHandler(new ODMRepositoryFactoryHandler($parameters));
                $this->generator->addHandler(new ORMRepositoryFactoryHandler($parameters));

                $this->generator->addHandler(new ManagerHandler($parameters));


                $this->generator->addHandler(new PrePersistProcessHandler($parameters));
                $this->generator->addHandler(new PostPersistProcessHandler($parameters));

                $this->generator->execute();
                $this->generator->clear();
            }
        }
    }

    /**
     *
     * Generate :
     * /Domain/Workflow/{entityName}/Handler/NEwWFHandler.php
     * /Domain/Workflow/{entityName}/Handler/UpdateWFHandler.php
     * /Domain/Workflow/{entityName}/Listener/WGGenerateVOLIstener.php
     * /Domain/Workflow/{entityName}/Listener/WFGetCurrency.php
     * /Domain/Workflow/{entityName}/Listener/WFPublishEvent.php
     * /Domain/Workflow/{entityName}/Listener/WFSaveEntity.php
     *
     */
    public function generateWorkFlow()
    {
        foreach ($this->entitiesToCreate as $entityName => $fields) {
            // Create entities
            $constructorParams = null;
            $managerArgs = null;
            // Create constructor params
            $endConstructorParams = "";
            $constructorParams = "";
            foreach ($this->entities[$entityName] as $field) {
                if ($field['type'] == 'valueObjectId' || ($field["type"] == 'id' && isset($field["voName"]))) {
                    $endConstructorParams = $field['voName'] . " $" . $field['name'] . " = null";
                    $managerArgs .= "$" . $field['name'] . ",";
                } elseif ($field["type"] == "valueObject") {
                    $constructorParams .= $field['name'] . " $" . $field['name'] . ",";
                } else {
                    $managerArgs .= " $" . $field['name'] . ",";
                    $constructorParams .= "$" . $field['name'] . ",";
                }
            }
            $constructorParams .= $endConstructorParams;


            $parameters = [
                'rootDir' => $this->rootDir . "/src",
                'projectDir' => $this->projectDir,
                'projectName' => str_replace('src/', '', $this->projectDir),
                'managerArgs' => trim($managerArgs, ', '),
                'fields' => $this->entities[$entityName],
                'valueObjects' => $this->valueObjects,
                'entityName' => $entityName,
                'constructorArgs' => trim($constructorParams, ', '),
                'destinationPath' => $this->destinationPath,
            ];

            $this->generator->addHandler(new NewWFHandlerHandler($parameters));
            $this->generator->addHandler(new UpdateWFHandlerHandler($parameters));
            $this->generator->addHandler(new PatchWFHandlerHandler($parameters));

            $this->generator->addHandler(new WFGenerateVOListenerHandler($parameters));
            $this->generator->addHandler(new WFGetCurrencyHandler($parameters));
            $this->generator->addHandler(new WFPublishEventHandler($parameters));
            $this->generator->addHandler(new WFSaveEntityHandler($parameters));
            $this->generator->addHandler(new WFRetrieveEntityHandler($parameters));

            $this->generator->execute();
            $this->generator->clear();
        }
    }

    public function generateValueObject()
    {
        // Create valueObjects
        foreach ($this->valueObjects as $name => $voToCreate) {
            $parameters = [
                'rootDir' => $this->rootDir . "/src",
                'projectDir' => $this->projectDir,
                'voName' => str_replace('vo', 'VO', $name),
                'projectName' => str_replace('src/', '', $this->projectDir),
                'valueObjects' => $this->valueObjects,
                'destinationPath' => $this->destinationPath,
            ];

            $composite = false;

            if (count($voToCreate['fields']) > 1) {
                $composite = true;
            }

            $constructorParams = "";
            $parameters['fields'] = $voToCreate['fields'];

            foreach ($voToCreate["fields"] as $field) {
                $constructorParams .= "$" . $field["name"] . ",";
            }
            $parameters["constructorParams"] = trim($constructorParams, ",");

            if ($composite) {
                $this->generator->addHandler(new ValueObjectCompositeHandler($parameters));
            } else {
                $this->generator->addHandler(new ValueObjectHandler($parameters));
            }

            $this->generator->addHandler(new ValueObjectTypeCouchDBHandler($parameters));
            $this->generator->addHandler(new ValueObjectTypeODMHandler($parameters));
            $this->generator->addHandler(new ValueObjectTypeORMHandler($parameters));

            $this->generator->execute();
            $this->generator->clear();
        }
    }

    public function generateTests()
    {
        foreach ($this->pathsToCreate as $route => $verbData) {
            foreach ($verbData as $verb => $data) {

                $parameters = [
                    'rootDir' => $this->rootDir . "/src",
                    'projectDir' => $this->projectDir,
                    'projectName' => str_replace('src/', '', $this->projectDir),
                    'actionName' => ucfirst(strtolower($data['action'])),
                    'entityName' => ucfirst(strtolower($data['entity'])),
                    'entityFields' => $this->entities[$data['entity']],
                    'fields' => $this->entities[$data['entity']],
                    'valueObjects' => $this->valueObjects,
                    'destinationPath' => $this->destinationPath,
                ];

            $this->generator->addHandler(new ORMRepositoryFactoryTestHandler($parameters));
            $this->generator->addHandler(new CountryManagerTestHandler($parameters));

            $this->generator->execute();
            $this->generator->clear();
        }
    }
}
