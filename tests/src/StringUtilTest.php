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
                    '%%.%%.%%'
                ),
                '.',
                '%%%%%.%%%%%.%%%%',
                null,
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
    
    /**
     * @dataProvider provideImplodeData
     */
    public function testImplode($expectedResult, $glue, array $pieces, array $options = array())
    {
        $this->assertSame($expectedResult, StringUtil::implode($glue, $pieces, $options));
    }
    
    public function provideImplodeData()
    {
        return array(
            array(
                'A.B.C',
                '.',
                array('A', 'B', 'C')
            ),
            array(
                '.A..B..%C%',
                '.',
                array('.A', '.B', '.%C%')
            ),
            array(
                '%.A.%.B.%.%%C%%',
                '.',
                array('.A', '.B', '.%C%'),
                array(
                    'escapeChar'    => '%'
                )
            ),
            array(
                '%%%%%.%%%%%.%%%%',
                '.',
                array(
                    '%%.%%.%%'
                ),
                array(
                    'escapeChar'    => '%'
                )
            ),
            array(
                'A- B  -- ',
                ' ',
                array(
                    'A B', '', '-', ''
                ),
                array(
                    'escapeChar'    => '-'
                )
            ),
            array(
                '% ,  , A% , B , % , , %%',
                ' , ',
                array(' , ', 'A , B', ' , , %'),
                array(
                    'escapeChar'    => '%'
                )
            )
        );
    }
    
    /**
     * @dataProvider provideImplodeExceptionsData
     */
    public function testImplodeExceptions(array $expectedException, $glue, array $pieces, array $options = array())
    {
        $this->setExpectedException(
            $expectedException[0],
            $expectedException[1]
        );
        
        StringUtil::implode($glue, $pieces, $options);
    }
    
    public function provideImplodeExceptionsData()
    {
        return array(
            array(
                array(
                    'InvalidArgumentException',
                    'If specified, option \'escapeChar\' must have exactly 1 character, 3 given.'
                ),
                '.',
                array(),
                array(
                    'escapeChar'    => 'abc'
                )
            ),
            array(
                array(
                    'InvalidArgumentException',
                    'Escape character cannot not be the same as the glue or occur in the glue.'
                ),
                '.',
                array(),
                array(
                    'escapeChar'    => '.'
                )
            )
        );
    }
    
    /**
     * @dataProvider provideStrposAllData
     */
    public function testStrposAll($expectedResult, $haystack, $needle, $offset = 0, $length = null)
    {
        $this->assertSame($expectedResult, StringUtil::strposAll($haystack, $needle, $offset, $length));
    }
    
    public function provideStrposAllData()
    {
        return array(
            array(
                array(),
                '     ',
                '#'
            ),
            array(
                array(
                    0,
                    1,
                    2
                ),
                '###',
                '#'
            ),
            array(
                array(
                    2,
                    4
                ),
                '  # #',
                '#'
            ),
            array(
                array(
                    4
                ),
                '    #',
                '#'
            ),
            array(
                array(
                    4
                ),
                ' @  @@@@ @',
                '@',
                3,
                2
            ),
            array(
                array(
                    1,
                    5,
                    7,
                    10,
                    12
                ),
                ' --- ---- -----',
                '--',
                1
            ),
            array(
                array(
                    2,
                    5
                ),
                'This is a test',
                'is',
            )
        );
    }
    
    /**
     * @dataProvider provideSubstrConsecutiveCountData
     */
    public function testSubstrConsecutiveCount($expectedResult, $haystack, $needle, $offset = 0, $length = null)
    {
        $this->assertSame($expectedResult, StringUtil::substrConsecutiveCount($haystack, $needle, $offset, $length));
    }
    
    public function provideSubstrConsecutiveCountData()
    {
        return array(
            array(
                array(),
                '   ',
                '@'
            ),
            array(
                array(1, 2, 4, 1, 2),
                '  @ @@ @@@@ @ @@   ',
                '@'
            ),
            array(
                array(1, 2, 4, 1, 2),
                '  @ @@ @@@@ @ @@   ',
                '@',
                2
            ),
            array(
                array(1, 4, 1, 2),
                '  @ @@ @@@@ @ @@   ',
                '@',
                5
            ),
            array(
                array(1, 3),
                '  @ @@ @@@@ @ @@   ',
                '@',
                5,
                5
            ),
            array(
                array(1, 2, 1),
                '  xxx  xxxx  xxx',
                'xx'
            ),
            array(
                array(1, 1),
                '  xxx  xxxx  xxx',
                'xx',
                0,
                10
            ),
            array(
                array(1, 2),
                '  xxx  xxxx  xxx',
                'xx',
                0,
                11
            ),
            array(
                array(1, 1, 1),
                'xxx xxxxx x xx xx xxx',
                'xxx'
            ),
            array(
                array(3, 1, 1),
                '11123233 433431 335012',
                '1'
            ),
            array(
                array(1, 1, 1),
                '11123233 433431 335012',
                '2'
            ),
            array(
                array(1, 2, 2, 1, 2),
                '11123233 433431 335012',
                '3'
            ),
            array(
                array(1, 1, 2, 1, 1),
                '--11123233--- 433-----4--31 335012--',
                '--'
            )
        );
    }
    
    /**
     * @dataProvider provideSubstrConsecutiveCountExceptionsData
     */
    public function testSubstrConsecutiveCountExceptions(array $expectedException, $haystack, $needle, $offset = 0, $length = null)
    {
        $this->setExpectedException(
            $expectedException[0],
            $expectedException[1]
        );
        
        StringUtil::substrConsecutiveCount($haystack, $needle, $offset, $length);
    }
    
    public function provideSubstrConsecutiveCountExceptionsData()
    {
        return array(
            array(
                array(
                    'InvalidArgumentException',
                    'Needle cannot be an empty string.'
                ),
                'abc',
                ''
            ),
            array(
                array(
                    'InvalidArgumentException',
                    'Offset + length(10) is greater than haystack length(9).'
                ),
                'abcdefghi',
                ' ',
                2,
                8
            )
        );
    }
    
    /**
     * @dataProvider provideSubstrMaxConsecutiveCountData
     */
    public function testSubstrMaxConsecutiveCount($expectedResult, $haystack, $needle, $offset = 0, $length = null)
    {
        $this->assertSame($expectedResult, StringUtil::substrMaxConsecutiveCount($haystack, $needle, $offset, $length));
    }
    
    public function provideSubstrMaxConsecutiveCountData()
    {
        return array(
            array(
                4,
                '  @ @@ @@@@ @ @@   ',
                '@'
            ),
            array(
                2,
                '  @ @@ @@@@ @ @@   ',
                '@',
                5,
                4
            )
        );
    }
}
