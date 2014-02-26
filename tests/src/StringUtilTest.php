<?php

namespace Telesto\Utils\Tests;

use Telesto\Utils\StringUtil;

class StringUtilTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideExplodeData
     */
    public function testExplode($expectedResult, $delimiter, $string, $limit = null, array $options = array())
    {
        $this->assertSame($expectedResult, StringUtil::explode($delimiter, $string, $limit, $options));
    }
    
    public function provideExplodeData()
    {
        return array(
            array(
                array(
                    'Once',
                    'Upon',
                    'a',
                    'Time',
                    'in',
                    'the',
                    'West'
                ),
                ' ',
                'Once Upon a Time in the West'
            ),
            array(
                array(
                    'Once',
                    'Upon',
                    'a Time in the West'
                ),
                ' ',
                'Once Upon a Time in the West',
                3
            ),
            array(
                array(
                    'Little',
                    'pink',
                    'flamingo'
                ),
                ' ',
                'Little pink flamingo',
                3
            ),
            array(
                array(
                    'Little',
                    'pink'
                ),
                ' ',
                'Little pink flamingo',
                -1
            ),
            array(
                array(
                    'Little pink flamingo'
                ),
                ' ',
                'Little pink flamingo',
                0
            ),
            array(
                array(
                    'A',
                    'B.C',
                    'D'
                ),
                '.',
                'A.B\\.C.D',
                null,
                array(
                    'escapeChar'    => '\\'
                )
            ),
            array(
                array(
                    'A',
                    'B.C',
                    'D.E'
                ),
                '.',
                'A.B\\.C.D.E',
                3,
                array(
                    'escapeChar'    => '\\'
                )
            ),
            array(
                array(),
                '.',
                'A.B\\.C.D.E',
                -4,
                array(
                    'escapeChar'    => '\\'
                )
            ),
            array(
                array(),
                '.',
                'A.B\\.C.D.E',
                -5,
                array(
                    'escapeChar'    => '\\'
                )
            ),
            array(
                array(
                    'A',
                    'B.C'
                ),
                '.',
                'A.B\\.C.D.E',
                -2,
                array(
                    'escapeChar'    => '\\'
                )
            ),
            array(
                array(
                    'A',
                    'B\\',
                    'C',
                    'D'
                ),
                '.',
                'A.B\\\\.C.D',
                null,
                array(
                    'escapeChar'    => '\\'
                )
            ),
            array(
                array(
                    'A',
                    'B\\.C',
                    'D'
                ),
                '.',
                'A.B\\\\\\.C.D',
                null,
                array(
                    'escapeChar'    => '\\'
                )
            ),
            array(
                array(
                    'A',
                    'B.C',
                    'D\\'
                ),
                '.',
                'A.B\\.C.D\\',
                null,
                array(
                    'escapeChar'    => '\\'
                )
            ),
            array(
                array(
                    '%%',
                    'B.%',
                    'D%'
                ),
                '.',
                '%%%%.B%.%%.D%',
                null,
                array(
                    'escapeChar'    => '%'
                )
            ),
            array(
                array(
                    '%%.B.%.D%'
                ),
                '.',
                '%%%%.B%.%%.D%',
                0,
                array(
                    'escapeChar'    => '%'
                )
            ),
            array(
                array(
                    'A B',
                    '',
                    '-',
                    ''
                ),
                ' ',
                'A- B  -- ',
                null,
                array(
                    'escapeChar'    => '-'
                )
            )
        );
    }
    
    /**
     * @dataProvider provideExplodeExceptionsData
     */
    public function testExplodeExceptions(
        array $expectedException,
        $delimiter,
        $string,
        $limit = null,
        array $options = array()
    )
    {
        $this->setExpectedException(
            $expectedException[0],
            $expectedException[1]
        );
        
        StringUtil::explode($delimiter, $string, $limit, $options);
    }
    
    public function provideExplodeExceptionsData()
    {
        return array(
            array(
                array(
                    'InvalidArgumentException',
                    'If specified, option \'escapeChar\' must have exactly 1 character, 0 given.'
                ),
                '.',
                'A.B.C.D',
                null,
                array(
                    'escapeChar'    => ''
                )
            ),
            array(
                array(
                    'InvalidArgumentException',
                    'If specified, option \'escapeChar\' must have exactly 1 character, 2 given.'
                ),
                '.',
                'A.B.C.D',
                null,
                array(
                    'escapeChar'    => '--'
                )
            ),
            array(
                array(
                    'InvalidArgumentException',
                    'Escape character cannot not be the same as the delimiter or occur in the delimiter.'
                ),
                '.',
                'A.B.C.D',
                null,
                array(
                    'escapeChar'    => '.'
                )
            ),
            array(
                array(
                    'InvalidArgumentException',
                    'Escape character cannot not be the same as the delimiter or occur in the delimiter.'
                ),
                ' . ',
                'A . B . C . D',
                null,
                array(
                    'escapeChar'    => '.'
                )
            )
        );
    }
}
