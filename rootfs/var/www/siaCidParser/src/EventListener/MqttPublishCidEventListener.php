<?php

namespace Notrix\SiaCid\EventListener;

use Notrix\SiaCid\CidEvent;
use Notrix\SiaCid\Exception\MqttTransportException;
use PhpMQTT\PhpMQTT;
use Psr\Log\LoggerInterface;

/**
 * Class MqttPublishCidEventListener
 */
class MqttPublishCidEventListener
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
    protected $topic;

    /**
     * @var array|null
     */
    protected $eventFilter;

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
     * @param string          $topic
     * @param array|null      $eventFilter
     * @param LoggerInterface $logger
     */
    public function __construct(
        PhpMQTT $client,
        $username,
        $password,
        $topic,
        array $eventFilter = null,
        LoggerInterface $logger = null
    ) {
        $this->client = $client;
        $this->username = $username;
        $this->password = $password;
        $this->topic = $topic;
        $this->eventFilter = $eventFilter;
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

        if ($this->eventFilter && !in_array($cid->getEvent(), $this->eventFilter)) {
            $this->debug('Event filtered. Not publishing', ['cid_event' => $cid->getEvent()]);

            return;
        }

        if ($this->client->connect(true, null, $this->username, $this->password)) {
            $this->debug('Connected to mqtt service');
            $this->client->publish($this->topic, json_encode($cid), 0);
            $this->debug('Published mqtt message', ['topic' => $this->topic]);
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
    protected function debug($message, array $context = [])
    {
        if ($this->logger) {
            $this->logger->debug($message, $context);
        }
    }
}
