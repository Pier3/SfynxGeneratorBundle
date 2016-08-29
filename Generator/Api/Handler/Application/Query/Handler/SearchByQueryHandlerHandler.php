<?php

namespace Sfynx\DddGeneratorBundle\Generator\Api\Handler\Application\Query\Handler;

use Sfynx\DddGeneratorBundle\Generator\Generalisation\AbstractHandler;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\HandlerInterface;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\ExecuteTrait;

class SearchByQueryHandlerHandler extends AbstractHandler implements HandlerInterface
{
    use ExecuteTrait;

    const SKELETON_DIR = 'Api/Application/Query/Handler';
    const SKELETON_TPL = 'SearchByQueryHandler.php.twig';

    protected $targetPattern = '%s/%s/Application/%s/Query/Handler/SearchByQueryHandler.php';
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
