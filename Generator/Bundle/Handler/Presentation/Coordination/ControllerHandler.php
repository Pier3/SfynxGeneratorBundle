<?php

namespace Sfynx\DddGeneratorBundle\Generator\Bundle\Handler\Presentation\Coordination;

use Sfynx\DddGeneratorBundle\Generator\Generalisation\AbstractHandler;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\HandlerInterface;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\ExecuteTrait;

class ControllerHandler extends AbstractHandler implements HandlerInterface
{
    use ExecuteTrait;

    const SKELETON_DIR = 'Bundle/Presentation/Coordination/';
    const SKELETON_TPL = 'Controller.php.twig';

    protected $targetPattern = '%s/%s/Presentation/Coordination/%s/Query/Controller.php';
    protected $target;

    protected function setTarget()
    {
        $this->target = sprintf(
            $this->targetPattern,
            $this->parameters['destinationPath'],
            $this->parameters['projectDir'],
            ucfirst($this->parameters['bundleName'])
        );
    }
}
