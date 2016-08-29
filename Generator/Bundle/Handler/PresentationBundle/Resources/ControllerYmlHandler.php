<?php

namespace Sfynx\DddGeneratorBundle\Generator\Bundle\Handler\PresentationBundle\Resources;

use Sfynx\DddGeneratorBundle\Generator\Generalisation\AbstractHandler;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\HandlerInterface;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\ExecuteTrait;

class ControllerYmlHandler extends AbstractHandler implements HandlerInterface
{
    use ExecuteTrait;

    const SKELETON_DIR = 'Bundle/PresentationBundle/Resources/config/';
    const SKELETON_TPL = 'controllers.php.twig';

    protected $targetPattern = '%s/%s/PresentationBundle/Resources/config/controllers.yml';
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
