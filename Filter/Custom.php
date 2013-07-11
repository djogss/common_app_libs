<?php 
class Filter_Custom 
{
	private static $_instance = null;
	private  $_filters;
	private function __construct()
	{
		$this->_filters = array();	
	}
	
	public static function getInstance()
	{
		 if (null === self::$_instance) {
            self::$_instance = new Filter_Custom();
        }
        return self::$_instance;		
	}
    
    /**
    * Get filters array
    * 
    */
	public function getFilters()
	{             
		return $this->_filters;
	}
    
    /**
     * Return filter instance
     * @param  array of filters
     * @return instance particulare filtere
     */
	private function _getRightFilterInstance($filters)
	{
		$type = null;
		isset($filters['type']) ? $type = $filters['type'] : $type = key($filters);
		switch($type)
		{
/*            House price from filter*/
			case "Cprice_from":
                return Filter_PriceMin::getInstance();
/*            House price to*/
            case "Cprice_to":
				return Filter_PriceMax::getInstance();
/*            House type to*/
			case "Ctype":
                return Filter_HouseType::getInstance();
/*            Living area from */
            case "Carea_from":
				return Filter_AreaMin::getInstance();
/*            Living area to*/
            case "Carea_to":
                return Filter_AreaMax::getInstance();
/*            Total area from*/
            case "Ctaf":
                return Filter_AreaTotalMin::getInstance();
/*            Total area to*/
            case "Ctat":
                return Filter_AreaTotalMax::getInstance();
            case "Ccity":
                return Filter_Cities::getInstance();
            case "Stype":
                return Filter_SeasonTypes::getFilterObj();
			default:
				return null;
		}
	}
	
	/* Sukuriamas naujo filtro objektas arba pridedamas naujas filtravimo kriterijus prie esamo objekto*/
	public function setFilters($filters)
	{
		$instance = $this->_getRightFilterInstance($filters);
		
		if($instance != null)
		{
			$instance->setFilter($filters);
			$filter = $instance->getFilterObj();
			$this->_filters[key($filters)] = $filter;
		}
	}
	public function removeFilters($filters)
	{
		$type = null;
		isset($filters['type']) ? $type = $filters['type'] : $type = key($filters);
		$instance = $this->_getRightFilterInstance($filters);
		if($instance != null)
		{
			$instance->removeFilter($filters);
		}
        if(!is_null($instance))
        {
        	$filter = $instance->getFilter();
		    if(empty($filter[key($filter)]))
			    unset($this->_filters[$type]);
        }
	}
	
}
?>