<?php

namespace Sfynx\DddGeneratorBundle\Generator\Api\Handler\Domain\Workflow\Listener;

use Sfynx\DddGeneratorBundle\Generator\Generalisation\AbstractHandler;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\HandlerInterface;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\ExecuteTrait;

class WFGenerateVOListenerHandler extends AbstractHandler implements HandlerInterface
{
    use ExecuteTrait;

    const SKELETON_DIR = 'Api/Domain/';
    const SKELETON_TPL = 'Workflow/Listener/WFGenerateVOListener.php.twig';

    protected $targetPattern = '%destinationPath$s/%projectDir$s/Domain/Workflow/%entityName$s/Listener/WFGenerateVOListener.php';
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
            ucfirst($this->parameters['entityName'])
        );
    }
}
