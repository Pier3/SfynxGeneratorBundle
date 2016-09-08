<?php
declare(strict_types = 1);

namespace Sfynx\DddGeneratorBundle\Util\Parsers;

/**
 * Class ParserFactory.
 *
 * @category Util
 * @package Parsers
 */
class ParserFactory
{
    const XML = 'xml';
    const YML = 'yml';
    const JSON = 'json';

    /**
     * Load the appropriate parser depending on the file name of the file to parse.
     *
     * @param string $fileName
     * @return array
     */
    public static function parse(string $fileName): array
    {
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        $parser = self::buildParser($extension);
        return $parser->parse($fileName);
    }

    /**
     * Build a parser object depending on the given extension.
     *
     * @param string $extension
     * @return ParserInterface
     */
    public static function buildParser(string $extension): ParserInterface
    {
        switch (mb_strtolower($extension)) {
            default:
            case self::YML:
                return new YmlParser();
            case self::XML:
                return new XmlParser();
            case self::JSON:
                return new JsonParser();
        }
    }
}
