<?php

namespace App;

use Itav\Component\Form;
use Silex\Application;

class InvoiceForm extends CommonForm
{
    /**
     * 
     * @param Application $app
     * @param Invoice $invoice
     * @return Form\Form
     */
    static public function addForm(Application $app, Invoice $invoice)
    {
        $interesantClient = new InteresantClient();
        //$productClient = new ProductClient();     

        $id = new Form\Input();
        $id
                ->setType(Form\Input::TYPE_HIDDEN)
                ->setName('invoice[id]')
                ->setValue($invoice->getId());

        $number = new Form\Input();
        $number
                ->setLabel('Number:')
                ->setName('invoice[number]')
                ->setValue($invoice->getNumber());

        $numberPlanSelect = self::numberPlanSelect($invoice);

        $npRepo = new NumberPlanRepo();
        $plan = $npRepo->find($invoice->getNumberPlanId());
        $dn = $plan->prepare($invoice->getNumber(), $invoice->getCreateDate());

        $displayNumber = new Form\Input();
        $displayNumber
                ->setLabel('Display Number:')
                ->setName('invoice[display_number]')
                ->setDisabled(true)
                ->setValue($dn);

        $createDate = new Form\Input();
        $createDate
                ->setLabel('Create date:')
                ->setName('invoice[create_date]')
                ->setValue($invoice->getCreateDate())
                ->setClass('_datepicker');

        $sellDate = new Form\Input();
        $sellDate
                ->setLabel('Sell date:')
                ->setName('invoice[sell_date]')
                ->setValue($invoice->getSellDate())
                ->setClass('_datepicker');

        $paymentDate = new Form\Input();
        $paymentDate
                ->setLabel('Payment date:')
                ->setName('invoice[payment_date]')
                ->setValue($invoice->getPaymentDate())
                ->setClass('_datepicker');

        $paymentMethods = self::paymentMethodSelect($invoice);

        $selectSeller = $interesantClient->getSelectInteresant($invoice->getSeller());
        $selectSeller
                ->setLabel('Select Seller:')
                ->setName('invoice[seller][id]');
        
        $selectBuyer = $interesantClient->getSelectInteresant($invoice->getBuyer());
        $selectBuyer
                ->setLabel('Select Buyer:')
                ->setName('invoice[buyer][id]');

        $issuer = new Form\Input();
        $issuer
                ->setLabel('Issuer:')
                ->setName('invoice[issuer]')
                ->setValue($invoice->getIssuer());

        $productRows = self::positionRows($invoice);

        $totalNet = new Form\Input();
        $totalNet
                ->setLabel('Total net:')
                ->setValue($invoice->getTotalNet());

        $totalTax = new Form\Input();
        $totalTax
                ->setLabel('Total tax:')
                ->setValue($invoice->getTotalTax());

        $totalGross = new Form\Input();
        $totalGross
                ->setLabel('Total gross:')
                ->setValue($invoice->getTotalGross());

        $taxSummaries = self::taxSummaries($invoice);

        $addButton = new Form\Button();
        $addButton
                ->setLabel('Add position')
                ->setType(Form\Button::TYPE_SUBMIT)
                ->setName('submit[add_position]');

        $submit = new Form\Button();
        $submit
                ->setLabel('Save')
                ->setType(Form\Button::TYPE_SUBMIT)
                ->setName('submit[save]');

        $fs = new Form\FieldSet();
        $fs->setElements([$id, $number, $numberPlanSelect, $displayNumber]);

        $fs2 = new Form\FieldSet();
        $fs2->setElements([$createDate, $sellDate, $paymentDate, $paymentMethods]);

        $fs3 = new Form\FieldSet();
        $fs3->setElements([$selectSeller, $selectBuyer, $issuer]);

        $fs4 = new Form\FieldSet();
        $fs4->setElements($productRows);

        $fs5 = new Form\FieldSet();
        $fs5->addElement($addButton);

        $fs6 = new Form\FieldSet();
        $fs6->setElements([$totalNet, $totalTax, $totalGross]);

        $fs7 = new Form\FieldSet();
        $fs7->setElements($taxSummaries);

        $fs8 = new Form\FieldSet();
        $fs8->addElement($submit);

        $form = new Form\Form();
        $form
                ->setName('invoiceAdd')
                ->setAction($app['url_generator']->generate('invoice_add'))
                ->setMethod('POST');

        $form
                ->addElement($fs)
                ->addElement($fs2)
                ->addElement($fs3)
                ->addElement($fs4)
                ->addElement($fs5)
                ->addElement($fs6)
                ->addElement($fs7)
                ->addElement($fs8);
        return $form;
    }
    /**
     * 
     * @param Invoice $invoice
     * @return Form\Select
     */
    static public function numberPlanSelect($invoice)
    {
        $repo = new NumberPlanRepo();
        $plans = $repo->findAll();
        $select = new Form\Select();
        $select
                ->setLabel('Select number plan:')
                ->setName('invoice[number_plan_id]');
        $options = [];
        foreach ($plans as $plan) {
            $option = new Form\Option();
            $option
                    ->setLabel($plan->getTemplate())
                    ->setValue($plan->getId())
                    ->setSelected($plan->getId() == $invoice->getNumberPlanId());
            $options[] = $option;
        }
        $select->setOptions($options);
        return $select;
    }

    /**
     * 
     * @param Invoice $invoice
     * @return Form\Select
     */
    static public function paymentMethodSelect($invoice)
    {
        $select = new Form\Select();
        $select
                ->setLabel('Select payment method:')
                ->setName('invoice[payment_type]');
        $options = [];
        $option = new Form\Option();
        $option
                ->setLabel('transfer')
                ->setSelected('transfer' === $invoice->getPaymentType());
        $options[] = $option;
        $option = new Form\Option();
        $option
                ->setLabel('cash')
                ->setSelected('cash' === $invoice->getPaymentType());
        $options[] = $option;
        $select->setOptions($options);

        return $select;
    }

    static public function positionRows(Invoice $invoice)
    {
        $i = 0;
        $positions = [];

        foreach ($invoice->getInvoiceItems() as $item) {

            $ordianl = new Form\Input();
            $ordianl
                    ->setType(Form\Input::TYPE_TEXT)
                    ->setName("invoice[invoice_items][$i][ordinal]")
                    ->setValue($i + 1)
                    ->setDisabled(true);

            $description = new Form\Input();
            $description
                    ->setLabel('Name:')
                    ->setName("invoice[invoice_items][$i][description]")
                    ->setValue($item->getDescription());

            $netPrice = new Form\Input();
            $netPrice
                ->setLabel('Price Net:')
                ->setName("invoice[invoice_items][$i][net_price]")
                ->setValue($item->getNetPrice());
            
            $unit = new Form\Select();
            $unit
                ->setLabel('Unit:')
                ->setName("invoice[invoice_items][$i][unit]")
                ->setOptions([ 
                    new Form\Option('szt.', 'szt.', ('szt.' == $item->getUnit())),
                    new Form\Option('kpl.', 'kpl.', ('kpl.' == $item->getUnit())),
                    new Form\Option('m', 'm', ('m' == $item->getUnit())),
                ]);
            
            $quantity = new Form\Input();
            $quantity
                ->setLabel('Qty:')
                ->setName("invoice[invoice_items][$i][quantity]")
                ->setValue($item->getQuantity());
            
            $netValue = new Form\Input();
            $netValue
                ->setLabel('Net value:')
                ->setName("invoice[invoice_items][$i][net_value]")
                ->setValue($item->getNetValue());            

            $taxSelect = self::taxSelect($item, $i);

            $taxValue = new Form\Input();
            $taxValue
                ->setLabel('Tax Value:')
                ->setName("invoice[invoice_items][$i][tax_value]")
                ->setValue($item->getTaxValue());

            $grossValue = new Form\Input();
            $grossValue
                ->setLabel('Gross value:')
                ->setName("invoice[invoice_items][$i][gross_value]")
                ->setValue($item->getGrossValue());

            $removeButton = new Form\Button();
            $removeButton
                    ->setLabel('Remove')
                    ->setType(Form\Button::TYPE_SUBMIT)
                    ->setName("submit[remove_item]")
                    ->setValue($i);

            $fs = new Form\FieldSet();
            $fs->setElements([
                $ordianl,
                $description,
                $quantity, 
                $unit,
                $netPrice,
                $netValue,
                $taxSelect,
                $taxValue,
                $grossValue,
                $removeButton,
            ]);

            $positions[] = $fs;
            $i++;
        }

        return $positions;
    }

    /**
     * 
     * @param Product $product 
     * @return Form\Select
     */
    static public function taxSelect($product, $i)
    {
        $productClient = new ProductClient();
        $taxes = $productClient->getTaxes();
        $select = new Form\Select();
        $select->setName("invoice[invoice_items][$i][tax][id]");
        $options = [];
        foreach ($taxes as $tax) {
            if ($flag = $tax->getId() == $product->getTax()->getId()) {
                $product->setTax($tax);
            }
            $option = new Form\Option();
            $option
                    ->setLabel($tax->getName())
                    ->setValue($tax->getId())
                    ->setSelected($flag);
            $options[] = $option;
        }
        $select->setOptions($options);
        return $select;
    }
    
    static public function taxSummaries(Invoice $invoice)
    {
        $taxSummaries = $invoice->getTaxSummaries();
        $elements = [];
        foreach ($taxSummaries as $taxSummary) {
            $fs = new Form\FieldSet();
            $totalNet = new Form\Input();
            $totalNet
                    ->setValue($taxSummary->getTotalNet());

            $taxName = new Form\Input();
            $taxName->setValue($taxSummary->getTaxName());

            $totalTax = new Form\Input();
            $totalTax->setValue($taxSummary->getTotalTax());

            $totalGross = new Form\Input();
            $totalGross->setValue($taxSummary->getTotalGross());
            $fs->setElements([$totalNet, $taxName, $totalTax, $totalGross]);
            $elements[] = $fs;
        }
        return $elements;
    }    
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
    
    /**
     * 
     * @param InvoiceCriteria $criteria
     * @return Form\FieldSet
     */
    static public function searchCriteria($criteria = null)
    {
        $date = new \DateTime();
        $year = $date->format('Y');
        $month = $date->format('m');
        if(null === $criteria){
            $criteria = new InvoiceCriteria();
            $date = new \DateTime();
            $criteria->setYear($year);
            $criteria->setMonth($month);      
        } 
        if(!$criteria->getMonth()){
            $criteria->setMonth($month);
        }
        if(!$criteria->getYear()){
            $criteria->setYear($year);
        }            
        
        $startYear = (int)$year - 5;
        $endYear = (int)$year + 1;

        $yearOptions = [];
        for($i=$startYear; $i<=$endYear; $i++){
            $opt = new Form\Option($i, $i, $i == $criteria->getYear());
            $yearOptions[] = $opt;
        }
        $monthOptions = [];
        for($i=1; $i<=12; $i++){
            $opt = new Form\Option($i, $i, $i == $criteria->getMonth());
            $monthOptions[] = $opt;
        }
        $fs = new Form\FieldSet();
        $selectYear = new Form\Select();
        $selectYear
            ->setLabel('Year:')
            ->setName('criteria[year]')
            ->setOptions($yearOptions);
        $selectMonth = new Form\Select();
        $selectMonth
            ->setLabel('Month:')
            ->setName('criteria[month]')
            ->setOptions($monthOptions);
        return $fs->setElements([$selectYear, $selectMonth]);
    }
    
    static public function duplicate(){
        $duplicateDate = new Form\Input();
        $duplicateDate
                ->setLabel('Sell date:')
                ->setName('invoice[sell_date]')
                ->setClass('_datepicker');        
        return $duplicateDate;
    }
       
    /**
     * 
     * @return Form\Button
     */
    static public function printDuplicate()
    {
        $printDuplicate = new Form\Button();
        $printDuplicate
            ->setLabel('Print Duplicate')
            ->setType(Form\Button::TYPE_SUBMIT)
            ->setName('submit[print_duplicate]');
        return $printDuplicate;
    }     
    
    /**
     * 
     * @return Form\FieldSet
     */
    static public function printButtons()
    {
        $pdf = new Form\Input();
        $pdf
            ->setLabel('pdf:')
            ->setType(Form\Input::TYPE_CHECKBOX)
            ->setName('criteria[print_pdf]');
        
        $fieldset = new Form\FieldSet();
        return $fieldset->setElements([parent::printAll(), self::printDuplicate(), $pdf]);
            
    }      
    
}