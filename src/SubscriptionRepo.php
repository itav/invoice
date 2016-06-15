<?php

namespace App;

use Itav\Component\Serializer\Serializer;

class SubscriptionRepo
{

    private $file = __DIR__ . '/storage/subscription.json';
    private $serializer;

    public function __construct()
    {
        $this->serializer = new Serializer();
    }

    /**
     * 
     * @param \App\Subscription $subscription
     * @return string | bool
     */
    public function save(Subscription $subscription)
    {
        $rows = json_decode(file_get_contents($this->file), true);
        $data = $this->serializer->normalize($subscription);
        $foundKey = false;
        foreach($rows as $key => $item){
            if($item['id'] == $subscription->getId()){
                $foundKey = $key;
                break;
            }
        }
        if(false !== $foundKey){
            unset($rows[$foundKey]);
        }
        $rows[] = $data;
        
        file_put_contents($this->file, json_encode($rows));
        return $subscription->getId();        
    }

    /**
     * 
     * @param int $id
     * @return \App\Subscription
     */
    public function find($id)
    {
        $rows = json_decode(file_get_contents($this->file), true);
        foreach($rows as $item){
            if($item['id'] == $id){
                return $this->serializer->unserialize($item, Subscription::class);
            }
        } 
        return null;
    }
    /**
     * 
     * @return Subscription[]
     */
    public function findAll()
    {
        $rows = json_decode(file_get_contents($this->file), true);
        $results = [];
        foreach($rows as $item){
            $results[] =  $this->serializer->unserialize($item, Subscription::class);
        } 
        return $results;
    }   
    
    /**
     * 
     * @param int $id
     * @return \App\Subscription
     */
    public function delete($id)
    {
        $rows = json_decode(file_get_contents($this->file), true);
        $foundKey = false;
        foreach($rows as $key => $item){
            if($item['id'] == $id){
                $foundKey = $key;
                break;
            }
        }
        if(false !== $foundKey){
            unset($rows[$foundKey]);
        }else{
            return false;
        }        
        file_put_contents($this->file, json_encode($rows));
        return true;
    }    
}