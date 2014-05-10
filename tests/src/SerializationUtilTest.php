<?php

namespace Telesto\Utils\Tests;

use Telesto\Utils\SerializationUtil as Util;

class UtilTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideUnserializeData
     */
    public function testUnserialize($expectedResult, $serialized)
    {
        if (is_object($expectedResult)) {
            $this->assertEquals($expectedResult, Util::unserialize($serialized));
        }
        else {
            $this->assertSame($expectedResult, Util::unserialize($serialized));
        }
    }
    
    public function provideUnserializeData()
    {
        return array(
            array(
                array(1, 2),
                serialize(array(1, 2))
            ),
            array(
                (object) array('x'=> 10, 'y'=> 20),
                serialize((object) array('x'=> 10, 'y'=> 20))
            ),
            array(
                false,
                serialize(false)
            )
        );
    }
    
    /**
     * @dataProvider provideUnserializeExceptionsData
     */
    public function testUnserializeExceptions(array $expectedException, $serialized, $hideNotice = false)
    {
        $this->setExpectedException($expectedException[0], $expectedException[1]);
        
        Util::unserialize($serialized, $hideNotice);
    }
    
    public function provideUnserializeExceptionsData()
    {
        return array(
            array(
                array(
                    'InvalidArgumentException',
                    'Serialized value must be a string, NULL given.'
                ),
                null
            ),
            array(
                array(
                    'InvalidArgumentException',
                    'Serialized value must be a string, stdClass given.'
                ),
                new \stdClass
            ),
            array(
                array(
                    'UnexpectedValueException',
                    'Invalid value after unserialization.'
                ),
                'invalid-string',
                true
            ),
            array(
                array(
                    'PHPUnit_Framework_Error_Notice',
                    ''
                ),
                'invalid-string',
                false
            )
        );
    }
}
