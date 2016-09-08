<?php
/**
 * Sfynx Api Generator Domain generation unit test
 * @author "Nicolas Blaudez <nblaudez@gmail.com>"
 */
namespace Sfynx\DddGeneratorBundle\Tests;

use \Phake;
use Sfynx\DddGeneratorBundle\Command\GenerateDddApiCommand;
use Sfynx\DddGeneratorBundle\Generator\Api\DddApiGenerator;
use Symfony\Component\Yaml\Parser;

class DomainTest extends \PHPUnit_Framework_TestCase
{

    protected $entities;
    protected $routes;
    protected $valueObjects;
    protected $environment;

    public function setup() {
        $this->assertNotNull($_ENV["swaggerFile"]);
        $this->assertNotNull($_ENV["contextName"]);
        $this->environment = $_ENV["contextName"];
        $this->swaggerFile = $_ENV["swaggerFile"];

        $ymlParser = new Parser();
        $this->dddApiCommand = new GenerateDddApiCommand();
        $this->dddApiCommand->setConfig($ymlParser->parse(file_get_contents($this->swaggerFile)));

        $this->entities = $this->dddApiCommand->parseEntities();
        $this->valueObjects = $this->dddApiCommand->parseValueObjects();
        $this->routes = $this->dddApiCommand->parseRoutes();
    }

    public function hasMethod($method, $class) {
        $methods = $class->getMethods();
        foreach ($methods as $classMethod) {
            if ($classMethod->name == $method) {
                return true;
            }
        }
        return false;
    }

    public function checkNamespace($namespace, $file) {

        $content = file_get_contents($file);
        
        if (preg_match('#\s*namespace\s*' . addslashes($namespace) . '\s*;#', $content)) {
            return true;
        } else {
            return false;
        }
    }

    public function checkClassName($className, $file) {
        $content = file_get_contents($file);

        if (preg_match('#\s*([Cc]lass|[Ii]nterface)\s*' . $className . '\s*#', $content)) {
            return true;
        } else {
            return false;
        }
    }

    public function testfilesExist() {
        $this->assertFileExists(__DIR__ . "/../../../../src/" . $this->environment . "/Domain");
        $this->assertFileExists(__DIR__ . "/../../../../src/" . $this->environment . "/Domain/Entity");
        foreach ($this->entities as $entity => $fields) {
            $this->assertFileExists(__DIR__ . "/../../../../src/" . $this->environment . "/Domain/Entity/" . $entity . ".php");
            $classNamespace = "\\" . $this->environment . "\\Domain\\Entity\\" . $entity;
            foreach ($fields as $field) {
                $class = new \ReflectionClass($classNamespace);
                $this->assertTrue($this->hasMethod("set" . ucfirst($field["name"]), $class));
                $this->assertTrue($this->hasMethod("get" . ucfirst($field["name"]), $class));
            }


            $this->assertFileExists(__DIR__ . "/../../../../src/" . $this->environment . "/Domain/Repository/" . $entity . "RepositoryInterface.php");
            $this->assertTrue($this->checkNamespace($this->environment . "\\Domain\\Repository", __DIR__ . "/../../../../src/" . $this->environment . "/Domain/Repository/" . $entity . "RepositoryInterface.php"));
            $this->assertTrue($this->checkClassName($entity . "RepositoryInterface", __DIR__ . "/../../../../src/" . $this->environment . "/Domain/Repository/" . $entity . "RepositoryInterface.php"));


            $this->assertFileExists(__DIR__ . "/../../../../src/" . $this->environment . "/Domain/Service/" . $entity . "/Factory/CouchDB/RepositoryFactory.php");
            $this->assertTrue($this->checkNamespace($this->environment . "\\Domain\\Service\\$entity\\Factory\\CouchDB", __DIR__ . "/../../../../src/" . $this->environment . "/Domain/Service/" . $entity . "/Factory/CouchDB/RepositoryFactory.php"));
            $this->assertTrue($this->checkClassName("RepositoryFactory", __DIR__ . "/../../../../src/" . $this->environment . "/Domain/Service/" . $entity . "/Factory/CouchDB/RepositoryFactory.php"));

            $this->assertFileExists(__DIR__ . "/../../../../src/" . $this->environment . "/Domain/Service/" . $entity . "/Factory/Odm/RepositoryFactory.php");
            $this->assertTrue($this->checkNamespace($this->environment . "\\Domain\\Service\\$entity\\Factory\\Odm", __DIR__ . "/../../../../src/" . $this->environment . "/Domain/Service/" . $entity . "/Factory/Odm/RepositoryFactory.php"));
            $this->assertTrue($this->checkClassName("RepositoryFactory", __DIR__ . "/../../../../src/" . $this->environment . "/Domain/Service/" . $entity . "/Factory/Odm/RepositoryFactory.php"));


            $this->assertFileExists(__DIR__ . "/../../../../src/" . $this->environment . "/Domain/Service/" . $entity . "/Factory/Orm/RepositoryFactory.php");
            $this->assertTrue($this->checkNamespace($this->environment . "\\Domain\\Service\\$entity\\Factory\\Orm", __DIR__ . "/../../../../src/" . $this->environment . "/Domain/Service/" . $entity . "/Factory/Orm/RepositoryFactory.php"));
            $this->assertTrue($this->checkClassName("RepositoryFactory", __DIR__ . "/../../../../src/" . $this->environment . "/Domain/Service/" . $entity . "/Factory/Orm/RepositoryFactory.php"));


            $this->assertFileExists(__DIR__ . "/../../../../src/" . $this->environment . "/Domain/Service/" . $entity . "/Manager/" . $entity . "Manager.php");
            $this->assertTrue($this->checkNamespace($this->environment . "\\Domain\\Service\\$entity\\Manager", __DIR__ . "/../../../../src/" . $this->environment . "/Domain/Service/" . $entity . "/Manager/" . $entity . "Manager.php"));
            $this->assertTrue($this->checkClassName($entity . "Manager", __DIR__ . "/../../../../src/" . $this->environment . "/Domain/Service/" . $entity . "/Manager/" . $entity . "Manager.php"));


            $this->assertFileExists(__DIR__ . "/../../../../src/" . $this->environment . "/Domain/Service/" . $entity . "/Processor/PostPersistProcess.php");
            $this->assertTrue($this->checkNamespace($this->environment . "\\Domain\\Service\\$entity\\Processor", __DIR__ . "/../../../../src/" . $this->environment . "/Domain/Service/" . $entity . "/Processor/PostPersistProcess.php"));
            $this->assertTrue($this->checkClassName("PostPersistProcess", __DIR__ . "/../../../../src/" . $this->environment . "/Domain/Service/" . $entity . "/Processor/PostPersistProcess.php"));


            $this->assertFileExists(__DIR__ . "/../../../../src/" . $this->environment . "/Domain/Service/" . $entity . "/Processor/PrePersistProcess.php");
            $this->assertTrue($this->checkNamespace($this->environment . "\\Domain\\Service\\$entity\\Processor", __DIR__ . "/../../../../src/" . $this->environment . "/Domain/Service/" . $entity . "/Processor/PrePersistProcess.php"));
            $this->assertTrue($this->checkClassName("PrePersistProcess", __DIR__ . "/../../../../src/" . $this->environment . "/Domain/Service/" . $entity . "/Processor/PrePersistProcess.php"));


            $this->assertFileExists(__DIR__ . "/../../../../src/" . $this->environment . "/Domain/Workflow/" . $entity . "/Handler/NewWFHandler.php");
            $this->assertTrue($this->checkNamespace($this->environment . "\\Domain\\Workflow\\$entity\\Handler", __DIR__ . "/../../../../src/" . $this->environment . "/Domain/Workflow/" . $entity . "/Handler/NewWFHandler.php"));
            $this->assertTrue($this->checkClassName("NewWFHandler", __DIR__ . "/../../../../src/" . $this->environment . "/Domain/Workflow/" . $entity . "/Handler/NewWFHandler.php"));

            $this->assertFileExists(__DIR__ . "/../../../../src/" . $this->environment . "/Domain/Workflow/" . $entity . "/Handler/UpdateWFHandler.php");
            $this->assertTrue($this->checkNamespace($this->environment . "\\Domain\\Workflow\\$entity\\Handler", __DIR__ . "/../../../../src/" . $this->environment . "/Domain/Workflow/" . $entity . "/Handler/UpdateWFHandler.php"));
            $this->assertTrue($this->checkClassName("UpdateWFHandler", __DIR__ . "/../../../../src/" . $this->environment . "/Domain/Workflow/" . $entity . "/Handler/UpdateWFHandler.php"));

            $this->assertFileExists(__DIR__ . "/../../../../src/" . $this->environment . "/Domain/Workflow/" . $entity . "/Listener/WFGenerateVOListener.php");
            $this->assertTrue($this->checkNamespace($this->environment . "\\Domain\\Workflow\\$entity\\Listener", __DIR__ . "/../../../../src/" . $this->environment . "/Domain/Workflow/" . $entity . "/Listener/WFGenerateVOListener.php"));
            $this->assertTrue($this->checkClassName("WFGenerateVOListener", __DIR__ . "/../../../../src/" . $this->environment . "/Domain/Workflow/" . $entity . "/Listener/WFGenerateVOListener.php"));

            $this->assertFileExists(__DIR__ . "/../../../../src/" . $this->environment . "/Domain/Workflow/" . $entity . "/Listener/WFGetCurrency.php");
            $this->assertTrue($this->checkNamespace($this->environment . "\\Domain\\Workflow\\$entity\\Listener", __DIR__ . "/../../../../src/" . $this->environment . "/Domain/Workflow/" . $entity . "/Listener/WFGetCurrency.php"));
            $this->assertTrue($this->checkClassName("WFGetCurrency", __DIR__ . "/../../../../src/" . $this->environment . "/Domain/Workflow/" . $entity . "/Listener/WFGetCurrency.php"));

            $this->assertFileExists(__DIR__ . "/../../../../src/" . $this->environment . "/Domain/Workflow/" . $entity . "/Listener/WFPublishEvent.php");
            $this->assertTrue($this->checkNamespace($this->environment . "\\Domain\\Workflow\\$entity\\Listener", __DIR__ . "/../../../../src/" . $this->environment . "/Domain/Workflow/" . $entity . "/Listener/WFPublishEvent.php"));
            $this->assertTrue($this->checkClassName("WFPublishEvent", __DIR__ . "/../../../../src/" . $this->environment . "/Domain/Workflow/" . $entity . "/Listener/WFPublishEvent.php"));

            $this->assertFileExists(__DIR__ . "/../../../../src/" . $this->environment . "/Domain/Workflow/" . $entity . "/Listener/WFSaveEntity.php");
            $this->assertTrue($this->checkNamespace($this->environment . "\\Domain\\Workflow\\$entity\\Listener", __DIR__ . "/../../../../src/" . $this->environment . "/Domain/Workflow/" . $entity . "/Listener/WFSaveEntity.php"));
            $this->assertTrue($this->checkClassName("WFSaveEntity", __DIR__ . "/../../../../src/" . $this->environment . "/Domain/Workflow/" . $entity . "/Listener/WFSaveEntity.php"));
        }

        foreach ($this->valueObjects as $voName => $fields) {
            $this->assertFileExists(__DIR__ . "/../../../../src/" . $this->environment . "/Domain/ValueObject/" . $voName . ".php");
            $this->assertTrue($this->checkNamespace($this->environment . "\\Domain\\ValueObject", __DIR__ . "/../../../../src/" . $this->environment . "/Domain/ValueObject/" . $voName . ".php"));
            $this->assertTrue($this->checkClassName($voName, __DIR__ . "/../../../../src/" . $this->environment . "/Domain/ValueObject/" . $voName . ".php"));
        }

    }


}