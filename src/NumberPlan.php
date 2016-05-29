<?php

namespace App;

class NumberPlan
{
    const MARKER_NUMBER = '%N';
    const MARKER_MONTH = '%m';
    const MARKER_YEAR = '%Y';
    
    const PERIOD_MONTHLY = 1;
    const PERIOD_YEARLY = 2;
    const PERIOD_CONTINUOUSLY = 3;
    
    private $id;
    private $template;
    private $periodType = self::PERIOD_MONTHLY;
    private $dafault = false;

    public function getId()
    {
        return $this->id;
    }

    public function getTemplate()
    {
        return $this->template;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function setTemplate($template)
    {
        $this->template = $template;
        return $this;
    }

    public function getPeriodType()
    {
        return $this->periodType;
    }

    public function isDefault()
    {
        return $this->dafault;
    }

    public function setPeriodType($periodType)
    {
        $this->periodType = $periodType;
        return $this;
    }

    public function setDafault($dafault)
    {
        $this->dafault = $dafault;
        return $this;
    }

        
    
    /**
     * 
     * @param int $number
     * @param \DateTime $date
     */    
    public function prepare($number, $date = null)
    {
        if(!$number){
            return $this->template;
        }
        $Y = self::MARKER_YEAR;
        $m = self::MARKER_MONTH;
        $N = $number;
        if($date){
            $Y = $date->format('Y');
            $m = $date->format('m');
        }
        $output = $this->template;
        $output = str_replace(self::MARKER_YEAR, $Y, $output);
        $output = str_replace(self::MARKER_MONTH, $m, $output);
        $output = str_replace(self::MARKER_NUMBER, $N, $output);
        
        return $output;
    }
}