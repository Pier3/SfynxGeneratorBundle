<?php

namespace Sfynx\DddGeneratorBundle\Generator\Api\Handler\ValueObject;

use Sfynx\DddGeneratorBundle\Generator\Generalisation\AbstractHandler;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\HandlerInterface;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\ExecuteTrait;

class ValueObjectTypeCouchDBHandler extends AbstractHandler implements HandlerInterface
{
    use ExecuteTrait;

    const SKELETON_DIR = 'Api/Infrastructure/EntityType';
    const SKELETON_TPL = 'CouchDB.php.twig';

    protected $targetPattern = '%s/%s/Infrastructure/EntityType/CouchDB/%sType.php';
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
            ucfirst($this->parameters['voName'])
        );
    }
}
