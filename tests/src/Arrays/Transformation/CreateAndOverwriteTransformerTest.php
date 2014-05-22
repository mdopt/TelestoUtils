<?php

namespace Telesto\Utils\Tests\Arrays\Transformation;

use Telesto\Utils\Arrays\Transformation\CreateAndOverwriteTransformer;

class CreateAndOverwriteTransformerTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructorArrayPrototypeException()
    {
        $this->setExpectedException('InvalidArgumentException');
        
        new CreateAndOverwriteTransformer(
            $this->getMock('Telesto\Utils\Arrays\Overwriting\Overwriter'),
            array(
                'arrayPrototype'    => 100
            )
        );
    }

    public function testTransform()
    {
        $input = new \ArrayObject;
        $expectedOutput = array('x' => 10);
        
        $mockOverwriter = $this->getMock('Telesto\Utils\Arrays\Overwriting\Overwriter');
        $mockOverwriter
            ->expects($this->once())
            ->method('overwrite')
            ->with(
                $this->identicalTo($input),
                $this->identicalTo($expectedOutput)
            )
        ;
        
        $transformer = new CreateAndOverwriteTransformer(
            $mockOverwriter,
            array(
                'arrayPrototype'    => $expectedOutput
            )
        );
        
        $output = $transformer->transform($input);
        $this->assertSame($expectedOutput, $output);
    }
    
    public function testTransformException()
    {
        $transformer = new CreateAndOverwriteTransformer(
            $this->getMock('Telesto\Utils\Arrays\Overwriting\Overwriter')
        );
        
        $this->setExpectedException('InvalidArgumentException');
        
        $transformer->transform(12);
    }
    
    public function testTransformArrayPrototypeException()
    {
        $transformer =new CreateAndOverwriteTransformer(
            $this->getMock('Telesto\Utils\Arrays\Overwriting\Overwriter')
        );
        
        $this->setExpectedException('InvalidArgumentException');
        
        $transformer->transform(array(), array('arrayPrototype' => 100));
    }
}
