<?php

namespace Sfynx\DddGeneratorBundle\Generator\Generalisation;

use Sfynx\DddGeneratorBundle\Twig\DDDExtension;
use Twig_SimpleFilter;

abstract class AbstractHandler
{
    protected $rootSkeletonDir;

    protected $skeletonDirs;

    protected $parameters;

    protected $templateName;

    /**
     * AbstractHandler constructor.
     * @param $commonParameters
     */
    public function __construct($commonParameters)
    {
        $this->parameters = $commonParameters;
        $this->rootSkeletonDir = dirname(dirname(__DIR__)) . '/Skeleton';
        $this->setTarget();
        $this->setTemplateName();
    }

    public function getRootSkeletonDir()
    {
        return $this->rootSkeletonDir;
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    public function getTemplateName()
    {
        return $this->templateName;
    }

    abstract protected function setTarget();

    abstract protected function setTemplateName();

    /**
     * Sets an array of directories to look for templates.
     *
     * The directories must be sorted from the most specific to the most
     * directory.
     *
     * @param array $skeletonDirs An array of skeleton dirs
     */
    public function setSkeletonDirs($skeletonDirs)
    {
        $this->skeletonDirs = is_array($skeletonDirs) ? $skeletonDirs : array($skeletonDirs);
    }

    public function setPermissions($target, $owner = 'www-data', $group = 'www-data', $rights = '0777')
    {
        /* If you are on linux,
               there is an issue on the chown/chmod command so
               we have to execute directly the sudo command here
           Else
               just call the php internal functions
        */
        if ('win' !== strtolower(substr(PHP_OS, 0, 3))) {
            `sudo chown {$owner}:{$group} {$target}`;
            `sudo chmod {$rights} {$target}`;
        } else {
            chown($target, $owner);
            chgrp($target, $group);
            chmod($target, $rights);
        }
    }

    protected function render($template, $parameters)
    {
        $twig = $this->getTwigEnvironment();
        $filter = new Twig_SimpleFilter('ucfirst', array(DDDExtension::class, 'ucfirstFilter'));
        $twig->addFilter($filter);

        return $twig->render($template, $parameters);
    }

    /**
     * Get the twig environment that will render skeletons.
     *
     * @return \Twig_Environment
     */
    protected function getTwigEnvironment()
    {
        return new \Twig_Environment(new \Twig_Loader_Filesystem($this->skeletonDirs), array(
            'debug' => true,
            'cache' => false,
            'strict_variables' => true,
            'autoescape' => false,
        ));
    }

    public function renderFile($template, $target, $parameters)
    {
        var_dump($target);die;

        if (!is_dir(dirname($target))) {
            mkdir(dirname($target), 0777, true);
        }

        echo "    # $target\n";
        return file_put_contents($target, $this->render($template, $parameters));
    }
}
