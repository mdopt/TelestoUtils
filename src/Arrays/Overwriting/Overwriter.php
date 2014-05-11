<?php

namespace Telesto\Utils\Arrays\Overwriting;

/**
 * Interface for overwriting one array|ArrayAccess using another
 */
interface Overwriter
{
    /**
     * @param   array|\ArrayAccess  $input
     * @param   array|\ArrayAccess  $output
     *
     * @return  array|\ArrayAccess
     */
    public function overwrite($input, &$output);
}
