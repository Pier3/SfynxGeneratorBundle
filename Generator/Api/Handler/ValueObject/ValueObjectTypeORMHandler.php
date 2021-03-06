<?php

namespace Sfynx\DddGeneratorBundle\Generator\Api\Handler\ValueObject;

use Sfynx\DddGeneratorBundle\Generator\Generalisation\AbstractHandler;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\HandlerInterface;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\ExecuteTrait;

class ValueObjectTypeORMHandler extends AbstractHandler implements HandlerInterface
{
    use  ExecuteTrait;

    const SKELETON_DIR = 'Api/Infrastructure/EntityType';
    const SKELETON_TPL = 'Orm.php.twig';

    protected $targetPattern = '%s/%s/Infrastructure/EntityType/Orm/%sType.php';
    protected $target;

    protected function setTarget()
    {
        $this->target = sprintf(
            $this->targetPattern,
            $this->parameters['rootDir'],
            $this->parameters['projectDir'],
            ucfirst($this->parameters['voName'])
        );
    }
}
