<?php

namespace Telesto\Utils\Tests\Arrays\Overwriting\Copy;

use Telesto\Utils\Arrays\Overwriting\Copy\AllKeysOverwriter;

class AllKeysOverwriterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideOverwriteData
     */
    public function testOverwrite(
        array $expectedOutput,
        array $input,
        array $output
    )
    {
        $overwriter = new AllKeysOverwriter;
        $overwriter->overwrite($input, $output);
        
        $this->assertSame($expectedOutput, $output);
    }
    
    public function provideOverwriteData()
    {
        return array(
            array(
                array(
                    'x'         => 100,
                    'y'         => 200,
                    'z'         => 30,
                    'another'   => 1000
                ),
                array(
                    'x'         => 100,
                    'y'         => 200,
                    'another'   => 1000
                ),
                array(
                    'x'         => 10,
                    'y'         => 20,
                    'z'         => 30
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
        $output
    )
    {
        $overwriter = new AllKeysOverwriter;
        $overwriter->overwrite($input, $output);
        
        // can't compare objects using assertSame
        // assertEquals doesn't require the same key order though
        $this->assertEquals($expectedOutput, $output);
    }
    
    public function provideOverwriteWithObjectsData()
    {
        return array(
            array(
                new \ArrayObject(
                    array(
                        'x'         => 100,
                        'y'         => 200,
                        'z'         => 30,
                        'another'   => 1000
                    )
                ),
                new \ArrayObject(
                    array(
                        'x'         => 100,
                        'y'         => 200,
                        'another'   => 1000
                    )
                ),
                new \ArrayObject(
                    array(
                        'x'         => 10,
                        'y'         => 20,
                        'z'         => 30
                    )
                )
            )
        );
    }
    
    public function testOverwriteExceptionNonTraversable()
    {
        $mockInput = $this->getMock('ArrayAccess');
        $overwriter = new AllKeysOverwriter;
        
        $this->setExpectedException(
            'InvalidArgumentException'
        );
        
        $output = array();
        $overwriter->overwrite($mockInput, $output);
    }
}
