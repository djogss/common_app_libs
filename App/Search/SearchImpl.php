<?php
//require_once 'App/Filter/Interface.php';

  class App_Search_SearchImpl extends App_Search_Interface
  {
      private $_filter    = null;
      private $_filterSQL = null;
      private $_model     = null;
      private $_needle    = null;
      
      public function __construct($filter = null)
      {
          if(is_null($filter))
            $this->_filter = new App_Filter_AdminFilterImpl();
          else $this->_filter = $filter;
      }
      
      private function _prepareSearch($options = array())
      {
          $this->_model = new service_Models_Contact();
          
          $this->_filterSQL = $this->_filter->setUpFilter($this->_model,$this->getNeedle());          
      }
      
      public function doSearch($options = array())
      {
          $this->_prepareSearch();
          return $this->_model->getAdapter()->fetchAll($this->_filterSQL);
      }
      
      public function setNeedle($needle)
      {
          $this->_needle = $needle;
      }
      
      public function getNeedle()
      {
          return $this->_needle;
      }
  }
?>
