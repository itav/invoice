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
    
    public function printAction(Application $app, $id)
    {
        $repo = new InvoiceRepo();
        if($id == 'all'){
    
            $invoices = $repo->findAll();
            return $app->stream($this->streamHtmlInvoices($app, $invoices), 200, array('Content-Type' => 'text/html'));            
        }
        
        $invoice = $repo->find($id);
        $this->preparePrintAddresses($invoice);
        
        $serializer = new Serializer();
        $invoice = $serializer->normalize($invoice);
        return  $app['twig']->render('print.html.twig', ['invoice' => $invoice]);
    }

    public function printPdfAction(Application $app, $id)
    {
        $repo = new InvoiceRepo();
        $filename =  'temp.html';
        $filenamepdf = 'temp.pdf';
        @unlink($filename);
        @unlink($filenamepdf);
        $invoices = [];
        if($id == 'all'){
            $invoices = $repo->findAll();
        } else {
            $invoices[] = $repo->find($id);
        }
        $serializer = new Serializer();
        $html = $app['twig']->render('print_header_pdf.html.twig');
        file_put_contents($filename, $html, FILE_APPEND);
        foreach($invoices as $invoice){

            $invoice = $serializer->normalize($invoice);
            $html = $app['twig']->render('print_all.html.twig', ['invoice' => $invoice]);
            file_put_contents($filename, $html, FILE_APPEND);
        }
        $html = $app['twig']->render('print_footer.html.twig');
        file_put_contents($filename, $html, FILE_APPEND);
        
        system(escapeshellcmd("wkhtmltopdf.sh $filename $filenamepdf"));
        $stream = function() use($filenamepdf, $filename) {
            $handle = fopen($filenamepdf, 'rb');
            readfile($filenamepdf);
            while(!feof($handle)){
                echo fread($handle, 1024);
                ob_flush();
                flush();
            }
            @fclose($handle);
            @unlink($filenamepdf);
            @unlink($filename);
        };
        return $app->stream($stream, 200, array('Content-Type' => 'application/pdf'));
    }

    /**
     * @param Application $app
     * @param Invoice[] $invoices
     */
    public function streamHtmlInvoices($app, array $invoices)
    {
        $serializer = new Serializer();
        echo $app['twig']->render('print_header.html.twig');
        foreach ($invoices as $invoice) {
            $invoice = $serializer->normalize($invoice);
            echo $app['twig']->render('print_all.html.twig', ['invoice' => $invoice]);
            ob_flush();
            flush();
        }
        echo $app['twig']->render('print_footer.html.twig');
    }

    public function addAction(Application $app, $id = null)
    {
        $invoice = new Invoice();
        $repo = new InvoiceRepo();

        if ($id) {
            $invoice = $repo->find($id);
        } else {

            $invoice->initDefaults();
        }
        $this->calculateInvoice($invoice);
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
        $this->calculateInvoice($invoice);
        if (isset($submit['add_position'])) {
            $item = new InvoiceItem();
            $tax = new Tax();
            $tax->setId(Invoice::TAX_DEFAULT_ID);
            $item->setTax($tax);
            $invoice->addInvoiceItem($item);
            $form = $this->prepareAddForm($app, $invoice);
            $formNorm = $serializer->normalize($form);
            return $app['templating']->render('page.php', array('form' => $formNorm));
        }
        if (isset($submit['remove_item'])) {
            $index = $submit['remove_item'];
            $invoice->delInvoiceItem($index);
            $invoice->reindexInvoiceItems();
            $form = $this->prepareAddForm($app, $invoice);
            $formNorm = $serializer->normalize($form);
            return $app['templating']->render('page.php', array('form' => $formNorm));
        }
        $valid = $this->validateInvoice($invoice);
        if ($valid) {
            $repo = new InvoiceRepo();
            $interesantClient = new InteresantClient();
            $seller = $interesantClient->getInteresantById($invoice->getSeller()->getId());
            $buyer = $interesantClient->getInteresantById($invoice->getBuyer()->getId());
            $invoice
                ->setSeller($seller)
                ->setBuyer($buyer);
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
        $id = $id;
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

        $numberPlanSelect = $this->prepareNumberPlanSelect($invoice);

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

        $productRows = $this->preparePositionRows($invoice);

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

        $taxSummaries = $this->prepareTaxSummaries($invoice);

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

    public function validateInvoice(Invoice $invoice)
    {
        $invoice->getId();
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

            $taxSelect = $this->prepareTaxSelect($item, $i);

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
    public function prepareTaxSelect($product, $i)
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

    /**
     * 
     * @param Invoice $invoice
     */
    private function calculateInvoice($invoice)
    {
        $productClient = new ProductClient();
        $items = $invoice->getInvoiceItems();
        $taxSummaries = [];

        foreach ($items as $item) {
            $net = round((float) $item->getNetValue(), 2);
            $tax = round((float) $item->getTaxValue(), 2);
            $gross = round((float) $item->getGrossValue(), 2);
            $invoice->setTotalNet($invoice->getTotalNet() + $net);
            $invoice->setTotalTax($invoice->getTotalTax() + $tax);
            $invoice->setTotalGross($invoice->getTotalGross() + $gross);
            $taxid = $item->getTax()->getId();
            $item->setTax($productClient->getTaxById($taxid));
            if (!in_array($taxid, array_keys($taxSummaries))) {
                $taxSummary = new TaxSummary();
                $taxSummary->setTaxName($item->getTax()->getName());

                $taxSummaries[$taxid] = $taxSummary;
            } else {
                $taxSummary = $taxSummaries[$taxid];
            }
            $taxSummary
                    ->setTaxName($item->getTax()->getName())
                    ->setTotalNet($taxSummary->getTotalNet() + $net)
                    ->setTotalTax($taxSummary->getTotalTax() + $tax)
                    ->setTotalGross($taxSummary->getTotalGross() + $gross);
        }
        $invoice->setTaxSummaries(array_values($taxSummaries));
    }

    public function prepareTaxSummaries(Invoice $invoice)
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
    
    private function preparePrintAddresses(Invoice $invoice)
    {
        $hasMain = false;
        $invoice->getSeller()->getAddresses();
        foreach($invoice->getSeller()->getAddresses() as $key => $address){
            if($address->getType() == Address::TYPE_MAIN){
                $mainSell = $address;
                $hasMain = true;
                break;
            }
        }
        if(!$hasMain){
            $mainSell = $invoice->getSeller()->getAddresses()[0];
        }
        $hasMain = false;
        $hasPost = false;
        foreach($invoice->getBuyer()->getAddresses() as $address){
            if($address->getType() == Address::TYPE_MAIN){
                $mainBuy = $address;
                $hasMain = true;
            }
            if($address->getType() == Address::TYPE_POST){
                $postBuy = $address;
                $hasPost = true;
            }            
        }
        if(!$hasPost){
            $mainBuy = $invoice->getBuyer()->getAddresses()[0];
        }
        if(!$hasPost){
            $postBuy = $mainBuy;
        }
        $invoice->getSeller()->setAddresses([]);
        $invoice->getBuyer()->setAddresses([]);
        $invoice->getSeller()->addAddress($mainSell, 'main');
        $invoice->getBuyer()->addAddress($mainBuy, 'main');
        $invoice->getBuyer()->addAddress($postBuy, 'post');
        return $invoice;
    }

}
