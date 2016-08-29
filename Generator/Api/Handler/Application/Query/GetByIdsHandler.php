<?php

namespace Sfynx\DddGeneratorBundle\Generator\Api\Handler\Application\Query;

use Sfynx\DddGeneratorBundle\Generator\Generalisation\AbstractHandler;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\HandlerInterface;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\ExecuteTrait;

class GetByIdsHandler extends AbstractHandler implements HandlerInterface
{
    use ExecuteTrait;

    const SKELETON_DIR = 'Api/Application/Query';
    const SKELETON_TPL = 'GetByIdsQuery.php.twig';

    protected $targetPattern = '%s/%s/Application/%s/Query/GetByIdsQuery.php';
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
