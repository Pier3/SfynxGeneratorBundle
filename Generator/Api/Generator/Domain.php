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
            $this->generateValueObject();
        } catch (\InvalidArgumentException $e) {
            fwrite(STDERR, $e->getMessage());
            exit;
        }
    }

    /**
     * Generate the entities elements int the "Domain" layer.
     * Those elements are Entities and Repository Interfaces, Services and Workflow.
     *
     * @throws \InvalidArgumentException
     */
    public function generateEntitiesElements()
    {
        foreach ($this->entitiesToCreate as $entityName => $fields) {
            $this->parameters['entityName'] = ucfirst(strtolower($entityName));
            $this->parameters['entityFields'] = $fields;
            $this->parameters['constructorArgs'] = $this->buildConstructorParamsString($entityName);
            $this->parameters['paramsString'] = $this->buildParamsString($entityName);
            $this->parameters['useRepository'] = $this->buildActionName();

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

    /**
     * Generate the Value Object part in the "Domain" layer.
     *
     * @throws \InvalidArgumentException
     */
    public function generateValueObject()
    {
        // Create valueObjects
        foreach ($this->valueObjectsToCreate as $name => $voToCreate) {
            $this->parameters['voName'] = ucfirst(str_replace('vo', 'VO', $name));
            $this->parameters['fields'] = $voToCreate['fields'];
            $this->parameters['constructorParams'] = $this->buildValueObjectParamsString($voToCreate['fields']);

            $handlerName = (count($voToCreate['fields']) > 1) ? 'valueObjectCompositeHandler' : 'valueObjectHandler';
            $this->addHandler($handlerName);
        }
        $this->generator->execute()->clear();
    }
}
