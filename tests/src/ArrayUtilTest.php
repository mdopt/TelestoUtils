<?php

namespace Telesto\Utils\Tests;

use Telesto\Utils\ArrayUtil;
use Telesto\Utils\Arrays\ReturnMode;

class ArrayUtilTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideGetKeysData
     */
    public function testGetKeys($expectedResult, $input)
    {
        $this->assertSame($expectedResult, ArrayUtil::getKeys($input));
    }
    
    public function provideGetKeysData()
    {
        return array(
            array(
                array(),
                array()
            ),
            array(
                array('x', 'y'),
                array(
                    'x'     => 10,
                    'y'     => 20
                )
            ),
            array(
                array(0, 'x', 'y'),
                new \ArrayObject(
                    array(
                        100,
                        'x'     => 10,
                        'y'     => 20
                    )
                )
            )
        );
    }
    
    /**
     * @dataProvider provideGetKeyExceptionsData
     */
    public function testGetKeyExceptions($expectedException, $input, $options = array())
    {
        $this->setExpectedException($expectedException[0], $expectedException[1]);
        
        ArrayUtil::getKeys($input, $options);
    }
    
    public function provideGetKeyExceptionsData()
    {
        return array(
            array(
                array(
                    'InvalidArgumentException',
                    ''
                ),
                102
            ),
            array(
                array(
                    'InvalidArgumentException',
                    ''
                ),
                102,
                array(
                    'omitValidation'    => false
                )
            ),
            array(
                array(
                    'PHPUnit_Framework_Error',
                    ''
                ),
                102,
                array(
                    'omitValidation'    => true
                )
            )
        );
    }
    
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
                array(),
                'nonExistingKey',
                array(
                    'returnMode'    => ReturnMode::ELEMENT_ONLY
                )
            ),
            array(
                array(null, false),
                array(),
                'nonExistingKey',
                array(
                    'returnMode'    => ReturnMode::BOTH
                )
            ),
            array(
                false,
                array(),
                'nonExistingKey',
                array(
                    'returnMode'    => ReturnMode::EXISTS_ONLY
                )
            ),
            array(
                null,
                array(
                    'x'             => null
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
                array('localhost', true),
                $exampleArray,
                array(
                    'database', 'host'
                ),
                array(
                    'returnMode'    => ReturnMode::BOTH
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
                    ''
                ),
                4,
                array('database', 'host')
            ),
            array(
                array(
                    'InvalidArgumentException',
                    ''
                ),
                new \stdClass,
                array('database', 'host')
            ),
            array(
                array(
                    'LengthException',
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
                    'InvalidArgumentException',
                    'Option \'returnMode\' must an be same as one of ReturnMode constants.'
                ),
                $exampleArray,
                'database.host',
                array(
                    'returnMode' => 'INVALID_MODE'
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
     * @dataProvider provideHasElementAtKeyPathData
     */
    public function testHasElementAtKeyPath(
        $expectedValue,
        $input,
        $keyPath,
        array $options = array()
    )
    {
        $this->assertSame($expectedValue, ArrayUtil::hasElementAtKeyPath($input, $keyPath, $options));
    }
    
    public function provideHasElementAtKeyPathData()
    {
        $exampleArray = $this->getExampleArray();
        
        return array(
            array(
                false,
                array(),
                'nonExistingKey'
            ),
            array(
                false,
                array(),
                'nonExistingKey',
                array( // these options should be ignored
                    'returnMode'        => ReturnMode::ELEMENT_ONLY,
                    'throwOnNonExisting'=> true
                )
            ),
            array(
                true,
                $exampleArray,
                'database/host',
                array( // but keySeparator should not
                    'keySeparator'      => '/'
                )
            ),
            array(
                true,
                $exampleArray,
                'database.host'
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
                    ''
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
     * @dataProvider provideGetKeyPathAsArray
     */
    public function testGetKeyPathAsArray($expectedValue, $keyPath, array $options = array())
    {
        $this->assertSame($expectedValue, ArrayUtil::getKeyPathAsArray($keyPath, $options));
    }
    
    public function provideGetKeyPathAsArray()
    {
        return array(
            array(
                array('x', 'y', 'z'),
                'x.y.z'
            ),
            array(
                array('users', '0'),
                'users.0'
            ),
            array(
                array('x', 'y', 'z'),
                'x/y/z',
                array(
                    'keySeparator'  => '/'
                )
            ),
            array(
                array('x.y.z'),
                'x.y.z',
                array(
                    'keySeparator'  => '/'
                )
            ),
            array(
                array('x', 'y', 'z'),
                array('x', 'y', 'z')
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
