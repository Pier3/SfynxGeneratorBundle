<?php
declare(strict_types = 1);

namespace Sfynx\DddGeneratorBundle\Bin;

use \Exception;
use Sfynx\DddGeneratorBundle\Console\SfynxGeneratorApplication;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class Generator.
 * This class is used by scripts to run the generator.
 *
 * @category bin
 */
class Generator
{
    const PROJECT_DIR = __DIR__ . '/../';

    const API_CONFIG = self::PROJECT_DIR . 'Resources/Api/config';
    const API_HANDLERS = self::PROJECT_DIR . 'Resources/Api/handlers';

    const API_COMMAND = 'dddapi.generator.command';

    public function run()
    {
        //Check that composer is correctly installed.
        self::checkComposerInstalled();

        //Define the application to run and the container.
        $app = new SfynxGeneratorApplication('Sfynx DDD Generator', 'v1.0');
        $container = new ContainerBuilder();

        //Try to register all required commands into the application, thanks to the container.
        try {
            $this->registerApiCommand($app, $container);
        } catch (Exception $e) {
            die('Exception occurs: ' . $e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }

        //Run the application if we managed to register all required commands.
        $app->run();
    }

    /**
     * Register the Api Generator command into the generator application, using the container.
     *
     * @param SfynxGeneratorApplication $app
     * @param ContainerBuilder          $container
     * @return Generator
     * @throws Exception
     */
    public function registerApiCommand(SfynxGeneratorApplication $app, ContainerBuilder $container): self
    {
        //Load the API configuration file
        $loader = new YamlFileLoader($container, new FileLocator(self::API_CONFIG));
        $loader->load('config.yml');
        $loader->load('services.yml');

        //Register the Api Generator Command.
        $app->add($container->get(self::API_COMMAND)->setRootDir(self::PROJECT_DIR));

        return $this;
    }

    /**
     * Check that composer is correctly installed on the generator.
     */
    public static function checkComposerInstalled()
    {
        if (false === self::includeIfExists(self::PROJECT_DIR . 'vendor/autoload.php') &&
            false === self::includeIfExists(self::PROJECT_DIR . '../../autoload.php')) {
            die(
                'You must set up the project dependencies, run the following commands:' . PHP_EOL .
                'curl -s http://getcomposer.org/installer | php' . PHP_EOL . 'php composer.phar install' . PHP_EOL
            );
        }
    }

    /**
     * Include a given file if exists. Returns true if file exists, false otherwise.
     *
     * @param string $file
     * @return bool
     */
    public static function includeIfExists(string $file): bool
    {
        if (file_exists($file)) {
            include $file;
            return true;
        }
        return false;
    }
}
