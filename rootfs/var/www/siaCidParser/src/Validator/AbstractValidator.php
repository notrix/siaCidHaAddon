<?php

namespace Notrix\SiaCid\Validator;

/**
 * Class AbstractValidator
 */
abstract class AbstractValidator
{
    /**
     * @param string $rawData
     *
     * @return bool
     */
    abstract public function isValid($rawData);
}
