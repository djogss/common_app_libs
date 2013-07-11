<?php
/**
 * ProfileLink helper
 *
 * Call as $this->profileLink() in your layout script
 */
class App_View_Helper_FilterBlock
{
    public $view;

    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }

    public function filterBlock()
    {
        return $this->renderSearchForm();
    }
    
    function renderSearchForm(){
        $form  = new default_forms_Filters();
        $ns = new Zend_Session_Namespace('filterData');
        $options = $ns->data;
        if(!empty($options))
            $form->populate($options); 
        return $form->render($this->view);
        
    }
}