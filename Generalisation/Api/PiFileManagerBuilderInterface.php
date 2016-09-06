<?php
declare(strict_types = 1);

namespace Sfynx\DddGeneratorBundle\Generalisation\Api;

/**
 * Interface PiFileManagerBuilderInterface
 * @category Generalisation
 * @package Api
 */
interface PiFileManagerBuilderInterface
{
    /**
     * Retrieves the directory name of a file.
     *
     * @param string $filename File name
     * @param string $separator
     * @return string
     */
    public static function getFileDirectoryName(string $filename, string $separator = '/'): string;
}
