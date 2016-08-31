<?php

namespace Sfynx\DddGeneratorBundle\Generator\Api\Handler\PresentationBundle\DependencyInjection;

use Sfynx\DddGeneratorBundle\Generator\Generalisation\AbstractHandler;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\HandlerInterface;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\ExecuteTrait;

class ConfigurationHandler extends AbstractHandler implements HandlerInterface
{
    use ExecuteTrait;

    const SKELETON_DIR = 'Api/PresentationBundle/DependencyInjection';
    const SKELETON_TPL = 'Configuration.php.twig';

    protected $targetPattern = '%s/%s/PresentationBundle/DependencyInjection/Configuration.php';
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
