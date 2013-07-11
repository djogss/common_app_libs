<?php
  class App_Form_Element_SeasonTypes extends Zend_Form_Element_Select
  {
    public function __construct($name,$opt=array())
    {
        $arr = $this->_getSeasonType($opt);
        
        $this->setMultiOptions($arr);
        parent::__construct($name,$opt);
    }    
     /**
      *
      */
     private function _getSeasonType($options)
     {
         $m = new price_Models_Season();
         $data = null;
         if($options)
            $data = $m->getAllEnabledPairs('season_name');
         else
            $data = $m->getAllPairs('season_name');
         return $this->translateDropBox($data);
     }  
     
    private function translateDropBox($data){
        $translator = $this->getTranslator();
        foreach($data as &$value)
        $value = $translator->translate($value);
        return $data;
    }      
  }
?>
