<?php
declare(strict_types = 1);

namespace Sfynx\DddGeneratorBundle\Generator\Api\Generator;

//Bundle part
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\InfrastructureBundle\InfrastructureBundleHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\InfrastructureBundle\DependencyInjection\{
    Compiler\CreateRepositoryFactoryPassHandler,
    ConfigurationHandler,
    InfrastructureBundleExtensionHandler
};

/**
 * Class InfrastructureBundle
 *
 * @category Generator
 * @package Api
 * @subpackage Generator
 */
class InfrastructureBundle extends LayerAbstract
{
    /**
     * Entry point of the generation of the "InfrastructureBundle" layer in DDD.
     * Call the generation of :
     * - Bundle ;
     * - Tests of the whole "InfrastructureBundle" layer.
     */
    public function generate()
    {
        $this->output->writeln('');
        $this->output->writeln('#############################################');
        $this->output->writeln('# GENERATE INFRASTRUCTURE BUNDLE STRUCTURE  #');
        $this->output->writeln('#############################################');
        $this->output->writeln('');

        $this->output->writeln('### BUNDLE GENERATION ###');
        $this->generateBundle();
        $this->output->writeln('### TEST GENERATION ###');
        //TODO: work on the generation of the tests.
        $this->generateTests();
    }

    /**
     * Generate the Bundle part in the "InfrastructureBundle" layer.
     */
    public function generateBundle()
    {
        $this->parameters['entities'] = $this->entitiesToCreate;

        $this->generator->addHandler(new CreateRepositoryFactoryPassHandler($this->parameters));
        $this->generator->addHandler(new ConfigurationHandler($this->parameters));
        $this->generator->addHandler(new InfrastructureBundleExtensionHandler($this->parameters));
        $this->generator->addHandler(new InfrastructureBundleHandler($this->parameters));

        $this->generator->execute()->clear();
    }


    public function generateTests()
    {
        return $this;
    }
}
