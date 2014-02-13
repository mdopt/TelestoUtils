<?php

namespace Telesto\Utils\Tests;

use Telesto\Utils\TypeUtil;

class TypeUtilTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideObjectTypeExistsData
     */
    public function testObjectTypeExists($expectedValue, $typeName)
    {
        $this->assertSame($expectedValue, TypeUtil::ObjectTypeExists($typeName));
    }
    
    public function provideObjectTypeExistsData()
    {
        return array(
            array(
                true,
                __NAMESPACE__ . '\Types\SimpleObject',
            ),
            array(
                true,
                __NAMESPACE__ . '\Types\ServiceInterface',
            ),
            array(
                true,
                __NAMESPACE__ . '\Types\Service',
            ),
            array(
                false,
                __NAMESPACE__ . '\Types\NonExistingClassName'
            )
        );
    }
    
    /**
     * @dataProvider provideObjectTypeExistsExceptionData
     */
    public function testObjectTypeExistsExceptions(array $expectedException, $typeName)
    {
        $this->setExpectedException(
            $expectedException[0],
            $expectedException[1]
        );
        
        TypeUtil::objectTypeExists($typeName);
    }
    
    public function provideObjectTypeExistsExceptionData()
    {
        return array(
            array(
                array(
                    'InvalidArgumentException',
                    'Object type name must be a string, array given.'
                ),
                array()
            )
        );
    }
    
    /**
     * @dataProvider provideGetTypeData
     */
    public function testGetType($expectedValue, $input)
    {
        $this->assertSame($expectedValue, TypeUtil::getType($input));
    }
    
    public function provideGetTypeData()
    {
        return array(
            array(
                'string',
                'Some text.'
            ),
            array(
                'integer',
                120
            ),
            array(
                'array',
                array()
            ),
            array(
                'stdClass',
                new \stdClass
            )
        );
    }
}
