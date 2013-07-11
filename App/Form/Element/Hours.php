<?php
class App_Form_Element_Hours extends Zend_Form_Element_Select
{
    public function __construct($name)
    {      
        $this->setLabel('H')
           ->addMultiOptions($this->_getHoursArray());
        parent::__construct($name);
    }
    
    private function _getHoursArray()
    {
        $hours = array();
        for($i = 0; $i <= 23; $i++)
            $hours[$i] = $i < 10 ?  "0".$i : $i;
        return $hours;
    }   
}
?>
