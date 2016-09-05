<?php

namespace Sfynx\DddGeneratorBundle\Generator\Api\Handler\Domain\Entity;

use Sfynx\DddGeneratorBundle\Generator\Generalisation\AbstractHandler;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\HandlerInterface;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\ExecuteTrait;

class EntityHandler extends AbstractHandler implements HandlerInterface
{
    use ExecuteTrait;

    const SKELETON_DIR = 'Api/Domain/Entity';
    const SKELETON_TPL = 'entity.php.twig';

    protected $targetPattern = '%s/%s/Domain/Entity/%s.php';
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
