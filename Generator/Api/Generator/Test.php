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
            $this->generatePhpunitXML();
        } catch (\InvalidArgumentException $e) {
            $this->errWriteln($e->getMessage());
            exit;
        }
    }

    public function generateApplicationTests()
    {
        foreach ($this->commandsQueriesList[self::COMMAND] as $data) {
            $constructorParams = '';
            $managerArgs = '';

            $this->parameters['actionName'] = ucfirst($data['action']);
            $this->parameters['entityName'] = ucfirst(strtolower($data['entity']));
            $this->parameters['entityFields'] = $this->entitiesToCreate[$data['entity']];

            $this->writeln(' - ' . $this->parameters['actionName'] . ' - ');

            foreach ($this->entitiesToCreate[$data['entity']] as $field) {
                $constructorParams .= '$' . $field['name'] . ', ';
                if (('new' === $data['action'] && 'id' !== $field['type']) || ('new' !== $data['action'])) {
                    $managerArgs .= '$' . $field['name'] . ', ';
                }
            }
            $this->parameters['constructorArgs'] = trim($constructorParams, ', ');
            $this->parameters['managerArgs'] = trim($managerArgs, ', ');

            $this->addHandlers('TestCommand', 'TestCommandHandlerDecorator', 'TestCommandHandler');

            $this->generator->execute();
            $this->generator->clear();
        }

        // TODO : do tests for queries, like commands
        foreach ($this->commandsQueriesList[self::QUERY] as $data) {
            $this->parameters['actionName'] = ucfirst($data['action']);
            $this->parameters['entityName'] = ucfirst(strtolower($data['entity']));
            $this->parameters['entityFields'] = $this->entitiesToCreate[$data['entity']];

            $this->writeln(' - ' . $this->parameters['actionName'] . ' - ');

            $this->addHandlers('TestQuery');

            $this->generator->execute();
            $this->generator->clear();
        }

        return $this;
    }

    public function generateDomainTests()
    {
        foreach (array_keys($this->entitiesToCreate) as $entityName) {
            $this->parameters['entityName'] = $entityName;
            $this->parameters['entityFields'] = $this->entitiesToCreate[$entityName];

            $this->addHandlers('TestManager', 'TestRepositoryFactory');

            $this->generator->execute();
            $this->generator->clear();
        }

        return $this;
    }

    public function generatePresentationTests()
    {

        foreach ($this->commandsQueriesList[self::COMMAND] as $data) {
            $this->parameters['actionName'] = ucfirst($data['action']);
            $this->parameters['entityName'] = ucfirst($data['entity']);
            $this->parameters['entityFields'] = $this->entitiesToCreate[$data['entity']];

            $this->addHandlers('TestCommandAdapter');
        }

        $this->generator->execute()->clear();

        // Generate controllers
        foreach ($this->entitiesGroups as $entityName => $entityGroups) {
            $this->parameters['entityName'] = $entityName;
            //Reset the controllerData list
            $this->parameters['controllerData'] = [];

            // Query
            $group = self::QUERY;
            //Set the parameter $group to its good value (might be a reset)
            $this->parameters['group'] = $group;
            //Fetch all controllerData for the given group (Command or Query)

            foreach ($entityGroups[$group] as $entityCommandData) {
                $this->parameters['controllerData'][] = $entityCommandData;
            }
            //Add the Handlers to the generator's stack.
            $this->addHandlers('TestControllerQuery');
            $this->generator->execute()->clear();

            // Command
            $group = self::COMMAND;
            //Set the parameter $group to its good value (might be a reset)
            $this->parameters['group'] = self::COMMAND;
            //Fetch all controllerData for the given group (Command or Query)
            foreach ($entityGroups[$group] as $entityCommandData) {
                $this->parameters['controllerData'][] = $entityCommandData;
            }
            //Add the Handlers to the generator's stack.
            $this->addHandlers('TestControllerCommand');

            $this->generator->execute()->clear();
        }
        return $this;
    }

    public function generatePhpunitXML()
    {
        $this->addHandlers('TestPHpunitXML');
        $this->generator->execute()->clear();
    }
}
