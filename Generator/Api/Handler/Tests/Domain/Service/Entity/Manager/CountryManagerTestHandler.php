<?php

namespace Sfynx\DddGeneratorBundle\Generator\Api\Handler\Tests\Domain\Service\Entity\Manager;

use Sfynx\DddGeneratorBundle\Generator\Generalisation\AbstractHandler;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\HandlerInterface;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\ExecuteTrait;

class CountryManagerTestHandler extends AbstractHandler implements HandlerInterface
{
    use ExecuteTrait;

    const SKELETON_DIR = 'Api/Tests/Domain/Service/Entity/Manager';
    const SKELETON_TPL = 'CountryManagerTest.php.twig';

    protected $targetPattern = '%s/%s/Tests/Domain/Service/%s/Manager/%sManagerTest.php';
    protected $target;

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
