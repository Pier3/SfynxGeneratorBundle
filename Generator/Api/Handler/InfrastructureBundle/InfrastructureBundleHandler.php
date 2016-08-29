<?php

namespace Sfynx\DddGeneratorBundle\Generator\Api\Handler\InfrastructureBundle;

use Sfynx\DddGeneratorBundle\Generator\Generalisation\AbstractHandler;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\HandlerInterface;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\ExecuteTrait;

class InfrastructureBundleHandler extends AbstractHandler implements HandlerInterface
{
    use ExecuteTrait;

    const SKELETON_DIR = 'Api/InfrastructureBundle';
    const SKELETON_TPL = 'InfrastructureBundle.php.twig';

    protected $targetPattern = '%s/%s/InfrastructureBundle/%sInfrastructureBundle.php';
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
