<?php
class Intraface_modules_shop_Shop extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('shop');
        $this->hasColumn('name',         'string',  255);
        $this->hasColumn('description',  'string',  65555);
        $this->hasColumn('identifier',   'string',  255);
        $this->hasColumn('show_online',  'integer',  1);
        $this->hasColumn('confirmation', 'string',  65555);
        $this->hasColumn('receipt',      'string',  65555);
        // $this->hasColumn('intranet_id',  'string',  65555);

    }
    
    public function setUp()
    {
        $this->loadTemplate('Intraface_Doctrine_Template_Intranet');
    }

    
    function getId()
    {
        return $this->id;
    }
    
    public function getName() 
    {
        return $this->name;
    }
    
    function getConfirmationText()
    {
        return $this->confirmation;
    }
}