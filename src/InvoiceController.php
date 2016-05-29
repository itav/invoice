<?php

namespace App;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Itav\Component\Serializer\Serializer;
use Itav\Component\Form;


class InvoiceController
{

    public function listAction()
    {
        $repo = new InvoiceRepo();
        $invoices = $repo->findAll();
        var_dump($invoices);
        return '';
    }

    public function addAction(Application $app, $id = null)
    {
        $invoice = new Invoice();

        if ($id) {
            $invoice = $repo->find($id);
        } else {

            $invoice->initDefaults();
        }
                
        $form = $this->prepareAddForm($app, $invoice);

        $serializer = new Serializer();
        $formNorm = $serializer->normalize($form);
        return $app['templating']->render('page.php', array('form' => $formNorm));
    }

    public function saveAction(Application $app, Request $request)
    {
        $invoiceData = $request->get('invoice');
        $taxData = $request->get('tax');
        $serializer = $app['serializer'];
        $invoice = new Invoice();
        $tax = new Tax();
        $invoice = $serializer->unserialize($invoiceData, Invoice::class, $invoice);
        $tax = $serializer->unserialize($taxData, Tax::class, $tax);
        $taxRepo = new TaxRepo();
        $tax = $taxRepo->find($tax->getId());
        $invoice->setTax($tax);
        $valid = $this->validateInvoice($invoice);
        if ($valid) {
            $repo = new InvoiceRepo();
            $savedId = $repo->save($invoice);
            var_dump($savedId);
            return '';
        }
        $form = $this->prepareAddForm($app, $invoice);
        $formNorm = $serializer->normalize($form);
        return $app['templating']->render('page.php', array('form' => $formNorm));
    }

    public function infoAction(Application $app, $id)
    {
        $serializer = new Serializer();
        $repo = new InvoiceRepo();
        $invoice = $repo->find($id);

        $form = $this->prepareAddForm($app, $invoice);
        $form->removeSubmits();
        $formNorm = $serializer->normalize($form);
        return $app['templating']->render('page.php', array('form' => $formNorm));
    }

    public function deleteAction($id)
    {
        
    }

    /**
     * 
     * @param Application $app
     * @param Invoice $invoice
     * @return \Itav\Component\Form\Form
     */
    public function prepareAddForm(Application $app, Invoice $invoice)
    {
        $interesantClient = new InteresantClient();

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

        $numberPlanSelect = $this->prepareNumberPlanSelect($invoice);

        $displayNumber = new Form\Input();
        $displayNumber
                ->setLabel('Display Number:')
                ->setName('invoice[number]')
                ->setDisabled(true)
                ->setValue($invoice->getDisplayNumber());

        $createDate = new Form\Input();
        $createDate
                ->setLabel('Create date:')
                ->setName('invoice[create_date]')
                ->setValue($invoice->getCreateDate())
                ->setClass('_datepicker');

        $sellDate = new Form\Input();
        $sellDate
                ->setLabel('Sell date:')
                ->setName('invoice[create_date]')
                ->setValue($invoice->getSellDate())
                ->setClass('_datepicker');

        $paymentDate = new Form\Input();
        $paymentDate
                ->setLabel('Payment date:')
                ->setName('invoice[create_date]')
                ->setValue($invoice->getPaymentDate())
                ->setClass('_datepicker');

        $paymentMethods = $this->preparePaymentMethodSelect($invoice);
        
        $selectSeller = $interesantClient->getSelectInteresant($invoice->getSeller());
        $selectSeller
                ->setLabel('Select Seller:')
                ->setName('invoice[seller][id]');
        $selectBuyer = $interesantClient->getSelectInteresant($invoice->getBuyer());
        $selectBuyer
                ->setLabel('Select Buyer:')
                ->setName('invoice[buyer][id]');
        
        $submit = new Form\Button();
        $submit
                ->setLabel('Zapisz')
                ->setType(Form\Button::TYPE_SUBMIT);

        $fs = new Form\FieldSet();
        $fs->setElements([$id, $number, $numberPlanSelect, $displayNumber]);

        $fs2 = new Form\FieldSet();
        $fs2->setElements([$createDate, $sellDate, $paymentDate, $paymentMethods]);

        $fs3 = new Form\FieldSet();
        $fs3->setElements([$selectSeller, $selectBuyer]);        
        
        $form = new Form\Form();
        $form
                ->setName('invoiceAdd')
                ->setAction($app['url_generator']->generate('invoice_add'))
                ->setMethod('POST');

        $form
                ->addElement($fs)
                ->addElement($fs2)
                ->addElement($fs3)
                ->addElement($submit);
        return $form;
    }

    public function validateInvoice($invoice)
    {
        return true;
    }

    /**
     * 
     * @param Invoice $invoice
     * @return Form\Select
     */
    public function prepareNumberPlanSelect($invoice)
    {
        $repo = new NumberPlanRepo();
        $plans = $repo->findAll();
        $select = new Form\Select();
        $select
                ->setLabel('Select number plan:')
                ->setName('numberplan[id]');
        $options = [];
        foreach ($plans as $plan) {
            $option = new Form\Option();
            $option
                    ->setLabel($plan->getTemplate())
                    ->setValue($plan->getId())
                    ->setSelected($plan->getId() === $invoice->getNumberPlanId());
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
    public function preparePaymentMethodSelect($invoice)
    {
        $select = new Form\Select();
        $select
                ->setLabel('Select payment method:')
                ->setName('numberplan[id]');
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

}
