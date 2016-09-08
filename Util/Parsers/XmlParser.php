<?php
declare(strict_types = 1);

namespace Sfynx\DddGeneratorBundle\Util\Parsers;

/**
 * Class XmlParser.
 *
 * @category Util
 * @package Parsers
 */
class XmlParser implements ParserInterface
{
    /**
     * Read the given XML file using its file name and parse it to return an array.
     *
     * @param string $fileName
     * @return array
     */
    public function parse(string $fileName): array
    {
        //TODO: implement the XML parser, then use the $content in the parser so remove the @noinspection tag.
        /** @noinspection PhpUnusedLocalVariableInspection */
        $content = file_get_contents($fileName);
        die('TODO: implement the XML parser.');
    }
}
