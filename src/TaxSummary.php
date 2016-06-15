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
        $this->totalNet = number_format((float)$totalNet, 2, '.', '');;
        return $this;
    }

    public function setTotalTax($totalTax)
    {
        $this->totalTax = number_format((float)$totalTax, 2, '.', '');;
        return $this;
    }

    public function setTotalGross($totalGross)
    {
        $this->totalGross = number_format((float)$totalGross, 2, '.', '');;
        return $this;
    }

    
}