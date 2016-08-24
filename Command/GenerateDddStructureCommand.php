<?php
//todo : à revoir

namespace Sfynx\DddGeneratorBundle\Command;

use DemoApiContext\PresentationBundle\DependencyInjection\Compiler\ResettingListenersPass;

use Sfynx\DddGeneratorBundle\Generator\Bundle\Handler\InfrastructureBundle\DependencyInjection\Compiler\CreateRepositoryFactoryPassHandler;
use Sfynx\DddGeneratorBundle\Generator\Bundle\Handler\InfrastructureBundle\DependencyInjection\InfrastructureBundleExtensionHandler;
use Sfynx\DddGeneratorBundle\Generator\Bundle\Handler\InfrastructureBundle\InfrastructureBundleHandler;
use Sfynx\DddGeneratorBundle\Generator\Bundle\Handler\Presentation\Coordination\ControllerHandler;
use Sfynx\DddGeneratorBundle\Generator\Bundle\Handler\PresentationBundle\Config\RoutingHandler;
use Sfynx\DddGeneratorBundle\Generator\Bundle\Handler\PresentationBundle\DependencyInjection\ConfigurationHandler as PConfigurationHandler;
use Sfynx\DddGeneratorBundle\Generator\Bundle\Handler\PresentationBundle\DependencyInjection\PresentationBundleExtensionHandler;
use Sfynx\DddGeneratorBundle\Generator\Bundle\Handler\PresentationBundle\DependencyInjection\Compiler\ResettingListenersPassHandler;
use Sfynx\DddGeneratorBundle\Generator\Bundle\Handler\PresentationBundle\PresentationBundleHandler;
use Sfynx\DddGeneratorBundle\Generator\Bundle\Handler\PresentationBundle\Resources\ControllerYmlHandler;
use Sfynx\DddGeneratorBundle\Generator\Bundle\Handler\PresentationBundle\Resources\ServicesHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

use Sfynx\DddGeneratorBundle\Generator\Bundle\Handler\InfrastructureBundle\DependencyInjection\ConfigurationHandler;

class GenerateDddStructureCommand extends Command
{
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

    public function interact(InputInterface $input, OutputInterface $output)
    {
        $dialog = $this->getHelper('question');
        $this->bundleName = $dialog->ask(
            $input,
            $output,
            new Question('Enter the name of the bundle : ')
        );

        //set argument : destination_path
        if (isset($_SERVER['SYMFONY_SFYNX_PATH_TO_DEST_FILES'])) {
            $destPath = $_SERVER['SYMFONY_SFYNX_PATH_TO_DEST_FILES'];
        } else {
            $destPath = $dialog->ask(
                $input,
                $output,
                new Question('destination path: ')
            );

            while (!is_dir($destPath) || !is_writable($destPath)) {
                //Set the entity name
                $output->writeln("This directory doesn't exist or is not writable");
                $dialog = $this->getHelper('question');
                $destPath = $dialog->ask(
                    $input,
                    $output,
                    new Question('Path to swagger yml file: ')
                );
            }
        }
        $input->setArgument('destination-path', $destPath);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $destPath = $input->getArgument('destination-path');
        if (null !== $destPath) {
            $this->rootDir = $destPath;
        }
        mkdir($this->rootDir."/".$this->bundleName);

        mkdir($this->rootDir."/".$this->bundleName."/Application");
        mkdir($this->rootDir."/".$this->bundleName."/Application/".$this->bundleName);
        mkdir($this->rootDir."/".$this->bundleName."/Application/".$this->bundleName."/Query");
        mkdir($this->rootDir."/".$this->bundleName."/Application/".$this->bundleName."/Command");

        mkdir($this->rootDir."/".$this->bundleName."/config");

        mkdir($this->rootDir."/".$this->bundleName."/Domain");
        mkdir($this->rootDir."/".$this->bundleName."/Domain/Entity");
        mkdir($this->rootDir."/".$this->bundleName."/Domain/Repository");
        mkdir($this->rootDir."/".$this->bundleName."/Domain/Service");
        mkdir($this->rootDir."/".$this->bundleName."/Domain/Service/".$this->bundleName);
        mkdir($this->rootDir."/".$this->bundleName."/Domain/Service/".$this->bundleName."/Factory");
        mkdir($this->rootDir."/".$this->bundleName."/Domain/Service/".$this->bundleName."/Factory/CouchDB");
        mkdir($this->rootDir."/".$this->bundleName."/Domain/Service/".$this->bundleName."/Factory/Odm");
        mkdir($this->rootDir."/".$this->bundleName."/Domain/Service/".$this->bundleName."/Factory/Orm");

        mkdir($this->rootDir."/".$this->bundleName."/Domain/Service/".$this->bundleName."/Manager");
        mkdir($this->rootDir."/".$this->bundleName."/Domain/Service/".$this->bundleName."/Processor");

        mkdir($this->rootDir."/".$this->bundleName."/Domain/Specification");
        mkdir($this->rootDir."/".$this->bundleName."/Domain/Specification/Infrastructure");
        mkdir($this->rootDir."/".$this->bundleName."/Domain/Specification/Infrastructure/".$this->bundleName);

        mkdir($this->rootDir."/".$this->bundleName."/Domain/Workflow");
        mkdir($this->rootDir."/".$this->bundleName."/Domain/Workflow/".$this->bundleName);
        mkdir($this->rootDir."/".$this->bundleName."/Domain/Workflow/".$this->bundleName."/Handler");
        mkdir($this->rootDir."/".$this->bundleName."/Domain/Workflow/".$this->bundleName."/Listener");

        mkdir($this->rootDir."/".$this->bundleName."/Infrastructure");
        mkdir($this->rootDir."/".$this->bundleName."/Infrastructure/Persistence");
        mkdir($this->rootDir."/".$this->bundleName."/Infrastructure/Persistence/Repository");
        mkdir($this->rootDir."/".$this->bundleName."/Infrastructure/Persistence/Repository/".$this->bundleName);

        mkdir($this->rootDir."/".$this->bundleName."/InfrastructureBundle");
        mkdir($this->rootDir."/".$this->bundleName."/InfrastructureBundle/DependencyInjection");
        mkdir($this->rootDir."/".$this->bundleName."/InfrastructureBundle/Resources");

        mkdir($this->rootDir."/".$this->bundleName."/Presentation");
        mkdir($this->rootDir."/".$this->bundleName."/Presentation/Adapter");
        mkdir($this->rootDir."/".$this->bundleName."/Presentation/Adapter/".$this->bundleName);
        mkdir($this->rootDir."/".$this->bundleName."/Presentation/Coordination/");
        mkdir($this->rootDir."/".$this->bundleName."/Presentation/Coordination/".$this->bundleName);
        mkdir($this->rootDir."/".$this->bundleName."/Presentation/Request/");
        mkdir($this->rootDir."/".$this->bundleName."/Presentation/Request/".$this->bundleName);

        mkdir($this->rootDir."/".$this->bundleName."/PresentationBundle");
        mkdir($this->rootDir."/".$this->bundleName."/PresentationBundle/DependencyInjection");
        mkdir($this->rootDir."/".$this->bundleName."/PresentationBundle/Resources");
        touch($this->rootDir."/".$this->bundleName."/PresentationBundle/Resources/Services.yml");
        mkdir($this->rootDir."/".$this->bundleName."/PresentationBundle/Resources/config");
        mkdir($this->rootDir."/".$this->bundleName."/PresentationBundle/Resources/config/application");
        mkdir($this->rootDir."/".$this->bundleName."/PresentationBundle/Resources/config/route");

        mkdir($this->rootDir."/".$this->bundleName."/PresentationBundle/Resources/config/public");
        mkdir($this->rootDir."/".$this->bundleName."/PresentationBundle/Resources/config/translations");


        $parameters = [
            'rootDir' => $this->rootDir,
            'projectDir' => "src",
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

    public function setGenerator($generator)
    {
        $this->generator=$generator;
    }

    public function setRootDir($rootDir) {
        $this->rootDir = $rootDir;
    }

}
