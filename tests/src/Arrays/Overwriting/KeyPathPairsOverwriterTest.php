<?php

namespace Telesto\Utils\Tests\Arrays\Overwriting;

use Telesto\Utils\Arrays\Overwriting\KeyPathPairsOverwriter;

use ArrayObject;

class KeyPathPairsOverwriterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideConstructorExceptionsData
     */
    public function testConstructorExceptions(
        $expectedException,
        $keyPathPairs,
        $settings = array()
    )
    {
        $this->setExpectedException(
            $expectedException[0],
            $expectedException[1]
        );
        
        new KeyPathPairsOverwriter($keyPathPairs, $settings);
    }
    
    public function provideConstructorExceptionsData()
    {
        return array(
            array(
                array(
                    'InvalidArgumentException',
                    'Argument $keyPathPairs must be an array of arrays, string given at index 1.'
                ),
                array(
                    array('x', 'y'),
                    'invalidValue'
                )
            ),
            array(
                array(
                    'LengthException',
                    'Arrays in $keyPathPairs must have exactly 2 elements, 3 given at index 1.'
                ),
                array(
                    array('x', 'y'),
                    array('x', 'y', 'z')
                )
            ),
            array(
                array(
                    'InvalidArgumentException',
                    'Invalid value for input key path at index 2:'
                ),
                array(
                    array('x', 'y'),
                    array('x', 'y'),
                    array(new \stdClass, 'y')
                )
            ),
            array(
                array(
                    'InvalidArgumentException',
                    'Invalid value for output key path at index 0:'
                ),
                array(
                    array('x', new \stdClass)
                )
            )
        );
    }

    /**
     * @dataProvider provideOverwriteData
     */
    public function testOverwrite(
        array $expectedOutput,
        array $input,
        array $output,
        array $keyPathPairs,
        array $settings = array()
    )
    {
        $overwriter = new KeyPathPairsOverwriter($keyPathPairs, $settings);
        $overwriter->overwrite($input, $output);
        
        $this->assertSame($expectedOutput, $output);
    }
    
    public function provideOverwriteData()
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
                array(),
                array(
                    array('database.host', 'database_host'),
                    array('database.user', 'database_user'),
                    array('non_existing_key', 'new_value')
                )
            ),
            array(
                array(
                    'x'                 => 'localhost',
                    'y'                 => 'localhost',
                ),
                $exampleArray,
                array(),
                array(
                    array('database.host', 'x'),
                    array('database.host', 'y')
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
                    'ext.ra'            => array(
                        'new_value'     => 4123
                    ),
                    'extra/new_value'   => 4123
                ),
                $exampleArray,
                array(),
                array(
                    array('database/host', 'db/data/host'),
                    array('database/user', 'db/data/user'),
                    array('non_existing_key', 'ext.ra/new_value'),
                    array('non_existing_key2', 'extra\/new_value')
                ),
                array(
                    'default'           => 4123,
                    'keySeparator'      => '/'
                )
            ),
            array(
                array(
                    'db_host'           => 'localhost',
                    'db_user'           => 'root',
                ),
                $exampleArray,
                array(),
                array(
                    array('database.host', 'db_host'),
                    array('database.user', 'db_user'),
                    array('non_existing_key', 'new_value'),
                    array('non_existing_key2', 'level1.level2.level3')
                ),
                array(
                    'throwOnNonExisting'=> false,
                    'omitNonExisting'   => true // should overwrite throwOnNonExisting
                )
            ),
            array(
                array(
                    'db_host'           => 'localhost',
                    'db_user'           => 'root',
                ),
                $exampleArray,
                array(),
                array(
                    array('database.host', 'db_host'),
                    array('database.user', 'db_user'),
                    array('non_existing_key', 'new_value'),
                    array('non_existing_key2', 'level1.level2.level3')
                ),
                array(
                    'throwOnNonExisting'=> false,
                    'omitNonExisting'   => true // should overwrite throwOnNonExisting
                )
            ),
            array(
                array(
                    'x'         => 10,
                    'y'         => 100,
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
                    'x'             => 30, // will get overwritten
                    'y'             => 100 // won't get overwritten
                ),
                array(
                    array('0', 'x'),
                    array('1.0', 'colors')
                )
            )
        );
    }
    
    /**
     * @dataProvider provideOverwriteWithObjectsData
     */
    public function testOverwriteWithObjects(
        $expectedOutput,
        $input,
        $output,
        array $keyPathPairs,
        array $settings = array()
    )
    {
        $overwriter = new KeyPathPairsOverwriter($keyPathPairs, $settings);
        $overwriter->overwrite($input, $output);
        
        // can't compare objects using assertSame
        // assertEquals doesn't require the same key order though
        $this->assertEquals($expectedOutput, $output);
    }
    
    public function provideOverwriteWithObjectsData()
    {
        $exampleArray = $this->getExampleArray();
        
        return array(
            array(
                new ArrayObject(
                    array(
                        'db'                => new ArrayObject(
                            array(
                                'host'      => 'localhost',
                                'user'      => 'root'
                            )
                        ),
                        'new_value'         => null
                    )
                ),
                $exampleArray,
                new ArrayObject,
                array(
                    array('database.host', 'db.host'),
                    array('database.user', 'db.user'),
                    array('non_existing_key', 'new_value')
                ),
                array(
                    'arrayPrototype'        => new ArrayObject
                )
            ),
            array(
                new ArrayObject(
                    array(
                        'db'                => array(
                            'host'      => 'localhost',
                            'user'      => 'root'
                        ),
                        'new_value'         => null
                    )
                ),
                $exampleArray,
                new ArrayObject,
                array(
                    array('database.host', 'db.host'),
                    array('database.user', 'db.user'),
                    array('non_existing_key', 'new_value')
                )
            )
        );
    }
    
    /**
     * @dataProvider provideOverwriteExceptionsData
     */
    public function testOverwriteExceptions(
        array $expectedException,
        array $input,
        array $output,
        array $keyPathPairs,
        array $settings = array()
    )
    {
        $this->setExpectedException(
            $expectedException[0],
            $expectedException[1]
        );
        
        $overwriter = new KeyPathPairsOverwriter($keyPathPairs, $settings);
        $overwriter->overwrite($input, $output);
    }
    
    public function provideOverwriteExceptionsData()
    {
        $exampleArray = $this->getExampleArray();
        
        return array(
            array(
                array(
                    'RuntimeException',
                    ''
                ),
                $exampleArray,
                array(),
                array(
                    array('database.host', 'db.host'),
                    array('database.non_existing_field', 'db.user')
                ),
                array(
                    'throwOnNonExisting'            => true
                )
            ),
            array(
                array(
                    'RuntimeException',
                    ''
                ),
                $exampleArray,
                array(),
                array(
                    array('database.host', 'db.host'),
                    array('database.user', 'db.host.0')
                ),
                array(
                    'throwOnCollision'              => true
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
