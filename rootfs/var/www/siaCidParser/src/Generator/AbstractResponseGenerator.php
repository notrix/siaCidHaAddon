<?php

namespace Notrix\SiaCid\Generator;

/**
 * Class AbstractGenerator
 */
abstract class AbstractResponseGenerator
{
    /**
     * @param string $rawData
     *
     * @return string
     */
    abstract public function getAckResponse($rawData);

    /**
     * @param string $rawData
     *
     * @return string
     */
    abstract public function getNakResponse($rawData);

    /**
     * @param string $rawData
     *
     * @return string
     */
    abstract public function getDuhResponse($rawData);
}
