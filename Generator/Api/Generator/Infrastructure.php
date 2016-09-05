<?php
declare(strict_types = 1);

namespace Sfynx\DddGeneratorBundle\Generator\Api\Generator;

//From root namespace
use Error, Exception;

//Persistence part.
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Infrastructure\Persistence\{
    Orm\RepositoryHandler as OrmRepositoryHandler,
    Odm\RepositoryHandler as OdmRepositoryHandler,
    CouchDb\RepositoryHandler as CouchDbRepositoryHandler,
    TraitEntityNameHandler
};

use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Tests\Presentation\Adapter\Entity\Command\DeleteCommandAdapterTestHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Tests\Presentation\Adapter\Entity\Command\NewCommandAdapterTestHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Tests\Presentation\Adapter\Entity\Command\PatchCommandAdapterTestHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Tests\Presentation\Adapter\Entity\Command\UpdateCommandAdapterTestHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Tests\Presentation\Coordination\Entity\Command\ControllerCommandTestHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Tests\Presentation\Coordination\Entity\Query\ControllerQueryTestHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Tests\Presentation\Request\Entity\Command\DeleteRequestTestHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Tests\Presentation\Request\Entity\Command\NewRequestTestHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Tests\Presentation\Request\Entity\Command\PatchRequestTestHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Tests\Presentation\Request\Entity\Command\UpdateRequestTestHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Tests\Presentation\Request\Entity\Query\GetAllRequestTestHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Tests\Presentation\Request\Entity\Query\GetRequestTestHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\Tests\Presentation\Request\Entity\Query\SearchByRequestTestHandler;
/**
 * Class Infrastructure
 *
 * @category Generator
 * @package Api
 * @subpackage Generator
 */
class Infrastructure extends LayerAbstract
{
    /**
     * Entry point of the generation of the "Infrastructure" layer in DDD.
     * Call the generation of :
     * - Persistence ;
     * - Tests of the whole "Infrastructure" layer.
     */
    public function generate()
    {
        $this->output->writeln('');
        $this->output->writeln('##############################################');
        $this->output->writeln('#     GENERATE INFRASTRUCTURE STRUCTURE      #');
        $this->output->writeln('##############################################');
        $this->output->writeln('');

        $this->output->writeln('### PERSISTENCE GENERATION ###');
        $this->generatePersistence();
        $this->output->writeln('### TEST GENERATION ###');

        $this->generateTests();
    }

    /**
     * Generate the Persistence part in the "Infrastructure" layer.
     */
    public function generatePersistence()
    {
        foreach ($this->entitiesGroups as $entityName => $entityGroups) {
            $this->parameters['entityName'] = $entityName;
            $this->parameters['constructorArgs'] = $this->buildConstructorParamsString($entityName);
            $this->parameters['entityFields'] = $this->entitiesToCreate[$entityName];

            $this->addCQRSRepositoriesToGenerator($entityGroups, self::COMMAND)
                ->addCQRSRepositoriesToGenerator($entityGroups, self::QUERY);

            $this->generator->addHandler(new TraitEntityNameHandler($this->parameters), true);
        }

        $this->generator->execute()->clear();
    }

    /**
     * Add Repositories handlers to the generator. For use in a loop for each C.Q.R.S. actions.
     *
     * @param array  $entityGroups
     * @param string $group
     * @return self
     */
    private function addCQRSRepositoriesToGenerator(array $entityGroups, string $group): self
    {
        //Set the parameter $group to its good value (might be a reset)
        $this->parameters['group'] = $group;

        //Fetch all actionName and add the handler for this actionName
        foreach ($entityGroups[$group] as $data) {
            $this->parameters['actionName'] = ucfirst($data['action']);
            $this->generator->addHandler(new OrmRepositoryHandler($this->parameters), true);
            $this->generator->addHandler(new OdmRepositoryHandler($this->parameters), true);
            $this->generator->addHandler(new CouchDbRepositoryHandler($this->parameters), true);
        }

        return $this;
    }


    /**
     *  Entry point of the generation of unit tests
     *
     */
    public function generateTests() {

        return $this;
    }
}