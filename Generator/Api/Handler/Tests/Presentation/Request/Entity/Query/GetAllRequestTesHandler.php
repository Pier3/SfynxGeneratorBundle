<?php

namespace Sfynx\DddGeneratorBundle\Generator\Api\Handler\Tests\Presentation\Request\Entity\Query;

use Sfynx\DddGeneratorBundle\Generator\Generalisation\AbstractHandler;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\HandlerInterface;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\ExecuteTrait;

class GetAllRequestTestHandler extends AbstractHandler implements HandlerInterface
{
    use ExecuteTrait;

    const SKELETON_DIR = 'Api/Tests/Presentation/Request/Entity/Query';
    const SKELETON_TPL = 'GetAllRequestTest.php.twig';

    protected $targetPattern = '%s/%s/Tests/Presentation/Request/%s/Query/GetAllRequestTest.php';
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
