<?php
  class App_Form_Element_Price extends Zend_Form_Element_Select
  {
    public function __construct($name,$opt=array())
    {
        $hrs = $this->_getPrices($opt);        
        $this->addMultiOptions($hrs)
           ->setRegisterInArrayValidator(false)
           ->setRequired(true);        
        parent::__construct($name,$opt);
    }    
     /**
      *
      */
      private function _getPrices()
      {
          $view = Zend_Layout::getMvcInstance()->getView();          
          $c = 8;
          $result = array($view->translate('Price range'));
          $vfrom = 0;
          $vto   = 50;
          for($i = 0; $i < $c; $i++)
          {                          
              if($i == $c-1) $vto = $view->translate('and more'); 
              $sep = ' - ';
              if($vfrom == 0)
              {
                $vfrom = $view->translate('to'); 
                $sep = ' ';
                 $result['to_'.$vto] = $vfrom . $sep . $vto;
              }
              else
                $result[$vfrom.'_'.$vto] = $vfrom . $sep . $vto;
                
              $vfrom = $vto;
              $vto += $i >= 1 ? 100 : 50;
              
          }
          return $result;
      }            
  }
?>
