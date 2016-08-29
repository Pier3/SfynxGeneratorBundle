<?php

namespace Sfynx\DddGeneratorBundle\Generator\Api\Handler\Tests\Application\Entity\Command\Handler\Validation;

use Sfynx\DddGeneratorBundle\Generator\Generalisation\AbstractHandler;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\HandlerInterface;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\ExecuteTrait;

class CommandValidationHandlerTestHandler extends AbstractHandler implements HandlerInterface
{
    use ExecuteTrait;

    const SKELETON_DIR = 'Api/Tests/Application/Entity/Command/Handler/Decorator';
    const SKELETON_TPL = '%sCommandValidationHandlerTest.php.twig';

    protected $targetPattern = '%s/%s/Tests/Application/Entity/Command/Handler/Validation/%sCommandValidationHandlerTest.php';
    protected $target;

    protected function setTemplateName()
    {
        $this->templateName = sprintf(self::SKELETON_TPL, $this->parameters['actionName']);
    }

    protected function setTarget()
    {
        $this->target = sprintf(
            $this->targetPattern,
            $this->parameters['destinationPath'],
            $this->parameters['projectDir'],
            $this->parameters['entityName'],
            $this->parameters['actionName']
        );
    }
}
