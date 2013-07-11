<?php
class Form_SubForm extends Zend_Form_SubForm
{
    public function  __construct()
    {
        parent::__construct();    
        $this->removeDecorator('Fieldset');
        $this->setDecorators(array('FormElements'));
    }
    protected function setCustomeDecorator($class)          
    {
        return array(
            'ViewHelper',
            'Label',
            'Description',
            'Errors',
            array(array('row'=>'HtmlTag'),array('tag'=>'div', 'class' => "$class"))
        );
     }   
}
?>
