<?php
  class App_Form_Element_Cities extends Zend_Form_Element_Select
  {
    public function __construct($name,$opt=array())
    {
        $city_array = $this->_getCities($opt);
        
        $this->setLabel("City")
                ->setMultiOptions($city_array);
        parent::__construct($name,$opt);
    }    
     /**
      *
      */
     private function _getCities($options)
     {
         $m = new city_Models_City();
         if($options)
            return $m->getAllEnabledPairs('city_name');
         else
            return $m->getAllPairs('city_name');
     }        
  }
?>
