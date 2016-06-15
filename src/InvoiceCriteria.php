<?php

namespace App;

class InvoiceCriteria
{
    private $year;
    private $month;
    private $numberPlan;
    
    public function __construct()
    {
        
    }
    
    public function getYear()
    {
        return $this->year;
    }

    public function getMonth()
    {
        return $this->month;
    }

    public function getNumberPlan()
    {
        return $this->numberPlan;
    }

    public function setYear($year)
    {
        $this->year = $year;
        return $this;
    }

    public function setMonth($month)
    {
        $this->month = $month;
        return $this;
    }

    public function setNumberPlan($numberPlan)
    {
        $this->numberPlan = $numberPlan;
        return $this;
    }
}

