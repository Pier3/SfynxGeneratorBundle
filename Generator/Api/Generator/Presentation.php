<?php
declare(strict_types = 1);

namespace Sfynx\DddGeneratorBundle\Generator\Api\Generator;

//Command adapter
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Adapter\Command\AdapterHandler as AdapterCommandHandler;
//Query adapter
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Adapter\Query\AdapterHandler as AdapterQueryHandler;
//Controller
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Presentation\Coordination\ControllerHandler;
//Request
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Request\RequestHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Tests\Presentation\Adapter\Entity\Command\CommandAdapterTestHandler;

/**
 * Class Presentation
 *
 * @category Generator
 * @package Api
 * @subpackage Generator
 */
class Presentation extends LayerAbstract
{
    /**
     * Entry point of the generation of the "Presentation" layer in DDD.
     * Call the generation of :
     * - Command adapters ;
     * - Query adapters ;
     * - Controllers (sub-layer "Coordination") ;
     * - Requests ;
     * - Tests of the whole "Presentation" layer.
     */
    public function generate()
    {
        $this->output->writeln('');
        $this->output->writeln('##############################################');
        $this->output->writeln('#      GENERATE PRESENTATION STRUCTURE       #');
        $this->output->writeln('##############################################');
        $this->output->writeln('');

        $this->output->writeln('### COMMAND ADAPTERS GENERATION ###');
        $this->generateCommandsAdapter();
        $this->output->writeln('### QUERY ADAPTERS GENERATION ###');
        $this->generateQueriesAdapter();
        $this->output->writeln('### COORDINATION CONTROLLERS GENERATION ###');
        $this->generateCoordinationControllers();
        $this->output->writeln('### REQUESTS GENERATION ###');
        $this->generateRequest();
        $this->output->writeln('### TESTS GENERATION ###');
        $this->output->writeln(' - BE MY GUEST ... -');

        $this->generateTests();
    }

    /**
     * Generate the Command adapters part in the "Presentation" layer.
     */
    public function generateCommandsAdapter()
    {
        foreach ($this->commandsQueriesList[self::COMMAND] as $data) {
            $this->parameters['actionName'] = ucfirst($data['action']);
            $this->parameters['entityName'] = ucfirst($data['entity']);
            $this->parameters['entityFields'] = $this->entitiesToCreate[$data['entity']];

            $this->generator->addHandler(new AdapterCommandHandler($this->parameters), true);
        }

        $this->generator->execute()->clear();
    }

    /**
     * Generate the Query adapters part in the "Presentation" layer.
     */
    public function generateQueriesAdapter()
    {
        foreach ($this->commandsQueriesList[self::QUERY] as $data) {
            $this->parameters['actionName'] = ucfirst($data['action']);
            $this->parameters['entityName'] = ucfirst($data['entity']);
            $this->parameters['entityFields'] = $this->entitiesToCreate[$data['entity']];

            $this->generator->addHandler(new AdapterQueryHandler($this->parameters), true);
        }
        $this->generator->execute()->clear();
    }

    /**
     * Generate the Controllers (Coordination) part in the "Presentation" layer.
     */
    public function generateCoordinationControllers()
    {
        foreach ($this->entitiesGroups as $entityName => $entityGroups) {
            $this->parameters['entityName'] = $entityName;

            $this->addCQRSCoordinationToGenerator($entityGroups, self::COMMAND)
                ->addCQRSCoordinationToGenerator($entityGroups, self::QUERY);
        }

        $this->generator->execute()->clear();
    }

    /**
     * Generate the Requests part in the "Presentation" layer.
     */
    public function generateRequest()
    {
        foreach ($this->entitiesGroups as $entityName => $entityGroups) {
            $this->parameters['entityName'] = $entityName;
            $this->parameters['entityFields'] = $this->entitiesToCreate[$entityName];

            $this->addCQRSRequestToGenerator($entityGroups, self::COMMAND)
                ->addCQRSRequestToGenerator($entityGroups, self::QUERY);
        }

        $this->generator->execute()->clear();
    }

    public function generateTests()
    {

        foreach ($this->commandsQueriesList[self::COMMAND] as $data) {
            $this->parameters['actionName'] = ucfirst($data['action']);
            $this->parameters['entityName'] = ucfirst($data['entity']);
            $this->parameters['entityFields'] = $this->entitiesToCreate[$data['entity']];

            $this->generator->addHandler(new CommandAdapterTestHandler($this->parameters), true);
        }

        $this->generator->execute()->clear();


        // Generate controllers
        foreach ($this->entitiesGroups as $entityName => $entityGroups) {
            $this->parameters['entityName'] = $entityName;
            //Set the parameter $group to its good value (might be a reset)
            $this->parameters['group'] = $group;

            //Reset the controllerData list
            $this->parameters['controllerData'] = [];

            //Fetch all controllerData for the given group (Command or Query)
            foreach ($entityGroups[$group] as $entityCommandData) {
                $this->parameters['controllerData'][] = $entityCommandData;
            }

            //Add the Handlers to the generator's stack.
            $this->generator->addHandler(new ControllerHandler($this->parameters), true);
        }

        return $this;
    }

    /**
     * Add Controller (Coordination) Handler to the generator. For use in a loop for each C.Q.R.S. actions.
     *
     * @param array  $entityGroups
     * @param string $group
     * @return self
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
        $this->generator->addHandler(new ControllerHandler($this->parameters), true);

        return $this;
    }

    /**
     * Add Request Handler to the generator. For use in a loop for each group of C.Q.R.S. actions (Command or Query).
     *
     * @param array  $entityGroups
     * @param string $group
     * @return self
     */
    private function addCQRSRequestToGenerator($entityGroups, $group): self
    {
        //Set the parameter $group to its good value (might be a reset)
        $this->parameters['group'] = $group;

        //Fetch all actionName and add the handler for this actionName
        foreach ($entityGroups[$group] as $data) {
            $this->parameters['actionName'] = ucfirst($data['action']);
            $this->generator->addHandler(new RequestHandler($this->parameters), true);
        }

        return $this;
    }
}
