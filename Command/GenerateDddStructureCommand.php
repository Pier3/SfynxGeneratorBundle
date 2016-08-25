<?php
//todo : à revoir

namespace Sfynx\DddGeneratorBundle\Command;

//use DemoApiContext\PresentationBundle\DependencyInjection\Compiler\ResettingListenersPass;
//use Sfynx\DddGeneratorBundle\Generator\Bundle\Handler\InfrastructureBundle\DependencyInjection\Compiler\CreateRepositoryFactoryPassHandler;
//use Sfynx\DddGeneratorBundle\Generator\Bundle\Handler\PresentationBundle\Resources\ServicesHandler;
//use Symfony\Component\Console\Input\InputOption;
//use Symfony\Component\Console\Question\ConfirmationQuestion;

use Sfynx\DddGeneratorBundle\Generator\Bundle\DddBundleGenerator;

use Sfynx\DddGeneratorBundle\Generator\Bundle\Handler\InfrastructureBundle\DependencyInjection\InfrastructureBundleExtensionHandler;
use Sfynx\DddGeneratorBundle\Generator\Bundle\Handler\InfrastructureBundle\InfrastructureBundleHandler;
use Sfynx\DddGeneratorBundle\Generator\Bundle\Handler\Presentation\Coordination\ControllerHandler;
use Sfynx\DddGeneratorBundle\Generator\Bundle\Handler\PresentationBundle\Config\RoutingHandler;
use Sfynx\DddGeneratorBundle\Generator\Bundle\Handler\PresentationBundle\DependencyInjection\ConfigurationHandler as PConfigurationHandler;
use Sfynx\DddGeneratorBundle\Generator\Bundle\Handler\PresentationBundle\DependencyInjection\PresentationBundleExtensionHandler;
use Sfynx\DddGeneratorBundle\Generator\Bundle\Handler\PresentationBundle\DependencyInjection\Compiler\ResettingListenersPassHandler;
use Sfynx\DddGeneratorBundle\Generator\Bundle\Handler\PresentationBundle\PresentationBundleHandler;
use Sfynx\DddGeneratorBundle\Generator\Bundle\Handler\PresentationBundle\Resources\ControllerYmlHandler;
use Sfynx\DddGeneratorBundle\Generator\Bundle\Handler\InfrastructureBundle\DependencyInjection\ConfigurationHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class GenerateDddStructureCommand extends Command
{
    /** @var DddBundleGenerator */
    protected $generator;
    protected $rootDir;
    protected $bundleName;

    /**
     * @see Command
     * @throws \InvalidArgumentException When the name is invalid
     */
    public function configure()
    {
        $this
            ->setName('sfynx:structure')
            ->setDescription('Generates a ddd structure')
            ->addArgument('destination-path', InputArgument::OPTIONAL, 'Destination path', '/tmp')
            ->setHelp("Generate a ddd structure");
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function interact(InputInterface $input, OutputInterface $output)
    {
        $dialog = $this->getHelper('question');
        $this->bundleName = $dialog->ask(
            $input,
            $output,
            new Question('Enter the name of the bundle: ')
        );

        //set argument : destination_path
        if (isset($_SERVER['SYMFONY_SFYNX_PATH_TO_DEST_FILES'])) {
            $destinationPath = $_SERVER['SYMFONY_SFYNX_PATH_TO_DEST_FILES'];
        } else {
            $destinationPath = $dialog->ask(
                $input,
                $output,
                new Question('destination path: ')
            );

            while (!is_dir($destinationPath) || !is_writable($destinationPath)) {
                //Set the entity name
                $output->writeln("This directory doesn't exist or is not writable");
                $dialog = $this->getHelper('question');
                $destinationPath = $dialog->ask(
                    $input,
                    $output,
                    new Question('Path to swagger yml file: ')
                );
            }
        }

        $input->setArgument('destination-path', $destinationPath);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $destinationPath = $input->getArgument('destination-path');
        $creationPoint = $this->rootDir . '/' . $this->bundleName;

        if (null !== $destinationPath) {
            $this->rootDir = $destinationPath;
        }

        mkdir($creationPoint);
        mkdir($creationPoint . '/Application');
        mkdir($creationPoint . '/Application/' . $this->bundleName);
        mkdir($creationPoint . '/Application/' . $this->bundleName . '/Query');
        mkdir($creationPoint . '/Application/' . $this->bundleName . '/Command');

        mkdir($creationPoint . '/config');

        mkdir($creationPoint . '/Domain');
        mkdir($creationPoint . '/Domain/Entity');
        mkdir($creationPoint . '/Domain/Repository');
        mkdir($creationPoint . '/Domain/Service');
        mkdir($creationPoint . '/Domain/Service/' . $this->bundleName);
        mkdir($creationPoint . '/Domain/Service/' . $this->bundleName . '/Factory');
        mkdir($creationPoint . '/Domain/Service/' . $this->bundleName . '/Factory/CouchDB');
        mkdir($creationPoint . '/Domain/Service/' . $this->bundleName . '/Factory/Odm');
        mkdir($creationPoint . '/Domain/Service/' . $this->bundleName . '/Factory/Orm');
        mkdir($creationPoint . '/Domain/Service/' . $this->bundleName . '/Manager');
        mkdir($creationPoint . '/Domain/Service/' . $this->bundleName . '/Processor');
        mkdir($creationPoint . '/Domain/Specification');
        mkdir($creationPoint . '/Domain/Specification/Infrastructure');
        mkdir($creationPoint . '/Domain/Specification/Infrastructure/' . $this->bundleName);
        mkdir($creationPoint . '/Domain/Workflow');
        mkdir($creationPoint . '/Domain/Workflow/' . $this->bundleName);
        mkdir($creationPoint . '/Domain/Workflow/' . $this->bundleName.'/Handler');
        mkdir($creationPoint . '/Domain/Workflow/' . $this->bundleName.'/Listener');

        mkdir($creationPoint . '/Infrastructure');
        mkdir($creationPoint . '/Infrastructure/Persistence');
        mkdir($creationPoint . '/Infrastructure/Persistence/Repository');
        mkdir($creationPoint . '/Infrastructure/Persistence/Repository/' . $this->bundleName);
        mkdir($creationPoint . '/InfrastructureBundle');
        mkdir($creationPoint . '/InfrastructureBundle/DependencyInjection');
        mkdir($creationPoint . '/InfrastructureBundle/Resources');

        mkdir($creationPoint . '/Presentation');
        mkdir($creationPoint . '/Presentation/Adapter');
        mkdir($creationPoint . '/Presentation/Adapter/' . $this->bundleName);
        mkdir($creationPoint . '/Presentation/Coordination/');
        mkdir($creationPoint . '/Presentation/Coordination/' . $this->bundleName);
        mkdir($creationPoint . '/Presentation/Request/');
        mkdir($creationPoint . '/Presentation/Request/' . $this->bundleName);

        mkdir($creationPoint . '/PresentationBundle');
        mkdir($creationPoint . '/PresentationBundle/DependencyInjection');
        mkdir($creationPoint . '/PresentationBundle/Resources');

        touch($creationPoint . '/PresentationBundle/Resources/Services.yml');

        mkdir($creationPoint . '/PresentationBundle/Resources/config');
        mkdir($creationPoint . '/PresentationBundle/Resources/config/application');
        mkdir($creationPoint . '/PresentationBundle/Resources/config/route');
        mkdir($creationPoint . '/PresentationBundle/Resources/config/public');
        mkdir($creationPoint . '/PresentationBundle/Resources/config/translations');

        $parameters = [
            'rootDir' => $this->rootDir,
            'projectDir' => 'src',
            'bundleName' => $this->bundleName
        ];

        // Files presentation
        $this->generator->addHandler(new PresentationBundleHandler($parameters));
        $this->generator->addHandler(new PConfigurationHandler($parameters));
        $this->generator->addHandler(new PresentationBundleExtensionHandler($parameters));
        $this->generator->addHandler(new ControllerYmlHandler($parameters));
        $this->generator->addHandler(new ControllerHandler($parameters));
        $this->generator->addHandler(new RoutingHandler($parameters));
        $this->generator->addHandler(new ResettingListenersPassHandler($parameters));

        // Files Infrastructure

        $this->generator->addHandler(new InfrastructureBundleHandler($parameters));
        $this->generator->addHandler(new ConfigurationHandler($parameters));
        $this->generator->addHandler(new InfrastructureBundleHandler($parameters));
        $this->generator->addHandler(new InfrastructureBundleExtensionHandler($parameters));

        $this->generator->execute();
    }

    /**
     * @param DddBundleGenerator $generator
     */
    public function setGenerator($generator)
    {
        $this->generator = $generator;
    }

    /**
     * @param string $rootDir
     */
    public function setRootDir($rootDir)
    {
        $this->rootDir = $rootDir;
    }
}
