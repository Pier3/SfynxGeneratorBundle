<?php

namespace Sfynx\DddGeneratorBundle\Generator\Api\Handler\Adapter\Query;

use Sfynx\DddGeneratorBundle\Generator\Generalisation\AbstractHandler;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\HandlerInterface;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\ExecuteTrait;

class AdapterHandler extends AbstractHandler implements HandlerInterface
{
    use ExecuteTrait;

    const SKELETON_DIR = 'Api/Presentation/Adapter';
    const SKELETON_TPL = '%sQueryAdapter.php.twig';

    protected $targetPattern = '%s/%s/Presentation/Adapter/%s/Query/%sQueryAdapter.php';
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
