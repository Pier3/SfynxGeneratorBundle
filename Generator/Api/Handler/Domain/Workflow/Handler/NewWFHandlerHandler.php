<?php

namespace Sfynx\DddGeneratorBundle\Generator\Api\Handler\Domain\Workflow\Handler;

use Sfynx\DddGeneratorBundle\Generator\Generalisation\AbstractHandler;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\HandlerInterface;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\ExecuteTrait;

class NewWFHandlerHandler extends AbstractHandler implements HandlerInterface
{
    use ExecuteTrait;

    const SKELETON_TPL = 'Workflow/Handler/NewWFHandler.php.twig';

    protected $targetPattern = '%destinationPath$s/%projectDir$s/Domain/Workflow/%entityName$s/Handler/NewWFHandler.php';
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
            $this->parameters['entityName']
        );
    }
}
