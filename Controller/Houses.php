<?php
/**
 * HouseController is the users controller for this application
 *
 */
class Controller_Houses extends Zend_Controller_Action
{
    public function indexRentAction()
    {
        $filter = new Zend_Session_Namespace('filter_store');
        isset($filter) ? $filter->filters_store = null : '';
        
        $this->_prepareFilterForms();

        $page = $this->_getParam('page');
        $this->view->page = $page;
        $model = new house_Models_House();
        $options[] = array('condition'=>'deleted','value'=>0,);
        $options[] = array('condition'=>'hidden','value'=>0, 'operator'=>'='); 
        $oCache = Zend_Registry::get('cache');
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($model->getAllDataSelect($options)));
        $paginator->setCache($oCache);
        $paginator->setCurrentPageNumber($page); 
        $paginator->setItemCountPerPage(Zend_Registry::get('config')->paginator->itemperpage);
        $houses = $paginator->getCurrentItems(); 
        $this->view->paginator = $paginator;
        if($paginator->getItemCount($houses) > 0)
            $this->view->results = $model->getParents($houses);
        else
            $this->view->results = null;
    }
    
    protected function _getParentsData($model,$arr,$p)
    {
        $oCache = Zend_Registry::get('cache');
        $oCacheId = 'houselists'.$p;
        if(!$oCache->test($oCacheId))
        {
            if(APPLICATION_ENVIRONMENT == 'development')
                App_Debug::fire('cacheing');
            $results = $model->getParents($arr);
            $oCache->save($results,$oCacheId);
        }
        else
        {
            if(APPLICATION_ENVIRONMENT == 'development')
                App_Debug::fire('from cache');
            $results = $oCache->load($oCacheId);
        }
        return $results;
    }
    
    protected function _prepareFilterForms()
    {
        $type = $this->_helper->form->create('Filters');
        $this->view->f_type = $type;
    }
    
    protected function _updateFiletypes(&$form)
    {
        $model_filetype = new house_Models_Filetype();
        $types = $model_filetype->getGroupedFileTypes();
        $support_filetypes = '';
        foreach($types as $value)
        {
            $t = explode(',',$value['GROUP_CONCAT(name)']);
            foreach($t as $v)
            $support_filetypes .= '*.'.$v.';';
        }
        $subform2 = $form->getSubForm('formhouseImages');
       
/*        Ikelimo lange nustatyo kokio tipo failus galima kelti */
        $file = $subform2->getElement('file');
        $file->setValue($support_filetypes);
    }
    
    public function houselistAction()
    {
        $this->view->title = $this->view->translate('House list');
        $model = new house_Models_House();
        $this->view->user_data = App_Auth::getInstance()->getIdentity();
        $options[] = array('condition'=>'deleted','value'=>0); 
//        $options[] = array('condition'=>'hidden','value'=>0); 
        $oCache = Zend_Registry::get('cache');
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($model->getAllDataSelect()));
        $paginator->setCache($oCache);
        $paginator->setCurrentPageNumber($this->_getParam('page')); 
        $paginator->setItemCountPerPage(Zend_Registry::get('config')->paginator->itemperpageadmin);
        $houses = $paginator->getCurrentItems(); 
        $this->view->paginator = $paginator;
        if($paginator->getItemCount($houses) > 0)
            $this->view->results = $model->getParents($houses);
        else
            $this->view->results = null;        
    }
    
    /**
    * Istraukia is duomenu bazes visas namo nuotraukas
    * ir issaugo i sesija kelia iki nuotrauku (path ir url) formatu
    * 
    * @param int $id - house ID
    * @return Serialize data  - house images
    */
    protected function _getHouseImages($id,$isPost)
    {
        $m = new house_Models_Image();
            $data = $m->getHouseImages($id);
            if(empty($data) && !$isPost)
                $this->_saveTmpFilename();
            else
            $m->setHouseImagesInfo($data);
        return Zend_Serializer::serialize($data);
        unset($m); 
    }
    
    protected function _getFileTypes()
    {
        $mft = new house_Models_Filetype();
        return $mft->getSupportedFileTypes();
    }
    
    /**
    * Uzpildo reikiamais duomenimis namo redagavimo forma
    * 
    * @param mixed $data
    */
    protected function _formUpdateManagment(&$data,$allHouseData,$id)
    {
        $m = new house_Models_Properties();
            $m->updateFormHouseProperties($data,$allHouseData);
            unset($m);
        $m = new house_Models_Price();
            $m->updateFormHousePrice($data);
            unset($m);
        $m = new house_Models_HousesEntertainment();
            $m->updateFormHouseEntertainment($data);
            unset($m);
        $m = new house_Models_HousesPlan();
            $m->updateFormHouseLayout($data);
            unset($m);
    }
    
    /**
    * put your comment there...
    * 
    * @param mixed $data
    * @param mixed $allData
    */
    private function _updateHouseRentInclude(&$data,$allData)
    {
        $data[0]['extra'] = $allData['RentInclude']['extra'];
        $data[0]['service'] = $allData['RentInclude']['service'];
        $data[0]['stuffofrent'] = $allData['RentInclude']['stuff_include'];
    }
    
    public function disableHouseAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $mh = new house_Models_House();
        $id = $this->_request->getParam('id',null);
        $Data = array('hidden'=>1);
        $mh->update($Data, "id=$id");
        $mh->clearCache();
        return $this->_helper->redirector('houselist');
    }
    public function enableHouseAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $mh = new house_Models_House();
        $id = $this->_request->getParam('id',null);
        $Data = array('hidden'=>0);
        $mh->update($Data, "id=$id");
        $mh->clearCache();
        return $this->_helper->redirector('houselist');
        
    }
    private function _updateHouseProperties(&$data)
    {
        
    }
    
    private function _updateHousePrice(&$data)
    {
        $mp = new house_Models_Price();
        $results = $mp->findXByY('all','house_id',$data[0]['id']);
        $i = 1;
        foreach($results as $value)
        {
            $data[0]['date_from']['date_from_'.$i] = $value['date_from'];
            $i++;
        }
//        Zend_Debug::dump($data);
    }
    private function _updateHousePlan(&$data)
    {
        $me = new house_Models_HousesPlan();
        $house_plan = $me->findXByY('all','house_houses_id',$data[0]['id']);
        $results = $me->getParents($house_plan);  
        $data[0]['house_plan_id'] = array();   
        foreach($results as $value)
        {
            $data[0]['house_plan_id'][] = $value['Plan']['id'];
        }
        
    }
    private function _updateHouseEntertainment(&$data)
    {
        $me = new house_Models_HousesEntertainment();
        $house_ent = $me->findXByY('all','house_houses_id',$data[0]['id']);
        $results = $me->getParents($house_ent); 
        $data[0]['house_entertainment_id'] = array();
        foreach($results as $value)
            $data[0]['house_entertainment_id'][] = $value['Entertainment']['id'];
    }
    public function viewAction()
    {
        $id = $this->_request->getParam('id', null);
        $contactForm = $this->getHelper('Form')->create('UserAsk');
        
        $mh = new house_Models_House();
        $me = new house_Models_HousesEntertainment(); 
        $mp = new house_Models_HousesPlan();
        $mi = new house_Models_Image();
        
/*        truputeli hardcodo :) */
        $oCache = Zend_Registry::get('cache');
        $oCacheId = 'houseView_'.$id;
        $oCacheEnt = 'houseView_'.$id.'_ent';
        $oCachePlan = 'houseView_'.$id.'_plan';
        $oCacheImg = 'houseView_'.$id.'_img';
        $oCachePrice = 'houseView_'.$id.'_price';
        $oCacheUser = 'houseView_'.$id.'_user';
        $oCacheRelatedata = 'houseView_'.$id.'_related';
        if(!$oCache->test($oCacheId))
        {
            $mh->devFirebugMessage('View is cached');
            $result = $mh->find($id);
            $results = $result->toArray();
            $ent = $me->getParents($me->findXByY('all','house_houses_id',$result[0]['id']));
            $plan = $mp->getParents($mp->findXByY('all','house_houses_id',$result[0]['id']));
            $img = $mi->findXByY('all','house_id',$result[0]['id']);
            $price = $result->current()->findDependentRowset('house_Models_Price')->toArray();
            $userinfo = $result->current()->findParentRow('user_Models_User')->toArray();
            $related_data = $mh->getParents($results);
            $oCache->save($ent,$oCacheEnt);
            $oCache->save($plan,$oCachePlan);
            $oCache->save($img,$oCacheImg);
            $oCache->save($price,$oCachePrice);
            $oCache->save($userinfo,$oCacheUser);
            $oCache->save($related_data,$oCacheRelatedata);
            $oCache->save($result,$oCacheId);
            
        }
        else
        {
            $mh->devFirebugMessage('House info from cache');
            $result = $oCache->load($oCacheId);
            $results = $result->toArray();
            $ent = $oCache->load($oCacheEnt);
            $plan = $oCache->load($oCachePlan);
            $img = $oCache->load($oCacheImg);
            $price = $oCache->load($oCachePrice);
            $userinfo = $oCache->load($oCacheUser);
            $related_data = $oCache->load($oCacheRelatedata);
            $results = $result->toArray();
        }

        $this->view->results = $results[0];
        $this->view->userinfo = $userinfo;
        $this->view->ent = $ent;
        $this->view->plan = $plan;
        $this->view->img = $img;
        foreach($img as $value)
            $value['main_photo'] == 1 ? $this->view->main_img = $value : '';
        # jeigu nebuvo parinkta main photo keliant nuotraukas
        is_null($this->view->main_img)?$this->view->main_img = $images[0]:'';
        $this->view->prices = $price;
//        Zend_Debug::dump($related_data);
        if(isset($related_data[$id]['RentInclude']))
            $this->view->rentinclude = $related_data[$id]['RentInclude'];
        if(isset($related_data[$id]['Properties']))
            $this->view->properties = $related_data[$id]['Properties'];
        $this->view->contactForm = $contactForm;
    }
    
    
    public function viewSellAction()
    {
        $this->viewAction();
        $this->_helper->viewRenderer('view-sell');
    }
    
    public function askusersAction()
    {
        $this->_helper->layout->disableLayout();
        $request = $this->getRequest();
        $id = $this->_request->getParam('id');
        $form = $this->getHelper('Form')->create('UserAsk');
        if($request->isPost())
        {
            if($form->isValid($request->getPost()))
            {
                $formData = $form->getValues();
                $id = $formData['house_id'];
                $houseData = $this->_getHouseInfo($id);
                $formData['housename'] = $houseData[$id]['House']['housename'];
                $mail = new App_Mail('UTF8');
                $mail->setTemplate('askusers')
                     ->setTemplateArgs($formData)
                     ->setBodyText($formData['message'])
                     ->addTo($houseData[$id]['User']['email'])
                     ->setFrom($formData['email_from'])
                     ->setSubject($this->view->translate('House %s info request. Date - ',$houseData[$id]['House']['housename']).date('Y-m-d'));
                if($mail->send())
                {
                    $this->_helper->viewRenderer('mail-send-success');
                }
                else
                    $this->_helper->flashMessenger($this->view->translate('something frong'));
                
            }
                
        }
        $form->setDefaults(array('house_id'=>$id));
        $this->view->form = $form;
    }
    
    protected function _getHouseInfo($id)
    {
        
        if(Zend_Registry::get('config')->cache->enabled == 1)
        {
            $oCache = Zend_Registry::get('cache');
            $oCacheRelatedata = 'houseView_'.$id.'_related';
            if(!$oCache->test($oCacheRelatedata))
            {
            	$mh = new house_Models_House();
            	$houseData = $mh->getParents($mh->find($id)->toArray());
            	$oCache->save($houseData,$oCacheRelatedata);
            }
            else
            {
            	$houseData = $oCache->load($oCacheRelatedata);
            }
        }
        else
        {
            $mh = new house_Models_House();
            $houseData = $mh->getParents($mh->find($id)->toArray());
        }
        return $houseData;
    }
    
    public function view2Action()
    {
        $this->viewAction(); 
    }
    /**
    * This function deletes a directory with all of it's content.
    * @param string - dir path
    * @param boolen - Second parameter is boolean to instruct the function 
    *                 if it should remove the directory or only the content
    */
    protected function _rmdir_r ( $dir, $DeleteMe = TRUE )
    {
        if ( ! $dh = @opendir ( $dir ) ) return;
        while ( false !== ( $obj = readdir ( $dh ) ) )
        {
            if ( $obj == '.' || $obj == '..') continue;
            if ( ! @unlink ( $dir . '/' . $obj ) ) $this->_rmdir_r ( $dir . '/' . $obj, true );
        }
        
        closedir ( $dh );
        if ( $DeleteMe )
        {
            @rmdir ( $dir );
        }
    }
    public function imageAction()
    {
//     $thumb = new App_Thumb_GdThumb('C:\Documents and Settings\Mantas\Desktop\test data\pro 1\test\3815635634_d940968be2_b.jpg.jpg');
//     $thumb->resize(200,200);
//     $thumb->save('C:\xampp\htdocs\zfprojects\domains\lacasa\public\houseimages\2009-04-27\e2a62976f218e361bdb142fd7292cb0e2bef593c\thumb_bug.png');
//    $options = array('resizeUp' => false, 'jpegQuality' => 60);
//    $img_src = 'C:\Documents and Settings\Mantas\Desktop\test data\pro\pro 1\test\3678135417_697ce88478_b.jpg';
//    $img_src = 'C:\Documents and Settings\Mantas\Desktop\test data\pro\pro 2\test\3829840250_8e8249145c_b.jpg';
//    
//     $thumb = App_Thumb_PhpThumbFactory::create($img_src,$options);
//     $thumb->save('C:\xampp\htdocs\zfprojects\domains\immo\public\houseimages\2009-04-27\f76bd90e3e0c7cf7e25812cb7d952ea1380ce202/11838_lowq.JPG');
//$ws = 'C:\xampp\htdocs\zfprojects\domains\immo\public\themes\immo2\images\design\empurialive.png';
//$thumb->createWatermark($ws);
//$thumb->createWatermark($mark_image, $position, $padding);
//$thumb->resize(0,800);
//$thumb->save('C:\Documents and Settings\Mantas\Desktop\test data\pro\pro 2\test\vert8000.jpg');
//$a = new ZendX_JQuery_View_Helper_AccordionContainer();
//$a->setView($this->view);
//$a->addPane("a", "a1saf", "blabla", array());
//$a->addPane("a", "a2asdf", "blabla", array());
//$a->addPane("a", "a3adf", "blabla", array());
//$this->view->assign("tabs", $a->accordionContainer("a", array(), array()));
//    $opt = $this->getOption('app');
//    Zend_Debug::dump($opt);
    }
}
