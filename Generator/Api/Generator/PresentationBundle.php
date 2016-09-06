<?php
declare(strict_types = 1);

namespace Sfynx\DddGeneratorBundle\Generator\Api\Generator;

//Bundle part
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\PresentationBundle\PresentationBundleHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\PresentationBundle\DependencyInjection\{
    PresentationBundleExtensionHandler,
    ConfigurationHandler,
    Compiler\ResettingListenersPassHandler
};

//Resource configuration part
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\PresentationBundle\Resources\{
    Application\ApplicationHandler,
    Controller\ControllerHandler,
    Route\RouteHandler,
    Controller\ControllerMultiTenantHandler,
    Controller\ControllerSwaggerHandler
};

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
        $this->output->writeln('');
        $this->output->writeln('######################################################');
        $this->output->writeln('#       GENERATE PRESENTATION BUNDLE STRUCTURE       #');
        $this->output->writeln('######################################################');
        $this->output->writeln('');

        try {
            $this->output->writeln('### BUNDLE GENERATION ###');
            $this->generateBundle();
            $this->output->writeln('### RESOURCES CONFIGURATION GENERATION ###');
            $this->generateResourcesConfiguration();
            $this->output->writeln('### TESTS GENERATION ###');

        } catch (\InvalidArgumentException $e) {
            fwrite(STDERR, $e->getMessage());
            exit;
        }
    }

    /**
     * Generate the Bundle part in the "PresentationBundle" layer.
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
     */
    public function generateResourcesConfiguration()
    {
        foreach ($this->entitiesGroups as $entityName => $entityGroups) {
            $this->parameters['entityName'] = strtolower($entityName);

            $this->addCQRSHandlerServiceToGenerator($entityGroups, self::COMMAND)
                ->addCQRSHandlerServiceToGenerator($entityGroups, self::QUERY);
        }

        $this->addHandlers(
            'controllerMultiTenantHandler',
            'controllerSwaggerHandler'
        );

        $this->generator->execute()->clear();
    }

    /**
     * Add Resource configuration handlers to the generator. For use in a loop for each C.Q.R.S. actions.
     *
     * @param array  $entityGroups
     * @param string $group
     * @return self
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
        $this->addHandlers(
            'applicationHandler',
            'controllerHandler',
            'routeHandler'
        );

        return $this;
    }
}
