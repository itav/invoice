<?php

namespace App;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Itav\Component\Serializer\Serializer;

class SubscriptionController
{
    public function listAction()
    {
        $repo = new SubscriptionRepo();
        $subs = $repo->findAll();
        var_dump($subs);
        return '';
    }


    public function addAction(Application $app, $id = null){
        $sub = new Subscription();
        if($id){
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
        $ser->unserialize($request->get('sub'), Subscription::class, $sub);
        $form = SubscriptionForm::addForm($app, $sub);

        $valid = $this->validateAddForm($form);
        if($valid){
            $repo = new SubscriptionRepo();
            $id = $repo->save($sub);
            var_dump($id);
            return ''; //return rendered list
        }
        $form = $ser->normalize($form);
        return $app['twig']->render($form['template'], ['data' => $form]);
    }
    
    private function validateAddForm($form)
    {
        return true;
    }
}