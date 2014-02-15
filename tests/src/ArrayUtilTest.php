<?php

namespace Telesto\Utils\Tests;

use Telesto\Utils\ArrayUtil;

class ArrayUtilTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideGetElementByKeyPathData
     */
    public function testGetElementByKeyPath($expectedValue, $input, $keyPath, array $options = array())
    {
        $this->assertSame($expectedValue, ArrayUtil::getElementByKeyPath($input, $keyPath, $options));
    }
    
    public function provideGetElementByKeyPathData()
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
                array(
                    'default'   => 'defaultValue'
                )
            ),
            array(
                'localhost',
                $exampleArrayObject,
                array(
                    'database', 'host'
                )
            ),
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
                array(
                    'keySeparator'  => '/'
                )
            )
        );
    }
    
    /**
     * @dataProvider provideGetElementByKeysPathExceptionsData
     */
    public function testGetElementByKeyPathExceptions(
        array $expectedException,
        $input,
        $keyPath,
        array $options = array()
    )
    {
        $this->setExpectedException(
            $expectedException[0],
            $expectedException[1]
        );
        
        ArrayUtil::getElementByKeyPath($input, $keyPath, $options);
    }
    
    public function provideGetElementByKeysPathExceptionsData()
    {
        $exampleArray = $this->getExampleArray();
        
        return array(
            array(
                array(
                    'InvalidArgumentException',
                    'Input must be an array or an instance of ArrayAccess, integer given.'
                ),
                4,
                array('database', 'host')
            ),
            array(
                array(
                    'InvalidArgumentException',
                    'Input must be an array or an instance of ArrayAccess, stdClass given.'
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
                    'Array of keys must contain only strings and integers, NULL given at index 1.'
                ),
                $exampleArray,
                array('database', null, 'host')
            ),
            array(
                array(
                    'InvalidArgumentException',
                    'Array of keys must contain only strings and integers, double given at index 2.'
                ),
                $exampleArray,
                array('database', 'host', 3.424)
            ),
            array(
                array(
                    'InvalidArgumentException',
                    'Key path must be a string or an array, NULL given.'
                ),
                $exampleArray,
                null
            ),
            array(
                array(
                    'InvalidArgumentException',
                    'Option \'keySeparator\' must be a string, integer given.'
                ),
                $exampleArray,
                'database.host',
                array(
                    'keySeparator'  => 3
                )
            ),
            array(
                array(
                    'RuntimeException',
                    'Element at ["database","non_existing_key"] does not exist.'
                ),
                $exampleArray,
                array('database', 'non_existing_key', 'non_existing_key2'),
                array(
                    'throwOnNonExisting'    => true
                )
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
        array $options = array()
    )
    {
        $this->assertSame(
            $expectedValue,
            ArrayUtil::transformByPathKeyMap(
                $input,
                $keyPathMap,
                $options
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
                array(
                    'default'           => 4123,
                    'keySeparator'      => '/'
                )
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
            array(
                'arrayPrototype'        => new \ArrayObject
            )
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
        array $options = array()
    )
    {
        $this->setExpectedException(
            $expectedException[0],
            $expectedException[1]
        );
        
        ArrayUtil::transformByPathKeyMap(
            $input,
            $keyPathMap,
            $options
        );
    }
    
    public function provideTransformByPathKeyMapExceptionsData()
    {
        $exampleArray = $this->getExampleArray();
        
        return array(
            array(
                array(
                    'InvalidArgumentException',
                    'Option \'arrayPrototype\' must an be array or an instance of ArrayAccess, integer given.'
                ),
                $exampleArray,
                array(
                    'database.host'     => 'db.host'
                ),
                array(
                    'arrayPrototype'    => 333
                )
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
                    'Key path map must be a string=> string array, invalid type array at index \'database.user\'.'
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
                array(
                    'throwOnNonExisting'            => true
                )
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
