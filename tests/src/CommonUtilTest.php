<?php

namespace Telesto\Utils\Tests;

use Telesto\Utils\CommonUtil;

class CommonUtilTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideVarToStringData
     */
    public function testVarToString($expectedValue, $var)
    {
        $this->assertSame($expectedValue, CommonUtil::varToString($var));
    }
    
    public function provideVarToStringData()
    {
        return array(
            array(
                '3123',
                3123
            ),
            array(
                '3.14',
                3.14
            ),
            array(
                'John',
                'John'
            ),
            array(
                'true',
                true
            ),
            array(
                'false',
                false
            ),
            array(
                'null',
                null
            ),
            array(
                'Object(stdClass)',
                new \stdClass
            ),
            array(
                'Resource(stream)',
                fopen('php://memory', 'r') // any type of stream will do in this test
            ),
            array(
                'Array(x => 10, y => 20)',
                array('x'=> 10, 'y'=> 20)
            )
        );
    }
    
    /**
     * @dataProvider provideCreateObjectData
     */
    public function testCreateObject($expectedResult, $className, $arguments)
    {
        $this->assertEquals($expectedResult, CommonUtil::createObject($className, $arguments));
    }
    
    public function provideCreateObjectData()
    {
        return array(
            array(
                new \DateTime('2014-04-20 14:12:32'),
                'DateTime',
                array(
                    '2014-04-20 14:12:32'
                )
            ),
            array(
                new \InvalidArgumentException('Message', 10),
                'InvalidArgumentException',
                array(
                    'Message',
                    10
                )
            )
        );
    }
    
    /**
     * @dataProvider provideUnserializeData
     */
    public function testUnserialize($expectedResult, $serialized)
    {
        if (is_object($expectedResult)) {
            $this->assertEquals($expectedResult, CommonUtil::unserialize($serialized));
        }
        else {
            $this->assertSame($expectedResult, CommonUtil::unserialize($serialized));
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
        
        CommonUtil::unserialize($serialized, $hideNotice);
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
