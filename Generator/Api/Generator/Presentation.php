<?php
declare(strict_types = 1);

namespace Sfynx\DddGeneratorBundle\Generator\Api\Generator;

use Symfony\Component\Console\Output\Output;

/**
 * Class Presentation
 *
 * @category Generator
 * @package Api
 * @subpackage Generator
 */
class Presentation extends LayerAbstract
{
    /** @var string */
    protected static $handlersFileName = 'presentation.yml';

    /** @var string */
    protected static $skeletonDir = 'Api/Presentation';

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
        $this->writeln('')
            ->writeln('##############################################')
            ->writeln('#      GENERATE PRESENTATION STRUCTURE       #')
            ->writeln('##############################################')
            ->writeln('');

        try {
            $this->writeln('### COMMAND ADAPTERS GENERATION ###')->generateCommandsAdapter();
            $this->writeln('### QUERY ADAPTERS GENERATION ###')->generateQueriesAdapter();
            $this->writeln('### COORDINATION CONTROLLERS GENERATION ###')->generateCoordinationControllers();
            $this->writeln('### REQUESTS GENERATION ###')->generateRequest();
        } catch (\InvalidArgumentException $e) {
            $this->writeln($e->getMessage(), Output::VERBOSITY_NORMAL);
            exit;
        }
    }

    /**
     * Generate the Command adapters part in the "Presentation" layer.
     * @throws \InvalidArgumentException
     */
    public function generateCommandsAdapter()
    {
        foreach ($this->commandsQueriesList[self::COMMAND] as $data) {
            $this->parameters['actionName'] = ucfirst($data['action']);
            $this->parameters['entityName'] = ucfirst($data['entity']);
            $this->parameters['entityFields'] = $this->entitiesToCreate[$data['entity']];

            $this->addHandler('adapterCommandHandler');
        }

        $this->generator->execute()->clear();
    }

    /**
     * Generate the Query adapters part in the "Presentation" layer.
     * @throws \InvalidArgumentException
     */
    public function generateQueriesAdapter()
    {
        foreach ($this->commandsQueriesList[self::QUERY] as $data) {
            $this->parameters['actionName'] = ucfirst($data['action']);
            $this->parameters['entityName'] = ucfirst($data['entity']);
            $this->parameters['entityFields'] = $this->entitiesToCreate[$data['entity']];

            $this->addHandler('adapterQueryHandler');
        }
        $this->generator->execute()->clear();
    }

    /**
     * Generate the Controllers (Coordination) part in the "Presentation" layer.
     * @throws \InvalidArgumentException
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
     * @throws \InvalidArgumentException
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
        $this->addHandler('controllerHandler');

        return $this;
    }

    /**
     * Add Request Handler to the generator. For use in a loop for each group of C.Q.R.S. actions (Command or Query).
     *
     * @param array $entityGroups
     * @param string $group
     * @return self
     * @throws \InvalidArgumentException
     */
    private function addCQRSRequestToGenerator($entityGroups, $group): self
    {
        //Set the parameter $group to its good value (might be a reset)
        $this->parameters['group'] = $group;

        //Fetch all actionName and add the handler for this actionName
        foreach ($entityGroups[$group] as $data) {
            $this->parameters['actionName'] = ucfirst($data['action']);

            $this->addHandler('requestHandler');
        }

        return $this;
    }
}
