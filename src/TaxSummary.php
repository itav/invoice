<?php

namespace App;

class TaxSummary
{
    private $taxName;
    private $totalNet;
    private $totalTax;
    private $totalGross;
    
    public function getTaxName()
    {
        return $this->taxName;
    }

    public function getTotalNet()
    {
        return $this->totalNet;
    }

    public function getTotalTax()
    {
        return $this->totalTax;
    }

    public function getTotalGross()
    {
        return $this->totalGross;
    }

    public function setTaxName($taxName)
    {
        $this->taxName = $taxName;
        return $this;
    }

    public function setTotalNet($totalNet)
    {
        $this->totalNet = $totalNet;
        return $this;
    }

    public function setTotalTax($totalTax)
    {
        $this->totalTax = $totalTax;
        return $this;
    }

    public function setTotalGross($totalGross)
    {
        $this->totalGross = $totalGross;
        return $this;
    }

    
}