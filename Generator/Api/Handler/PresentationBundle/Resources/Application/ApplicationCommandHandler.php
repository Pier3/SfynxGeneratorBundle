<?php

namespace Sfynx\DddGeneratorBundle\Generator\Api\Handler\PresentationBundle\Resources\Application;

use Sfynx\DddGeneratorBundle\Generator\Generalisation\AbstractHandler;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\HandlerInterface;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\ExecuteTrait;

class ApplicationCommandHandler extends AbstractHandler implements HandlerInterface
{
    use ExecuteTrait;

    const SKELETON_DIR = 'Api/PresentationBundle/Resources/config/application';
    const SKELETON_TPL = 'application_command.yml.twig';

    protected $targetPattern = '%s/%s/PresentationBundle/Resources/config/application/%s_command.yml';
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
