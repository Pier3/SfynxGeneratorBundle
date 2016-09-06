<?php

namespace Sfynx\DddGeneratorBundle\Twig;

class DDDExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('ucfirst', array($this, 'ucfirstFilter')),
        );
    }

    public static function ucfirstFilter($string)
    {
        return ucfirst($string);
    }

    public function getName()
    {
        return 'sfynx_dddgeneratorbundle_extension_filter';
    }
}