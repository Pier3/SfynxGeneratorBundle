<?php
declare(strict_types=1);

namespace Sfynx\DddGeneratorBundle\Generator\Api\Generator;

//Entities and Repositories Interfaces
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Domain\Entity\EntityHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Domain\Repository\EntityRepositoryInterfaceHandler;

//Services
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Domain\Service\{
    Manager\ManagerHandler,
    Processor\PrePersistProcessHandler,
    Processor\PostPersistProcessHandler,
    CouchDB\RepositoryFactoryHandler as CouchDbRepositoryFactoryHandler,
    Odm\RepositoryFactoryHandler as OdmRepositoryFactoryHandler,
    Orm\RepositoryFactoryHandler as OrmRepositoryFactoryHandler
};

//Workflow Handlers
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Domain\Workflow\Handler\{
    NewWFHandlerHandler,
    PatchWFHandlerHandler,
    UpdateWFHandlerHandler
};

//Workflow Listeners
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Domain\Workflow\Listener\{
    WFGenerateVOListenerHandler,
    WFGetCurrencyHandler,
    WFPublishEventHandler,
    WFRetrieveEntityHandler,
    WFSaveEntityHandler
};

/**
 * Class Domain
 *
 * @category Generator
 * @package Api
 * @subpackage Generator
 */
class Domain extends LayerAbstract
{
    /**
     * Entry point of the generation of the "Domain" layer in DDD.
     * Call the generation of :
     * - Entities and Repositories Interfaces ;
     * - Services ;
     * - Workflow ;
     * - Value objects ;
     * - Tests of the whole "Domain" layer.
     */
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
        $this->output->writeln(' - GOOD LUCK, PREPARE YOUR BRAIN -');
        //TODO: work on the generation of the Value Objects.
        //$this->generateValueObject();

        $this->output->writeln('### TESTS GENERATION ###');
        $this->output->writeln(' - BE MY GUEST ... -');
        //TODO: work on the generation of the tests.
        //$this->generateTests();
    }

    /**
     * Generate the Entities and repositories interfaces part in the "Domain" layer.
     */
    public function generateEntitiesAndRepositoriesInterfaces()
    {
        foreach ($this->entitiesToCreate as $entityName => $fields) {
            $this->output->writeln(' - Entity: ' . $entityName . ' -');

            $this->parameters['entityName'] = ucfirst(strtolower($entityName));
            $this->parameters['entityFields'] = $fields;
            $this->parameters['constructorArgs'] = $this->buildConstructorParamsString($entityName);

            $this->generator->addHandler(new EntityHandler($this->parameters), true);
            $this->generator->addHandler(new EntityRepositoryInterfaceHandler($this->parameters), true);
        }

        $this->generator->execute()->clear();
    }

    /**
     * Generate the Services part in the "Domain" layer.
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
            $this->parameters['constructorArgs'] = $this->buildConstructorParamsString($entityName);

            $this->generator->addHandler(new CouchDbRepositoryFactoryHandler($this->parameters), true);
            $this->generator->addHandler(new OdmRepositoryFactoryHandler($this->parameters), true);
            $this->generator->addHandler(new OrmRepositoryFactoryHandler($this->parameters), true);

            $this->generator->addHandler(new ManagerHandler($this->parameters), true);

            $this->generator->addHandler(new PrePersistProcessHandler($this->parameters), true);
            $this->generator->addHandler(new PostPersistProcessHandler($this->parameters), true);
        }

        $this->generator->execute()->clear();
    }

    /**
     * Generate the Workflow part in the "Domain" layer.
     */
    public function generateWorkflow()
    {
        foreach (array_keys($this->entitiesToCreate) as $entityName) {
            $this->parameters['entityName'] = $entityName;
            $this->parameters['entityFields'] = $this->entities[$entityName];

            $this->generator->addHandler(new NewWFHandlerHandler($this->parameters), true);
            $this->generator->addHandler(new UpdateWFHandlerHandler($this->parameters), true);
            $this->generator->addHandler(new PatchWFHandlerHandler($this->parameters), true);

            $this->generator->addHandler(new WFGenerateVOListenerHandler($this->parameters), true);
            $this->generator->addHandler(new WFGetCurrencyHandler($this->parameters), true);
            $this->generator->addHandler(new WFPublishEventHandler($this->parameters), true);
            $this->generator->addHandler(new WFSaveEntityHandler($this->parameters), true);
            $this->generator->addHandler(new WFRetrieveEntityHandler($this->parameters), true);
        }
        $this->generator->execute()->clear();
    }

    /*
    public function generateValueObject()
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
    }
    */

    public function generateTests()
    {
        // TODO: make some FUN .. or tests
    }
}
