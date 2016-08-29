<?php

namespace Sfynx\DddGeneratorBundle\Generator\Api\Handler\Tests\Presentation\Request\Entity\Command;

use Sfynx\DddGeneratorBundle\Generator\Generalisation\AbstractHandler;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\HandlerInterface;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\ExecuteTrait;

class UpdateRequestTestHandler extends AbstractHandler implements HandlerInterface
{
    use ExecuteTrait;

    const SKELETON_DIR = 'Api/Tests/Presentation/Request/Entity/Command';
    const SKELETON_TPL = 'UpdateRequestTest.php.twig';

    protected $targetPattern = '%s/%s/Tests/Presentation/Request/%s/Command/UpdateRequestTest.php';
    protected $target;

    protected function setTarget()
    {
        $this->target = sprintf(
            $this->targetPattern,
            $this->parameters['destinationPath'],
            $this->parameters['projectDir'],
            ucfirst($this->parameters['entityName'])
        );
    }
}
