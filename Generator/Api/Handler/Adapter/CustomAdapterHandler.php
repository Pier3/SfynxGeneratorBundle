<?php

namespace Sfynx\DddGeneratorBundle\Generator\Api\Handler\Adapter;

use Sfynx\DddGeneratorBundle\Generator\Generalisation\AbstractHandler;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\HandlerInterface;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\ExecuteTrait;

class CustomAdapterHandler extends AbstractHandler implements HandlerInterface
{
    use  ExecuteTrait;

    const SKELETON_DIR = 'Api/Presentation/Adapter';
    const SKELETON_TPL = 'CustomQueryAdapter.php.twig';

    protected $targetPattern = '%s/%s/Presentation/Adapter/%s/query/%sQueryAdapter.php';
    protected $target;

    protected function setTarget()
    {
        $this->target = sprintf(
            $this->targetPattern,
            $this->parameters['rootDir'],
            $this->parameters['projectDir'],
            ucfirst($this->parameters['entityName']),
            ucfirst($this->parameters['actionName'])
        );
    }
}
