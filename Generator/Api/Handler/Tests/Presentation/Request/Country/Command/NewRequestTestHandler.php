<?php

namespace Sfynx\DddGeneratorBundle\Generator\Api\Handler\Tests\Presentation\Request\Command;

use Sfynx\DddGeneratorBundle\Generator\Generalisation\AbstractHandler;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\HandlerInterface;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\ExecuteTrait;

class NewRequestTestHandler extends AbstractHandler implements HandlerInterface
{
    use  ExecuteTrait;

    const SKELETON_DIR = 'Api/Tests/Presentation/Request/Command';
    const SKELETON_TPL = 'NewRequestTest.php.twig';

    protected $targetPattern = '%s/%s/Tests/Presentation/Request/Command/NewRequestTest.php';
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