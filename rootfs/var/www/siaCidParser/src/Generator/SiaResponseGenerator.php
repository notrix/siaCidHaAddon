<?php

namespace Notrix\SiaCid\Generator;
use mermshaus\CRC\AbstractCRC;
use Notrix\SiaCid\Exception\InvalidFormatException;
use Notrix\SiaCid\Validator\AbstractValidator;

/**
 * Class SiaResponseGenerator
 */
class SiaResponseGenerator extends AbstractResponseGenerator
{
    /**
     * @var AbstractCRC
     */
    protected $crc;

    /**
     * @var AbstractValidator
     */
    protected $validator;

    /**
     * SiaCidParser constructor
     *
     * @param AbstractValidator $validator
     * @param AbstractCRC       $crc
     */
    public function __construct(AbstractValidator $validator, AbstractCRC $crc)
    {
        $this->validator = $validator;
        $this->crc = $crc;
    }

    /**
     * @param string $rawData
     *
     * @return string
     *
     * @throws InvalidFormatException
     */
    public function getAckResponse($rawData)
    {
        return $this->getResponse($rawData, 'ACK');
    }

    /**
     * @param string $rawData
     *
     * @return string
     *
     * @throws InvalidFormatException
     */
    public function getNakResponse($rawData)
    {
        return $this->getResponse($rawData, 'NAK');
    }

    /**
     * @param string $rawData
     *
     * @return string
     *
     * @throws InvalidFormatException
     */
    public function getDuhResponse($rawData)
    {
        return $this->getResponse($rawData, 'DUH');
    }

    /**
     * @param string $rawData
     *
     * @return string
     *
     * @throws InvalidFormatException
     */
    protected function getResponse($rawData, $status)
    {
        if (
            !$this->validator->isValid($rawData) ||
            !preg_match('/"(?<rec>\d+)L(?<line>\d+)#(?<account>\d+)\[[^\]]*\](?<time>_?)/', $rawData, $matches)
        ) {
            throw new InvalidFormatException('Invalid SIA CID format');
        }

        $ackBody = sprintf(
            '"%s"%sL%d#%s[]%s',
            $status,
            $matches['rec'],
            $matches['line'],
            $matches['account'],
            $matches['time'] ? date('_H:i:s,m-d-Y') : ''
        );

        $ackLen = sprintf("%04s", dechex(strlen($ackBody)));

        $this->crc->reset();
        $this->crc->update($ackBody);

        $ackCrc = $this->crc->finish();

        return chr(10) . $ackCrc . $ackLen . $ackBody . chr(13);
    }
}
