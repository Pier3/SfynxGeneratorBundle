<?php

namespace Sfynx\DddGeneratorBundle\Generator\Api\Handler\Tests\Domain\Service\Entity\Manager;

use Sfynx\DddGeneratorBundle\Generator\Generalisation\AbstractHandler;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\Handler;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\HandlerInterface;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\ExecuteTrait;

class ManagerTestHandler extends Handler implements HandlerInterface
{
    use ExecuteTrait;

    const SKELETON_DIR = 'Api/Tests/Domain/Service/Entity/Manager';
    const SKELETON_TPL = 'ManagerTest.php.twig';

    protected $targetPattern = '%destinationPath$s/%s/Tests/Domain/Service/%s/Manager/%sManagerTest.php';
    protected $target;

    protected function setTemplateName()
    {
        $this->templateName = sprintf(self::SKELETON_TPL);
    }


    protected function setTarget()
    {
        $this->target = sprintf(
            $this->targetPattern,
            $this->parameters['destinationPath'],
            $this->parameters['projectDir'],
            ucfirst($this->parameters['entityName']),
            ucfirst($this->parameters['entityName'])
        );
    }
}
