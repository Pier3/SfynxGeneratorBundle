<?php

namespace Sfynx\DddGeneratorBundle\Generator\Api\Handler\PresentationBundle;

use Sfynx\DddGeneratorBundle\Generator\Generalisation\AbstractHandler;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\HandlerInterface;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\ExecuteTrait;

class PresentationBundleHandler extends AbstractHandler implements HandlerInterface
{
    use ExecuteTrait;

    const SKELETON_DIR = 'Api/PresentationBundle';
    const SKELETON_TPL = 'PresentationBundle.php.twig';

    protected $targetPattern = '%s/%s/PresentationBundle/%sPresentationBundle.php';
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
