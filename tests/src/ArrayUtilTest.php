<?php

namespace Telesto\Utils\Tests;

use Telesto\Utils\ArrayUtil;

class ArrayUtilTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideGetElementByKeyPathData
     */
    public function testGetElementByKeyPath(
        $expectedValue,
        $input,
        $keyPath,
        array $options = array()
    )
    {
        $this->assertSame($expectedValue, ArrayUtil::getElementByKeyPath($input, $keyPath, $options));
    }
    
    public function provideGetElementByKeyPathData()
    {
        $exampleArray = $this->getExampleArray();
        $exampleArrayObject = new \ArrayObject($exampleArray);
        
        return array(
            array(
                null,
                array(),
                'nonExistingKey'
            ),
            array(
                null,
                array(
                    'x'         => null
                ),
                'x',
                array(
                    'throwOnNonExisting'    => true
                )
            ),
            array(
                null,
                new \ArrayObject(
                    array(
                        'x'         => null
                    )
                ),
                'x',
                array(
                    'throwOnNonExisting'    => true
                )
            ),
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
     * @dataProvider provideSetElementByKeyPathData
     */
    public function testSetElementByKeyPath(
        $expectedValue,
        $input,
        $keyPath,
        $element,
        array $options = array()
    )
    {
        ArrayUtil::setElementByKeyPath($input, $keyPath, $element, $options);
        $this->assertSame($expectedValue, $input);
    }
    
    public function provideSetElementByKeyPathData()
    {
        return array(
            array(
                array(
                    'x'     => 100
                ),
                array(),
                array('x'),
                100
            ),
            array(
                array(
                    'x'     => array(
                        'y' => 100
                    )
                ),
                array(
                    'x'     => 100
                ),
                array('x', 'y'),
                100
            ),
            array(
                array(
                    'x'     => array(
                        'y' => 100
                    )
                ),
                array(
                    'x'     => array(
                        'y' => 'this value will be replaced'
                    )
                ),
                array('x', 'y'),
                100
            ),
            array(
                array(
                    'user'  => array(
                        'id'        => 10,
                        'login'     => 'root'
                    )
                ),
                array(
                    'user'  => array(
                        'id'        => 10
                    )
                ),
                'user.login',
                'root'
            )
        );
    }
    
    public function testSetElementByKeyPathWithObjects()
    {
        $arrayObject = new \ArrayObject(
            array(
                'user'          => new \ArrayObject(
                    array(
                        'id'    => 10,
                        'login' => 'root'
                    )
                ),
                'colors'        => array(   // no new \ArrayObject here on purpose
                    'red',
                    'green',
                    'blue'
                )
            )
        );
        
        $expectedResult = new \ArrayObject(
            array(
                'user'          => new \ArrayObject(
                    array(
                        'id'        => 10,
                        'login'     => 'root',
                        'password'  => 'password123456'
                    )
                ),
                'colors'        => array(   // no new \ArrayObject here on purpose
                    'red',
                    'green',
                    'blue'
                )
            )
        );
        
        ArrayUtil::setElementByKeyPath($arrayObject, 'user.password', 'password123456');
        $this->assertEquals($expectedResult, $arrayObject);
        
        $expectedResult2 = clone $expectedResult;
        $expectedResult2['colors'][2] = new \ArrayObject(
            array(
                'azure'
            )
        );
        
        ArrayUtil::setElementByKeyPath($arrayObject, 'colors/2/0', 'azure', array('arrayPrototype'=> new \ArrayObject, 'keySeparator'=> '/'));
        $this->assertEquals($expectedResult2, $arrayObject);
    }
    
    /**
     * @dataProvider provideSetElementByKeyPathExceptionsData
     */
    public function testSetElementByKeyPathExceptions(
        array $expectedException,
        $input,
        $keyPath,
        $element,
        array $options = array()
    )
    {
        $this->setExpectedException(
            $expectedException[0],
            $expectedException[1]
        );
        
        ArrayUtil::setElementByKeyPath($input, $keyPath, $element, $options);
    }
    
    public function provideSetElementByKeyPathExceptionsData()
    {
        return array(
            array(
                array(
                    'InvalidArgumentException',
                    'Input must be an array or an instance of ArrayAccess, integer given.'
                ),
                1302,
                'database.host',
                'localhost'
            ),
            array(
                array(
                    'InvalidArgumentException',
                    'Option \'keySeparator\' must be a string, array given.'
                ),
                array(),
                'database.host',
                'localhost',
                array(
                    'keySeparator'      => array()
                )
            ),
            array(
                array(
                    'InvalidArgumentException',
                    'Option \'arrayPrototype\' must an be array or an instance of ArrayAccess, string given.'
                ),
                array(),
                'database.host',
                'localhost',
                array(
                    'arrayPrototype'    => 'invalidArrayPrototype'
                )
            ),
            array(
                array(
                    'RuntimeException',
                    'Collision at ["database","user"]: Element should not exist, be an array or an instance of ArrayAccess, string given.'
                ),
                array(
                    'database'          => array(
                        'user'          => 'root'
                    )
                ),
                'database.user.deeper_key',
                'localhost',
                array(
                    'throwOnCollision'  => true
                )
            )
        );
    }
    
    /**
     * @dataProvider provideUnsetElementByKeyPathData
     */
    public function testUnsetElementByKeyPath(
        $expectedValue,
        $input,
        $keyPath,
        array $options = array()
    )
    {
        ArrayUtil::unsetElementByKeyPath($input, $keyPath, $options);
        
        if (is_object($input)) {
            $this->assertEquals($expectedValue, $input);
        }
        else {
            $this->assertSame($expectedValue, $input);
        }
    }
    
    public function provideUnsetElementByKeyPathData()
    {
        return array(
            array(
                array(
                    'x'         => 100,
                ),
                array(
                    'x'         => 100,
                    'y'         => 200
                ),
                'y'
            ),
            array(
                new \ArrayObject(),
                new \ArrayObject(
                    array(
                        'x'     => 100
                    )
                ),
                'x'
            ),
            array(
                array(
                    'colors'        => array(
                        0           => 'red',
                        2           => 'blue'
                    )
                ),
                array(
                    'colors'        => array(
                        'red',
                        'green',
                        'blue'
                    )
                ),
                array('colors', 1)
            ),
            array(
                array(),
                array(),
                'nonExistingKey'
            ),
            array(
                new \ArrayObject(),
                new \ArrayObject(),
                'nonExistingKey'
            ),
            array(
                array(
                    'user'          => array(
                        'id'        => 10
                    )
                ),
                array(
                    'user'          => array(
                        'id'        => 10,
                        'favorite_movies'   => array(
                            'The Terminator',
                            'Pulp Fiction'
                        )
                    )
                ),
                'user/favorite_movies',
                array(
                    'keySeparator'  => '/'
                )
            ),
            array(
                array(),
                array(
                    'x'             => null,
                ),
                'x',
                array(
                    'throwOnNonExisting'    => true
                )
            )
        );
    }
    
    /**
     * @dataProvider provideUnsetElementByKeyPathExceptionsData
     */
    public function testUnsetElementByKeyPathExceptions(
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
        
        ArrayUtil::unsetElementByKeyPath($input, $keyPath, $options);
    }
    
    public function provideUnsetElementByKeyPathExceptionsData()
    {
        return array(
            array(
                array(
                    'RuntimeException',
                    'Element at ["x"] does not exist.'
                ),
                array(
                    'y'         => 10
                ),
                'x.y.z',
                array(
                    'throwOnNonExisting'    => true
                )
            ),
            array(
                array(
                    'RuntimeException',
                    'Element at ["database","host"] does not exist.'
                ),
                array(
                    'database'  => array()
                ),
                'database.host',
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
            ),
            array(
                array(
                    'RuntimeException',
                    'Collision at ["db","host"]: Element should not exist, be an array or an instance of ArrayAccess, string given.'
                ),
                $exampleArray,
                array(
                    'database.host'                 => 'db.host',
                    'database.user'                 => 'db.host.0'
                ),
                array(
                    'throwOnCollision'              => true
                )
            )
        );
    }
    
    /**
     * @dataProvider provideCopyByKeyPathMapData
     */
    public function testCopyByKeyPathMap(
        $expectedValue,
        $input,
        $output,
        array $keyPathMap,
        array $options = array()
    )
    {
        ArrayUtil::copyByKeyPathMap($input, $output, $keyPathMap, $options);
        
        if (is_object($output)) {
            $this->assertEquals($expectedValue, $output);
        }
        else {
            $this->assertSame($expectedValue, $output);
        }
    }
    
    public function provideCopyByKeyPathMapData()
    {
        return array(
            array(
                array(
                    'x'         => 10,
                    'colors'    => array(
                        'red',
                        'green'
                    )
                ),
                array(
                    10,
                    array(
                        array(
                            'red',
                            'green'
                        )
                    )
                ),
                array(
                    'x'             => 30 // will get overwritten
                ),
                array(
                    '0'             => 'x',
                    '1.0'           => 'colors'
                )
            ),
            array(
                new \ArrayObject(
                    array(
                        'name'      => 'John'
                    )
                ),
                new \ArrayObject(
                    array(
                        'John',
                        'Mike'
                    )
                ),
                new \ArrayObject(),
                array(
                    '0'             => 'name'
                )
            )
        );
    }
    
    /**
     * @dataProvider providerCopyByPathMapExceptionsData
     */
    public function testCopyByKeyPathMapExceptions(
        array $exceptionData,
        $input,
        $output,
        array $keyPathMap,
        array $options = array()
    )
    {
        $this->setExpectedException(
            $exceptionData[0],
            $exceptionData[1]
        );
        
        ArrayUtil::copyByKeyPathMap($input, $output, $keyPathMap, $options);
    }
    
    public function providerCopyByPathMapExceptionsData()
    {
        return array(
            array(
                array(
                    'InvalidArgumentException',
                    'Output must be an array or an instance of ArrayAccess, integer given.'
                ),
                array(),
                1203,
                array(
                    'x'         => 'y'
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
