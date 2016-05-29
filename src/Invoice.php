<?php

namespace App;

class Invoice
{
    const STATUS_DRAFT = 1;
    const STATUS_DELETED = 2;
    const STATUS_SENT = 3;

    private $id;
    private $number;
    private $displayNumber;
    private $numberPlanId;
    /**
     *
     * @var \DateTime
     */
    private $createDate;
    /**
     *
     * @var \DateTime
     */    
    private $sellDate;
    /**
     *
     * @var \DateTime
     */    
    private $paymentDate;
    private $paymentType;
    /**
     *
     * @var Interesant
     */
    private $seller;
    /**
     *
     * @var Interesant
     */
    private $buyer;
    /**
     *
     * @var Address
     */
    private $postAdres;
    /**
     *
     * @var Product[] 
     */
    private $products = [];
    private $totalNet;
    private $totalTax;
    private $totalGross;
    /**
     *
     * @var TaxSummary[]
     */
    private $taxSummaries = [];
    private $issuer;

    
    public function __construct()
    {
        $this->id = uniqid();
        
        $this->createDate = new \DateTime();
        $this->sellDate = new \DateTime();
        $this->paymentDate = new \DateTime();
        //$this->seller = new Interesant();
        //$this->buyer = new Interesant();
        $this->status = self::STATUS_DRAFT;
    }
    
    public function initDefaults()
    {
        $this->createDate = new \DateTime();
        $this->sellDate = new \DateTime();
        $this->paymentDate = new \DateTime('+14 days');
        $this->paymentType = 1;
        
        $npRepo = new NumberPlanRepo();
        $repo = new InvoiceRepo();
        $plan = $npRepo->findDefault();
        if(!$plan){
            return;
        }
        
        $this->numberPlanId = $plan->getId();
        $this->number = $repo->getNextFreeNumber($plan, $this->createDate);
        $this->displayNumber = $plan->prepare($this->number, $this->createDate);
    }    
    
    
    public function getId()
    {
        return $this->id;
    }

    public function getNumber()
    {
        return $this->number;
    }

    public function getDisplayNumber()
    {
        return $this->displayNumber;
    }

    public function getNumberPlanId()
    {
        return $this->numberPlanId;
    }

    public function getCreateDate()
    {
        return $this->createDate;
    }

    public function getSellDate()
    {
        return $this->sellDate;
    }

    public function getPaymentDate()
    {
        return $this->paymentDate;
    }

    public function getPaymentType()
    {
        return $this->paymentType;
    }

    public function getSeller()
    {
        return $this->seller;
    }

    public function getBuyer()
    {
        return $this->buyer;
    }

    public function getPostAdres()
    {
        return $this->postAdres;
    }

    public function getProducts()
    {
        return $this->products;
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

    public function getTaxSummaries()
    {
        return $this->taxSummaries;
    }

    public function getIssuer()
    {
        return $this->issuer;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function setNumber($number)
    {
        $this->number = $number;
        return $this;
    }

    public function setDisplayNumber($displayNumber)
    {
        $this->displayNumber = $displayNumber;
        return $this;
    }

    public function setNumberPlanId($numberPlanId)
    {
        $this->numberPlanId = $numberPlanId;
        return $this;
    }

    public function setCreateDate($createDate)
    {
        $this->createDate = $createDate;
        return $this;
    }

    public function setSellDate($sellDate)
    {
        $this->sellDate = $sellDate;
        return $this;
    }

    public function setPaymentDate($paymentDate)
    {
        $this->paymentDate = $paymentDate;
        return $this;
    }

    public function setPaymentType($paymentType)
    {
        $this->paymentType = $paymentType;
        return $this;
    }

    public function setSeller(Interesant $seller)
    {
        $this->seller = $seller;
        return $this;
    }

    public function setBuyer(Interesant $buyer)
    {
        $this->buyer = $buyer;
        return $this;
    }

    public function setPostAdres(Address $postAdres)
    {
        $this->postAdres = $postAdres;
        return $this;
    }

    public function setProducts(array $products)
    {
        $this->products = $products;
        return $this;
    }

    public function addProduct($product)
    {
        $this->products[] = $product;
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

    public function setTaxSummaries(array $taxSummaries)
    {
        $this->taxSummaries = $taxSummaries;
        return $this;
    }
    
    public function addTaxSummary($taxSummary)
    {
        $this->taxSummaries[] = $taxSummary;
        return $this;
    }
    
    public function setIssuer($issuer)
    {
        $this->issuer = $issuer;
        return $this;
    }

   
}
