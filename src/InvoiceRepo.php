<?php

namespace App;

use Itav\Component\Serializer\Serializer;

class InvoiceRepo
{

    private $file = __DIR__ . '/storage/invoice.json';
    private $serializer;

    public function __construct()
    {
        $this->serializer = new Serializer();
    }

    /**
     * 
     * @param \App\Invoice $invoice
     * @return string | bool
     */
    public function save(Invoice $invoice)
    {
        $rows = json_decode(file_get_contents($this->file), true);
        $data = $this->serializer->normalize($invoice);
        $foundKey = false;
        foreach($rows as $key => $item){
            if($item['id'] == $invoice->getId()){
                $foundKey = $key;
                break;
            }
        }
        if(false !== $foundKey){
            unset($rows[$foundKey]);
        }
        $rows[] = $data;
        
        file_put_contents($this->file, json_encode($rows));
        return $invoice->getId();        
    }

    /**
     * 
     * @param int $id
     * @return \App\Invoice
     */
    public function find($id)
    {
        $invoice = new Invoice();
        $rows = json_decode(file_get_contents($this->file), true);
        foreach($rows as $item){
            if($item['id'] == $id){
                $this->serializer->unserialize($item, Invoice::class, $invoice);
                return $invoice;
            }
        } 
        return $invoice;
    }
    /**
     * 
     * @return Invoice[]
     */
    public function findAll()
    {
        $rows = json_decode(file_get_contents($this->file), true);
        $results = [];
        foreach($rows as $item){
            $results[] =  $this->serializer->unserialize($item, Invoice::class);
        } 
        return $results;
    } 
    
    /**
     * 
     * @param int $id
     * @return \App\Subscription
     */
    public function delete($id)
    {
        $rows = json_decode(file_get_contents($this->file), true);
        $foundKey = false;
        foreach($rows as $key => $item){
            if($item['id'] == $id){
                $foundKey = $key;
                break;
            }
        }
        if(false !== $foundKey){
            unset($rows[$foundKey]);
        }else{
            return false;
        }        
        file_put_contents($this->file, json_encode($rows));
        return true;
    }        
    
    /**
     * 
     * @param NumberPlan $plan
     * @param \DateTime $date
     * @return int
     */
    public function getNextFreeNumber($plan, $date = null)
    {
        if(!($plan instanceof NumberPlan)){
            return null;
        }
        if(!$date){
            $date = new \DateTime();
        }        
        $max = 1;
        $type = $plan->getPeriodType();
        
        if($type == NumberPlan::PERIOD_MONTHLY){
            
            $invoices = $this->findByMonthAndPlan($date, $plan);
        }
        if($type == NumberPlan::PERIOD_YEARLY){
            
            $invoices = $this->findByYearAndPlan($date, $plan);
        }
        if($type == NumberPlan::PERIOD_CONTINUOUSLY){
            
            $invoices = $this->findByNumberPlan($plan);
        }
        foreach ($invoices as $invoice){
            if($invoice->getNumber() >= $max){
                $max = $invoice->getNumber() + 1;
            }
        }
        return $max;
    }
    /**
     * 
     * @param NumberPlan $plan
     * @return \App\Invoice[]
     */
    public function findByNumberPlan($plan)
    {
        $invoices = [];
        if(!($plan instanceof NumberPlan)){
            return $invoices;
        }
        $id = $plan->getId();
        $rows = json_decode(file_get_contents($this->file), true);
        foreach($rows as $item){
            $invoice = new Invoice();
            $this->serializer->unserialize($item, Invoice::class, $invoice);
            if ($invoice->getNumberPlanId() == $id) {
                $invoices[] = $invoice;
            }
        } 
        return $invoices;
    }
    /**
     * 
     * @param \DateTime $date
     * @return \App\Invoice[]
     */
    public function findByYear($date)
    {
        $invoices = [];
        if(!($date instanceof \DateTime)){
            return $invoices;
        }
        $year = $date->format('Y');
        $rows = json_decode(file_get_contents($this->file), true);
        foreach($rows as $item){
            $invoice = new Invoice();
            $this->serializer->unserialize($item, Invoice::class, $invoice);
            $createDate = $invoice->getCreateDate();
            if ($createDate->format('Y') == $year) {
                $invoices[] = $invoice;
            }
        } 
        return $invoices;
    }
    /**
     * 
     * @param \DateTime $date
     * @return \App\Invoice[]
     */
    public function findByMonth($date)
    {
        $invoices = [];
        if(!($date instanceof \DateTime)){
            return $invoices;
        }
        $year = $date->format('Y');
        $month = $date->format('m');
        $rows = json_decode(file_get_contents($this->file), true);
        foreach($rows as $item){
            $invoice = new Invoice();
            $this->serializer->unserialize($item, Invoice::class, $invoice);
            $createDate = $invoice->getCreateDate();
            if ($createDate->format('Y') == $year && $createDate->format('m') == $month) {
                $invoices[] = $invoice;
            }
        }
        return $invoices;
    }
    
        /**
     * 
     * @param InvoiceCriteria $criteria
     * @return \App\Invoice[]
     */
    public function findByCriteria($criteria)
    {
        $invoices = [];
        $month = $criteria->getMonth() ? $criteria->getMonth() : null;
        $year = $criteria->getYear() ? $criteria->getYear(): null;
        $np = $criteria->getNumberPlan() ? $criteria->getNumberPlan() : null;
        $rows = json_decode(file_get_contents($this->file), true);
        foreach($rows as $item){
            $invoice = new Invoice();
            $this->serializer->unserialize($item, Invoice::class, $invoice);
            $createDate = $invoice->getCreateDate();
            if ($year && $createDate->format('Y') != $year) {
                continue;
            }
            if ($month && $createDate->format('m') != $month) {
                continue;
            }
            if ($np && $invoice->getNumberPlanId() != $np) {
                continue;
            } 
            $invoices[] = $invoice;
        }
        return $invoices;
    }
    
    /**
     * 
     * @param \DateTime $date
     * @param NumberPlan $plan
     * @return \App\Invoice[]
     */
    public function findByYearAndPlan($date, $plan)
    {
        $invoices = [];
        if(!($date instanceof \DateTime)){
            return $invoices;
        }
        if(!($plan instanceof NumberPlan)){
            return $invoices;
        }        
        $year = $date->format('Y');
        $id = $plan->getId();
        
        $rows = json_decode(file_get_contents($this->file), true);
        foreach($rows as $item){
            $invoice = new Invoice();
            $this->serializer->unserialize($item, Invoice::class, $invoice);
            $createDate = $invoice->getCreateDate();
            if ($createDate->format('Y') == $year && $invoice->getNumberPlanId() == $id) {
                $invoices[] = $invoice;
            }
        }
        return $invoices;        
    }    
    /**
     * 
     * @param \DateTime $date
     * @param NumberPlan $plan
     * @return \App\Invoice[]
     */
    public function findByMonthAndPlan($date, $plan)
    {
        $invoices = [];
        if(!($date instanceof \DateTime)){
            return $invoices;
        }
        if(!($plan instanceof NumberPlan)){
            return $invoices;
        }        
        $year = $date->format('Y');
        $month = $date->format('m');
        $id = $plan->getId();
        
        $rows = json_decode(file_get_contents($this->file), true);
        foreach($rows as $item){
            $invoice = new Invoice();
            $this->serializer->unserialize($item, Invoice::class, $invoice);
            $createDate = $invoice->getCreateDate();
            if ($createDate->format('Y') == $year && $createDate->format('m') == $month && $invoice->getNumberPlanId() == $id) {
                $invoices[] = $invoice;
            }
        }
        return $invoices;        
    }     
    
    /**
     * 
     * @param Invoice $model
     * @return \App\Invoice[]
     */
    public function findByBuyerDatePlanAndPrice($model)
    {
        $invoices = [];
        if(!($model instanceof Invoice)){
            return $invoices;
        }
        
        $rows = json_decode(file_get_contents($this->file), true);
        foreach($rows as $item){
            $invoice = new Invoice();
            $this->serializer->unserialize($item, Invoice::class, $invoice);
            if ($invoice->getBuyer()->getId() == $model->getBuyer()->getId() 
                && $invoice->getNumberPlanId() == $model->getNumberPlanId()
                && $invoice->getTotalGross() == $model->getTotalGross()
                && $invoice->getCreateDate() == $model->getCreateDate()) {
                $invoices[] = $invoice;
            }
        }
        return $invoices;         
    }       
}
