<?php
declare(strict_types = 1);

namespace Sfynx\DddGeneratorBundle\Util;

use Sfynx\DddGeneratorBundle\Generalisation\Api\PiFileManagerBuilderInterface;

/**
 * Class PiFileManager.
 *
 * @category Util
 */
class PiFileManager implements PiFileManagerBuilderInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getFileDirectoryName(string $filename, string $separator = '/'): string
    {
        if (file_exists($filename)) {
            return dirname($filename);
        }

        $filename = explode($separator, $filename);
        array_pop($filename);

        return implode($separator, $filename);
    }
}
