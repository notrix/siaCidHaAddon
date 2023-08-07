<?php

namespace Notrix\SiaCid\Parser;

use Notrix\SiaCid\Cid;
use Notrix\SiaCid\Exception\InvalidFormatException;
use Notrix\SiaCid\Validator\AbstractValidator;

/**
 * Class SiaCidParser
 */
class SiaCidParser extends AbstractParser
{
    /**
     * @var AbstractValidator
     */
    protected $validator;

    /**
     * SiaCidParser constructor
     *
     * @param AbstractValidator $validator
     */
    public function __construct(AbstractValidator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param string $rawData
     *
     * @return Cid
     *
     * @throws InvalidFormatException
     */
    public function parse($rawData)
    {
        if (!$this->validator->isValid($rawData)) {
            throw new InvalidFormatException('Invalid SIA CID format');
        }

        $cid = new Cid();

        $data = $this->getDataPart($rawData);
        if ($data && preg_match('/#?(?<account>\d+)\|(?<status>\d)(?<event>\d+)\s(?<zone>\d+)\s(?<user>\d+)/', $data, $matches)) {
            $cid->setAccount($matches['account'])
                ->setStatus($matches['status'])
                ->setEvent($matches['event'])
                ->setZone($matches['zone'])
                ->setUser($matches['user'])
                ->setTime($this->getTime($rawData));
        }

        return $cid;
    }

    /**
     * @param string $rawData
     *
     * @return string|null
     */
    protected function getDataPart($rawData)
    {
        if (preg_match('/\[(?<data>[^\]]+)\]/', $rawData, $match)) {
            return $match['data'];
        }

        return null;
    }

    /**
     * @param string $rawData
     *
     * @return \DateTime
     */
    protected function getTime($rawData)
    {
        $timeZone = new \DateTimeZone('UTC');

        // _21:32:13,07-25-2017
        if (preg_match('/_(?<timestamp>\d{2}:\d{2}:\d{2},\d{2}-\d{2}-\d{4})$/', $rawData, $matches)) {
            return \DateTime::createFromFormat('H:i:s,m-d-Y', $matches['timestamp'], $timeZone);
        }

        return new \DateTime('now', $timeZone);
    }
}
