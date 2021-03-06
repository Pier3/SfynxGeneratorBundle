<?php

namespace Sfynx\DddGeneratorBundle\Generator\Api\Handler\InfrastructureBundle\DependencyInjection;

use Sfynx\DddGeneratorBundle\Generator\Generalisation\AbstractHandler;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\HandlerInterface;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\ExecuteTrait;

class InfrastructureBundleExtensionHandler extends AbstractHandler implements HandlerInterface
{
    use  ExecuteTrait;

    const SKELETON_DIR = 'Api/InfrastructureBundle/DependencyInjection';
    const SKELETON_TPL = 'InfrastructureBundleExtension.php.twig';

    protected $targetPattern = '%s/%s/InfrastructureBundle/DependencyInjection/%sInfrastructureBundleExtension.php';
    protected $target;

    protected function setTarget()
    {
        $this->target = sprintf(
            $this->targetPattern,
            $this->parameters['rootDir'],
            $this->parameters['projectDir'],
            ucfirst($this->parameters['projectName'])
        );
    }
}
