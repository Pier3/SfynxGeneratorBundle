<?php

namespace Sfynx\DddGeneratorBundle\Generator\Bundle\Handler\InfrastructureBundle\DependencyInjection;

use Sfynx\DddGeneratorBundle\Generator\Generalisation\AbstractHandler;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\HandlerInterface;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\ExecuteTrait;

class ConfigurationHandler extends AbstractHandler implements HandlerInterface
{
    use  ExecuteTrait;

    const SKELETON_DIR = 'Bundle/InfrastructureBundle/DependencyInjection';
    const SKELETON_TPL = 'Configuration.php.twig';

    protected $targetPattern = '%s/%s/InfrastructureBundle/DependencyInjection/Configuration.php';
    protected $target;

    protected function setTarget()
    {
        $this->target = sprintf(
            $this->targetPattern,
            $this->parameters['projectDir'],
            ucfirst($this->parameters['bundleName'])
        );
    }
}
