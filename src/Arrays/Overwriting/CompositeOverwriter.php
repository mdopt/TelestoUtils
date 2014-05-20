<?php

namespace Telesto\Utils\Arrays\Overwriting;

use Telesto\Utils\TypeUtil;

use LengthException;
use InvalidArgumentException;

class CompositeOverwriter implements Overwriter
{
    /**
     * @var Overwriter[]
     */
    protected $overwriters;
    
    public function __construct(array $overwriters)
    {
        if (count($overwriters) === 0) {
            throw new LengthException('At least one overwriter should be given.');
        }
        
        foreach ($overwriters as $index => $overwriter) {
            if (!($overwriter instanceof Overwriter)) {
                throw new InvalidArgumentException(sprintf(
                    'Argument $overwriters must be an array of %s instances, invalid type %s at index %s.',
                    __NAMESPACE__ . '\Overwriter',
                    TypeUtil::getType($overwriter),
                    $index
                ));
            }
        }
        
        $this->overwriters = $overwriters;
    }
    
    /**
     * {@inheritdoc}
     */
    public function overwrite($input, &$output, array $options = array())
    {
        foreach ($this->overwriters as $overwriter) {
            $overwriter->overwrite($input, $output, $options);
        }
    }
}
