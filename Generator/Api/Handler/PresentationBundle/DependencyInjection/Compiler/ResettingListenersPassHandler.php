<?php

namespace Sfynx\DddGeneratorBundle\Generator\Api\Handler\PresentationBundle\DependencyInjection\Compiler;

use Sfynx\DddGeneratorBundle\Generator\Generalisation\AbstractHandler;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\HandlerInterface;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\ExecuteTrait;

class ResettingListenersPassHandler extends AbstractHandler implements HandlerInterface
{
    use ExecuteTrait;

    const SKELETON_DIR = 'Api/PresentationBundle/DependencyInjection/Compiler';
    const SKELETON_TPL = 'ResettingListenersPass.php.twig';

    protected $targetPattern = '%s/%s/PresentationBundle/DependencyInjection/Compiler/ResettingListenersPass.php';
    protected $target;

    protected function setTemplateName()
    {
        $this->templateName = self::SKELETON_TPL;
    }

    protected function setTarget()
    {
        $this->target = sprintf(
            $this->targetPattern,
            $this->parameters['destinationPath'],
            $this->parameters['projectDir'],
            $this->parameters['projectName']
        );
    }
}
