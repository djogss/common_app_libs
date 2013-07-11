<?php       
class App_Controller_Plugin_MenuNavigation extends Zend_Controller_Plugin_Abstract {
    
    /**
     * Called before an action is dispatched by Zend_Controller_Dispatcher.
     *
     * @param   Zend_Controller_Request_Abstract    $request    The request.
     *
     * @throws  Zend_Exception  If the request is not allowed and the front controller is configured
     *                          to throw exceptions.
     */
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        //Zend_Debug::dump($navigation);
        if(Zend_Registry::isRegistered('cache')) {
            $oCache = Zend_Registry::get('cache');
            $sCacheId = 'zend_navigation';
             
            if ( ! $oCache->test( $sCacheId ) ) {
                $data = new Zend_Config_Xml(CONFIGS_PATH . 'navigation.xml', APPLICATION_ENVIRONMENT);
                $nav = $data->navigation;
                $topNav = $data->navigation->home->pages->top_menu->pages;
                $adminNav = $nav->home->pages->admin->pages;
                $userNav = $nav->home->pages->users->pages;
                $oCache->save( $nav, $sCacheId );
            } else {
                $nav = $oCache->load( $sCacheId );
                $topNav = $nav->home->pages->top_menu->pages;
                $adminNav = $nav->home->pages->admin->pages;
                $userNav = $nav->home->pages->users->pages;
            }
        } else {
            $data = new Zend_Config_Xml(CONFIGS_PATH . 'navigation.xml', APPLICATION_ENVIRONMENT);
            $nav = $data->navigation;    
            $topNav = $data->navigation->home->pages->top_menu->pages;
            $adminNav = $nav->home->pages->admin->pages;
            $userNav = $nav->home->pages->users->pages;
            
        }     

        $navigation = new Zend_Navigation($nav);
        Zend_Registry::set('Zend_Navigation', $navigation);
        
        $navigationConfig = $this->_prepareDynamicMenu();
        $mainav = new Zend_Navigation($navigationConfig); 
        $this->_setActiveMenuID($mainav);
        Zend_Registry::set('Main_Navigation', $mainav);
        
        Zend_Registry::set('Admin_Navigation', new Zend_Navigation($adminNav));
        Zend_Registry::set('User_Navigation', new Zend_Navigation($userNav));
        Zend_Registry::set('Top_Navigation', new Zend_Navigation($topNav));
//        
        $this->_prepareDynamicLeftMenu();
        
        if(Zend_Registry::isRegistered('acl')) {
            $view = Zend_Layout::getMvcInstance()->getView();
            $role = App_Auth::getRole();
            $view->navigation()->menu()->setAcl(Zend_Registry::get('acl'));
            $view->navigation()->menu()->setRole(App_Auth::getRole());
        }
    }                                
    public function _prepareDynamicMenu($data=array())
    {
        $m = new city_Models_City();
        $MenuTitle = $m->getAllEnabledCities();  
        $m = new menu_Models_Menu();
        
        $subMenuTitle = $m->getAllEnabledMenu();
        $i =0;
        $results = array("nav" => array(
                "label" => "Home",
                "module" => "default",
                "controller" =>"index",
                "action" =>"index",
                "pages" => array()
                ));
        $counter = count($MenuTitle);
        foreach($MenuTitle as $v):
            $route = 'by-city';
            $module = 'object';
            $controller = 'objects';
            $action = 'get-info-by-city';
            $params = array('city_id'=>$v['id'],'title' => $v['city_name']);
            $SubMenuTitle[0] = 'About ';  
            $menuArray[$v['city_name']] = array('label'=>$v['city_name'].$v['id']); 
            $results['nav']['pages'][$v['city_name']]= array(
                                'label'     => $v['city_name'],
                                'module'    => $module,
                                'controller'=> $controller,
                                'action'    => $action,
                                'id'        => $v['id'],
                                'route'     => $route,
                                'params'    => $params,
                                'type'      => 'mvc',
                                'pages'     => array()
                                
                                
                                );
            if(!empty($subMenuTitle))                                
                $results = $this->_prepareDynamicSubMenu($subMenuTitle,$v,'dynamic',$results);
            if(!empty($subMenuTitle))                                
                $results = $this->_prepareStaticSubMenu($subMenuTitle,$v,'static',$results);
        endforeach; 
        return $results;
    }
    
    private function _prepareDynamicSubMenu($subMenuTitle,$mainMenu,$type,$results)
    {
        $view = Zend_Layout::getMvcInstance()->getView();
        $v = $mainMenu;
        $userInfo = App_Auth::getInstance()->getIdentity();
        foreach($subMenuTitle as $vv):
                if($v['id'] == $vv['city_id'] && $vv['type'] == $type)
                {
                    if($userInfo != null)                                                                              
                        $results['nav']['pages'][$v['city_name']]['pages'][$vv['menu_name']] = array(
                                            'label'     => $vv['name_after_loged_in'] != null ? $view->translate($vv['name_after_loged_in']): $vv['menu_name'],
                                            'module'    => 'object',
                                            'controller'=> 'objects',
                                            'action'    => 'get-info-by-city',
                                            'route'            => 'by-city-info',
                                            'params'    => array('city_id'  =>  $v['id'],
                                                                 'title'    =>  $v['city_name'],
                                                                 'subtitle' =>  $vv['menu_name']),
                                            );
                    else
                        $results['nav']['pages'][$v['city_name']]['pages'][$vv['menu_name']] = array(
                                            'label'     => $view->translate($vv['menu_name']),
                                            'module'    => 'object',
                                            'controller'=> 'objects',
                                            'action'    => 'get-info-by-city',
                                            'route'            => 'by-city-info',
                                            'params'    => array('city_id'  =>  $v['id'],
                                                                 'title'    =>  $v['city_name'],
                                                                 'subtitle' =>  $vv['menu_name']),
                                            );
                }
        endforeach;
        return $results;
    }
    private function _prepareStaticSubMenu($subMenuTitle,$mainMenu,$type,$results)
    {
        $view = Zend_Layout::getMvcInstance()->getView();
        $v = $mainMenu;
        $userInfo = App_Auth::getInstance()->getIdentity();
        foreach($subMenuTitle as $vv):
                if($vv['type'] == $type && !empty($vv['route']))
                {
                    if($userInfo != null)
                        $results['nav']['pages'][$v['city_name']]['pages'][$vv['menu_name']] = array(
                                        'label'     => $vv['name_after_loged_in'] != null ? $view->translate($vv['name_after_loged_in']): $vv['menu_name'],
                                        'route'            => $vv['route']);     
                    else
                        $results['nav']['pages'][$v['city_name']]['pages'][$vv['menu_name']] = array(
                                            'label'     => $view->translate($vv['menu_name']),
                                            'route'            => $vv['route']);
                }
        endforeach;
        return $results;
    }    
    private function _prepareDynamicLeftMenu()
    {
        $m = new default_Models_Menu();
        $leftmenu = new Zend_Navigation($m->getLeftMenu());
        Zend_Registry::set('leftmenu', $leftmenu);
    }
    
    private function _setActiveMenuID($mainav)
    {
        $ns = new Zend_Session_Namespace('menu');
        $ns->activeMainMenuID = null;
        $defaultID = null;
        $hasActive = false;
        foreach ($mainav as $page)
        {
            foreach ($page as $page)
            {
//                echo $page->label ." ". $page->id ." ". $page->isActive() ." | ";
                // storing first menu element id for later if active will not be found
                if(is_null($defaultID))
                    $defaultID = $page->id;
                if($page->isActive() == 1)
                {
                    $ns->activeMainMenuID = $page->id;
                    $hasActive = true;
                    $ns->ActiveName = $page->label;
                }
            }
        }
        if(isset($ns->ActiveName) && !$hasActive)
        {            
            foreach ($mainav as $page)
            {
                foreach ($page as $page)
                {
                    if($ns->ActiveName == $page->label)
                    {
                        $ns->activeMainMenuID = $page->id;
                    }
                }
            }
        }
        else
        {
            if(!isset($ns->ActiveName) && !$hasActive)
            {
                foreach ($mainav as $page)
                {
                    foreach ($page as $page)
                    {
                        $ns->activeMainMenuID = $page->id;
                        break;
                    }
                    break;
                }   
                
            }
        }
    }
}