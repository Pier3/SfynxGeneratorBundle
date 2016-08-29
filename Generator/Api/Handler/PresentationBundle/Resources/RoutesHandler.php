<?php

namespace Sfynx\DddGeneratorBundle\Generator\Api\Handler\PresentationBundle\Resources;

use Sfynx\DddGeneratorBundle\Generator\Generalisation\AbstractHandler;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\HandlerInterface;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\ExecuteTrait;

class RoutesHandler extends AbstractHandler implements HandlerInterface
{
    use ExecuteTrait;

    const SKELETON_DIR = 'Api/PresentationBundle/Resources/routes';
    const SKELETON_TPL = 'routes.php.twig';

    protected $targetPattern = '%s/%s/PresentationBundle/Resources/config/routes/routing_%s.yml';
    protected $target;

    protected function setTarget()
    {
        $this->target = sprintf(
            $this->targetPattern,
            $this->parameters['destinationPath'],
            $this->parameters['projectDir'],
            strtolower($this->parameters['entityName'])

        );
    }
}
