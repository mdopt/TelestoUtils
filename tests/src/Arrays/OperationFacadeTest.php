<?php

namespace Telesto\Utils\Tests\Arrays;

use Telesto\Utils\Arrays\OperationFacade;
use Telesto\Utils\Arrays\Transformation;
use Telesto\Utils\Arrays\Overwriting;

class OperationFacadeTest extends \PHPUnit_Framework_TestCase
{
    public function testRegisterOverwriterCreator()
    {
        $test = $this;
        $mockOverwriter = $this->getMock('Telesto\Utils\Arrays\Overwriting\Overwriter');
        $expectedArguments = array(10, 20);
        
        $creator = function ($arguments) use ($test, $mockOverwriter, $expectedArguments) {
            $test->assertSame($expectedArguments, func_get_args());
            
            return $mockOverwriter;
        };
        
        OperationFacade::registerOverwriterCreator('test.operation1', $creator);
        $this->assertSame($mockOverwriter, OperationFacade::createOverwriter('test.operation1', 10, 20));
    }
    
    /**
     * @dataProvider provideRegisterOverwriterCreatorExceptionsData
     */
    public function testRegisterOverwriterCreatorExceptions(
        array $expectedException,
        $name,
        $creator
    )
    {
        $this->setExpectedException($expectedException[0], $expectedException[1]);
        OperationFacade::registerOverwriterCreator($name, $creator);
    }
    
    public function provideRegisterOverwriterCreatorExceptionsData()
    {
        return array(
            array(
                array(
                    'LogicException',
                    'Overwriter creator \'copy.allKeys\' is already registered.'
                ),
                'copy.allKeys',
                function () {}
            ),
            array(
                array(
                    'InvalidArgumentException',
                    ''
                ),
                'test.someNewOverwriter',
                1024
            )
        );
    }
    
    public function testRegisterTransformerCreator()
    {
        $test = $this;
        $mockTransformer = $this->getMock('Telesto\Utils\Arrays\Transformation\Transformer');
        $expectedArguments = array('x', 'y');
        
        $creator = function ($arguments) use ($test, $mockTransformer, $expectedArguments) {
            $test->assertSame($expectedArguments, func_get_args());
            
            return $mockTransformer;
        };
        
        OperationFacade::registerOverwriterCreator('test.operation2', $creator);
        $this->assertSame($mockTransformer, OperationFacade::createOverwriter('test.operation2', 'x', 'y'));
    }
    
    /**
     * @dataProvider provideRegisterOverwriterCreatorExceptionsData
     */
    public function testRegisterTransformerCreatorExceptions(
        array $expectedException,
        $name,
        $creator
    )
    {
        $this->setExpectedException($expectedException[0], $expectedException[1]);
        OperationFacade::registerOverwriterCreator($name, $creator);
    }
    
    public function provideRegisterTransformerCreatorExceptionsData()
    {
        return array(
            array(
                array(
                    'LogicException',
                    'Transformer creator \'copy.allKeys\' is already registered.'
                ),
                'copy.allKeys',
                function () {}
            ),
            array(
                array(
                    'InvalidArgumentException',
                    ''
                ),
                'someNewOverwriter',
                1024
            )
        );
    }
    
    public function testCreateOverwriter()
    {
        $this->assertEquals(
            new Overwriting\Copy\AllKeysOverwriter,
            OperationFacade::createOverwriter('copy.allKeys')
        );
        
        $this->assertEquals(
            new Overwriting\Copy\KeyPathMapOverwriter(
                array(
                    array('x', 'y')
                )
            ),
            OperationFacade::createOverwriter(
                'copy.byKeyPathMap',
                array(
                    array('x', 'y')
                )
            )
        );
    }
    
    public function testCreateTransformer()
    {
        $this->assertEquals(
            new Transformation\CreateAndOverwriteTransformer(
                new Overwriting\Copy\KeyPathMapOverwriter(
                    array(
                        array('x', 'y')
                    )
                )
            ),
            OperationFacade::createTransformer(
                'copy.byKeyPathMap',
                array(
                    array('x', 'y')
                )
            )
        );
    }
    
    public function testOverwrite()
    {
        $input = new \ArrayObject;
        $output = new \ArrayObject;
        
        $mockOverwriter = $this->getMock('Telesto\Utils\Arrays\Overwriting\Overwriter');
        $mockOverwriter->expects($this->once())
            ->method('overwrite')
            ->with(
                $this->identicalTo($input),
                $this->identicalTo($output)
            )
        ;
        
        $test = $this;
        $expectedArguments = array(10, 20);
        
        $creator = function () use ($mockOverwriter, $test, $expectedArguments) {
            $test->assertSame($expectedArguments, func_get_args());
            return $mockOverwriter;
        };
        
        OperationFacade::registerOverwriterCreator('test.overwrite.operation3', $creator);
        OperationFacade::overwrite($input, $output, 'test.overwrite.operation3', 10, 20);
    }
    
    public function testTransform()
    {
        $input = new \ArrayObject;
        $output = new \ArrayObject;
        
        $mockTransformer = $this->getMock('Telesto\Utils\Arrays\Transformation\Transformer');
        $mockTransformer->expects($this->once())
            ->method('transform')
            ->with(
                $this->identicalTo($input)
            )
            ->will(
                $this->returnValue($output)
            )
        ;
        
        $test = $this;
        $expectedArguments = array(100, 200);
        
        $creator = function () use ($mockTransformer, $test, $expectedArguments) {
            $test->assertSame($expectedArguments, func_get_args());
            return $mockTransformer;
        };
        
        OperationFacade::registerTransformerCreator('test.transform.operation4', $creator);
        $this->assertSame($output, OperationFacade::transform($input, 'test.transform.operation4', 100, 200));
    }
}
