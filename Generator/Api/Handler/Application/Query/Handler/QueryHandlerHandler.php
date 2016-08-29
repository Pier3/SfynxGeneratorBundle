<?php

namespace Sfynx\DddGeneratorBundle\Generator\Api\Handler\Application\Query\Handler;

use Sfynx\DddGeneratorBundle\Generator\Generalisation\AbstractHandler;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\HandlerInterface;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\ExecuteTrait;

class QueryHandlerHandler extends AbstractHandler implements HandlerInterface
{
    use ExecuteTrait;

    const SKELETON_DIR = 'Api/Application/Query/Handler';
    const SKELETON_TPL = '%sQueryHandler.php.twig';

    protected $targetPattern = '%s/%s/Application/%s/Query/Handler/%sQueryHandler.php';
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
