<?php
declare(strict_types = 1);

namespace Sfynx\DddGeneratorBundle\Generator\Api\Generator;

/**
 * Class PresentationBundle
 *
 * @category Generator
 * @package Api
 * @subpackage Generator
 */
class PresentationBundle extends LayerAbstract
{
    /** @var string */
    protected static $handlersFileName = 'presentationBundle.yml';

    /** @var string */
    protected static $skeletonDir = 'Api/PresentationBundle';

    /**
     * Entry point of the generation of the "PresentationBundle" layer in DDD.
     * Call the generation of :
     * - Bundles ;
     * - Resources Configuration ;
     * - Tests of the whole "Presentation" layer.
     */
    public function generate()
    {
        $this->writeln('')
            ->writeln('######################################################')
            ->writeln('#       GENERATE PRESENTATION BUNDLE STRUCTURE       #')
            ->writeln('######################################################')
            ->writeln('');

        try {
            $this->writeln('### BUNDLE GENERATION ###')->generateBundle();
            $this->writeln('### RESOURCES CONFIGURATION GENERATION ###')->generateResourcesConfiguration();
        } catch (\InvalidArgumentException $e) {
            $this->errWriteln($e->getMessage());
            exit;
        }
    }

    /**
     * Generate the Bundle part in the "PresentationBundle" layer.
     * @throws \InvalidArgumentException
     */
    public function generateBundle()
    {
        $this->parameters['entities'] = $this->entitiesToCreate;

        $this->addHandlers(
            'presentationBundleHandler',
            'presentationBundleExtensionHandler',
            'configurationHandler',
            'resettingListenersPassHandler'
        );

        $this->generator->execute()->clear();
    }

    /**
     * Generate the Resource Configuration part in the "PresentationBundle" layer.
     * @throws \InvalidArgumentException
     */
    public function generateResourcesConfiguration()
    {
        foreach ($this->entitiesGroups as $entityName => $entityGroups) {
            $this->parameters['entityName'] = strtolower($entityName);

            $this->addCQRSHandlerServiceToGenerator($entityGroups, self::COMMAND)
                ->addCQRSHandlerServiceToGenerator($entityGroups, self::QUERY);
        }

        $this->addHandlers('controllerMultiTenantHandler', 'controllerSwaggerHandler');

        $this->generator->execute()->clear();
    }

    /**
     * Add Resource configuration handlers to the generator. For use in a loop for each C.Q.R.S. actions.
     *
     * @param array $entityGroups
     * @param string $group
     * @return self
     * @throws \InvalidArgumentException
     */
    private function addCQRSHandlerServiceToGenerator(array $entityGroups, string $group): self
    {
        //Set the parameter $group to its good value (might be a reset)
        $this->parameters['group'] = strtolower($group);

        //Reset the controllerData list
        $this->parameters['controllerData'] = [];

        //Fetch all controllerData for the given group (Command or Query)
        foreach ($entityGroups[$group] as $entityCommandData) {
            $this->parameters['controllerData'][] = $entityCommandData;
        }

        //Add the Handlers to the generator's stack.
        $this->addHandlers('applicationHandler', 'controllerHandler', 'routeHandler');

        return $this;
    }
}
