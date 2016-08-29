<?php

namespace Sfynx\DddGeneratorBundle\Generator\Api\Handler\Domain\Service\Processor;

use Sfynx\DddGeneratorBundle\Generator\Generalisation\AbstractHandler;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\HandlerInterface;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\ExecuteTrait;

class PrePersistProcessHandler extends AbstractHandler implements HandlerInterface
{
    use ExecuteTrait;

    const SKELETON_DIR = 'Api/Domain/Service/Processor';
    const SKELETON_TPL = 'PrePersistProcess.php.twig';

    protected $targetPattern = '%s/%s/Domain/Service/%s/Processor/PrePersistProcess.php';
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
