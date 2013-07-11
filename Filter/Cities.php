<?php 
class Filter_Cities extends Filter_Filter 
{
	private static $_instance = null;
	protected  $_filters;
	protected $_searchByName;
	protected static $_name = "Ccity";

	private function __construct()
	{
		$this->_filters = array();
		$this->_name = 'Ccity';
		$this->_searchByName = 'city_id';
	}
	public static function getInstance()
	{
		$fs = new Zend_Session_Namespace('filter_store');
		$obj = $fs->filters_store;
		$filter = null;
		if(is_object($obj))
 	    	$filter = $obj->getFilters();
 	    if(isset($filter[self::$_name]) )
			return $filter[self::$_name];
		elseif(null === self::$_instance)
            self::$_instance = new self();
            
        return self::$_instance;		
	}
	public function setFilter($params)          
	{
		$cm = new city_Models_City();
		$name = $cm->findXByY('name','id',$params[(key($params))]);
		$this->_filters[$this->_searchByName][$params[key($params)]] = $name['name'];
	}
	
}
?>
