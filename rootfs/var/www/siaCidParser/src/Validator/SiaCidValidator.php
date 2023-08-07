<?php

namespace Notrix\SiaCid\Validator;

use mermshaus\CRC\AbstractCRC;
use Psr\Log\LoggerInterface;

/**
 * Class SiaCidValidator
 */
class SiaCidValidator extends AbstractValidator
{
    /**
     * @var AbstractCRC
     */
    protected $crc;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * SiaCidValidator constructor
     *
     * @param AbstractCRC     $crc
     * @param LoggerInterface $logger
     */
    public function __construct(AbstractCRC $crc, LoggerInterface $logger = null)
    {
        $this->crc = $crc;
        $this->logger = $logger;
    }

    /**
     * @param string $rawData
     *
     * @return bool
     */
    public function isValid($rawData)
    {
        $this->debug(__METHOD__);

        if (ord($rawData[0]) != 10 || ord(substr($rawData, -1)) != 13) {
            $this->debug('Invalid first and last characters');

            return false;
        }
        $rawData = substr($rawData, 1,-1);
        if (!preg_match('/^(?<crc>.+)(?<length>[0-9A-F]{4})(?<data>"[A-Z\-]+"\d{4}L\d+#\d{4}\[[^\]]*\])(?<time>_\d{2}:\d{2}:\d{2},\d{2}-\d{2}-\d{4})?$/', $rawData, $matches)) {
            $this->debug('Invalid message format');

            return false;
        }

        $data = $matches['data'];
        if (!empty($matches['time'])) {
            $data .= $matches['time'];
        }

        if (strlen($data) != hexdec($matches['length'])) {
            $this->debug('Invalid data length');

            return false;
        }

        $this->crc->reset();
        $this->crc->update($data);
        $crc = $this->crc->finish();

        if ($matches['crc'] != $crc) {
            $this->debug('Invalid crc signature');

            return false;
        }

        $this->debug('Message validated successfully');

        return true;
    }

    /**
     * @param string $message
     * @param array  $context
     */
    protected function debug($message, array $context = [])
    {
        if ($this->logger) {
            $this->logger->debug($message, $context);
        }
    }
}
