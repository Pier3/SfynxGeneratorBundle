<?php

namespace Sfynx\DddGeneratorBundle\Generator\Api\Handler\PresentationBundle\Resources\Controller;

use Sfynx\DddGeneratorBundle\Generator\Generalisation\AbstractHandler;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\HandlerInterface;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\ExecuteTrait;

class ControllerCommandHandler extends AbstractHandler implements HandlerInterface
{
    use ExecuteTrait;

    const SKELETON_DIR = 'Api/PresentationBundle/Resources/config/controller';
    const SKELETON_TPL = 'controller_command.yml.twig';

    protected $targetPattern = '%s/%s/PresentationBundle/Resources/config/controller/%s_command.yml';
    protected $target;

    protected function setTarget()
    {
        $this->target = sprintf(
            $this->targetPattern,
            $this->parameters['destinationPath'],
            $this->parameters['projectDir'],
            strtolower($this->parameters['entityName'])
        );
    }
}
