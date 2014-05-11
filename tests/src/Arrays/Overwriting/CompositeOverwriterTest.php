<?php

namespace Telesto\Utils\Tests\Arrays\Overwriting;

use Telesto\Utils\Arrays\Overwriting\CompositeOverwriter;

class CompositeOverwriterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideConstructorExceptionsData
     */
    public function testConstructorExceptions(
        $expectedException,
        array $overwriters
    )
    {
        $this->setExpectedException(
            $expectedException[0],
            $expectedException[1]
        );
        
        new CompositeOverwriter($overwriters);
    }
    
    public function provideConstructorExceptionsData()
    {
        return array(
            array(
                array(
                    'LengthException',
                    ''
                ),
                array()
            ),
            array(
                array(
                    'InvalidArgumentException',
                    'Argument $overwriters must be an array of Telesto\Utils\Arrays\Overwriting\Overwriter instances, invalid type string at index 1.'
                ),
                array(
                    $this->createOverwriterMock(),
                    'invalidValue'
                )
            )
        );
    }
    
    public function testOverwrite()
    {
        $input = new \ArrayObject;
        $output = new \ArrayObject;
        
        $mockOverwriters = array(
            $this->createOverwriterMock(),
            $this->createOverwriterMock()
        );
        
        foreach ($mockOverwriters as $mockOverwriter) {
            $mockOverwriter
                ->expects($this->once())
                ->method('overwrite')
                ->with(
                    $this->identicalTo($input),
                    $this->identicalTo($output)
                )
            ;
        }
        
        $overwriter = new CompositeOverwriter($mockOverwriters);
        $overwriter->overwrite($input, $output);
    }
    
    protected function createOverwriterMock()
    {
        return $this->getMock('Telesto\\Utils\\Arrays\\Overwriting\\Overwriter');
    }
}
