<?php

namespace Telesto\Utils\Tests\Arrays\Overwriting\Copy\KeyPathMap;

use Telesto\Utils\Arrays\Overwriting\Copy\KeyPathMap\WildcardOverwriter;

use ArrayObject;

class WildcardOverwriterTest extends \PHPUnit_Framework_TestCase
{
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
        $overwriter = new WildcardOverwriter($keyPathMap, $defaultOptions);
        $overwriter->overwrite($input, $output, $options);
        
        $this->assertSame($expectedOutput, $output);
    }
    
    public function provideOverwriteData()
    {
        return array(
            array(
                array(
                    's'                 => array(
                        'x'             => array(
                            'first'     => 1,
                            'second'    => 3
                        ),
                        'y'             => array(
                            'first'     => 2,
                            'second'    => 4
                        )
                    )
                ),
                array(
                    'static'            => array(
                        'first'         => array(
                            'x'         => 1,
                            'y'         => 2,
                        ),
                        'second'        => array(
                            'x'         => 3,
                            'y'         => 4
                        )
                    )
                ),
                array(),
                array(
                    'static.%var1%.%var2%'  => 's.%var2%.%var1%',
                )
            ),
            array(
                array(
                    's'                 => array(
                        'x.first'       => 1,
                        'y.first'       => 2,
                        'x.second'      => 3,
                        'y.second'      => 4
                    )
                ),
                array(
                    'static'            => array(
                        'first'         => array(
                            'x'         => 1,
                            'y'         => 2,
                        ),
                        'second'        => array(
                            'x'         => 3,
                            'y'         => 4
                        )
                    )
                ),
                array(),
                array(
                    'static/%var1%/%var2%'  => 's/%var2%.%var1%',
                ),
                array(
                    'keySeparator'      => '/'
                )
            )
        );
    }
}
