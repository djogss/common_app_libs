<?php
  class App_Form_Element_Hr extends Zend_Form_Element_Select
  {
    public function __construct($name,$opt=array())
    {
        $hrs = $this->_getHrs($opt);        
        $this->setLabel('Choose health-resort')
           ->addMultiOptions($hrs)
           ->setRegisterInArrayValidator(false)
           ->setRequired(true);        
        parent::__construct($name,$opt);
    }    
     /**
      *
      */
      private function _getHrs($cities = null)
      {
          $m = new place_Models_Healthresort();
          if($cities != null)
          {
              $options = array('disabled'=>0,'city_id' => key($cities));
              return $m->getXModelPairs(false,false,'hr_name',$options);
          }
          else
            return $m->getAllEnabledPairs('hr_name');          
      }       
  }
?>
