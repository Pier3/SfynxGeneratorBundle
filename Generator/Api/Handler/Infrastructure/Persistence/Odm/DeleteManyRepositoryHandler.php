<?php

namespace Sfynx\DddGeneratorBundle\Generator\Api\Handler\Infrastructure\Persistence\Odm;

use Sfynx\DddGeneratorBundle\Generator\Generalisation\AbstractHandler;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\HandlerInterface;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\ExecuteTrait;

class DeleteManyRepositoryHandler extends AbstractHandler implements HandlerInterface
{
    use  ExecuteTrait;

    const SKELETON_DIR = 'Api/Infrastructure/Persistence/Odm';
    const SKELETON_TPL = 'DeleteManyRepository.php.twig';

    protected $targetPattern = '%s/%s/Infrastructure/Persistence/Repository/%s/Odm/DeleteManyRepository.php';
    protected $target;

    protected function setTarget()
    {
        $this->target = sprintf(
            $this->targetPattern,
            $this->parameters['rootDir'],
            $this->parameters['projectDir'],
            ucfirst($this->parameters['entityName'])
        );
    }
}
