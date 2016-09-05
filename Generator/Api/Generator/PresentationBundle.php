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

        $this->output->writeln('### BUNDLE GENERATION ###');
        $this->generateBundle();
        $this->output->writeln('### RESOURCES CONFIGURATION GENERATION ###');
        $this->generateResourcesConfiguration();
        $this->output->writeln('### TESTS GENERATION ###');
        //TODO: work on the generation of the tests.
        $this->generateTests();
    }

    /**
     * Generate the Bundle part in the "PresentationBundle" layer.
     */
    public function generateBundle()
    {
        $this->parameters['entities'] = $this->entitiesToCreate;

        $this->generator->addHandler(new PresentationBundleHandler($this->parameters));
        $this->generator->addHandler(new PresentationBundleExtensionHandler($this->parameters));
        $this->generator->addHandler(new ConfigurationHandler($this->parameters));
        $this->generator->addHandler(new ResettingListenersPassHandler($this->parameters));

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

        $this->generator->addHandler(new ControllerMultiTenantHandler($this->parameters));
        $this->generator->addHandler(new ControllerSwaggerHandler($this->parameters));

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
        $this->parameters['group'] = $group;

        //Reset the controllerData list
        $this->parameters['controllerData'] = [];

        //Fetch all controllerData for the given group (Command or Query)
        foreach ($entityGroups[$group] as $entityCommandData) {
            $this->parameters['controllerData'][] = $entityCommandData;
        }

        //Add the Handlers to the generator's stack.
        $this->generator->addHandler(new ApplicationHandler($this->parameters), true);
        $this->generator->addHandler(new ControllerHandler($this->parameters), true);
        $this->generator->addHandler(new RouteHandler($this->parameters), true);

        return $this;
    }

    public function generateTests()
    {
        return $this;
    }
}
