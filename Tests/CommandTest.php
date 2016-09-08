<?php
/**
 * Sfynx Api Generator Command Unit Test
 * @author "Nicolas Blaudez <nblaudez@gmail.com>"
 */
namespace Sfynx\DddGeneratorBundle\Tests;

use \Phake;
use Sfynx\DddGeneratorBundle\Command\GenerateDddApiCommand;
use Symfony\Component\Yaml\Parser;

class CommandTest extends \PHPUnit_Framework_TestCase
{


    public function setup() {


        $this->assertNotNull($_ENV["swaggerFile"]);
        $this->assertNotNull($_ENV["contextName"]);
        $this->environment = $_ENV["contextName"];
        $this->swaggerFile = $_ENV["swaggerFile"];

        $ymlParser = new Parser();
        $this->dddApiCommand = new GenerateDddApiCommand();
        $this->dddApiCommand->setConfig($ymlParser->parse(file_get_contents($this->swaggerFile)));
    }


    public function testParsingValueObject() {
        $this->assertCount(1, $this->dddApiCommand->parseValueObjects());
    }

    public function testParsingEntities() {
        $this->assertCount(1, $this->dddApiCommand->parseEntities());
    }

    public function testParseRoutes() {
        $this->assertCount(4, $this->dddApiCommand->parseRoutes());
    }

}
