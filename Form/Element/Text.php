<?php
class Form_Element_Text extends Zend_Form_Element_Text  
{
    public function init()
    {
        $this->setAttribs(array(
            'class'=>'text'
            ));
    }
}
?>
