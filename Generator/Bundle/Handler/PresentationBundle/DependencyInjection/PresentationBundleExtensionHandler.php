<?php

namespace Sfynx\DddGeneratorBundle\Generator\Bundle\Handler\PresentationBundle\DependencyInjection;

use Sfynx\DddGeneratorBundle\Generator\Generalisation\AbstractHandler;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\HandlerInterface;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\ExecuteTrait;

class PresentationBundleExtensionHandler extends AbstractHandler implements HandlerInterface
{
    use ExecuteTrait;

    const SKELETON_DIR = 'Bundle/PresentationBundle/DependencyInjection';
    const SKELETON_TPL = 'PresentationBundleExtension.php.twig';

    protected $targetPattern = '%s/%s/PresentationBundle/DependencyInjection/PresentationBundleExtension.php';
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
