<?php
declare(strict_types = 1);

namespace Sfynx\DddGeneratorBundle\Twig;

/**
 * Class DDDExtension.
 *
 * @category Twig
 */
class DDDExtension extends \Twig_Extension
{
    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [new \Twig_SimpleFilter('ucfirst', [$this, 'ucfirstFilter'])];
    }

    /**
     * Return the same string passed as argument but with an upper cased first character.
     * Declared here to be a Twig function.
     *
     * @param $string
     * @return string
     */
    public static function ucfirstFilter(string $string): string
    {
        return ucfirst($string);
    }

    /**
     * Return the name of the Twig function extension created here.
     *
     * @return string
     */
    public function getName(): string
    {
        return 'sfynx_dddGeneratorBundle_extension_filter';
    }
}
