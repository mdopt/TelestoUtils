<?php

namespace Telesto\Utils\Tests;

use Telesto\Utils\ArrayUtil;

class ArrayUtilTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideGetElementByKeysData
     */
    public function testGetElementByKeys($expectedValue, $input, array $keys, $defaultValue = null)
    {
        $this->assertSame($expectedValue, ArrayUtil::getElementByKeys($input, $keys, $defaultValue));
    }
    
    public function provideGetElementByKeysData()
    {
        $exampleArray = $this->getExampleArray();
        $exampleArrayObject = new \ArrayObject($exampleArray);
        
        return array(
            array(
                array(
                    'host'      => 'localhost',
                    'user'      => 'root',
                    'password'  => 'XXXX'
                ),
                $exampleArray,
                array(
                    'database'
                )
            ),
            array(
                'localhost',
                $exampleArray,
                array(
                    'database', 'host'
                )
            ),
            array(
                null,
                $exampleArray,
                array(
                    'database', 'host', 'non_existing_key'
                )
            ),
            array(
                'defaultValue',
                $exampleArray,
                array(
                    'database', 'host', 'non_existing_key'
                ),
                'defaultValue'
            ),
            array(
                'localhost',
                $exampleArrayObject,
                array(
                    'database', 'host'
                )
            )
        );
    }
    
    /**
     * @dataProvider provideGetElementByKeysExceptionsData
     */
    public function testGetElementByKeysExceptions(
        array $expectedException,
        $input,
        array $keys,
        $defaultValue = null,
        $throwOnNonExisting = false
    )
    {
        $this->setExpectedException(
            $expectedException[0],
            $expectedException[1]
        );
        
        ArrayUtil::getElementByKeys($input, $keys, $defaultValue, $throwOnNonExisting);
    }
    
    public function provideGetElementByKeysExceptionsData()
    {
        $exampleArray = $this->getExampleArray();
        
        return array(
            array(
                array(
                    'InvalidArgumentException',
                    'Input must be an array or instance of ArrayAccess.'
                ),
                4,
                array('database', 'host')
            ),
            array(
                array(
                    'InvalidArgumentException',
                    'Input must be an array or instance of ArrayAccess.'
                ),
                new \stdClass,
                array('database', 'host')
            ),
            array(
                array(
                    'InvalidArgumentException',
                    'At least one key must be given.'
                ),
                $exampleArray,
                array()
            ),
            array(
                array(
                    'InvalidArgumentException',
                    'Array of keys must contain only strings and integers, NULL given.'
                ),
                $exampleArray,
                array('database', null, 'host')
            ),
            array(
                array(
                    'InvalidArgumentException',
                    'Array of keys must contain only strings and integers, double given.'
                ),
                $exampleArray,
                array('database', 'host', 3.424)
            ),
            array(
                array(
                    'RuntimeException',
                    'Element at ["database","host","non_existing_key"] does not exist.'
                ),
                $exampleArray,
                array('database', 'host', 'non_existing_key'),
                null,
                true
            )
        );
    }
    
    /**
     * @dataProvider provideGetElementKeyPathData
     */
    public function testGetElementByKeyPath($expectedValue, $input, $keyPath, $defaultValue = null, $keySeparator = '.')
    {
        $this->assertSame($expectedValue, ArrayUtil::getElementByKeyPath($input, $keyPath, $defaultValue, false, $keySeparator));
    }
    
    public function provideGetElementKeyPathData()
    {
        $exampleArray = $this->getExampleArray();
        
        return array(
            array(
                array(
                    'host'      => 'localhost',
                    'user'      => 'root',
                    'password'  => 'XXXX'
                ),
                $exampleArray,
                'database'
            ),
            array(
                'root',
                $exampleArray,
                'database.user'
            ),
            array(
                'root',
                $exampleArray,
                'database/user',
                null,
                '/'
            )
        );
    }
    
    /**
     * @dataProvider provideGetElementByKeyPathExceptionsData
     */
    public function testGetElementByKeyPathExceptions(
        array $expectedException,
        $input,
        $keyPath,
        $defaultValue = null,
        $throwOnNonExisting = false,
        $keySeparator = '.'
    )
    {
        $this->setExpectedException(
            $expectedException[0],
            $expectedException[1]
        );
        
        ArrayUtil::getElementByKeyPath($input, $keyPath, $defaultValue, $throwOnNonExisting, $keySeparator);
    }
    
    public function provideGetElementByKeyPathExceptionsData()
    {
        $exampleArray = $this->getExampleArray();
        
        return array(
            array(
                array(
                    'InvalidArgumentException',
                    'Key path must be a string, array given.'
                ),
                $exampleArray,
                array()
            ),
            array(
                array(
                    'InvalidArgumentException',
                    'Key separator must be a string, NULL given.'
                ),
                $exampleArray,
                'database.host',
                null,
                false,
                null
            ),
            array(
                array(
                    'RuntimeException',
                    'Element at database/host/non_existing_key does not exist.'
                ),
                $exampleArray,
                'database/host/non_existing_key',
                null,
                true,
                '/'
            )
        );
    }
    
    /**
     * @dataProvider provideTransformByPathKeyMapData
     */
    public function testTransformByPathKeyMap(
        $expectedValue,
        $input,
        array $keyPathMap,
        $defaultValue = null,
        $throwOnNonExisting = false,
        $keySeparator = '.'
    )
    {
        $this->assertSame(
            $expectedValue,
            ArrayUtil::transformByPathKeyMap(
                $input,
                $keyPathMap,
                $defaultValue,
                $throwOnNonExisting,
                $keySeparator
            )
        );
    }
    
    public function provideTransformByPathKeyMapData()
    {
        $exampleArray = $this->getExampleArray();
        
        return array(
            array(
                array(
                    'database_host'     => 'localhost',
                    'database_user'     => 'root',
                    'new_value'         => null
                ),
                $exampleArray,
                array(
                    'database.host'     => 'database_host',
                    'database.user'     => 'database_user',
                    'non_existing_key'  => 'new_value'
                )
            ),
            array(
                array(
                    'db'                => array(
                        'data'          => array(
                            'host'      => 'localhost',
                            'user'      => 'root'
                        )
                    ),
                    'extra'             => array(
                        'new_value'     => 4123
                    )
                ),
                $exampleArray,
                array(
                    'database/host'     => 'db/data/host',
                    'database/user'     => 'db/data/user',
                    'non_existing_key'  => 'extra/new_value'
                ),
                4123,
                false,
                '/'
            )
        );
    }
    
    public function testTransformByPathKeyMapWithObjects()
    {
        $exampleArray = $this->getExampleArray();
        $exampleArrayObject = new \ArrayObject($exampleArray);
        
        $expectedResult = new \ArrayObject(
            array(
                'db'                    => new \ArrayObject(
                    array(
                        'pass'          => 'XXXX',
                        'host'          => 'localhost'
                    )
                )
            )
        );
        
        $result = ArrayUtil::transformByPathKeyMap(
            $exampleArrayObject,
            array(
                'database.password'     => 'db.pass',
                'database.host'         => 'db.host'
            ),
            null,
            false,
            '.',
            $arrayPrototype = new \ArrayObject
        );
        
        $this->assertEquals($expectedResult, $result);
        $this->assertNotSame($expectedResult, $result);
    }
    
    /**
     * @dataProvider provideTransformByPathKeyMapExceptionsData
     */
    public function testTransformArrayByPathKeyMapExceptions(
        array $expectedException,
        $input,
        array $keyPathMap,
        $defaultValue = null,
        $throwOnNonExisting = false,
        $keySeparator = '.',
        $arrayPrototype = array()
    )
    {
        $this->setExpectedException(
            $expectedException[0],
            $expectedException[1]
        );
        
        ArrayUtil::transformByPathKeyMap(
            $input,
            $keyPathMap,
            $defaultValue,
            $throwOnNonExisting,
            $keySeparator,
            $arrayPrototype
        );
    }
    
    public function provideTransformByPathKeyMapExceptionsData()
    {
        $exampleArray = $this->getExampleArray();
        
        return array(
            array(
                array(
                    'InvalidArgumentException',
                    'Array prototype must an be array or instance of ArrayAccess.'
                ),
                $exampleArray,
                array(
                    'database.host' => 'db.host'
                ),
                null,
                false,
                '.',
                333
            ),
            array(
                array(
                    'InvalidArgumentException',
                    'Path key map must have at least one element.'
                ),
                $exampleArray,
                array()
            ),
            array(
                array(
                    'InvalidArgumentException',
                    'Key path map must be a string=> string array, invalid key \'3\'.'
                ),
                $exampleArray,
                array(
                    'database.host' => 'db.host',
                    3               => 'extra.new_field'
                )
            ),
            array(
                array(
                    'InvalidArgumentException',
                    'Key path map must be a string=> string array, invalid type array at key \'database.user\'.'
                ),
                $exampleArray,
                array(
                    'database.host' => 'db.host',
                    'database.user' => array()
                )
            ),
            array(
                array(
                    'RuntimeException',
                    ''
                ),
                $exampleArray,
                array(
                    'database.host'                 => 'db.host',
                    'database.non_existing_field'   => 'db.user'
                ),
                null,
                true
            )
        );
    }
    
    protected function getExampleArray()
    {
        return array(
            'database'      => array(
                'host'      => 'localhost',
                'user'      => 'root',
                'password'  => 'XXXX'
            )
        );
    }
}
