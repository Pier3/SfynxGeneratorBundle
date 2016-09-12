<?php
declare(strict_types = 1);

namespace Sfynx\DddGeneratorBundle\Generator\Api\Generator;

/**
 * Class Test
 *
 * @category Generator
 * @package Api
 * @subpackage Generator
 */
class Test extends LayerAbstract
{
    /** @var string */
    protected static $handlersFileName = 'tests.yml';

    /** @var string */
    protected static $skeletonDir = 'Api/Tests';

    /**
     * Entry point of the generation of the Tests part of the whole DDD application.
     * Call the generation of tests for each DDD layers.
     */
    public function generate()
    {
        $this->writeln('')
            ->writeln('##############################################')
            ->writeln('#               GENERATE TESTS               #')
            ->writeln('##############################################')
            ->writeln('');

        try {
            $this->writeln('### GENERATE APPLICATION TESTS ###')->generateApplicationTests();
            $this->writeln('### GENERATE DOMAIN TESTS ###')->generateDomainTests();
            $this->writeln('### GENERATE PRESENTATION TESTS ###')->generatePresentationTests();
            $this->writeln('### GENERATE PHP UNIT XML ###')->generatePhpUnitXML();
        } catch (\InvalidArgumentException $e) {
            $this->errWriteln($e->getMessage());
            exit;
        }
    }

    /**
     * Generate test for the Application layer
     * @return Test
     */
    public function generateApplicationTests(): self
    {
        $this->writeln('### COMMANDS GENERATION ###')->generateCommands();
        $this->writeln('### QUERIES GENERATION ###')->generateQueries();

        return $this;
    }

    public function generateCommands()
    {
        foreach ($this->commandsQueriesList[self::COMMAND] as $data) {
            $constructorParams = '';
            $managerArgs = '';

            $this->parameters['actionName'] = ucfirst($data['action']);
            $this->parameters['entityName'] = ucfirst(strtolower($data['entity']));
            $this->parameters['entityFields'] = $this->entitiesToCreate[$data['entity']];

            $this->parameters['constructorArgs'] = $this->buildConstructorParamsString($data['entity']);
            $this->parameters['managerArgs'] = $this->buildConstructorParamsString($data['entity']);

            $this->addHandlers('testCommand', 'testCommandHandlerDecorator', 'testCommandHandler');

        }

        $this->generator->execute()->clear();

        return $this;
    }

    /**
     * Generate the Query part in the "Application" layer.
     * @throws \InvalidArgumentException
     */
    protected function generateQueries()
    {
        foreach ($this->commandsQueriesList[self::QUERY] as $data) {
            $this->parameters['actionName'] = ucfirst($data['action']);
            $this->parameters['entityName'] = ucfirst(strtolower($data['entity']));
            $this->parameters['entityFields'] = $this->entitiesToCreate[$data['entity']];

            $this->addHandlers('testQuery');
        }

        $this->generator->execute()->clear();
    }

    /**
     * @return Test
     */
    public function generateDomainTests(): self
    {
        foreach ($this->entitiesToCreate as $entityName => $fields) {
            $this->parameters['entityName'] = $entityName;
            $this->parameters['entityFields'] = $fields;

            $this->addHandlers('testManager', 'testRepositoryFactory');
        }

        $this->generator->execute()->clear();

        return $this;
    }

    /**
     * @return Test
     */
    public function generatePresentationTests(): self
    {
        foreach ($this->commandsQueriesList[self::COMMAND] as $data) {
            $this->parameters['actionName'] = ucfirst($data['action']);
            $this->parameters['entityName'] = ucfirst($data['entity']);
            $this->parameters['entityFields'] = $this->entitiesToCreate[$data['entity']];

            $this->addHandler('testCommandAdapter');
        }

        $this->generator->execute()->clear();

        // Generate controllers
        foreach ($this->entitiesGroups as $entityName => $entityGroups) {
            $this->parameters['entityName'] = $entityName;

            $this->addCQRSCoordinationToGenerator($entityGroups, self::COMMAND)
                ->addCQRSCoordinationToGenerator($entityGroups, self::QUERY);
        }

        $this->generator->execute()->clear();

        return $this;
    }

    /**
     * @return Test
     */
    public function generatePhpUnitXML(): self
    {
        $this->addHandler('testPhpUnitXML');
        $this->generator->execute()->clear();

        return $this;
    }

    /**
     * Add Controller (Coordination) Handler to the generator. For use in a loop for each C.Q.R.S. actions.
     *
     * @param array $entityGroups
     * @param string $group
     * @return self
     * @throws \InvalidArgumentException
     */
    private function addCQRSCoordinationToGenerator(array $entityGroups, string $group): self
    {
        //Set the parameter $group to its good value (might be a reset)
        $this->parameters['group'] = $group;

        //Reset the controllerData list
        $this->parameters['controllerData'] = [];

        //Fetch all controllerData for the given group (Command or Query)
        foreach ($entityGroups[$group] as $entityCommandData) {
            $this->parameters['controllerData'][] = $entityCommandData;
        }

        //Add the Handlers to the generator's stack.
        $this->addHandler('testController'.$group);

        return $this;
    }
}
