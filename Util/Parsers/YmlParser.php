<?php
declare(strict_types = 1);

namespace Sfynx\DddGeneratorBundle\Util\Parsers;

use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Parser;

/**
 * Class YmlParser.
 *
 * @category Util
 * @package Parsers
 */
class YmlParser implements ParserInterface
{
    /**
     * Read the given YML file using its file name and parse it to return an array.
     *
     * @param string $fileName
     * @return array
     * @throws ParseException
     */
    public function parse(string $fileName): array
    {
        return (new Parser())->parse(file_get_contents($fileName));
    }
}
