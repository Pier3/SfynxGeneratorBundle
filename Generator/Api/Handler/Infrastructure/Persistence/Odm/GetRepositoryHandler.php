<?php
namespace Sfynx\DddGeneratorBundle\Generator\Api\Handler\Infrastructure\Persistence\Odm;

use Sfynx\DddGeneratorBundle\Generator\Generalisation\AbstractHandler;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\HandlerInterface;
use Sfynx\DddGeneratorBundle\Generator\Generalisation\ExecuteTrait;

class GetRepositoryHandler extends AbstractHandler implements HandlerInterface
{
    use ExecuteTrait;

    const SKELETON_DIR = 'Api/Infrastructure/Persistence/Odm';
    const SKELETON_TPL = 'GetRepository.php.twig';

    protected $targetPattern = '%s/%s/Infrastructure/Persistence/Repository/%s/Odm/GetRepository.php';
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
