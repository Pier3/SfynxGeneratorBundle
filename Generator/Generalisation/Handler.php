<?php
declare(strict_types = 1);

namespace Sfynx\DddGeneratorBundle\Generator\Generalisation;

use Exception;
use Sfynx\DddGeneratorBundle\Util\StringManipulation;
use Twig_Environment;
use Twig_Error_Loader;
use Twig_Error_Runtime;
use Twig_Error_Syntax;

use Sfynx\DddGeneratorBundle\Util\PiFileManager;
use Sfynx\DddGeneratorBundle\Twig\DDDExtension;
use Twig_Loader_Filesystem;
use Twig_SimpleFilter;

/**
 * Class Handler.
 *
 * @category Generator
 * @package Generalisation
 */
class Handler implements HandlerInterface
{
    /** @var string */
    protected $rootSkeletonDir;
    /** @var string[] */
    protected $skeletonDirs;
    /** @var array */
    protected $parameters;
    /** @var string */
    protected $templateName;
    /** @var string */
    protected $skeletonDir;
    /** @var string */
    protected $skeletonTpl;
    /** @var string */
    protected $targetPattern;
    /** @var string */
    protected $target;
    /** @var string */
    protected $handlerName;

    /**
     * AbstractHandler constructor.
     * @param array $commonParameters
     */
    public function __construct(array $commonParameters)
    {
        $this->parameters = $commonParameters;
        //If key "skeletonDir" is set, give its value to $this->skeletonDir, otherwise, set null.
        $this->skeletonDir = $commonParameters['skeletonDir'] ?? null;
        $this->skeletonTpl = $commonParameters['skeletonTpl'] ?? null;
        $this->targetPattern = $commonParameters['targetPattern'] ?? null;
        $this->handlerName = $commonParameters['handlerName'] ?? null;

        $this->rootSkeletonDir = dirname(dirname(__DIR__)) . '/Skeleton';
        $this->setTarget();
        $this->setTemplateName();
    }

    public function execute()
    {
        try {
            $this->setSkeletonDirs($this->getRootSkeletonDir() . '/' . $this->skeletonDir);
            $targetDir = PiFileManager::getFileDirectoryName($this->target);
            if (!file_exists(PiFileManager::getFileDirectoryName($this->target))) {
                mkdir($targetDir, 0777, true);
            }
            $this->renderFile($this->getTemplateName(), $this->target, $this->getParameters());
            $this->setPermissions($this->target);
        } catch (Exception $e) {
            $errorMessage = PHP_EOL . ' # /!\ Exception occurs during the execution of handler "%s":' . PHP_EOL
                . '    %s' . PHP_EOL . PHP_EOL;
            fwrite(STDERR, sprintf($errorMessage, $this->handlerName, $e->getMessage()));
        }
    }

    /**
     * Return the root skeleton directory path
     * @return string
     */
    public function getRootSkeletonDir(): string
    {
        return $this->rootSkeletonDir;
    }

    /**
     * Return the common parameters.
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Return the name of the template used to create the concrete file.
     * @return string
     */
    public function getTemplateName(): string
    {
        return $this->templateName;
    }

    /**
     * Set the name of the target file to be created.
     */
    protected function setTarget()
    {
        $this->target = StringManipulation::vksprintf($this->targetPattern, $this->parameters);
    }

    /**
     * Set the name of the template file used for creation.
     */
    protected function setTemplateName()
    {
        $this->templateName = StringManipulation::vksprintf($this->skeletonTpl, $this->parameters);
    }

    /**
     * Set an array of directories to look for templates.
     * The directories must be sorted from the most specific to the most generic directory.
     *
     * @param array|string $skeletonDirs An array of skeleton dirs
     * @return Handler
     */
    public function setSkeletonDirs($skeletonDirs): self
    {
        $this->skeletonDirs = is_array($skeletonDirs) ? $skeletonDirs : [$skeletonDirs];
        return $this;
    }

    /**
     * Set the permissions to the target giving it the good owners and rights.
     *
     * @param string $target
     * @param string $owner
     * @param string $group
     */
    public function setPermissions(string $target, string $owner = 'www-data', string $group = 'www-data')
    {
        //If you are on linux, there is an issue on the chown/chmod command so we have to execute directly the `sudo`
        //command here. Otherwise, just call the php internal functions
        if (0 !== stripos(PHP_OS, 'win')) {
            `sudo chown {$owner}:{$group} {$target}`;
            `sudo chmod 777 {$target}`;
        } else {
            chown($target, $owner);
            chgrp($target, $group);
            chmod($target, '0777');
        }
    }

    /**
     * Render the template using the parameters.
     *
     * @param string $template
     * @param array  $parameters
     * @return string
     * @throws Twig_Error_Syntax
     * @throws Twig_Error_Runtime
     * @throws Twig_Error_Loader
     */
    protected function render(string $template, array $parameters): string
    {
        $twig = $this->getTwigEnvironment();
        $twig->addFilter(new Twig_SimpleFilter('ucfirst', [DDDExtension::class, 'ucfirstFilter']));

        return $twig->render($template, $parameters);
    }

    /**
     * Get the twig environment that will render skeletons.
     *
     * @return Twig_Environment
     */
    protected function getTwigEnvironment(): Twig_Environment
    {
        return new Twig_Environment(
            new Twig_Loader_Filesystem($this->skeletonDirs),
            [
                'debug' => true,
                'cache' => false,
                'strict_variables' => true,
                'autoescape' => false,
            ]
        );
    }

    /**
     * Render the template file with parameters to the target file given in argument.
     *
     * @param string $template
     * @param string $target
     * @param array  $parameters
     * @throws Twig_Error_Syntax
     * @throws Twig_Error_Runtime
     * @throws Twig_Error_Loader
     */
    public function renderFile(string $template, string $target, array $parameters)
    {
        if (!is_dir(dirname($target))) {
            mkdir(dirname($target), 0777, true);
        }

        echo '    # ' . $target . PHP_EOL;
        file_put_contents($target, $this->render($template, $parameters));
    }
}
