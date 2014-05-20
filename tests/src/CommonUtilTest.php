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
}
