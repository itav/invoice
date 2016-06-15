<?php

namespace App;

use Itav\Component\Form;

class CommonForm
{
    /**
     * 
     * @return Form\FieldSet
     */
    static public function navigation()
    {
        $fieldset = new Form\FieldSet();
        return $fieldset->setElements([self::cancel(), self::edit(), self::delete()]);
            
    }
      
    /**
     * 
     * @return Form\Button
     */    
    static public function cancel()
    {
        $cancel = new Form\Button();
        $cancel
            ->setLabel('Cancel')
            ->setType(Form\Button::TYPE_SUBMIT)
            ->setName('submit[cancel]');
        return $cancel;
    }
    /**
     * 
     * @return Form\Button
     */
    static public function edit()
    {
        $edit = new Form\Button();
        $edit
            ->setLabel('Edit')
            ->setType(Form\Button::TYPE_SUBMIT)
            ->setName('submit[edit]');
        return $edit;
    }
    /**
     * 
     * @return Form\Button
     */
    static public function add()
    {
        $add = new Form\Button();
        $add
            ->setLabel('Add')
            ->setType(Form\Button::TYPE_SUBMIT)
            ->setName('submit[add]');
        return $add;
    }    
    /**
     * 
     * @return Form\Button
     */
    static public function delete()
    {
        $delete = new Form\Button();
        $delete
            ->setLabel('Delete')
            ->setType(Form\Button::TYPE_SUBMIT)
            ->setName('submit[del]');
        return $delete;
    }    
    /**
     * 
     * @return Form\Button
     */
    static public function printOne()
    {
        $print = new Form\Button();
        $print
            ->setLabel('Print')
            ->setType(Form\Button::TYPE_SUBMIT)
            ->setName('submit[print]');
        return $print;
    }
        /**
     * 
     * @return Form\Button
     */
    static public function printAll()
    {
        $printAll = new Form\Button();
        $printAll
            ->setLabel('Print All')
            ->setType(Form\Button::TYPE_SUBMIT)
            ->setName('submit[print_all]');
        return $printAll;
    }
}
