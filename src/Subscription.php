<?php

namespace App;

class Subscription
{
    const PERIOD_MONTHLY = 'monthly';
    const PERIOD_YEARLY = 'yearly';
    
    const STATUS_ACTIVE = 1;
    const STATUS_DELETED = 2;
    const STATUS_SUSPENDED = 3;        
    
    private $id;
    private $invoiceId;
    private $period;
    /**
     *
     * @var \DateTime
     */
    private $from;
    /**
     *
     * @var \DateTime
     */    
    private $to;
    private $status;
    
    public function __construct()
    {
        $this->id = uniqid();
        $this->from = new \DateTime();
        $this->to = new \DateTime();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getInvoiceId()
    {
        return $this->invoiceId;
    }

    public function getPeriod()
    {
        return $this->period;
    }

    public function getFrom()
    {
        return $this->from;
    }

    public function getTo()
    {
        return $this->to;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function setInvoiceId($invoiceId)
    {
        $this->invoiceId = $invoiceId;
        return $this;
    }

    public function setPeriod($period)
    {
        $this->period = $period;
        return $this;
    }

    public function setFrom(\DateTime $from)
    {
        $this->from = $from;
        return $this;
    }

    public function setTo(\DateTime $to)
    {
        $this->to = $to;
        return $this;
    }

    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

   
}