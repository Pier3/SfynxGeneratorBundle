<?php
declare(strict_types = 1);

namespace Sfynx\DddGeneratorBundle\Util\Parsers;

/**
 * Class JsonParser.
 *
 * @category Util
 * @package Parsers
 */
class JsonParser implements ParserInterface
{
    /**
     * Read the given json file using its file name and parse it to return an array.
     *
     * @param string $fileName
     * @return array
     */
    public function parse(string $fileName): array
    {
        //TODO: implement the JSON parser.
        die('TODO: implement the Json parser.');
    }
}
