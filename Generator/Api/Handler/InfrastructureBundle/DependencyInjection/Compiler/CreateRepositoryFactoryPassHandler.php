<?php

namespace Sfynx\DddGeneratorBundle\Generator\Api\Handler\InfrastructureBundle\DependencyInjection\Compiler;

use Sfynx\DddGeneratorBundle\Generator\Generalisation\AbstractHandler;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\HandlerInterface;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\ExecuteTrait;

class CreateRepositoryFactoryPassHandler extends AbstractHandler implements HandlerInterface
{
    use ExecuteTrait;

    const SKELETON_DIR = 'Api/InfrastructureBundle/DependencyInjection/Compiler';
    const SKELETON_TPL = 'CreateRepositoryFactoryPass.php.twig';

    protected $targetPattern = '%s/%s/InfrastructureBundle/DependencyInjection/Compiler/CreateRepositoryFactoryPass.php';
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
