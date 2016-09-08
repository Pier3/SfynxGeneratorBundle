<?php
declare(strict_types = 1);

namespace Sfynx\DddGeneratorBundle\Util\Parsers;

/**
 * Interface ParserInterface.
 *
 * @category Util
 * @package Parsers
 */
interface ParserInterface
{
    /**
     * Read the given file using its file name and parse it to return an array.
     *
     * @param string $fileName
     * @return array
     */
    public function parse(string $fileName): array;
}
