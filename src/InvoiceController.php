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
        $submit = $request->get('submit');

        $serializer = $app['serializer'];
        $invoice = new Invoice();
        $invoice = $serializer->unserialize($invoiceData, Invoice::class, $invoice);
        if (isset($submit['add_position'])) {
            $invoice->addProduct(new Product());
            $form = $this->prepareAddForm($app, $invoice);
            $formNorm = $serializer->normalize($form);
            return $app['templating']->render('page.php', array('form' => $formNorm));
        }
        if (isset($submit['remove_item'])) {
            $index = $submit['remove_item'];
            $invoice->delProduct($index);
            $invoice->reindexProducts();
            $form = $this->prepareAddForm($app, $invoice);
            $formNorm = $serializer->normalize($form);
            return $app['templating']->render('page.php', array('form' => $formNorm));
        }        
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
        $productClient = new ProductClient();

        $id = new Form\Input();
        $id
                ->setType(Form\Input::TYPE_HIDDEN)
                ->setName('invoice[id]')
                ->setValue($invoice->getId());

        $number = new Form\Input();
        $number
                ->setLabel('Number:')
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

        $issuer = new Form\Input();
        $issuer
                ->setLabel('Issuer:')
                ->setName('invoice[issuer]')
                ->setValue($invoice->getIssuer());

        //$selectProduct = $productClient->getSelectProduct(new Product());

        $productRows = $this->preparePositionRows($invoice);

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
        $fs5->setElements([$submit, $addButton]);

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
                ->addElement($fs5);
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
                ->setName('invoice[number_plan_id]');
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

    public function preparePositionRows(Invoice $invoice)
    {
        $i = 0;

        $positions = [];
        foreach ($invoice->getProducts() as $product) {

            $id = new Form\Input();
            $id
                    ->setType(Form\Input::TYPE_HIDDEN)
                    ->setName("[products][$i][id]")
                    ->setValue($product->getId());

            $number = new Form\Input();
            $number
                    ->setType(Form\Input::TYPE_TEXT)
                    ->setName("invoice[products][$i][position]")
                    ->setValue($i + 1)
                    ->setDisabled(true);

            $name = new Form\Input();
            $name
                    ->setLabel('Name:')
                    ->setName("invoice[products][$i][name]")
                    ->setValue($product->getName());

            $priceNet = new Form\Input();
            $priceNet
                    ->setLabel('Price Net:')
                    ->setName("invoice[products][$i][price_net]")
                    ->setValue($product->getPriceNet());

            $taxSelect = $this->prepareTaxSelect($product, $i);

            $taxValue = new Form\Input();
            $taxValue
                    ->setLabel('Tax Value:')
                    ->setName("invoice[products][$i][tax_value]")
                    ->setValue($product->getTaxValue());

            $priceGross = new Form\Input();
            $priceGross
                    ->setLabel('Price Gross:')
                    ->setName("invoice[products][$i][price_gross]")
                    ->setValue($product->getPriceGross());

            $removeButton = new Form\Button();
            $removeButton
                    ->setLabel('Remove')
                    ->setType(Form\Button::TYPE_SUBMIT)
                    ->setName("submit[remove_item]")
                    ->setValue($i);

            $fs = new Form\FieldSet();
            $fs->setElements([$id, $number, $name, $priceNet, $taxSelect, $taxValue, $priceGross, $removeButton]);

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
    public function prepareTaxSelect($product, $i)
    {
        $productClient = new ProductClient();
        $taxes = $productClient->getTaxes();
        $select = new Form\Select();
        $select->setName("invoice[products][$i][tax][id]");
        $options = [];
        foreach ($taxes as $tax) {
            $option = new Form\Option();
            $option
                    ->setLabel($tax->getName())
                    ->setValue($tax->getId())
                    ->setSelected($tax->getId() === $product->getTax()->getId());
            $options[] = $option;
        }
        $select->setOptions($options);
        return $select;
    }

}
