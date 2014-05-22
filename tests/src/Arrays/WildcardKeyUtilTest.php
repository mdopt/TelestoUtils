<?php

namespace Telesto\Utils\Arrays;

use Telesto\Utils\Arrays\WildcardKeyUtil;

class WildcardKeyUtilTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideGetInputKeyPathsData
     */
    public function testGetInputKeyPaths($expectedResult, $array, $inputPathRepr, array $options = array())
    {
        $this->assertSame($expectedResult, WildcardKeyUtil::getInputKeyPaths($array, $inputPathRepr, $options));
    }
    
    public function provideGetInputKeyPathsData()
    {
        return array(
            array(
                array(
                    array('static')
                ),
                array(),
                array(
                    'keyPath'       => array('static'),
                    'parameters'    => array()
                )
            ),
            array(
                array(
                    array('first'),
                    array('second'),
                    array('third')
                ),
                array(
                    'first'         => 1,
                    'second'        => 2,
                    'third'         => 3
                ),
                array(
                    'keyPath'       => array('%x%'),
                    'parameters'    => array(
                        0           => 'x'
                    )
                )
            ),
            array(
                array(
                    array('static', 'static2')
                ),
                array(),
                array(
                    'keyPath'       => array('static', 'static2'),
                    'parameters'    => array()
                )
            ),
            array(
                array(
                    array('first', 'static'),
                    array('second', 'static')
                ),
                array(
                    'first'         => array(
                        'static'    => 10
                    ),
                    'second'        => 10
                ),
                array(
                    'keyPath'       => array('%x%', 'static'),
                    'parameters'    => array(
                        0           => 'x'
                    )
                )
            ),
            array(
                array(
                    array('static', 'first'),
                    array('static', 'second'),
                    array('static', 'third')
                ),
                array(
                    'static'        => array(
                        'first'     => 1,
                        'second'    => 2,
                        'third'     => 3
                    )
                ),
                array(
                    'keyPath'       => array('static', '%x%'),
                    'parameters'    => array(
                        1           => 'x'
                    )
                )
            ),
            array(
                array(
                    array('data', 'colors', 0, 'name'),
                    array('data', 'colors', 0, 'hex'),
                    array('data', 'colors', 1, 'name'),
                    array('data', 'colors', 1, 'hex'),
                    array('data', 'people', 0, 'name'),
                    array('data', 'people', 0, 'city')
                ),
                array(
                    'data'              => array(
                        'colors'        => array(
                            array(
                                'name'  => 'red',
                                'hex'   => 'ff0000'
                            ),
                            array(
                                'name'  => 'blue',
                                'hex'   => '0000ff'
                            )
                        ),
                        'people'        => array(
                            array(
                                'name'  => 'John',
                                'city'  => 'New York'
                            )
                        )
                    )
                ),
                array(
                    'keyPath'       => array('data', '%x%', '%y%', '%z%'),
                    'parameters'    => array(
                        1           => 'x',
                        2           => 'y',
                        3           => 'z'
                    )
                )
            ),
            array(
                array(),
                array(
                    'static'        => 1023
                ),
                array(
                    'keyPath'       => array('static', '%x%'),
                    'parameters'    => array(
                        1           => 'x'
                    )
                ),
                array(
                    'omitNonExisting'   => true
                )
            ),
            array(
                array(),
                array(),
                array(
                    'keyPath'       => array('static', '%x%'),
                    'parameters'    => array(
                        1           => 'x'
                    )
                ),
                array(
                    'omitNonExisting'   => true
                )
            )
        );
    }
    
    /**
     * @dataProvider provideGetInputKeyPathsExceptionsData
     */
    public function testGetInputKeyPathsExceptions($expectedException, $array, $inputPathRepr, array $options = array())
    {
        $this->setExpectedException($expectedException[0], $expectedException[1]);
        
        WildcardKeyUtil::getInputKeyPaths($array, $inputPathRepr, $options);
    }
    
    public function provideGetInputKeyPathsExceptionsData()
    {
        return array(
            array(
                array(
                    'InvalidArgumentException',
                    'Argument $array is not compatible type'
                ),
                1023,
                array(
                    'keyPath'       => array('static'),
                    'parameters'    => array()
                )
            ),
            array(
                array(
                    'InvalidArgumentException',
                    'Element at key path ["static"] is not compatible type'
                ),
                array(
                    'static'        => 1023
                ),
                array(
                    'keyPath'       => array('static', '%x%'),
                    'parameters'    => array(
                        1           => 'x'
                    )
                )
            ),
            array(
                array(
                    'DomainException',
                    'Element at key path ["static"] does not exist.'
                ),
                array(),
                array(
                    'keyPath'       => array('static', '%x%'),
                    'parameters'    => array(
                        1           => 'x'
                    )
                )
            )
        );
    }
    
    /**
     * @dataProvider provideGetOutputKeyPathData
     */
    public function testGetOutputKeyPath(
        $expectedResult,
        array $inputKeyPath,
        array $inputPathRepr,
        array $outputPathRepr
    )
    {
        $this->assertSame($expectedResult, WildcardKeyUtil::getOutputKeyPath($inputKeyPath, $inputPathRepr, $outputPathRepr));
    }
    
    public function provideGetOutputKeyPathData()
    {
        return array(
            array(
                array('static', 'first'),
                array('static', 'first'),
                array(
                    'keyPath'       => array('static', '%x%'),
                    'parameters'    => array(
                        1           => 'x'
                    )
                ),
                array(
                    array(
                        array(false, 'static')
                    ),
                    array(
                        array(true, 'x')
                    )
                )
            ),
            array(
                array('static', '0_1'),
                array('static', '0', '1'),
                array(
                    'keyPath'       => array('static', '%x%', '%y%'),
                    'parameters'    => array(
                        1           => 'x',
                        2           => 'y'
                    )
                ),
                array(
                    array(
                        array(false, 'static')
                    ),
                    array(
                        array(true, 'x'),
                        array(false, '_'),
                        array(true, 'y')
                    )
                )
            )
        );
    }
    
    /**
     * @dataProvider provideGetPathMapReprData
     */
    public function testGetPathMapRepr(
        $expectedResult,
        $keyPathMap,
        $options = array()
    )
    {
        $this->assertSame(
            $expectedResult,
            WildcardKeyUtil::getPathMapRepr($keyPathMap, $options)
        );
    }
    
    public function provideGetPathMapReprData()
    {
        return array(
            array(
                array(
                    array(
                        array(
                            'keyPath'   => array('static', '%x%', '%y%'),
                            'parameters'=> array(
                                1       => 'x',
                                2       => 'y'
                            )
                        ),
                        array(
                            array(
                                array(
                                    array(false, 's')
                                ),
                                array(
                                    array(true, 'y')
                                ),
                                array(
                                    array(true, 'x')
                                )
                            ),
                            array(
                                array(
                                    array(false, 's2_'),
                                    array(true, 'y'),
                                    array(false, '_'),
                                    array(true, 'x')
                                )
                            )
                        )
                    ),
                    array(
                        array(
                            'keyPath'   => array('static3', '%x%'),
                            'parameters'=> array(
                                1       => 'x'
                            )
                        ),
                        array(
                            array(
                                array(
                                    array(false, 's3')
                                ),
                                array(
                                    array(false, 'deeper')
                                ),
                                array(
                                    array(true, 'x')
                                )
                            )
                        )
                    )
                ),
                array(
                    'static/%x%/%y%'    => array('s/%y%/%x%', 's2_%y%_%x%'),
                    'static3/%x%'       => 's3/deeper/%x%'
                ),
                array(
                    'keySeparator'      => '/'
                )
            )
        );
    }
    
    /**
     * @dataProvider provideGetPathReprData
     */
    public function testGetPathRepr(
        $expectedResult,
        $inputKeyPath,
        $outputKeyPaths,
        $options = array()
    )
    {
        $this->assertSame(
            $expectedResult,
            WildcardKeyUtil::getPathRepr($inputKeyPath, $outputKeyPaths, $options)
        );
    }
    
    public function provideGetPathReprData()
    {
        return array(
            array(
                array(
                    array(
                        'keyPath'       => array('static', '%x%', '%y%'),
                        'parameters'    => array(
                            1           => 'x',
                            2           => 'y'
                        )
                    ),
                    array(
                        array(
                            array(
                                array(false, 's')
                            ),
                            array(
                                array(true, 'y')
                            ),
                            array(
                                array(true, 'x')
                            )
                        ),
                        array(
                            array(
                                array(false, 's2')
                            ),
                            array(
                                array(true, 'x')
                            ),
                            array(
                                array(true, 'y')
                            )
                        )
                    )
                ),
                'static.%x%.%y%',
                array('s.%y%.%x%', 's2.%x%.%y%')
            ),
            array(
                array(
                    array(
                        'keyPath'       => array('static', '%x%', '%y%'),
                        'parameters'    => array(
                            1           => 'x',
                            2           => 'y'
                        )
                    ),
                    array(
                        array(
                            array(
                                array(false, 's')
                            ),
                            array(
                                array(true, 'y'),
                                array(false, '_'),
                                array(true, 'x')
                            )
                        )
                    )
                ),
                'static/%x%/%y%',
                's/%y%_%x%',
                array(
                    'keySeparator'      => '/'
                )
            )
        );
    }
    
    /**
     * @dataProvider provideGetPathReprExceptionsData
     */
    public function testGetPathReprExceptions(
        $expectedException,
        $inputKeyPath,
        $outputKeyPaths,
        $options = array()
    )
    {
        $this->setExpectedException($expectedException[0], $expectedException[1]);
        
        WildcardKeyUtil::getPathRepr($inputKeyPath, $outputKeyPaths, $options);
    }
    
    public function provideGetPathReprExceptionsData()
    {
        return array(
            array(
                array(
                    'DomainException',
                    'Parameters ["y","z"] in the input(\'static.%x%.%y%.%z%\') are not used in the output(\'s.%x%\').'
                ),
                'static.%x%.%y%.%z%',
                's.%x%'
            ),
            array(
                array(
                    'DomainException',
                    'Parameters ["y","z"] in the output(\'static.%x%\') are not defined in the input(\'s2.%y%.%z%.%x%\').'
                ),
                'static.%x%',
                array('s.%x%', 's2.%y%.%z%.%x%')
            )
        );
    }
    
    /**
     * @dataProvider provideGetInputPathReprData
     */
    public function testGetInputPathRepr($expectedResult, $keyPath, array $options = array())
    {
        $this->assertSame($expectedResult, WildcardKeyUtil::getInputPathRepr($keyPath, $options));
    }
    
    public function provideGetInputPathReprData()
    {
        return array(
            array(
                array(
                    'keyPath'       => array('static', '%x%', '%y%'),
                    'parameters'    => array(
                        1           => 'x',
                        2           => 'y'
                    )
                ),
                'static.%x%.%y%'
            ),
            array(
                array(
                    'keyPath'       => array('static', '%x%', '%y%'),
                    'parameters'    => array(
                        1           => 'x',
                        2           => 'y'
                    )
                ),
                array('static', '%x%', '%y%')
            ),
            array(
                array(
                    'keyPath'       => array('static', 'static2', '%param1%', '%param2%', 'static3'),
                    'parameters'    => array(
                        2           => 'param1',
                        3           => 'param2'
                    )
                ),
                'static/static2/%param1%/%param2%/static3',
                array(
                    'keySeparator'  => '/'
                )
            ),
            array(
                array(
                    'keyPath'       => array('static', 'static2', 'static3'),
                    'parameters'    => array()
                ),
                'static.static2.static3'
            ),
            array(
                array(
                    'keyPath'       => array('static'),
                    'parameters'    => array()
                ),
                'static'
            ),
            array(
                array(
                    'keyPath'       => array('static', '%', '%x%', '%^'),
                    'parameters'    => array()
                ),
                'static.%%.%%x%%.%%^'
            )
        );
    }
    
    /**
     * @dataProvider provideGetInputPathReprExceptionsData
     */
    public function testGetInputPathReprExceptions(
        $expectedException,
        $keyPath
    )
    {
        $this->setExpectedException(
            $expectedException[0],
            $expectedException[1]
        );
        
        WildcardKeyUtil::getInputPathRepr($keyPath);
    }
     
    public function provideGetInputPathReprExceptionsData()
    {
        return array(
            array(
                array(
                    'LogicException',
                    'Parameter \'x\' occurs more than once in key path \'static.%x%.static2.%x%\'.'
                ),
                'static.%x%.static2.%x%'
            ),
            array(
                array(
                    'LogicException',
                    'Parameter \'x\' occurs more than once in key path ["%x%","static","%x%"].'
                ),
                array('%x%', 'static', '%x%')
            ),
            array(
                array(
                    'LogicException',
                    'Invalid key \'%^\' in key path \'static.%^.%x%\': every wildcard(\'%\') character that does not mark a parameter must be escaped using double wildcard(\'%%\').'
                ),
                'static.%^.%x%'
            ),
            array(
                array(
                    'LogicException',
                    'Invalid key \'%x\' in key path ["static","%x"]:'
                ),
                array('static', '%x')
            )
        );
    }
    
    /**
     * @dataProvider provideGetOutputKeyReprData
     */
    public function testGetOutputKeyRepr($expectedResult, $key)
    {
        $this->assertSame($expectedResult, WildcardKeyUtil::getOutputKeyRepr($key));
    }
    
    public function provideGetOutputKeyReprData()
    {
        return array(
            array(
                array(
                    array(false, 'static1')
                ),
                'static1'
            ),
            array(
                array(
                    array(false, 'static1%')
                ),
                'static1%%'
            ),
            array(
                array(
                    array(false, '%static1%static2%')
                ),
                '%%static1%%static2%%'
            ),
            array(
                array(
                    array(false, 'static1%'),
                    array(true, 'x'),
                    array(false, '%%')
                ),
                'static1%%%x%%%%%'
            ),
            array(
                array(
                    array(false, 'static1'),
                    array(true, 'x'),
                    array(true, 'y'),
                ),
                'static1%x%%y%'
            ),
            array(
                array(
                    array(false, 'static1'),
                    array(true, 'x'),
                    array(true, 'y'),
                    array(false, 'static2'),
                    array(true, 'z'),
                    array(false, 's')
                ),
                'static1%x%%y%static2%z%s'
            )
        );
    }
    
    /**
     * @dataProvider provideGetOutputKeyReprExceptionsData
     */
    public function testGetOutputKeyReprExceptions($expectedException, $key)
    {
        $this->setExpectedException($expectedException[0], $expectedException[1]);
        
        WildcardKeyUtil::getOutputKeyRepr($key);
    }
    
    public function provideGetOutputKeyReprExceptionsData()
    {
        return array(
            array(
                array(
                    'DomainException',
                    'Invalid parameter \'x@#\'.'
                ),
                'static%x@#%'
            ),
            array(
                array(
                    'DomainException',
                    'Started but not finished parameter: \'dd\' in \'%x%static%dd\'.'
                ),
                '%x%static%dd'
            ),
            array(
                array(
                    'DomainException',
                    'Unescaped wildcard character(%) at the end: \'%x%static%\'.'
                ),
                '%x%static%'
            )
        );
    }
    
    /**
     * @dataProvider provideGetOutputKeyReprParamsData
     */
    public function testGetOutputKeyReprParams($expectedResult, $repr)
    {
        $this->assertSame($expectedResult, WildcardKeyUtil::getOutputKeyReprParams($repr));
    }
    
    public function provideGetOutputKeyReprParamsData()
    {
        return array(
            array(
                array(),
                array(
                    array(false, 'static1')
                )
            ),
            array(
                array(
                    'x',
                    'y'
                ),
                array(
                    array(true, 'x'),
                    array(true, 'y'),
                    array(false, 'static1'),
                    array(false, 'static2'),
                    array(true, 'x')
                )
            )
        );
    }
    
    /**
     * @dataProvider provideGetMultipleOutputKeyReprParamsData
     */
    public function testGetMultipleOutputKeyReprParams($expectedResult, $reprs)
    {
        $this->assertSame($expectedResult, WildcardKeyUtil::getMultipleOutputKeyReprParams($reprs));
    }
    
    public function provideGetMultipleOutputKeyReprParamsData()
    {
        return array(
            array(
                array(),
                array(
                    array(
                        array(false, 'static1')
                    )
                )
            ),
            array(
                array(
                    'x',
                    'y',
                    'z'
                ),
                array(
                    array(
                        array(true, 'x'),
                        array(true, 'y'),
                        array(false, 'static1'),
                        array(false, 'static2'),
                        array(true, 'x')
                    ),
                    array(
                        array(true, 'y'),
                        array(true, 'z'),
                        array(true, 'z')
                    )
                )
            )
        );
    }
}
