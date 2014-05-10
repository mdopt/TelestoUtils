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
        
        if (is_object($prototype)) {
            $this->assertEquals($prototype, $factory->createArray());
        }
        else {
            $this->assertSame($prototype, $factory->createArray());
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
