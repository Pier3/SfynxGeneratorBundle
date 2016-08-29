<?php

namespace Sfynx\DddGeneratorBundle\Generator\Api\Handler\InfrastructureBundle\DependencyInjection;

use Sfynx\DddGeneratorBundle\Generator\Generalisation\AbstractHandler;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\HandlerInterface;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\ExecuteTrait;

class ConfigurationHandler extends AbstractHandler implements HandlerInterface
{
    use ExecuteTrait;

    const SKELETON_DIR = 'Api/InfrastructureBundle/DependencyInjection';
    const SKELETON_TPL = 'Configuration.php.twig';

    protected $targetPattern = '%s/%s/InfrastructureBundle/DependencyInjection/Configuration.php';
    protected $target;

    protected function setTarget()
    {
        $this->target = sprintf(
            $this->targetPattern,
            $this->parameters['destinationPath'],
            $this->parameters['projectDir'],
            ucfirst($this->parameters['projectName'])
        );
    }
}
