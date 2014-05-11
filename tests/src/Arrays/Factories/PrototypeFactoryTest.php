<?php

namespace Telesto\Utils\Tests\Arrays\Factories;

use Telesto\Utils\Arrays\Factories\PrototypeFactory;

class PrototypeFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideCreateArrayData
     */
    public function testCreateArray($prototype)
    {
        $factory = new PrototypeFactory($prototype);
        $newArray = $factory->createArray();
        
        if (is_object($prototype)) {
            $this->assertNotSame($prototype, $newArray);
            $this->assertEquals($prototype, $newArray);
        }
        else {
            $this->assertSame($prototype, $newArray);
        }
    }
    
    public function provideCreateArrayData()
    {
        return array(
            array(
                array(
                    'x'     => 10,
                    'y'     => 20
                )
            ),
            array(
                new \ArrayObject(
                    array(
                        'login' => 'root',
                        'pass'  => 'secret'
                    )
                )
            )
        );
    }
    
    public function testCreateArrayException()
    {
        $this->setExpectedException('InvalidArgumentException');
        
        $factory = new PrototypeFactory(1);
    }
}
