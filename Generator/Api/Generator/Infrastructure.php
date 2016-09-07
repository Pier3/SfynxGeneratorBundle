<?php
declare(strict_types = 1);

namespace Sfynx\DddGeneratorBundle\Generator\Api\Generator;

/**
 * Class Infrastructure
 *
 * @category Generator
 * @package Api
 * @subpackage Generator
 */
class Infrastructure extends LayerAbstract
{
    /** @var string */
    protected static $handlersFileName = 'infrastructure.yml';

    /** @var string */
    protected static $skeletonDir = 'Api/Infrastructure';

    /**
     * Entry point of the generation of the "Infrastructure" layer in DDD.
     * Call the generation of :
     * - Persistence ;
     * - Tests of the whole "Infrastructure" layer.
     */
    public function generate()
    {
        $this->writeln('')
            ->writeln('##############################################')
            ->writeln('#     GENERATE INFRASTRUCTURE STRUCTURE      #')
            ->writeln('##############################################')
            ->writeln('');

        try {
            $this->writeln('### PERSISTENCE GENERATION ###')->generatePersistence();
            $this->writeln('### VALUE OBJECTS GENERATION ###')->generateValueObject();
        } catch (\InvalidArgumentException $e) {
            $this->errWriteln($e->getMessage());
            exit;
        }
    }

    /**
     * Generate the Persistence part in the "Infrastructure" layer.
     *
     * @throws \InvalidArgumentException
     */
    public function generatePersistence()
    {
        foreach ($this->entitiesGroups as $entityName => $entityGroups) {
            $this->parameters['entityName'] = $entityName;
            $this->parameters['constructorArgs'] = $this->buildConstructorParamsString($entityName);
            $this->parameters['entityFields'] = $this->entitiesToCreate[$entityName];

            //Fetch all actionName and add the handler for this actionName
            foreach (array_merge(LayerAbstract::COMMANDS_LIST, LayerAbstract::QUERIES_LIST) as $actionName) {
                $this->parameters['actionName'] = ucfirst($actionName);
                $this->addHandlers('ormRepositoryHandler', 'odmRepositoryHandler', 'couchDbRepositoryHandler');
            }

            $this->addHandler('traitEntityNameHandler');
        }

        $this->generator->execute()->clear();
    }

    /**
     * Generate the Value Object through Entity Types part in the "Infrastructure" layer.
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

            $this->addHandlers(
                'valueObjectTypeCouchDBHandler',
                'valueObjectTypeOdmHandler',
                'valueObjectTypeOrmHandler'
            );
        }
        $this->generator->execute()->clear();
    }
}
