<?php

namespace App;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class CronController
{
    public function generateInvoice(Application $app, Request $request, $id)
    {
        $from = new \DateTime($request->get('from', date('Y-m-d')));
        $to = new \DateTime($request->get('to', null));
        $repo = new SubscriptionRepo();
        $subs = [];
        if($id == 'all'){
            $subs = $repo->findAll();
        } else {
            $subs[] = $repo->find($id);
        }
        
        $counter = 0;
        foreach($subs as $sub){
            if($sub->getTo()){
                if($from > $sub->getTo()){
                    continue;
                }
                $to = ($to && $to <= $sub->getTo()) ? $to : $sub->getTo();

            } else {
                $to = ($to) ? $to : new \DateTime();
            }
            $from = ($from >= $sub->getFrom()) ? $from : $sub->getFrom();
            $counter += $this->doInvoices($sub, $from, $to);
        }
        return $counter;
    }
    
    public function doInvoices(Subscription $sub, \DateTime $from, \DateTime $to)
    {
        $counter = 0;
        $period = $sub->getPeriod();
        $repo = new InvoiceRepo();
        $npRepo = new NumberPlanRepo();
        $invoice = $repo->find($sub->getInvoiceId());
        if($period == Subscription::PERIOD_MONTHLY){

            $createDate = $invoice->getCreateDate();
            $day = $createDate->format('d');
            $startDay = $from->format('d');
            $lastDay = ((int)$day === (int)$createDate->format('t'));
            $current = clone $from;
            do{

                $month = $current->format('m');
                $year = $current->format('Y');
                $invoiceDate = ($lastDay) ? new \DateTime("last day of $year-$month") :  new \DateTime("$year-$month-$day") ;
                if($invoiceDate < $from ){
                    $current = $this->nextMonth($current, $startDay);
                    continue;
                }
                if($invoiceDate > $to){
                    break;
                }
                $newInvoice = clone $invoice;
                $paymentInv = $invoice->getPaymentDate()->diff($invoice->getSellDate(), true);
                $paymentDate = new \DateTime($invoiceDate->format('Y-m-d'));
                $newInvoice
                    ->setId(uniqid())
                    ->setCreateDate($invoiceDate)
                    ->setSellDate($invoiceDate)
                    ->setPaymentDate($paymentDate->add($paymentInv));
                if(count($repo->findByBuyerDatePlanAndPrice($newInvoice)) > 0){
                    $current = $this->nextMonth($current, $startDay);
                    continue;                    
                }
                $plan = $npRepo->find($newInvoice->getNumberPlanId());
                $newInvoice->setNumber($repo->getNextFreeNumber($plan, $invoiceDate));
                $newInvoice->setDisplayNumber($plan->prepare($newInvoice->getNumber(), $invoiceDate));

                $id = $repo->save($newInvoice);
                (!$id) ? : $counter++;
                $current = $this->nextMonth($current, $startDay);
            }while ($invoiceDate <= $to);
            return $counter;
        }
        if($period == Subscription::PERIOD_YEARLY){
            //TODO implement this
            return 0;
        }
        
    }
    
    /**
     * 
     * @param \DateTime $date
     */
    private function nextMonth($date, $d)
    {
        
        $day = $d;
        if($day <= 28){
            return $date->add(new \DateInterval('P1M'));
        }
        $month = $date->format('m');
        $year = $date->format('Y');
        if($month == 12){
            $month = '01';
            $year++;
        } else {
            $month++;
        }
        $next = new \DateTime("$year-$month-01");
        $last = $next->format('t');       
        if($day > $last){
            while ($day != $last){
                $day--;
            }
        }        
        return new \DateTime("$year-$month-$day");
    }
}
