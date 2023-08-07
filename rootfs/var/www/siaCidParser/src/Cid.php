<?php

namespace Notrix\SiaCid;

/**
 * Class Cid
 */
class Cid implements \JsonSerializable
{
    /**
     * @var int
     */
    protected $account;

    /**
     * @var int
     */
    protected $status;

    /**
     * @var int
     */
    protected $event;

    /**
     * @var int
     */
    protected $zone;

    /**
     * @var int
     */
    protected $user;

    /**
     * @var \DateTimeInterface
     */
    protected $time;

    /**
     * @return int
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * @param int $account
     *
     * @return static
     */
    public function setAccount($account)
    {
        $this->account = (int) $account;

        return $this;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param int $status
     *
     * @return static
     */
    public function setStatus($status)
    {
        $this->status = (int) $status;

        return $this;
    }

    /**
     * @return int
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param int $event
     *
     * @return static
     */
    public function setEvent($event)
    {
        $this->event = (int) $event;

        return $this;
    }

    /**
     * @return int
     */
    public function getZone()
    {
        return $this->zone;
    }

    /**
     * @param int $zone
     *
     * @return static
     */
    public function setZone($zone)
    {
        $this->zone = (int) $zone;

        return $this;
    }

    /**
     * @return int
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param int $user
     *
     * @return static
     */
    public function setUser($user)
    {
        $this->user = (int) $user;

        return $this;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @param \DateTimeInterface $time
     *
     * @return static
     */
    public function setTime(\DateTimeInterface $time)
    {
        $this->time = $time;

        return $this;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return !$this->account && !$this->user && !$this->event && !$this->zone;
    }

    /**
     * @return array|null
     */
    public function jsonSerialize()
    {
        if ($this->isEmpty()) {
            return null;
        }

        $data = get_object_vars($this);
        if ($this->time) {
            $data['time'] = $this->time->format('Y-m-d H:i:s');
        }

        return array_filter($data);
    }
}