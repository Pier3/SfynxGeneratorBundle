<?php

namespace Sfynx\DddGeneratorBundle\Generator\Api\Handler\Tests\Domain\Service\Entity\Factory\Orm;

use Sfynx\DddGeneratorBundle\Generator\Generalisation\AbstractHandler;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\HandlerInterface;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\ExecuteTrait;

class RepositoryFactoryTestHandler extends AbstractHandler implements HandlerInterface
{
    use ExecuteTrait;

    const SKELETON_DIR = 'Api/Tests/Domain/Service/Entity/Factory/Orm';
    const SKELETON_TPL = 'RepositoryFactoryTest.php.twig';

    protected $targetPattern = '%s/%s/Tests/Domain/Service/%s/Factory/Orm/RepositoryFactoryTest.php';
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
