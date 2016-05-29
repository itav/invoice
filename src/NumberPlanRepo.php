<?php

namespace App;

class NumberPlanRepo
{
    /**
     * 
     * @param int $id
     * @return NumberPlan 
     */
    public function find($id)
    {
        $result = null;
        $plans = $this->findAll();
        foreach ($plans as $plan){
            if($id == $plan->getId()){
                $result = $plan;
                break;
            }
        }
        return $result;
    }
    
    /**
     * 
     * @return NumberPlan
     */
    public function findDefault()
    {
        $result = null;
        $plans = $this->findAll();
        foreach ($plans as $plan){
            if($plan->isDefault()){
                $result = $plan;
                break;
            }
        }
        return $result;        
    }
    /**
     * @return NumberPlan[]
     */
    public function findAll()
    {
        $numberPlans = [];
        $numberPlan = new NumberPlan();
        $numberPlan
                ->setId(1)
                ->setTemplate('%N/%m/SMTS/%Y');
        $numberPlans[] = $numberPlan;
        
        $numberPlan = new NumberPlan();
        $numberPlan
                ->setId(2)
                ->setTemplate('%N/%m/SWRS/%Y');
        $numberPlans[] = $numberPlan;
        
        $numberPlan = new NumberPlan();
        $numberPlan
                ->setId(3)
                ->setTemplate('%N/%m/NET/%Y')
                ->setDafault(true);
        $numberPlans[] = $numberPlan;
        return $numberPlans;
    }
}

