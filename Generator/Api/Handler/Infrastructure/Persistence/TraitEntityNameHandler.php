<?php

namespace Sfynx\DddGeneratorBundle\Generator\Api\Handler\Infrastructure\Persistence;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\AbstractHandler;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\HandlerInterface;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\ExecuteTrait;

class TraitEntityNameHandler extends AbstractHandler implements HandlerInterface
{
    use ExecuteTrait;

    const SKELETON_DIR = 'Api/Infrastructure/Persistence';
    const SKELETON_TPL = 'Persistence/TraitEntityName.php.twig';

    protected $targetPattern = '%destinationPath$s/%projectDir$s/Infrastructure/Persistence/Repository/%entityName$s/TraitEntityName.php';
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
            ucfirst($this->parameters['entityName'])
        );
    }
}
