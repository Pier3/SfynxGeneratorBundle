<?php

namespace Sfynx\DddGeneratorBundle\Generator\Api\Handler\Tests\Presentation\Adapter\Entity\Command;

use Sfynx\DddGeneratorBundle\Generator\Generalisation\AbstractHandler;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\HandlerInterface;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\ExecuteTrait;

class NewCommandAdapterTestHandler extends AbstractHandler implements HandlerInterface
{
    use  ExecuteTrait;

    const SKELETON_DIR = 'Api/Tests/Presentation/Adapter/Entity/Command';
    const SKELETON_TPL = 'NewCommandAdapterTest.php.twig';

    protected $targetPattern = '%s/%s/Tests/Presentation/Adapter/%s/Command/NewCommandAdapterTest.php';
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