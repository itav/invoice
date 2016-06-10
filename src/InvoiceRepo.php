<?php

namespace App;

use Itav\Component\Serializer\Serializer;

class InvoiceRepo
{

    private $file = __DIR__ . '/storage/invoice.csv';
    private $fileOld = __DIR__ . '/storage/invoice.csv.old';
    private $fileTemp = __DIR__ . '/storage/invoice.csv.temp';

    public function save(Invoice $invoice)
    {
        if (!file_exists($this->file)) {
            return false;
        }
        $serializer = new Serializer();
        $id = $invoice->getId();
        $rows = [];
        file_put_contents($this->fileTemp, '');
        $handle = fopen($this->file, 'r+');
        $found = false;
        while(($line = fgets($handle, 4096)) !== false){
            $item = new Invoice();
            $itemData = $this->unescape(str_getcsv($line)[0]);
            $item = $serializer->unserialize($itemData, Invoice::class, $item);
            if ($item->getId() !== $id) {
                file_put_contents($this->fileTemp, $line, FILE_APPEND);
                continue;
            }
            $found = true;
        }
        fclose($handle);
        $data = $this->escape(json_encode($serializer->normalize($invoice)));
        if(!$found){
            if(($result = file_put_contents($this->file, $data, FILE_APPEND)) === false){
                return false;
            }
            return $id;
        }
        if(($result = file_put_contents($this->fileTemp, $data, FILE_APPEND)) === false){
            return false;
        }
        rename($this->file, $this->fileOld);
        rename($this->fileTemp, $this->file);        
        return $id;
    }

    /**
     * 
     * @param int $id
     * @return \App\Invoice
     */
    public function find($id)
    {
        $serializer = new Serializer();
        $handle = fopen($this->file, 'r+');
        while(($line = fgets($handle, 4096)) !== false){
            $item = new Invoice();
            $itemData = $this->unescape(str_getcsv($line)[0]);
            $item = $serializer->unserialize($itemData, Invoice::class, $item);
            if ($item->getId() === $id) {
                fclose($handle);
                return $item;
            }
        }
        return null;
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
        $result = 1;
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
            
            ($invoice->getNumber() <= $result) ? : $result = $invoice->getNumber();
        }
        return $result;
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
        
        $serializer = new Serializer();
        $handle = fopen($this->file, 'r+');
        while(($line = fgets($handle, 4096)) !== false){
            $item = new Invoice();
            $itemData = $this->unescape(str_getcsv($line)[0]);
            $item = $serializer->unserialize($itemData, Invoice::class, $item);
            if ($item->getNumberPlanId() == $id) {
                $invoices[] = $item;
            }
        }
        fclose($handle);
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
        
        $serializer = new Serializer();
        $handle = fopen($this->file, 'r+');
        while(($line = fgets($handle, 4096)) !== false){
            $item = new Invoice();
            $itemData = $this->unescape(str_getcsv($line)[0]);
            $item = $serializer->unserialize($itemData, Invoice::class, $item);
            $createDate = $item->getCreateDate();
            if ($createDate->format('Y') == $year) {
                $invoices[] = $item;
            }
        }
        fclose($handle);
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
        
        $serializer = new Serializer();
        $handle = fopen($this->file, 'r+');
        while(($line = fgets($handle, 4096)) !== false){
            $item = new Invoice();
            $itemData = $this->unescape(str_getcsv($line)[0]);
            $item = $serializer->unserialize($itemData, Invoice::class, $item);
            $createDate = $item->getCreateDate();
            if ($createDate->format('Y') == $year && $createDate->format('m') == $month) {
                $invoices[] = $item;
            }
        }
        fclose($handle);
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
        
        $serializer = new Serializer();
        $handle = fopen($this->file, 'r+');
        while(($line = fgets($handle, 4096)) !== false){
            $item = new Invoice();
            $itemData = $this->unescape(str_getcsv($line)[0]);
            $item = $serializer->unserialize($itemData, Invoice::class, $item);
            $createDate = $item->getCreateDate();
            if ($createDate->format('Y') == $year && $item->getNumberPlanId() == $id) {
                $invoices[] = $item;
            }
        }
        fclose($handle);
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
        
        $serializer = new Serializer();
        $handle = fopen($this->file, 'r+');
        while(($line = fgets($handle, 4096)) !== false){
            $item = new Invoice();
            $itemData = $this->unescape(str_getcsv($line)[0]);
            $item = $serializer->unserialize($itemData, Invoice::class, $item);
            $createDate = $item->getCreateDate();
            if ($createDate->format('Y') == $year && $createDate->format('m') == $month && $item->getNumberPlanId() == $id) {
                $invoices[] = $item;
            }
        }
        fclose($handle);
        return $invoices;
    }     
    /**
     * 
     * @return Invoice[]
     */
    public function findAll()
    {
        $serializer = new Serializer();
        $handle = fopen($this->file, 'r+');
        $items = [];
        while(($line = fgets($handle, 4096)) !== false){
            $item = new Invoice();
            $itemData = $this->unescape(str_getcsv($line)[0]);
            $item = $serializer->unserialize($itemData, Invoice::class, $item);
            $items[] = $item;
        }
        fclose($handle);
        return $items;
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
        
        $serializer = new Serializer();
        $handle = fopen($this->file, 'r+');
        while(($line = fgets($handle, 4096)) !== false){
            $item = new Invoice();
            $itemData = $this->unescape(str_getcsv($line)[0]);
            $item = $serializer->unserialize($itemData, Invoice::class, $item);
            if ($item->getBuyer()->getId() == $model->getBuyer()->getId() 
                && $item->getNumberPlanId() == $model->getNumberPlanId()
                && $item->getTotalGross() == $model->getTotalGross()
                && $item->getCreateDate() == $model->getCreateDate()) {
                $invoices[] = $item;
            }
        }
        fclose($handle);
        return $invoices;
    }    
    
    private function escape($line)
    {
        return '"' . str_replace('"', '\"', $line) . '"' . PHP_EOL;

    }
    private function unescape($line)
    {
        return str_replace('\"', '"', $line);

    }    
}
