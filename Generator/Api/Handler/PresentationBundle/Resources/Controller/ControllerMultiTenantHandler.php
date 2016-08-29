<?php

namespace Sfynx\DddGeneratorBundle\Generator\Api\Handler\PresentationBundle\Resources\Controller;

use Sfynx\DddGeneratorBundle\Generator\Generalisation\AbstractHandler;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\HandlerInterface;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\ExecuteTrait;

class ControllerMultiTenantHandler extends AbstractHandler implements HandlerInterface
{
    use ExecuteTrait;

    const SKELETON_DIR = 'Api/PresentationBundle/Resources/config/controller';
    const SKELETON_TPL = 'multiTenant.yml.twig';

    protected $targetPattern = '%s/%s/PresentationBundle/Resources/config/controller/multiTenant.yml';
    protected $target;

    protected function setTarget()
    {
        $this->target = sprintf(
            $this->targetPattern,
            $this->parameters['destinationPath'],
            $this->parameters['projectDir']
        );
    }
}
