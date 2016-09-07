<?php
declare(strict_types = 1);

namespace Sfynx\DddGeneratorBundle\Generator\Api\Generator;

/**
 * Class InfrastructureBundle
 *
 * @category Generator
 * @package Api
 * @subpackage Generator
 */
class InfrastructureBundle extends LayerAbstract
{
    /** @var string */
    protected static $handlersFileName = 'infrastructureBundle.yml';

    /** @var string */
    protected static $skeletonDir = 'Api/InfrastructureBundle';

    /**
     * Entry point of the generation of the "InfrastructureBundle" layer in DDD.
     * Call the generation of :
     * - Bundle ;
     * - Tests of the whole "InfrastructureBundle" layer.
     */
    public function generate()
    {
        $this->writeln('')
            ->writeln('#############################################')
            ->writeln('# GENERATE INFRASTRUCTURE BUNDLE STRUCTURE  #')
            ->writeln('#############################################')
            ->writeln('');

        try {
            $this->writeln('### BUNDLE GENERATION ###')->generateBundle();
        } catch (\InvalidArgumentException $e) {
            $this->errWriteln($e->getMessage());
            exit;
        }
    }

    /**
     * Generate the Bundle part in the "InfrastructureBundle" layer.
     * @throws \InvalidArgumentException
     */
    public function generateBundle()
    {
        $this->parameters['entities'] = $this->entitiesToCreate;

        $this->addHandlers(
            'createRepositoryFactoryPassHandler',
            'createDynamicConnectionPassHandler',
            'configurationHandler',
            'infrastructureBundleExtensionHandler',
            'infrastructureBundleHandler'
        );

        $this->generator->execute()->clear();
    }
}
