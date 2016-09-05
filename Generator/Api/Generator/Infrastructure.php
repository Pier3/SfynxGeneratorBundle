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
        $this->output->writeln('');
        $this->output->writeln('##############################################');
        $this->output->writeln('#     GENERATE INFRASTRUCTURE STRUCTURE      #');
        $this->output->writeln('##############################################');
        $this->output->writeln('');

        $this->output->writeln('### PERSISTENCE GENERATION ###');
        $this->generatePersistence();
        $this->output->writeln('### TEST GENERATION ###');
        //TODO: work on the generation of the tests.
        //$this->generateTests();
    }

    /**
     * Generate the Persistence part in the "Infrastructure" layer.
     */
    public function generatePersistence()
    {
        foreach ($this->entitiesGroups as $entityName => $entityGroups) {
            $this->parameters['entityName'] = $entityName;
            $this->parameters['constructorArgs'] = $this->buildConstructorParamsString($entityName);
            $this->parameters['entityFields'] = $this->entitiesToCreate[$entityName];

            $this->addCQRSRepositoriesToGenerator($entityGroups, self::COMMAND)
                ->addCQRSRepositoriesToGenerator($entityGroups, self::QUERY);

            $this->addHandler('traitEntityNameHandler');
        }

        $this->generator->execute()->clear();
    }

    /**
     * Add Repositories handlers to the generator. For use in a loop for each C.Q.R.S. actions.
     *
     * @param array  $entityGroups
     * @param string $group
     * @return self
     */
    private function addCQRSRepositoriesToGenerator(array $entityGroups, string $group): self
    {
        //Set the parameter $group to its good value (might be a reset)
        $this->parameters['group'] = $group;

        //Fetch all actionName and add the handler for this actionName
        foreach ($entityGroups[$group] as $data) {
            $this->parameters['actionName'] = ucfirst($data['action']);
            $this->addHandlers('ormRepositoryHandler', 'odmRepositoryHandler', 'couchDbRepositoryHandler');
        }

        return $this;
    }
}