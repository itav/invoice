<?php

namespace App;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Itav\Component\Serializer\Serializer;
use Itav\Component\Form;
use Itav\Component\Table;

class SubscriptionController
{
    private $repo;
    private $invRepo;
    private $serializer;
    
    public function __construct()
    {
        $this->repo = new SubscriptionRepo();
        $this->invRepo = new InvoiceRepo();
        $this->serializer = new Serializer();
    }

    public function listAction(Application $app, Request $request)
    {
        $submit = $request->get('submit');
        if(isset($submit['add'])){
            return $app->redirect('/sub/add');
        }
        $subs = $this->repo->findAll();
        $table = new Table\Table();
        $i = 1;
        foreach ($subs as $sub) {
            $id = $sub->getId();
            $invoice = new Invoice();
            $invoice = $this->invRepo->find($sub->getInvoiceId());
            
            $actions = "<a href='/sub/info/$id'>info</a>&nbsp"
                . "<a href='/sub/edit/$id'>edit</a>&nbsp"
                . "<a href='/sub/del/$id'>del</a>";
            $row = new Table\Tr();
            $row->setElements([
                new Table\Td($i++),
                new Table\Td($invoice->getBuyer()->getName().' '.$invoice->getBuyer()->getLastName()),
                new Table\Td($invoice->getDisplayNumber()),
                new Table\Td($invoice->getTotalGross()),
                new Table\Td($sub->getFrom()->format('Y-m-d')),
                new Table\Td($sub->getTo()->format('Y-m-d')),
                new Table\Td($actions),
            ]);
            $table->addElement($row);
        }
        $form = new Form\Form();
        $form->addElement(SubscriptionForm::add());
            
        $tableNorm = $this->serializer->normalize($table);
        $formNorm = $this->serializer->normalize($form);
        return $app['twig']->render('list.twig', [
            'form' => $formNorm,
            'table' => $tableNorm,
        ]);        
    }

    public function addAction(Application $app, $id = null)
    {
        $sub = new Subscription();
        if ($id) {
            $repo = new SubscriptionRepo();
            $sub = $repo->find($id);
        }
        $ser = new Serializer();
        $form = SubscriptionForm::addForm($app, $sub);
        $form = $ser->normalize($form);
        return $app['twig']->render('sub_add.twig', ['form' => $form]);
    }

    public function saveAction(Application $app, Request $request)
    {
        $ser = new Serializer();
        $sub = new Subscription();
        $subData = $request->get('sub');
        $ser->unserialize($subData, Subscription::class, $sub);
        $sub->setStatus(isset($subData['suspended']) ? Subscription::STATUS_SUSPENDED : Subscription::STATUS_ACTIVE);
        $id= $sub->getId();        
        $submit = $request->get('submit');
        if (isset($submit['edit'])){
            return $app->redirect("/sub/edit/$id");
        }
        if (isset($submit['del'])){
            return $app->redirect("/sub/del/$id");
        }
        if (isset($submit['cancel'])){
            return $app->redirect("/sub/list");
        }

        $form = SubscriptionForm::addForm($app, $sub);        
        $valid = $this->validateAddForm($form);
        if ($valid) {
            $repo = new SubscriptionRepo();
            $id = $repo->save($sub);
            return $this->infoAction($app, $request, $id);
        }
        $form = $ser->normalize($form);
        return $app['twig']->render($form['template'], ['data' => $form]);
    }

    public function deleteAction(Application $app, $id)
    {
        $repo = new SubscriptionRepo();
        $repo->delete($id);
        return $app->redirect("/sub/list");
    }
    
    public function infoAction(Application $app, Request $request, $id)
    {      
        $repo = new SubscriptionRepo();
        $sub = $repo->find($id);
        $ser = new Serializer();
        $form = SubscriptionForm::addForm($app, $sub);
        $form->removeSubmits();
        $form->addElement(SubscriptionForm::navigation());
        
        $norm = $ser->normalize($form);

        return $app['twig']->render('sub_add.twig', ['form' => $norm]);
    }    

    private function validateAddForm($form)
    {
        return true;
    }

}
