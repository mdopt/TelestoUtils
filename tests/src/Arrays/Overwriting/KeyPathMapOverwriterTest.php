<?php

namespace Telesto\Utils\Tests\Arrays\Overwriting;

use Telesto\Utils\Arrays\Overwriting\KeyPathMapOverwriter;

use ArrayObject;

class KeyPathMapOverwriterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideConstructorExceptionsData
     */
    public function testConstructorExceptions(
        $expectedException,
        $keyPathMap,
        $settings = array()
    )
    {
        $this->setExpectedException(
            $expectedException[0],
            $expectedException[1]
        );
        
        new KeyPathMapOverwriter($keyPathMap, $settings);
    }
    
    public function provideConstructorExceptionsData()
    {
        return array(
            array(
                array(
                    'InvalidArgumentException',
                    'Invalid output key path for input key path \'y\'(subindex 1):'
                ),
                array(
                    'x'     => 'y',
                    'y'     => array('z', new \stdClass)
                )
            ),
            array(
                array(
                    'InvalidArgumentException',
                    'Invalid output key path for input key path \'y\':'
                ),
                array(
                    'x'     => 'y',
                    'y'     => new \stdClass
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
        array $keyPathMap,
        array $defaultOptions = array(),
        array $options = array()
    )
    {
        $overwriter = new KeyPathMapOverwriter($keyPathMap, $defaultOptions);
        $overwriter->overwrite($input, $output, $options);
        
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
                    'database.host'     => 'database_host',
                    'database.user'     => 'database_user',
                    'non_existing_key'  => 'new_value'
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
                    'database.host'     => array('x', 'y')
                )
            ),
            array(
                array(
                    'y'                 => 10,
                ),
                array(),
                array(),
                array(
                    'x'                 => 'y'
                ),
                array(
                    'default'           => 100
                ),
                array(
                    'default'           => 10 // should overwrite defaultOptions
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
                    'database/host'     => 'db/data/host',
                    'database/user'     => 'db/data/user',
                    'non_existing_key'  => 'ext.ra/new_value',
                    'non_existing_key2' => 'extra\/new_value'
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
                    'database.host'     => 'db_host',
                    'database.user'     => 'db_user',
                    'non_existing_key'  => 'new_value',
                    'non_existing_key2' => 'level1.level2.level3'
                ),
                array(
                    'throwOnNonExisting'=> true,
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
                    'database.host'     => 'db_host',
                    'database.user'     => 'db_user',
                    'non_existing_key'  => 'new_value',
                    'non_existing_key2' => 'level1.level2.level3'
                ),
                array(
                    'throwOnNonExisting'=> true,
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
                    'database.host'     => 'db_host',
                    'database.user'     => 'db_user',
                    'non_existing_key'  => 'new_value',
                    'non_existing_key2' => 'level1.level2.level3'
                ),
                array(
                    'omitNonExisting'   => false
                ),
                array(
                    'omitNonExisting'   => true // should overwrite defaultOptions
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
                    '0'             => 'x',
                    '1.0'           => 'colors'
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
        array $keyPathMap,
        array $defaultOptions = array()
    )
    {
        $overwriter = new KeyPathMapOverwriter($keyPathMap, $defaultOptions);
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
                    'database.host'         => 'db.host',
                    'database.user'         => 'db.user',
                    'non_existing_key'      => 'new_value'
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
                    'database.host'         => 'db.host',
                    'database.user'         => 'db.user',
                    'non_existing_key'      => 'new_value'
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
        array $keyPathMap,
        array $defaultOptions = array(),
        array $options = array()
    )
    {
        $this->setExpectedException(
            $expectedException[0],
            $expectedException[1]
        );
        
        $overwriter = new KeyPathMapOverwriter($keyPathMap, $defaultOptions);
        $overwriter->overwrite($input, $output, $options);
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
                    'database.host'                 => 'db.host',
                    'database.non_existing_field'   => 'db.user'
                ),
                array(
                    'throwOnNonExisting'    => true
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
                    'database.host'         => 'db.host',
                    'database.user'         => 'db.host.0'
                ),
                array(
                    'throwOnCollision'      => true
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
                    'database.host'         => 'db.host',
                    'database.user'         => 'db.host.0'
                ),
                array(),
                array(
                    'throwOnCollision'      => true
                )
            ),
            array(
                array(
                    'InvalidArgumentException',
                    ''
                ),
                array(),
                array(),
                array(
                    'database.host'         => 'db.host'
                ),
                array(),
                array(
                    'arrayPrototype'        => new \stdClass
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
