<?php

namespace App;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Itav\Component\Serializer\Serializer;
use Itav\Component\Form;
use Itav\Component\Table;

class InvoiceController
{
    protected $serializer;
    protected $repo;
    protected $nbRepo;


    public function __construct()
    {
        $this->serializer = new Serializer();
        $this->repo = new InvoiceRepo();
        $this->nbRepo = new NumberPlanRepo();
    }

    public function listAction(Application $app, Request $request)
    {
        $submit = $request->get('submit');
        $criteriaData = $request->get('criteria');
        
        if(isset($submit['add'])){
            return $app->redirect('/add');
        }
        $criteria = new InvoiceCriteria();
        if($criteriaData){
            $this->serializer->unserialize($criteriaData, InvoiceCriteria::class, $criteria);
        }
        if(isset($submit['print_all'])){
            if(isset($criteriaData['print_pdf'])){
                return $this->printPdfAction($app, 'all', $criteria);
            }
            return $this->printAction($app, 'all', $criteria);
        }
        if(isset($submit['print_duplicate'])){
            if(isset($criteriaData['print_pdf'])){
                return $this->printPdfAction($app, 'all', $criteria, true);
            }            
            return $this->printAction($app, 'all', $criteria, true);
        }
        $invoices = $this->repo->findByCriteria($criteria);
        $table = new Table\Table();
        $i = 1;
        foreach ($invoices as $invoice) {
            $id = $invoice->getId();
            $actions = "<a href='/print/$id' target='_blank'>print</a>&nbsp"
                . "<a href='/print/pdf/$id' target='_blank'>printPDF</a>&nbsp"
                . "<a href='/info/$id'>info</a>&nbsp"
                . "<a href='/edit/$id'>edit</a>&nbsp"
                . "<a href='/del/$id'>del</a>";
            $row = new Table\Tr();
            $row->setElements([
                new Table\Td($i++),
                new Table\Td($invoice->getDisplayNumber()),
                new Table\Td($invoice->getBuyer()->getName() . $invoice->getBuyer()->getLastName() .' '. $invoice->getBuyer()->getFirstName()),
                new Table\Td($invoice->getTotalGross()),
                new Table\Td($actions),
            ]);
            $table->addElement($row);
        }
        
        $form = new Form\Form();
        $form
            ->addElement(InvoiceForm::add())
            ->addElement(InvoiceForm::searchCriteria($criteria))
            ->addElement(InvoiceForm::printButtons())
            ->addElement(new Form\Button('List', null, Form\Button::TYPE_SUBMIT));
            
        
        $tableNorm = $this->serializer->normalize($table);
        $formNorm = $this->serializer->normalize($form);
        return $app['twig']->render('list.twig', [
            'form' => $formNorm,
            'table' => $tableNorm,
        ]);
    }

    public function addAction(Application $app, $id = null)
    {
        $invoice = new Invoice();
        $repo = new InvoiceRepo();

        if ($id) {
            $invoice = $repo->find($id);
        } else {

            $invoice->initDefaults();
            InvoiceLogic::calculateInvoice($invoice);

        }
        $form = InvoiceForm::addForm($app, $invoice);

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
        $id = $invoice->getId();
        if (isset($submit['edit'])){
            return $app->redirect("/edit/$id");
        }
        if (isset($submit['del'])){
            return $app->redirect("/del/$id");
        }
        if (isset($submit['cancel'])){
            return $app->redirect("/list");
        }        
        
        InvoiceLogic::calculateInvoice($invoice);
        
        if (isset($submit['add_position'])) {
            $item = new InvoiceItem();
            $tax = new Tax();
            $tax->setId(Invoice::TAX_DEFAULT_ID);
            $item->setTax($tax);
            $invoice->addInvoiceItem($item);
            $form = InvoiceForm::addForm($app, $invoice);
            $formNorm = $serializer->normalize($form);
            return $app['templating']->render('page.php', array('form' => $formNorm));
        }
        if (isset($submit['remove_item'])) {
            $index = $submit['remove_item'];
            $invoice->delInvoiceItem($index);
            $invoice->reindexInvoiceItems();
            $form = InvoiceForm::addForm($app, $invoice);
            $formNorm = $serializer->normalize($form);
            return $app['templating']->render('page.php', array('form' => $formNorm));
        }
        $valid = InvoiceLogic::validate($invoice);
        if ($valid) {
            $repo = new InvoiceRepo();
            $interesantClient = new InteresantClient();
            $seller = $interesantClient->getInteresantById($invoice->getSeller()->getId());
            $buyer = $interesantClient->getInteresantById($invoice->getBuyer()->getId());
            $invoice
                ->setSeller($seller)
                ->setBuyer($buyer);
            $savedId = $repo->save($invoice);
            return $app->redirect("/info/$savedId");
        }
        $form = InvoiceForm::addForm($app, $invoice);
        $formNorm = $serializer->normalize($form);
        return $app['templating']->render('page.php', array('form' => $formNorm));
    }

    public function infoAction(Application $app, $id)
    {
        $invoice = $this->repo->find($id);

        $form = InvoiceForm::addForm($app, $invoice);
        $form->removeSubmits();
        $form->addElement(InvoiceForm::navigation());
        
        $norm = $this->serializer->normalize($form);

        return $app['twig']->render('sub_add.twig', ['form' => $norm]);        
    }

    public function deleteAction(Application $app, $id)
    {
        $this->repo->delete($id);
        return $app->redirect("/list");
    }
    
    public function printAction(Application $app, $id, $criteria = null, $duplicate = null)
    {
        $repo = new InvoiceRepo();
        if($id == 'all'){
            if($criteria){
                $invoices = $repo->findByCriteria($criteria);
            } else {
                $invoices = $repo->findAll();
            }
            return $app->stream($this->streamHtmlInvoicesAction($app, $invoices, $duplicate), 200, array('Content-Type' => 'text/html'));            
        }
        
        $invoice = $repo->find($id);
        InvoiceLogic::preparePrintAddresses($invoice);
        
        $serializer = new Serializer();
        $invoice = $serializer->normalize($invoice);
        return  $app['twig']->render('print.html.twig', ['invoice' => $invoice]);
    }

    public function printPdfAction(Application $app, $id, $criteria = null, $duplicate = null)
    {
        $repo = new InvoiceRepo();
        $filename =  'temp.html';
        $filenamepdf = 'temp.pdf';
        @unlink($filename);
        @unlink($filenamepdf);
        $invoices = [];
        if($id == 'all'){
            if($criteria){
                $invoices = $repo->findByCriteria($criteria);
            } else {
                $invoices = $repo->findAll();
            }
        } else {
            $invoices[] = $repo->find($id);
        }
        $serializer = new Serializer();
        $html = $app['twig']->render('print_header_pdf.html.twig');
        file_put_contents($filename, $html, FILE_APPEND);
        $typeInfo = $duplicate ? 'Duplikat z dn '. (new \DateTime())->format('Y-m-d') : '';
        foreach($invoices as $invoice){
            InvoiceLogic::preparePrintAddresses($invoice);
            $invoice->setTypeInfo($typeInfo);
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
    public function streamHtmlInvoicesAction($app, array $invoices, $duplicate = null)
    {
        $serializer = new Serializer();
        echo $app['twig']->render('print_header.html.twig');
        if($duplicate){
            $date = new \DateTime();  
            $info = 'Duplikat z dnia '. $date->format('Y-m-d');
        }
        foreach ($invoices as $invoice) {
            if($duplicate){
                $invoice->setTypeInfo($info);
            }
            InvoiceLogic::preparePrintAddresses($invoice);
            $invoice = $serializer->normalize($invoice);
            echo $app['twig']->render('print_all.html.twig', ['invoice' => $invoice]);
            ob_flush();
            flush();
        }
        echo $app['twig']->render('print_footer.html.twig');
    }
}
