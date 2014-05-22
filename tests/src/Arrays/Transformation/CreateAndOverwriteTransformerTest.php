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
        $options = array('setting1'=> 1, 'setting2'=> 2);
        
        $mockOverwriter = $this->getMock('Telesto\Utils\Arrays\Overwriting\Overwriter');
        $mockOverwriter
            ->expects($this->once())
            ->method('overwrite')
            ->with(
                $this->identicalTo($input),
                $this->identicalTo($expectedOutput),
                $this->identicalTo($options)
            )
        ;
        
        $transformer = new CreateAndOverwriteTransformer(
            $mockOverwriter,
            array(
                'arrayPrototype'    => $expectedOutput
            )
        );
        
        $output = $transformer->transform($input, $options);
        $this->assertSame($expectedOutput, $output);
    }
    
    public function testTransformWithArrayPrototypePerOperation()
    {
        $mockOverwriter = $this->getMock('Telesto\Utils\Arrays\Overwriting\Overwriter');
        $mockOverwriter
            ->expects($this->any())
            ->method('overwrite')
        ;
        
        $transformer = new CreateAndOverwriteTransformer($mockOverwriter);
        
        $output = $transformer->transform(
            array(),
            array(
                'arrayPrototype'    => array(1, 2, 3, 4)
            )
        );
        
        $this->assertSame(array(1, 2, 3, 4), $output);
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
