<?php

namespace App;

class Invoice
{

    const STATUS_DRAFT = 1;
    const STATUS_DELETED = 2;
    const STATUS_SENT = 3;
    
    const TAX_DEFAULT_ID = 1;
    

    private $id;
    private $typeInfo;
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
     * @var InvoiceItem[] 
     */
    private $invoiceItems = [];
    private $totalNet = 0;
    private $totalTax = 0;
    private $totalGross = 0;

    /**
     *
     * @var TaxSummary[]
     */
    private $taxSummaries = [];
    private $issuer;
    private $totalInWords;
    private $info;

    public function __construct()
    {
        $this->id = uniqid();

        $this->createDate = new \DateTime();
        $this->sellDate = new \DateTime();
        $this->paymentDate = new \DateTime();
        $this->seller = new Interesant();
        $this->buyer = new Interesant();
        $this->status = self::STATUS_DRAFT;
    }

    public function initDefaults()
    {
        $this->createDate = new \DateTime();
        $this->sellDate = new \DateTime();
        $this->paymentDate = new \DateTime('+14 days');
        $this->paymentType = 1;
        
        $item = new InvoiceItem();
        $tax = new Tax();
        $tax->setId(self::TAX_DEFAULT_ID);
        $item->setTax($tax);
        $this->addInvoiceItem($item);

        $npRepo = new NumberPlanRepo();
        $repo = new InvoiceRepo();
        $plan = $npRepo->findDefault();
        if (!$plan) {
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

    public function getInvoiceItems()
    {
        return $this->invoiceItems;
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

    public function setInvoiceItems(array $invoiceItems)
    {
        $this->invoiceItems = $invoiceItems;
        return $this;
    }

    public function addInvoiceItem($invoiceItem)
    {
        $this->invoiceItems[] = $invoiceItem;
        return $this;
    }
    
    public function delInvoiceItem($index)
    {
        if(array_key_exists($index, $this->invoiceItems)){
            unset($this->invoiceItems[$index]);
        }
        return $this;
    }

    public function reindexInvoiceItems()
    {
        $this->invoiceItems = array_values($this->invoiceItems);
        return $this;
    }

    public function setTotalNet($totalNet)
    {
        $this->totalNet = number_format((float)$totalNet, 2, '.', '');
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
    
    public function getTotalInWords()
    {
        return $this->totalInWords;
    }

    public function getInfo()
    {
        return $this->info;
    }

    public function setTotalInWords($totalInWords)
    {
        $this->totalInWords = $totalInWords;
        return $this;
    }

    public function setInfo($info)
    {
        $this->info = $info;
        return $this;
    }

    public function getTypeInfo()
    {
        return $this->typeInfo;
    }

    public function setTypeInfo($typeInfo)
    {
        $this->typeInfo = $typeInfo;
        return $this;
    }
}
