<?php

namespace Sfynx\DddGeneratorBundle\Generator\Api\Generator;

use Sfynx\DddGeneratorBundle\Generator\Api\Handler\PresentationBundle\DependencyInjection\PresentationBundleExtensionHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\PresentationBundle\PresentationBundleHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\PresentationBundle\DependencyInjection\ConfigurationHandler as PBConfigurationHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\PresentationBundle\DependencyInjection\Compiler\ResettingListenersPassHandler as PBResettingListenersPass;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\PresentationBundle\Resources\Application\ApplicationCommandHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\PresentationBundle\Resources\Application\ApplicationQueryHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\PresentationBundle\Resources\Controller\ControllerCommandHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\PresentationBundle\Resources\Controller\ControllerMultiTenantHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\PresentationBundle\Resources\Controller\ControllerQueryHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\PresentationBundle\Resources\Controller\ControllerSwaggerHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\PresentationBundle\Resources\File\MultiTenant\MultiTenantHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\PresentationBundle\Resources\Route\RouteCommandHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\PresentationBundle\Resources\Route\RouteMultiTenantHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\PresentationBundle\Resources\Route\RouteQueryHandler;
use Sfynx\DddGeneratorBundle\Generator\Api\Handler\PresentationBundle\Resources\Route\RouteSwaggerHandler;

class PresentationBundle
{
    protected $generator;
    protected $entities = [];
    protected $entitiesToCreate = [];
    protected $valueObjects = [];
    protected $valueObjectsToCreate = [];
    protected $paths = [];
    protected $pathsToCreate = [];
    protected $projectDir;
    protected $destinationPath;

    public function __construct($generator, $entities, $entitiesToCreate, $valueObjects, $valueObjectsToCreate, $paths, $pathsToCreate, $rootDir, $projectDir, $destinationPath, $output)
    {
        $this->generator = $generator;
        $this->destinationPath = $destinationPath;
        $this->output = $output;
        $this->entities = $entities;
        $this->entitiesToCreate = $entitiesToCreate;
        $this->valueObjects = $valueObjects;
        $this->valueObjectsToCreate = $valueObjectsToCreate;
        $this->paths = $paths;
        $this->pathsToCreate = $pathsToCreate;
        $this->projectDir = $projectDir;
        $this->rootDir = $rootDir;
    }

    public function generate()
    {

        $this->output->writeln("#############################################");
        $this->output->writeln("# GENERATE PRESENTATION-BUNDLE  STRUCTURE   #");
        $this->output->writeln("#############################################");

        $this->generateBundle();
        $this->generateResourcesConfiguration();
    }


    public function generateBundle()
    {
        $parameters = [
            'rootDir' => $this->rootDir . "/src",
            'projectDir' => $this->projectDir,
            'projectName' => str_replace('src/', '', $this->projectDir),
            'entities' => $this->entities,
            'destinationPath' => $this->destinationPath,
        ];

        $this->generator->addHandler(new PresentationBundleHandler($parameters));
        $this->generator->addHandler(new PresentationBundleExtensionHandler($parameters));
        $this->generator->addHandler(new PBConfigurationHandler($parameters));
        $this->generator->addHandler(new PBResettingListenersPass($parameters));

        $this->generator->execute();
        $this->generator->clear();
    }


    public function generateResourcesConfiguration()
    {
        foreach ($this->entities as $entity => $vo) {
            $parameters = [
                'rootDir' => $this->rootDir . "/src",
                'projectDir' => $this->projectDir,
                'projectName' => str_replace('src/', '', $this->projectDir),
                'routes' => $this->paths,
                'entityName' => $entity,
                'destinationPath' => $this->destinationPath
            ];

            /**
             * Launch creation for Resources/config/application
             * => application_command
             * => application_query
             */
            $this->generator->addHandler(new ApplicationCommandHandler($parameters));
            $this->generator->addHandler(new ApplicationQueryHandler($parameters));

            /**
             * Launch creation for Resources/config/controller
             * => controller_command
             * => controller_query
             * only
             * the creation of multiTenant and swagger will be done outside of the foreach
             */
            $this->generator->addHandler(new ControllerCommandHandler($parameters));
            $this->generator->addHandler(new ControllerQueryHandler($parameters));

            /**
             * Launch creation for Resources/config/file
             * Creation of the multiTenant stragegy for all entities
             *
             */
            $this->generator->addHandler(new MultiTenantHandler($parameters));

            /**
             * Launch creation for Resources/config/route
             * => route_command
             * => route_query
             * only
             * the creation of multiTenant and swagger will be done outside of the foreach
             */
            $this->generator->addHandler(new RouteCommandHandler($parameters));
            $this->generator->addHandler(new RouteQueryHandler($parameters));

            $this->generator->execute();
            $this->generator->clear();
        }

        $parameters = [
            'rootDir' => $this->rootDir . "/src",
            'projectDir' => $this->projectDir,
            'projectName' => str_replace('src/', '', $this->projectDir),
            'entities' => $this->entities,
            'destinationPath' => $this->destinationPath,
        ];

        $this->generator->addHandler(new ControllerMultiTenantHandler($parameters));
        $this->generator->addHandler(new ControllerSwaggerHandler($parameters));

        $this->generator->addHandler(new RouteMultiTenantHandler($parameters));
        $this->generator->addHandler(new RouteSwaggerHandler($parameters));

        $this->generator->execute();
        $this->generator->clear();


    }

}
