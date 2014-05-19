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
     * @dataProvider provideCreateObjectData
     */
    public function testCreateObject($expectedResult, $className, $arguments)
    {
        $this->assertEquals($expectedResult, TypeUtil::createObject($className, $arguments));
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
    
    /**
     * @dataProvider provideIsTypeData
     */
    public function testIsType($expectedValue, $input, $type)
    {
        $this->assertSame($expectedValue, TypeUtil::isType($input, $type));
    }
    
    public function provideIsTypeData()
    {
        return array(
            array(
                true,
                10,
                'int'
            ),
            array(
                true,
                null,
                'int|null'
            ),
            array(
                false,
                null,
                'string'
            ),
            array(
                false,
                null,
                'string|int|array'
            ),
            array(
                true,
                'John Doe',
                'string|int|array'
            ),
            array(
                true,
                array(10, 20),
                'array'
            ),
            array(
                false,
                new \stdClass,
                'array'
            ),
            array(
                true,
                new \stdClass,
                'stdClass'
            )
        );
    }
    
    /**
     * @dataProvider provideRequireTypeData
     */
    public function testRequireType(
        $expectedException,
        $input,
        $type,
        $argumentName,
        $options = array()
    ) {
        if ($expectedException) {
            $this->setExpectedException($expectedException[0], $expectedException[1], $expectedException[2]);
        }
        
        TypeUtil::requireType($input, $type, $argumentName, $options);
    }
    
    public function provideRequireTypeData()
    {
        return array(
            array(
                array(
                    'InvalidArgumentException',
                    'Invalid argument type for $radius: int|null expected, got stdClass.',
                    0
                ),
                new \stdClass,
                'int|null',
                '$radius'
            ),
            array(
                array(
                    'RuntimeException',
                    '$radius has invalid type stdClass (expected int|null).',
                    10
                ),
                new \stdClass,
                'int|null',
                '$radius',
                array(
                    'exceptionClass'    => 'RuntimeException', // RuntimeException doesn't make sense in real situations, but it's just for tests
                    'exceptionCode'     => 10,
                    'messageFormat'     => '%1$s has invalid type %3$s (expected %2$s).'
                )
            ),
            array(
                null,
                104523,
                'int|null',
                '$radius'
            )
        );
    }
}
