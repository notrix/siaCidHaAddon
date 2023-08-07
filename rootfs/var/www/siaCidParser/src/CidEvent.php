<?php

namespace Notrix\SiaCid;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class CidEvent
 */
class CidEvent extends Event
{
    const EVENT_RECEIVED = 'notrix.cid.event_received';

    /**
     * @var Cid
     */
    protected $cid;

    /**
     * CidEvent constructor
     *
     * @param Cid|null $cid
     */
    public function __construct(Cid $cid = null)
    {
        $this->cid = $cid;
    }

    /**
     * @return Cid
     */
    public function getCid()
    {
        return $this->cid;
    }

    /**
     * @param Cid $cid
     *
     * @return static
     */
    public function setCid(Cid $cid)
    {
        $this->cid = $cid;

        return $this;
    }
}