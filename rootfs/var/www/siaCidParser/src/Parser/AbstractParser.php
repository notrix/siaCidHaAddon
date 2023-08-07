<?php

namespace Notrix\SiaCid\Parser;

use Notrix\SiaCid\Cid;

/**
 * Class AbstractParser
 */
abstract class AbstractParser 
{
    /**
     * @param string $rawData
     *
     * @return Cid
     */
    abstract public function parse($rawData);
}
