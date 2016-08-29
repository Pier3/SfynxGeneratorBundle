<?php

namespace Sfynx\DddGeneratorBundle\Generator\Bundle\Handler\InfrastructureBundle;

use Sfynx\DddGeneratorBundle\Generator\Generalisation\AbstractHandler;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\HandlerInterface;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\ExecuteTrait;

class InfrastructureBundleHandler extends AbstractHandler implements HandlerInterface
{
    use ExecuteTrait;

    const SKELETON_DIR = 'Bundle/InfrastructureBundle';
    const SKELETON_TPL = 'InfrastructureBundle.php.twig';

    protected $targetPattern = '%s/%s/InfrastructureBundle/InfrastructureBundle.php';
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
