<?php

namespace Telesto\Utils\Arrays;

use Telesto\Utils\Arrays\WildcardKeyUtil;

class WildcardKeyUtilTest extends \PHPUnit_Framework_TestCase
{
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
