<?php

class App_Controller_Action extends Zend_Controller_Action
{
    /**
     * Get the session namespace we're using
     *
     * @return Zend_Session_Namespace
     */
    public function getSessionNamespace()
    {
        if (null === $this->_session) {
            $this->_session =
                new Zend_Session_Namespace($this->_namespace);
        }

        return $this->_session;
    }
     /**
     * Get a list of forms already stored in the session
     *
     * @return array
     */
    public function getStoredForms()
    {
        $stored = array();
        foreach ($this->getSessionNamespace() as $key => $value) {
            $stored[] = $key;
        }

        return $stored;
    }
    /**
     * Get list of all subforms available
     *
     * @return array
     */
    public function getPotentialForms()
    {
        return array_keys($this->getForm()->getSubForms());
    }
    /**
     * What sub form was submitted?
     *
     * @return false|Zend_Form_SubForm
     */
    public function getCurrentSubForm()
    {
        $request = $this->getRequest();
        if (!$request->isPost()) {
            return false;
        }

        foreach ($this->getPotentialForms() as $name) {
            if ($data = $request->getPost($name, false)) {
                if (is_array($data)) {
                    return $this->getForm()->getSubForm($name);
                    break;
                }
            }
        }

        return false;
    }
    /**
     * Get the next sub form to display
     *
     * @return Zend_Form_SubForm|false
     */
    public function getNextSubForm()
    {
        $storedForms    = $this->getStoredForms();
        $potentialForms = $this->getPotentialForms();

        foreach ($potentialForms as $name) {
            if (!in_array($name, $storedForms)) {
                return $this->getForm()->getSubForm($name);
            }
        }

        return false;
    }
     /**
     * Is the sub form valid?
     *
     * @param  Zend_Form_SubForm $subForm
     * @param  array $data
     * @return bool
     */
    public function subFormIsValid(Zend_Form_SubForm $subForm,
                                   array $data)
    {
        $name = $subForm->getName();
        if ($subForm->isValid($data)) {
            $this->getSessionNamespace()->$name = $subForm->getValues();
            return true;
        }

        return false;
    }

    /**
     * Is the full form valid?
     *
     * @return bool
     */
    public function formIsValid()
    {
        $data = array();
        foreach ($this->getSessionNamespace() as $key => $info) {
            $data[$key] = $info;
        }

        return $this->getForm()->isValid($data);
    }

    public function disableAction()
    {
        $service_id = $this->_getParam('service_id');
        $obj = is_null($service_id) ? $this->_model :  object_Models_Factory::getAppropriateObject($service_id);
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $id = $this->_request->getParam('id',null);
        $Data = array('disabled'=>1);
        $obj->update($Data, "id=$id");
        if(is_null($service_id))
            return $this->_helper->redirector("index",$obj->getControllerName());
        else
            $this->_helper->redirector->gotoUrl($_SERVER['HTTP_REFERER']);
//            return $this->_helper->redirector("index",$obj->getControllerName(),'object',array('id'=>$service_id));        
            
    }
    public function enableAction()
    {
        $service_id = $this->_getParam('service_id');
        $obj = is_null($service_id) ? $this->_model : object_Models_Factory::getAppropriateObject($service_id);
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $id = $this->_request->getParam('id',null);
        $Data = array('disabled'=>0);
        $obj->update($Data, "id=$id");
        if(is_null($service_id))
            return $this->_helper->redirector("index",$obj->getControllerName());
        else
            $this->_helper->redirector->gotoUrl($_SERVER['HTTP_REFERER']);
//            return $this->_helper->redirector("index",$obj->getControllerName(),'object',array('id'=>$service_id));
    }
    
    protected function _prepareDefaultData()
    {
        $m = new object_Models_Event();
        $d = date("Y-m-d");
        $events = $m->getAllEventsByDatesRange();
        $pe = $m->getProximateEvents($events);
        $this->view->eData = $pe;
        
        $this->view->cData = Zend_Json::encode($events);
        $m = new content_Models_Pages();
        $this->view->nData = $m->getNews();
        $w = new App_Weather_Weather();    
        $this->view->wData = $w->getWeather();
    } 
    
    protected function translateDynamicData($data, $target = "name"){
        $newData = $data;
        foreach($data as $key => $value)
        $newData[$key][$target] = $this->view->translate($value[$target]);
        return $newData;
      }
}
