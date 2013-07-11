<?php
  class App_Form_Element_ServiceType extends Zend_Form_Element_Select
    {
        public function isValid($value, $context = null)
        {
            $this->setValue($value);
            if ($this->isRequired() && $value == 0)
            {
                $this->addError('Value is required');
                return false;
            }           
            return parent::isValid($value, $context);
        }
   }
?>
