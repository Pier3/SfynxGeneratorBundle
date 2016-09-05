<?php

namespace Sfynx\DddGeneratorBundle\Generator\Api\Handler\Infrastructure\Persistence\Orm;

use Sfynx\DddGeneratorBundle\Generator\Generalisation\AbstractHandler;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\HandlerInterface;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\ExecuteTrait;

class RepositoryHandler extends AbstractHandler implements HandlerInterface
{
    use ExecuteTrait;

    const SKELETON_DIR = 'Api/Infrastructure/';
    const SKELETON_TPL = 'Persistence/Orm/Repository.php.twig';

    protected $targetPattern = '%destinationPath$s/%projectDir$s/Infrastructure/Persistence/Repository/%entityName$s/Orm/%actionName$sRepository.php';
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
