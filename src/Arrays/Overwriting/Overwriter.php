<?php

namespace Telesto\Utils\Arrays\Overwriting;

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
