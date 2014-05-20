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
     * @param   array               $options    Additional options of the ovewrwriting.
     *                                          This is non-standard argument, meaning that each overwriter
     *                                          supports only it's own options (if any). Every supported option
     *                                          should be also settable using transformer's constructor.
     *
     * @return  array|\ArrayAccess
     */
    public function overwrite($input, &$output, array $options = array());
}
