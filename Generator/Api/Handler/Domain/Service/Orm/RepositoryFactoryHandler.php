<?php

namespace Sfynx\DddGeneratorBundle\Generator\Api\Handler\Domain\Service\Orm;

use Sfynx\DddGeneratorBundle\Generator\Generalisation\AbstractHandler;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\HandlerInterface;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\ExecuteTrait;

class RepositoryFactoryHandler extends AbstractHandler implements HandlerInterface
{
    use ExecuteTrait;

    const SKELETON_DIR = 'Api/Domain/Service/Orm';
    const SKELETON_TPL = 'RepositoryFactory.php.twig';

    protected $targetPattern = '%s/%s/Domain/Service/%s/Factory/Orm/RepositoryFactory.php';
    protected $target;

    protected function setTemplateName()
    {
        $this->templateName = self::SKELETON_TPL;
    }

    protected function setTarget()
    {
        $this->target = sprintf(
            $this->targetPattern,
            $this->parameters['destinationPath'],
            $this->parameters['projectDir'],
            $this->parameters['entityName']
        );
    }
}
