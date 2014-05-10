<?php

namespace Telesto\Utils\Tests\Arrays\Transformation;

use Telesto\Utils\Arrays\Transformation\CreateAndOverwriteTransformer;

class CreateAndOverwriteTransformerTest extends \PHPUnit_Framework_TestCase
{
    public function testTransform()
    {
        $factoryResult = new \ArrayObject;
        
        $mockFactory = $this->getMock('Telesto\\Utils\\Arrays\\Factories\\Factory');
        $mockFactory
            ->expects($this->once())
            ->method('createArray')
            ->will($this->returnValue($factoryResult))
        ;
        
        $input = new \ArrayObject;
        
        $mockOverwriter = $this->getMock('Telesto\\Utils\\Arrays\\Overwriting\\Overwriter');
        $mockOverwriter
            ->expects($this->once())
            ->method('overwrite')
            ->with(
                $this->identicalTo($input),
                $this->identicalTo($factoryResult)
            )
        ;
        
        $transformer = new CreateAndOverwriteTransformer($mockFactory, $mockOverwriter);
        $output = $transformer->transform($input);
        $this->assertSame($factoryResult, $output);
    }
}
