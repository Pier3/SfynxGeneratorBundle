<?php
namespace Sfynx\DddGeneratorBundle\Generator\Api\Generator;

use Sfynx\DddGeneratorBundle\Generator\Api\DddApiGenerator;
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
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Domain\Service\Odm\RepositoryFactoryHandler as OdmRepositoryFactoryHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Domain\Service\Orm\RepositoryFactoryHandler as OrmRepositoryFactoryHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Domain\Service\CouchDB\RepositoryFactoryHandler as CouchDBRepositoryFactoryHandler;

use Symfony\Component\Console\Output\OutputInterface;

class Domain
{
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
    protected $entitiesList;

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
        $this->entitiesList = $this->parseRoutes();
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
        $this->output->writeln('##############################################');
        $this->output->writeln('#          GENERATE DOMAIN STRUCTURE         #');
        $this->output->writeln('##############################################');
        $this->output->writeln('');

        $this->output->writeln('### ENTITIES & REPOSITORIES INTERFACES GENERATION ###');
        $this->generateEntitiesAndRepositoriesInterfaces();

        $this->output->writeln('### SERVICES GENERATION ###');
        $this->generateServices();

        $this->output->writeln('### WORKFLOW GENERATION ###');
        $this->generateWorkflow();

        $this->output->writeln('### VALUE OBJECTS GENERATION ###');
        $this->output->writeln(' - GOODLUCK, PREPARE YOUR BRAIN -');
        //$this->generateValueObject();exit;

        $this->output->writeln('### TESTS GENERATION ###');
        $this->output->writeln(' - BE MY GUEST ... -');
        $this->generateTests();
    }

    public function parseRoutes()
    {
        $entities = [];

        foreach ($this->pathsToCreate as $route => $verbData) {
            foreach ($verbData as $verb => $data) {
                $entities[] = $data;
            }
        }

        return $entities;
    }

    /**
     *
     * Generate :
     * /Domain/Entity/{entityName}.php
     * /Domain/Repository/{EntityName}RepositoryInterface.php
     */
    public function generateEntitiesAndRepositoriesInterfaces()
    {
        foreach ($this->entitiesToCreate as $entityName => $fields) {
            $this->output->writeln(' - Entity: ' . $entityName . ' -');

            $templateStringParameters = '';
            foreach ($fields as $fieldName => $field) {
                $templateStringParameters .= '$' . $fieldName . ', ';
            }

            $this->parameters['entityName'] = ucfirst(strtolower($entityName));
            $this->parameters['entityFields'] = $fields;
            $this->parameters['fields'] = $fields; //todo: unify these entityFields and fields
            $this->parameters['constructorArgs'] = trim($templateStringParameters, ', ');
            $this->parameters['managerArgs'] = trim($templateStringParameters, ', ');

            $this->generator->addHandler(new EntityHandler($this->parameters));
            $this->generator->addHandler(new EntityRepositoryInterfaceHandler($this->parameters));

            $this->generator->execute();
            $this->generator->clear();
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
        foreach ($this->entitiesToCreate as $entityName => $fields) {
            $this->output->writeln(' - Entity: ' . $entityName . ' -');

            $templateStringParameters = '';
            foreach ($fields as $fieldName => $field) {
                $templateStringParameters .= '$' . $fieldName . ', ';
            }

            $this->parameters['entityName'] = ucfirst(strtolower($entityName));
            $this->parameters['entityFields'] = $fields;
            $this->parameters['fields'] = $fields; //todo: unify these entityFields and fields
            $this->parameters['constructorArgs'] = trim($templateStringParameters, ', ');
            $this->parameters['managerArgs'] = trim($templateStringParameters, ', ');


            $this->generator->addHandler(new CouchDBRepositoryFactoryHandler($this->parameters));

            $this->generator->addHandler(new OdmRepositoryFactoryHandler($this->parameters));
            $this->generator->addHandler(new OrmRepositoryFactoryHandler($this->parameters));

            $this->generator->addHandler(new ManagerHandler($this->parameters));

            $this->generator->addHandler(new PrePersistProcessHandler($this->parameters));
            $this->generator->addHandler(new PostPersistProcessHandler($this->parameters));

            $this->generator->execute();
            $this->generator->clear();
        }
    }

    /**
     *
     * Generate :
     * /Domain/Workflow/{entityName}/Handler/NEwWFHandler.php
     * /Domain/Workflow/{entityName}/Handler/UpdateWFHandler.php
     * /Domain/Workflow/{entityName}/Listener/WGGenerateVOListener.php
     * /Domain/Workflow/{entityName}/Listener/WFGetCurrency.php
     * /Domain/Workflow/{entityName}/Listener/WFPublishEvent.php
     * /Domain/Workflow/{entityName}/Listener/WFSaveEntity.php
     *
     */
    public function generateWorkflow()
    {
        foreach (array_keys($this->entitiesToCreate) as $entityName) {
            // Create entities
            $constructorParams = null;
            $managerArgs = null;

            // Create constructor params
            $endConstructorParams = '';
            $constructorParams = '';

            foreach ($this->entities[$entityName] as $field) {
                $managerArgs .= '$' . $field['name'] . ', ';

                if ('valueObjectId' === $field['type'] || ('id' === $field['type'] && isset($field['voName']))) {
                    $endConstructorParams = $field['voName'] . ' $' . $field['name'] . ' = null';
                } else {
                    $constructorParams .=
                        ('valueObject' === $field['type'] ? $field['name'] : '') . '$' . $field['name'] . ', ';
                }
            }

            $constructorParams .= $endConstructorParams;

            $this->parameters['managerArgs'] = trim($managerArgs, ', ');
            $this->parameters['entityName'] = $entityName;
            $this->parameters['fields'] = $this->entities[$entityName];
            $this->parameters['constructorArgs'] = trim($constructorParams, ', ');
            $this->parameters['destinationPath'] = $this->destinationPath;

            $this->generator->addHandler(new NewWFHandlerHandler($this->parameters));
            $this->generator->addHandler(new UpdateWFHandlerHandler($this->parameters));
            $this->generator->addHandler(new PatchWFHandlerHandler($this->parameters));

            $this->generator->addHandler(new WFGenerateVOListenerHandler($this->parameters));
            $this->generator->addHandler(new WFGetCurrencyHandler($this->parameters));
            $this->generator->addHandler(new WFPublishEventHandler($this->parameters));
            $this->generator->addHandler(new WFSaveEntityHandler($this->parameters));
            $this->generator->addHandler(new WFRetrieveEntityHandler($this->parameters));

            $this->generator->execute();
            $this->generator->clear();
        }
    }

    /*public function generateValueObject()
    {
        // Create valueObjects
        foreach ($this->valueObjects as $name => $voToCreate) {
            $constructorParams = '';

            $this->parameters['voName'] = str_replace('vo', 'VO', $name);
            $this->parameters['fields'] = $voToCreate['fields'];

            $composite = (count($voToCreate['fields']) > 1);

            foreach ($voToCreate['fields'] as $field) {
                $constructorParams .= '$' . $field['name'] . ', ';
            }

            $this->parameters['constructorParams'] = trim($constructorParams, ', ');

            if ($composite) {
                $this->generator->addHandler(new ValueObjectCompositeHandler($this->parameters));
            } else {
                $this->generator->addHandler(new ValueObjectHandler($this->parameters));
            }




            $this->generator->addHandler(new ValueObjectTypeCouchDBHandler($this->parameters));

            $this->generator->addHandler(new ValueObjectTypeOdmHandler($this->parameters));

            $this->generator->addHandler(new ValueObjectTypeOrmHandler($this->parameters));




            $this->generator->execute();
            $this->generator->clear();
        }
    }*/

    public function generateTests()
    {
        // TODO: make some FUN .. or tests
    }
}
