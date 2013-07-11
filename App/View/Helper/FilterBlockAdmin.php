<?php
/**
 * ProfileLink helper
 *
 * Call as $this->profileLink() in your layout script
 */
class App_View_Helper_FilterBlockAdmin
{
    public $view;

    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }

    public function filterBlockAdmin()
    {   
        return $this->renderSearchForm();
    }
    
    function renderSearchForm(){
        $form  = new default_forms_FiltersAdmin();
        $ns = new Zend_Session_Namespace('filterDataAdmin');
        $options = $ns->data;
        if(!empty($options))
            $form->populate($options); 
        return $form->render($this->view);
        
    }
}