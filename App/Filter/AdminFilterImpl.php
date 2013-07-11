<?php
class App_Filter_AdminFilterImpl extends App_Filter_Interface
{
    private $_model  = null;
    private $_select = null;
    private $_modelName = null;
    
    public function setUpFilter($model,$needle = null)
    {
        unset($needle["Search"]);
        $this->_model = $model;
        $select       = null;
        if(is_null($this->_modelName))
            $this->_modelName = $model->getDBTableName();
            
        if(is_null($this->_select))
                $this->_select = $model->select($this->_modelName,'*');
                
                
        $this->_select->setIntegrityCheck(false);
        $this->_select->joinRight('object_objects','object_objects.service_contact_id ='. $this->_modelName.'.id',array('*'))
               ->joinLeft ('service_types' ,'object_objects.service_type_id=service_types.id',array('service_services_id','ServiceTypeName'=>'name'))
               ->joinLeft ('user_users'    ,'user_users.id=object_objects.user_id',array('UserName'=>'name'))
               ->joinLeft ('object_titles' ,'object_titles.object_id=object_objects.id',array('Titles'=>'title'));
       
        if(!is_null($needle))
        {
            if($needle["filter_pID"] != 0)
                $this->_select = $this->_select->where("service_contacts.place_id =". $needle["filter_pID"]);
            
            if($needle["filter_sID"] != 0)
                $this->_select = $this->_select->where("service_types.service_services_id =". $needle["filter_sID"]);
            
            if($needle["filter_stID"] != 0)
                $this->_select = $this->_select->where("service_types.id =". $needle["filter_stID"]);
            
//            if($needle["filter_rtID"] != 0)
//                $this->_select = $this->_select->where();
//            
//            if($needle["filter_prID"] != 0)
//                $this->_select = $this->_select->where();
//                
//            if($needle["filter_cID"] != 0)
//                $this->_select = $this->_select->where();
            if(isset($needle["searchBy"]) && $needle["searchBy"] != "")
                $this->_select = $this->_select->where($this->_modelName.".email like \"%" . $needle["searchBy"]
                                                    ."%\" OR object_titles.title like \"%" . $needle["searchBy"] . "%\"");
                                         
        }
        $this->_select = $this->_select->group('object_objects.id');
        
        return $this->_select;
    }
    
    public function setUpWhereClouse($options = array())
    {
        
    }
    
    private function setUpSelect($model)
    {
//        if(is)
//        $this->_select = $this->_model->select($this->)
    }
    
    
}
?>
