<?php

namespace Sfynx\DddGeneratorBundle\Generator\Api\Handler\Tests\Domain\Entity;

use Sfynx\DddGeneratorBundle\Generator\Generalisation\AbstractHandler;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\HandlerInterface;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\ExecuteTrait;

class CountryTestHandler extends AbstractHandler implements HandlerInterface
{
    use ExecuteTrait;

    const SKELETON_DIR = 'Api/Tests/Domain/Entity';
    const SKELETON_TPL = 'EntityTestHandler.php.twig';

    protected $targetPattern = '%s/%s/Tests/Application/Entity/Command/%sTestHandler.php';
    protected $target;

    protected function setTarget()
    {
        $this->target = sprintf(
            $this->targetPattern,
            $this->parameters['destinationPath'],
            $this->parameters['projectDir'],
            ucfirst($this->parameters['entityName']),
            ucfirst($this->parameters['actionName'])
        );
    }
}
