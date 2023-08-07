<?php

namespace Notrix\SiaCid\EventListener;

use Notrix\SiaCid\CidEvent;
use Notrix\SiaCid\Exception\MqttTransportException;
use PhpMQTT\PhpMQTT;
use Psr\Log\LoggerInterface;

/**
 * Class MqttPublishCidTransformedEventListener
 */
class MqttPublishCidTransformedEventListener
{
    /**
     * @var PhpMQTT
     */
    protected $client;

    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var string
     */
    protected $topicPrefix;

    /**
     * @var string
     */
    protected $topicLog;

    /**
     * @var array|null
     */
    protected $eventMap;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * MqttPublishCidEventListener constructor
     *
     * @param PhpMQTT         $client
     * @param string          $username
     * @param string          $password
     * @param string          $topicPrefix
     * @param string          $topicLog
     * @param array|null      $eventMap
     * @param LoggerInterface $logger
     */
    public function __construct(
        PhpMQTT $client,
        $username,
        $password,
        $topicPrefix,
        $topicLog,
        array $eventMap = null,
        LoggerInterface $logger = null
    ) {
        $this->client = $client;
        $this->username = $username;
        $this->password = $password;
        $this->topicPrefix = $topicPrefix;
        $this->topicLog = $topicLog;
        $this->eventMap = $eventMap;
        $this->logger = $logger;
    }

    /**
     * @param CidEvent $event
     *
     * @throws MqttTransportException
     */
    public function publish(CidEvent $event)
    {
        $this->debug(__METHOD__);

        $cid = $event->getCid();
        if ($cid->isEmpty()) {
            $this->debug('Empty cid. Not publishing');

            return;
        }

        if ($this->client->connect(true, null, $this->username, $this->password)) {
            if ($this->eventMap && !isset($this->eventMap[$cid->getEvent()])) {
                $this->debug('Event filtered. Not publishing', ['cid_event' => $cid->getEvent()]);

                $this->client->publish($this->topicLog, sprintf('%s-%s', $cid->getEvent(), $cid->getStatus()), 0);
                $this->client->close();

                return;
            }

            list($topic, $statuses) = $this->eventMap[$cid->getEvent()];
            if ($statuses && !isset($statuses[$cid->getStatus()])) {
                $this->debug('Status filtered. Not publishing', ['cid_status' => $cid->getStatus()]);

                $this->client->publish($this->topicLog, sprintf('%s-%s', $cid->getEvent(), $cid->getStatus()), 0);
                $this->client->close();

                return;
            }

            $topic = sprintf('%s/%s', $this->topicPrefix, $topic);
            $message = $statuses[$cid->getStatus()];

            $this->debug('Connected to mqtt service');
            $this->client->publish($topic, $message, 0);
            $this->debug('Published mqtt message', ['topic' => $topic, 'message' => $message]);
            $this->client->close();
            $this->debug('Closed connected to mqtt service');
        } else {
            throw new MqttTransportException('Could not connect to mqtt service');
        }
    }

    /**
     * @param mixed $message
     * @param array $context
     */
    private function debug($message, array $context = [])
    {
        if ($this->logger) {
            $this->logger->debug($message, $context);
        }
    }
}
