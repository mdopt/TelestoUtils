<?php

namespace Telesto\Utils\Arrays\Overwriting\Copy;

use Telesto\Utils\Arrays\Overwriting\Overwriter;
use Telesto\Utils\Arrays\ValidationUtil;
use Telesto\Utils\TypeUtil;

/**
 * Ovewriter which uses all keys from input to overwrite output.
 *
 * It has to know the input keys, so it won't work with objects that don't
 * implement Traversable interface. Standard array will work as expected.
 */
class AllKeysOverwriter implements Overwriter
{
    /**
     * {@inheritdoc}
     */
    public function overwrite($input, &$output, array $options = array())
    {
        ValidationUtil::requireArrayOrArrayAccess($input, '$input');
        ValidationUtil::requireArrayOrArrayAccess($output, '$output');
        TypeUtil::requireType($input, 'array|Traversable', '$input');
        
        foreach ($input as $key => $value) {
            $output[$key] = $value;
        }
    }
}
