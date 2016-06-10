<?php

namespace App;

use Itav\Component\Form;

class InvoiceForm
{
    /**
     * 
     * @return \Itav\Component\Form\Select
     */
    static public function selectInvoice()
    {
        $repo = new InvoiceRepo();
        $invoices = $repo->findAll();
        
        $select = new Form\Select();
        $select
            ->setLabel('Select Invoice:')
            ->setName('invoice[id]');
        
        $options = [];
        
        foreach ($invoices as $invoice){
            $option = new Form\Option();
            $option
                ->setLabel($invoice->getDisplayNumber())
                ->setValue($invoice->getId());
            $options[] = $option;
        }
        $select->setOptions($options);
        return $select;
        
    }
    
}