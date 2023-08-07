<?php

namespace Notrix\SiaCid\Exception;

/**
 * Class UnknownDataFormatException
 */
class UnknownDataFormatException extends SiaCidException
{
    /**
     * @var string
     */
    protected $data;

    /**
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param string $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }
}