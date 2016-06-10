<?php

namespace App;

class InvoiceItem
{
    const STATUS_ACTIVE = 1;
    const STATUS_DELETED = 2;

    private $ordinal;
    private $description = '';
    private $quantity = 1;
    private $unit = 'szt.';
    private $netPrice = 0;
    private $netValue = 0;
    private $taxValue = 0;
    private $grossValue = 0;
    /**
     *
     * @var Tax
     */
    private $tax;
    private $productId;
    private $status;
    
    public function __construct()
    {
        $this->id = uniqid();
        $this->status = self::STATUS_ACTIVE;
        $this->tax = new Tax();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
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

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getTaxValue()
    {
        return $this->taxValue;
    }

    public function getPriceGross()
    {
        return $this->priceGross;
    }

    public function getTax()
    {
        return $this->tax;
    }

    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    public function setTaxValue($taxValue)
    {
        $this->taxValue = $taxValue;
        return $this;
    }

    public function setPriceGross($priceGross)
    {
        $this->priceGross = $priceGross;
        return $this;
    }

    /**
     * 
     * @param Tax $tax
     * @return \App\Product
     */
    public function setTax($tax) {
        $this->tax = $tax;
        return $this;
    } 
    
    public function getOrdinal()
    {
        return $this->ordinal;
    }

    public function getQuantity()
    {
        return $this->quantity;
    }

    public function getUnit()
    {
        return $this->unit;
    }

    public function getNetPrice()
    {
        return $this->netPrice;
    }

    public function getNetValue()
    {
        return $this->netValue;
    }

    public function getGrossValue()
    {
        return $this->grossValue;
    }

    public function getProductId()
    {
        return $this->productId;
    }

    public function setOrdinal($ordinal)
    {
        $this->ordinal = $ordinal;
        return $this;
    }

    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
        return $this;
    }

    public function setUnit($unit)
    {
        $this->unit = $unit;
        return $this;
    }

    public function setNetPrice($netPrice)
    {
        $this->netPrice = $netPrice;
        return $this;
    }

    public function setNetValue($netValue)
    {
        $this->netValue = $netValue;
        return $this;
    }

    public function setGrossValue($grossValsue)
    {
        $this->grossValue = $grossValsue;
        return $this;
    }

    public function setProductId($productId)
    {
        $this->productId = $productId;
        return $this;
    }

 
}
