<?php

namespace Sfynx\DddGeneratorBundle\Generator\Api\Handler\PresentationBundle\Resources\Route;

use Sfynx\DddGeneratorBundle\Generator\Generalisation\AbstractHandler;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\HandlerInterface;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\ExecuteTrait;

class RouteSwaggerHandler extends AbstractHandler implements HandlerInterface
{
    use ExecuteTrait;

    const SKELETON_DIR = 'Api/PresentationBundle/Resources/config/route';
    const SKELETON_TPL = 'swagger.yml.twig';

    protected $targetPattern = '%s/%s/PresentationBundle/Resources/config/route/swagger.yml';
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
