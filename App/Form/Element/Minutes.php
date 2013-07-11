<?php
class App_Form_Element_Minutes extends Zend_Form_Element_Select
{
    public function __construct($name)
    {      
        $this->setLabel('Minutes')
           ->addMultiOptions($this->_getMinutesArray());
        parent::__construct($name);
    }
    
    private function _getMinutesArray()
    {
        $hours = array();
        for($i = 0; $i <= 59; $i++)
            $hours[$i] = $i < 10 ?  "0".$i : $i;
        return $hours;
    }   
}
?>
