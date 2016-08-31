<?php

namespace Sfynx\DddGeneratorBundle\Generator\Api\Handler\PresentationBundle\DependencyInjection;

use Sfynx\DddGeneratorBundle\Generator\Generalisation\AbstractHandler;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\HandlerInterface;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\ExecuteTrait;

class PresentationBundleExtensionHandler extends AbstractHandler implements HandlerInterface
{
    use ExecuteTrait;

    const SKELETON_DIR = 'Api/PresentationBundle/DependencyInjection';
    const SKELETON_TPL = 'PresentationBundleExtension.php.twig';

    protected $targetPattern = '%s/%s/PresentationBundle/DependencyInjection/%sPresentationBundleExtension.php';
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
