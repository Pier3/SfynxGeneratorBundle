<?php
declare(strict_types = 1);

namespace Sfynx\DddGeneratorBundle\Generator\Api\Generator;

/**
 * Class Domain
 *
 * @category Generator
 * @package Api
 * @subpackage Generator
 */
class Domain extends LayerAbstract
{
    /** @var string */
    protected static $handlersFileName = 'domain.yml';

    /** @var string */
    protected static $skeletonDir = 'Api/Domain';

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

        try {
            $this->output->writeln('### ENTITIES ELEMENTS GENERATION ###');
            $this->generateEntitiesElements();

            $this->output->writeln('### VALUE OBJECTS GENERATION ###');
            $this->output->writeln(' - GOOD LUCK, PREPARE YOUR BRAIN -');
            //TODO: work on the generation of the Value Objects.
            //$this->generateValueObject();
        } catch (\InvalidArgumentException $e) {
            fwrite(STDERR, $e->getMessage());
            exit;
        }
    }

    /**
     * Generate the entities elements int the "Domain" layer.
     * Those elements are Entities and Repository Interfaces, Services and Workflow.
     * @throws \InvalidArgumentException
     */
    public function generateEntitiesElements()
    {
        foreach ($this->entitiesToCreate as $entityName => $fields) {
            $this->parameters['entityName'] = ucfirst(strtolower($entityName));
            $this->parameters['entityFields'] = $fields;
            $this->parameters['constructorArgs'] = $this->buildConstructorParamsString($entityName);

            $this->addHandlers(
                //Entities and Repository Interfaces
                'entityHandler',
                'entityRepositoryHandlerInterfaceHandler',
                //Services
                'couchDbRepositoryFactoryHandler',
                'odmRepositoryFactoryHandler',
                'ormRepositoryFactoryHandler',
                'managerHandler',
                'prePersistProcessHandler',
                'postPersistProcessHandler',
                //Workflow
                'newWFHandlerHandler',
                'updateWFHandlerHandler',
                'patchWFHandlerHandler',
                'wFGenerateVOListenerHandler',
                'wFGetCurrencyHandler',
                'wFPublishEventHandler',
                'wFSaveEntityHandler',
                'wFRetrieveEntityHandler'
            );
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
}
