<?php 
class Filter_AreaTotalMax extends Filter_Filter 
{
	private static $_instance = null;
	protected  $_filters;
	protected $_searchByName;
	protected static $_name = "Ctat"; //total area to

	private function __construct()
	{
		$this->_filters = array();
		$this->_name = 'Ctat';
		$this->_searchByName = 'total_area_to';
	}
	public static function getInstance()
	{
		$fs = new Zend_Session_Namespace('filter_store');
		$obj = $fs->filters_store;
		$filter = null;
		if(is_object($obj))
 	    	$filter = $obj->getFilters();
        self::$_instance = new self(); //problemos su remove'inimu filtru (siaip viskas veikai)
            
        return self::$_instance;		
	}
	public function setFilter($params)          
	{
        $view = new Zend_View();
		$this->_filters[$this->_searchByName][$params[key($params)]] = $view->translate('total area <').' '.number_format($params[key($params)],'0',',','.').' m2';
	}
	
}
?>
