<?php

namespace App;

use Itav\Component\Form;
use Silex\Application;

class SubscriptionForm
{
    /**
     * @param Application $app
     * @param Subscription $sub
     * @return Form\Form
     */
    static public function addForm(Application $app, Subscription $sub)
    {
        $id = new Form\Input();
        $id
            ->setType(Form\Input::TYPE_HIDDEN)
            ->setName('sub[id]')
            ->setValue($sub->getId());
                
        $token = new Form\Input();
        $token
            ->setType(Form\Input::TYPE_HIDDEN)
            ->setName('csrf-token')
            ->setValue(md5(uniqid()));
        
        $invoice = InvoiceForm::selectInvoice();
        $invoice
            ->setLabel('Set invoice')
            ->setName('sub[invoice_id]');
        foreach ($invoice->getOptions() as $option){
            $option->setSelected($option->getValue() == $sub->getInvoiceId());
        }
            
        $period = self::selectPeriod($sub);
        
        $from = new Form\Input();
        $from
            ->setLabel('From date:')
            ->setName('sub[from]')            
            ->setValue($sub->getFrom()->format('Y-m-d'))
            ->addClass('_datepicker');
        $to = new Form\Input();
        $to
            ->setLabel('To date:')
            ->setName('sub[to]')            
            ->setValue($sub->getTo()->format('Y-m-d'))
            ->addClass('_datepicker');            
        $suspended = new Form\Input();
        $suspended
            ->setType(Form\Input::TYPE_CHECKBOX)
            ->setLabel('Suspended:')
            ->setName('sub[suspended]')
            ->setChecked($sub->getStatus() == Subscription::STATUS_SUSPENDED);
        $submit = new Form\Button();
        $submit
            ->setType(Form\Button::TYPE_SUBMIT)
            ->setLabel('Save');
            

        $row1 = new Form\FieldSet();
        $row1->setElements([$period, $from, $to]);
        $row2 = new Form\FieldSet();
        $row2->setElements([$id, $token,$invoice, $suspended]);        
        
        $form = new Form\Form();
        $form
            ->setName('subAdd')
            ->setMethod(Form\Form::METHOD_POST)
            ->setAction($app['url_generator']->generate('sub_add'))
            ->addElement($row1)
            ->addElement($row2)
            ->addElement($submit);
            
        return $form;
    }
    
    /**
     * 
     * @param \App\Subscription $sub
     * @return \Itav\Component\Form\Select
     */
    static public function selectPeriod(Subscription $sub)
    {
        $options = [];
        $option = new Form\Option();
        $option
            ->setLabel(Subscription::PERIOD_MONTHLY)
            ->setValue(Subscription::PERIOD_MONTHLY)
            ->setSelected($sub->getPeriod() == Subscription::PERIOD_MONTHLY);
        
        $options[] = $option;
        $option = new Form\Option();
        $option
            ->setLabel(Subscription::PERIOD_YEARLY)
            ->setValue(Subscription::PERIOD_YEARLY)
            ->setSelected($sub->getPeriod() == Subscription::PERIOD_YEARLY);
        $options[] = $option;
        $select = new Form\Select();
        $select
            ->setLabel('Select Period Type:')
            ->setName('sub[period]')
            ->setOptions($options);
        
        return $select;
    }
}