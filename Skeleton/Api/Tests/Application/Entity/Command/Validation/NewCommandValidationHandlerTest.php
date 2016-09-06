<?php
declare(strict_types = 1);

namespace Tests\Application\Country\Application\Country\Command\Validation;

require_once __DIR__ . '/../../../../TraitPrivateMethod.php.twig';

use Sfynx\DddBundle\Layer\Application\Generalisation\Validation\ValidationHandler\ValidationErrorHandler;
use Sfynx\DddBundle\Layer\Application\Generalisation\Validation\ValidationHandler\ValidatorInterface;
use DemoCountry\Application\Country\Command\NewCommand;
use DemoCountry\Application\Country\Command\Validation\ValidationHandler\NewCommandValidationHandler;
use \Phake;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Tests\TraitPrivateMethod;

class NewCommandValidationHandlerTest extends \PHPUnit_Framework_TestCase
{
    use TraitPrivateMethod;

    protected $validationHandler;
    protected $validator;
    protected $command;
    protected $message;
    protected $transaction;
    protected $jsonRequest;

    public function setUp()
    {
        $this->libelle="FRANCe";
        $this->indicatif="033";
        $this->iso="FR";
        $this->selectable=true;
        $this->reference="001";

        $this->command = Phake::partialMock(NewCommand::class, $this->libelle,$this->indicatif,$this->iso,$this->selectable,$this->reference);
        //Symfony\Component\Validator\Validator
        $this->validator = Phake::mock(ValidatorInterface::class);
        $this->validationHandler = new NewCommandValidationHandler($this->validator);
    }

    public function testConstraints()
    {
        //call initConstraints()
        $this->callPrivateMethod(
            NewCommandValidationHandler::class,
            'initConstraints',
            $this->validationHandler
        );

        //call getConstraints()
        $constraints = $this->callPrivateMethod(
            NewCommandValidationHandler::class,
            'getConstraints',
            $this->validationHandler
        );
        $keysToCheck = [
            'libelle',
            'indicatif',
            'iso',
            'selectable',
            'reference',
        ];
        //verify that all keys exist
        foreach($keysToCheck as $key) {
            $this->assertEquals(true, array_key_exists($key, $constraints));
        }

        //verify that all values are Constraint or array of Constraint
        foreach($constraints as $constraint) {
            $this->assertEquals(true, (($constraint instanceof Constraint) or is_array($constraint)));
            if (is_array($constraint)) {
                foreach($constraint as $subCons) {
                    $this->assertEquals(true, ($subCons instanceof Constraint));
                }
            }
        }
    }

    public function testProcessWithoutError()
    {
        //override validateValue method from validator mock to return an empty ArrayObject (ArrayIteratorAggregate)
        Phake::when($this->validator)->validateValue(Phake::anyParameters())->thenReturn(new \ArrayObject());

        $result = $this->validationHandler->process($this->command);

        //verify that initConstraints() has been called by checking $constraints is not empty and is array

        //call getConstraints()
        $constraints = $this->callPrivateMethod(
            NewCommandValidationHandler::class,
            'getConstraints',
            $this->validationHandler
        );

        $this->assertEquals(true, !empty($constraints));
        $this->assertEquals(true, is_array($constraints));

        //verify that validateValue from mock is called
        Phake::verify($this->validator, Phake::times(1))->validateValue(Phake::anyParameters());

        //verify that $result is true because there are no errors
        $this->assertEquals(true, $result);
    }

    /**
     * @expectedException \Exception
     */
    public function testProcessException()
    {
        //override validateValue method from validator mock to return an array of errors
        $error = Phake::mock(ConstraintViolationInterface::class);
        Phake::when($this->validator)->validateValue(Phake::anyParameters())->thenReturn(
            new \ArrayObject(
                [
                    $error
                ]
            )
        );

        //call process to throw Exception
        $this->validationHandler->process($this->command);

    }
}
