<?php

namespace Sfynx\DddGeneratorBundle\Generator\Bundle\Handler\PresentationBundle;

use Sfynx\DddGeneratorBundle\Generator\Generalisation\AbstractHandler;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\HandlerInterface;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\ExecuteTrait;

class PresentationBundleHandler extends AbstractHandler implements HandlerInterface
{
    use ExecuteTrait;

    const SKELETON_DIR = 'Bundle/PresentationBundle/';
    const SKELETON_TPL = 'PresentationBundle.php.twig';

    protected $targetPattern = '%s/%s/PresentationBundle/PresentationBundle.php';
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
